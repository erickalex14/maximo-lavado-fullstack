import { defineStore } from 'pinia'
import { useNotificationStore } from './notification'
import { useLoadingStore } from './loading'
import { useApi } from '@/composables/useApi'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    isAuthenticated: false,
    loading: false,
    token: localStorage.getItem('auth_token') || null
  }),

  getters: {
    isLoggedIn: (state) => state.isAuthenticated && state.user !== null
  },

  actions: {
    async login(credentials) {
      const loadingStore = useLoadingStore()
      const notificationStore = useNotificationStore()
      const api = useApi()
      
      this.loading = true
      loadingStore.startLoading('Iniciando sesión...')
      
      try {        const response = await api.post('/login', credentials, {
          showError: false
        })

        if (response.success) {
          await this.fetchUser()
          notificationStore.success('Sesión iniciada correctamente', 'Bienvenido')
          return { success: true }
        } else {
          notificationStore.error(response.message || 'Error al iniciar sesión')
          return { success: false, message: response.message || 'Error al iniciar sesión' }
        }
      } catch (error) {
        console.error('Login error:', error)
        const message = 'Error al conectar con el servidor'
        notificationStore.error(message)
        return { success: false, message }
      } finally {
        this.loading = false
        loadingStore.stopLoading()
      }
    },

    async fetchUser() {
      try {
        const response = await fetch('/api/user', {
          headers: {
            'Accept': 'application/json',
            'Authorization': this.token ? `Bearer ${this.token}` : undefined
          },
          credentials: 'include'
        })

        if (response.ok) {
          const userData = await response.json()
          this.user = userData
          this.isAuthenticated = true
          return userData
        } else if (response.status === 401) {
          this.logout()
        }
      } catch (error) {
        console.error('Error fetching user:', error)
        this.logout()
      }
    },

    async logout() {
      const loadingStore = useLoadingStore()
      const notificationStore = useNotificationStore()
      
      loadingStore.startLoading('Cerrando sesión...')
      
      try {
        await fetch('/logout', {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          credentials: 'include'
        })
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.user = null
        this.isAuthenticated = false
        this.token = null
        localStorage.removeItem('auth_token')
        loadingStore.stopLoading()
        notificationStore.info('Sesión cerrada correctamente')
      }
    },

    async checkAuth() {
      if (this.token && !this.user) {
        await this.fetchUser()
      }
    },

    setToken(token) {
      this.token = token
      localStorage.setItem('auth_token', token)
    }
  }
})
