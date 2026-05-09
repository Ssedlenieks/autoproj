import { ref } from 'vue'
import axios from 'axios'

const user = ref(null)
const isAuthenticated = ref(false)
const loading = ref(true)

export function useAuth() {
  const checkAuth = async () => {
    console.log('checkAuth called')
    try {
      const response = await axios.get('/me')
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
      console.error('Auth check failed:', err)
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
      await axios.post('/logout')
      console.log('Logout successful')
    } catch (err) {
      console.error('Logout failed:', err)
    } finally {
      user.value = null
      isAuthenticated.value = false
      localStorage.removeItem('user')
      setTimeout(() => {
        window.location.href = '/'
      }, 300)
    }
  }

  return {
    user,
    isAuthenticated,
    loading,
    checkAuth,
    setUser,
    logout,
  }
}