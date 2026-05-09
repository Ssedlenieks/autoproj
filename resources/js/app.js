import './bootstrap'
import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import ThemeToggle from './components/ThemeToggle.vue'
import axios from 'axios'
import Vue3Toastify from 'vue3-toastify'
import 'vue3-toastify/dist/index.css'
import { useDarkMode } from './composables/useDarkMode'

const { colorScheme, toggleDarkMode } = useDarkMode()

axios.defaults.baseURL = import.meta.env.VITE_APP_URL || 'http://autoproj.test'
axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

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
      path: '/admin',
      name: 'Admin',
      component: () => import('./pages/AdminPanel.vue'),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
      path: '/editor',
      name: 'Editor',
      component: () => import('./pages/EditorPanel.vue'),
      meta: { requiresAuth: true, requiresEditor: true },
    },
    {
      path: '/leaderboards',
      name: 'Leaderboards',
      component: () => import('./pages/Leaderboards.vue'),
      meta: { requiresAuth: true },
    },

    {
      path: '/explore',
      name: 'Explore',
      component: () => import('./pages/PublicProjects.vue'),
      meta: { requiresAuth: true },
    },

    {
      path: '/projects/:id',
      name: 'ProjectView',
      component: () => import('./pages/ProjectView.vue'),
      props: true,
      meta: { requiresAuth: true },
    },
  ],
})

router.beforeEach((to, from, next) => {
  const user = JSON.parse(localStorage.getItem('user') || 'null')
  const isAuthenticated = !!user
  const roleId = user?.role_id

  if (to.meta.requiresAuth && !isAuthenticated) {
    return next({ name: 'Login', query: { redirect: to.fullPath } })
  }

  if ((to.name === 'Login' || to.name === 'Register') && isAuthenticated) {
    return next({ name: 'Dashboard' })
  }

  if (to.meta.requiresAdmin && roleId !== 2) {
    return next({ name: 'Dashboard' })
  }

  if (to.meta.requiresEditor && roleId !== 2 && roleId !== 3) {
    return next({ name: 'Dashboard' })
  }

  next()
})

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