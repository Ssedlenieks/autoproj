import { ref } from 'vue'
import axios from 'axios'

const user = ref(null)
const isAuthenticated = ref(false)
const loading = ref(true)

export function useAuth() {
  const checkAuth = async () => {
    loading.value = true // reset loading at start of every check
    console.log('checkAuth called')

    try {
      const response = await axios.get('/me', { withCredentials: true })
      console.log('ME endpoint response:', response.status)
      console.log('ME endpoint data:', response.data)

      if (response.data.success) {
        user.value = response.data.user
        isAuthenticated.value = true
        localStorage.setItem('user', JSON.stringify(response.data.user))
        console.log('User authenticated:', response.data.user.name)
      } else {
        user.value = null
        isAuthenticated.value = false
        localStorage.removeItem('user')
        console.log('Not authenticated')
      }
    } catch (err) {
      // 401 is expected when not logged in — only log unexpected errors
      if (err.response?.status !== 401) {
        console.error('Auth check failed:', err)
      } else {
        console.log('Not authenticated (401)')
      }
      user.value = null
      isAuthenticated.value = false
      localStorage.removeItem('user')
    } finally {
      loading.value = false
    }
  }

  const setUser = (userData) => {
    user.value = userData
    isAuthenticated.value = !!userData
    if (userData) {
      localStorage.setItem('user', JSON.stringify(userData))
    } else {
      localStorage.removeItem('user')
    }
  }

  const logout = async () => {
    try {
      console.log('Logging out...')
      await axios.post('/logout', {}, { withCredentials: true })
      console.log('Logout successful')
    } catch (err) {
      // Still clear state even if logout request fails
      console.error('Logout request failed:', err)
    } finally {
      user.value = null
      isAuthenticated.value = false
      localStorage.removeItem('user')
      setTimeout(() => {
        window.location.href = '/'
      }, 300)
    }
  }

  // Computed helper — grab user from localStorage as fallback
  // while /me is still loading (prevents flicker on page refresh)
  const getUserFromCache = () => {
    try {
      const cached = localStorage.getItem('user')
      return cached ? JSON.parse(cached) : null
    } catch {
      return null
    }
  }

  // If user state is empty but localStorage has data, hydrate it instantly
  // so router guards don't incorrectly redirect on page refresh
  if (user.value === null && !isAuthenticated.value) {
    const cached = getUserFromCache()
    if (cached) {
      user.value = cached
      isAuthenticated.value = true
      // checkAuth will still verify with the server and correct if needed
    }
  }

  return {
    user,
    isAuthenticated,
    loading,
    checkAuth,
    setUser,
    logout,
    getUserFromCache,
  }
}