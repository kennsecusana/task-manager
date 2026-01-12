import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useRouter } from 'nuxt/app'
import type { User, LoginPayload } from '@@/types'
import { useApi } from '@@/composables/useApi'

export const useAuthStore = defineStore('auth', () => {
  const api = useApi()
  const router = useRouter()

  const user = ref<User | null>(null)
  const isAuthenticated = computed(() => !!user.value)
  const isLoading = ref(false)
  const error = ref<string | null>(null)

  const login = async (credentials: LoginPayload): Promise<void> => {
    isLoading.value = true
    error.value = null

    try {
      const response = await api.post<{ user: User; token: string }>('/login', credentials)
      
      // Store token in localStorage
      if (import.meta.client) {
        localStorage.setItem('auth_token', response.token)
      }
      
      // Set user data
      user.value = response.user
      
      await router.push('/')
    }
    catch (err: any) {
      error.value = err.message || 'Login failed'
      throw err
    }
    finally {
      isLoading.value = false
    }
  }

  const logout = async (): Promise<void> => {
    try {
      await api.post('/logout', {})
    }
    catch (err: any) {
      console.error('Logout failed:', err)
    }
    finally {
      // Ckear user data and token
      user.value = null
      if (import.meta.client) {
        localStorage.removeItem('auth_token')
      }
      await router.push('/login')
    }
  }

  const fetchUser = async (): Promise<void> => {
    try {
      const response = await api.get<User>('/user')

      // Set user data
      user.value = response
    }
    catch (err) {
      user.value = null
      if (import.meta.client) {
        localStorage.removeItem('auth_token')
      }
    }
  }

  return {
    user,
    isAuthenticated,
    isLoading,
    error,
    login,
    logout,
    fetchUser
  }
})