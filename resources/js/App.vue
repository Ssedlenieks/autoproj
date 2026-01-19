<template>
  <div v-if="!loading" class="app">
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

export default {
  name: 'App',
  setup() {
    const { checkAuth, loading } = useAuth()

    onMounted(async () => {
      console.log('App mounted, checking auth...')
      await checkAuth()
      console.log('Auth check complete, loading:', loading.value)
    })

    return {
      loading,
    }
  },
}
</script>

<style scoped>
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

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
