<template>
  <div v-if="!loading" class="app">

    <!-- Global controls: fixed top-right on all pages except Hero, Admin and Editor -->
    <div v-if="$route.path !== '/' && $route.path !== '/admin' && $route.path !== '/editor'" class="global-controls">
      <ThemeToggle />
    </div>

    <router-view />
  </div>

  <div v-else class="loading-screen">
    <div class="spinner"></div>
    <p>Loading...</p>
  </div>
</template>

<script>
import { useAuth } from './composables/useAuth'
import { onMounted } from 'vue'
import ThemeToggle from './components/ThemeToggle.vue'

export default {
  name: 'App',
  components: {
    ThemeToggle
  },
  setup() {
    const { checkAuth, loading } = useAuth()
    onMounted(async () => await checkAuth())
    return { loading }
  }
}
</script>

<style scoped>
.global-controls {
  position: fixed;
  top: 1.25rem;
  right: 2rem;
  display: flex;
  align-items: center;
  gap: 12px;
  z-index: 2147483647;
}

@media (max-width: 768px) {
  .global-controls {
    top: 1rem;
    right: 1rem;
    gap: 8px;
  }
}

.loading-screen {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
  color: #ffd700;
}
.spinner {
  width: 50px;
  height: 50px;
  border: 3px solid rgba(255, 215, 0, 0.2);
  border-top-color: #ffd700;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 20px;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>