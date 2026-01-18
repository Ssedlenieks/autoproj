import './bootstrap';
import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import axios from 'axios'

axios.defaults.baseURL = 'http://localhost/autoproj/public'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: () => import('./pages/Hero.vue') },
    { path: '/builder', component: () => import('./pages/CarBuilder.vue') }
  ]
})

createApp(App).use(router).mount('#app')
