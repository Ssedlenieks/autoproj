import './bootstrap'
import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import ThemeToggle from './components/ThemeToggle.vue'
import axios from 'axios'

// Initialize dark mode
import { useDarkMode } from './composables/useDarkMode'
const { colorScheme, toggleDarkMode } = useDarkMode()

axios.defaults.baseURL = 'http://localhost/autoproj/public'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: () => import('./pages/Hero.vue') },
    { path: '/builder', component: () => import('./pages/CarBuilder.vue') },
    { path: '/register', component: () => import('./pages/Register.vue') },
    { path: '/login', component: () => import('./pages/Login.vue') },
  ]
})

const app = createApp(App)

app.provide('colorScheme', colorScheme)
app.provide('toggleDarkMode', toggleDarkMode)
app.component('ThemeToggle', ThemeToggle)

app.use(router)
app.mount('#app')
