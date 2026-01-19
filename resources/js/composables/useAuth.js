import { ref } from 'vue'

const user = ref(null)
const isAuthenticated = ref(false)
const loading = ref(true)

export function useAuth() {
  const checkAuth = async () => {
    console.log('checkAuth called')
    try {
      const response = await fetch('/me', {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        credentials: 'include',
      })

      console.log('ME endpoint response:', response.status)
      const data = await response.json()
      console.log('ME endpoint data:', data)

      if (response.ok) {
        user.value = data.user
        isAuthenticated.value = true
        console.log('User authenticated:', data.user.name)
      } else {
        user.value = null
        isAuthenticated.value = false
        console.log('Not authenticated')
      }
    } catch (err) {
      console.error('Auth check failed:', err)
      user.value = null
      isAuthenticated.value = false
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    try {
      console.log('Logging out...')
      const response = await fetch('/logout', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
        credentials: 'include',
      })

      console.log('Logout response:', response.status)

      user.value = null
      isAuthenticated.value = false

      // Refresh the page to reset everything
      setTimeout(() => {
        window.location.href = '/'
      }, 300)
    } catch (err) {
      console.error('Logout failed:', err)
    }
  }

  return {
    user,
    isAuthenticated,
    loading,
    checkAuth,
    logout,
  }
}
