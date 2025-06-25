import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    isAuthenticated: false,
    loading: false
  }),

  getters: {
    isLoggedIn: (state) => state.isAuthenticated && state.user !== null
  },

  actions: {
    async login(credentials) {
      this.loading = true
      try {
        // Get CSRF cookie first
        await axios.get('/sanctum/csrf-cookie').catch(() => {
          // Continue if sanctum route doesn't exist
        })

        const response = await axios.post('/login', credentials, {
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        })

        if (response.data.success) {
          this.user = response.data.user
          this.isAuthenticated = true
          return { success: true }
        } else {
          return { success: false, message: response.data.message }
        }
      } catch (error) {
        console.error('Login error:', error)
        let message = 'Error al conectar con el servidor'
        
        if (error.response?.data?.message) {
          message = error.response.data.message
        } else if (error.response?.data?.errors) {
          message = Object.values(error.response.data.errors).flat().join(', ')
        }
        
        return { success: false, message }
      } finally {
        this.loading = false
      }
    },

    async logout() {
      try {
        await axios.post('/logout')
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.user = null
        this.isAuthenticated = false
      }
    },

    async checkAuth() {
      try {
        const response = await axios.get('/user', {
          headers: {
            'Accept': 'application/json'
          }
        })
        
        if (response.data.success && response.data.user) {
          this.user = response.data.user
          this.isAuthenticated = true
          return true
        }
      } catch (error) {
        console.error('Auth check error:', error)
      }
      
      this.user = null
      this.isAuthenticated = false
      return false
    },

    async fetchUser() {
      try {
        const response = await axios.get('/api/user')
        this.user = response.data
        this.isAuthenticated = true
      } catch (error) {
        console.error('Error fetching user:', error)
        this.user = null
        this.isAuthenticated = false
      }
    }
  }
})
