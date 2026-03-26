import './bootstrap'
import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import ThemeToggle from './components/ThemeToggle.vue'
import axios from 'axios'
import Vue3Toastify from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import { useDarkMode } from './composables/useDarkMode'

// Dark mode
const { colorScheme, toggleDarkMode } = useDarkMode()

// Axios config
axios.defaults.baseURL = 'http://autoproj.test'
axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// Router
const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'Home',
      component: () => import('./pages/Hero.vue'),
    },
    {
      path: '/builder',
      name: 'Builder',
      component: () => import('./pages/CarBuilder.vue'),
    },
    {
      path: '/register',
      name: 'Register',
      component: () => import('./pages/Register.vue'),
    },
    {
      path: '/login',
      name: 'Login',
      component: () => import('./pages/Login.vue'),
    },
    {
      path: '/parts/:carId/:engineId',
      name: 'PartSelector',
      component: () => import('./pages/PartSelector.vue'),
      props: true,
    },
    {
      path: '/dashboard',
      name: 'Dashboard',
      component: () => import('./pages/Dashboard.vue'),
      meta: { requiresAuth: true },
    },
    {
      path: '/leaderboards',
      name: 'Leaderboards',
      component: () => import('./pages/Leaderboards.vue'),
      meta: { requiresAuth: true },
    },
  ],
})

// Auth guard
router.beforeEach((to, from, next) => {
  const user = JSON.parse(localStorage.getItem('user') || 'null')
  const isAuthenticated = !!user

  if (to.meta.requiresAuth && !isAuthenticated) {
    next({ name: 'Login', query: { redirect: to.fullPath } })
  } else if ((to.name === 'Login' || to.name === 'Register') && isAuthenticated) {
    next({ name: 'Dashboard' })
  } else {
    next()
  }
})

// Axios interceptor
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      localStorage.removeItem('user')
      router.push({ name: 'Login' })
    }
    return Promise.reject(error)
  }
)

// Vue app
const app = createApp(App)

app.provide('colorScheme', colorScheme)
app.provide('toggleDarkMode', toggleDarkMode)
app.component('ThemeToggle', ThemeToggle)

app.use(router)
app.use(Vue3Toastify, {
  autoClose: 4000,
  position: 'top-right',
})
app.mount('#app')
