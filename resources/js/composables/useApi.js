import { ref } from 'vue'
import { useLoadingStore } from '@/stores/loading'
import { useNotificationStore } from '@/stores/notification'
import { useAuthStore } from '@/stores/auth'

export function useApi() {
  const loading = useLoadingStore()
  const notification = useNotificationStore()
  const auth = useAuthStore()
  
  const isLoading = ref(false)
  const error = ref(null)
  // Initialize CSRF by calling sanctum endpoint
  const initializeCSRF = async () => {
    try {
      await fetch('/sanctum/csrf-cookie', {
        method: 'GET',
        credentials: 'include'
      })
    } catch (error) {
      console.log('Sanctum CSRF endpoint not available, using meta token')
    }
  }

  const makeRequest = async (url, options = {}) => {
    isLoading.value = true
    error.value = null
    loading.startLoading(options.loadingMessage || 'Cargando...')

    try {
      // Ensure CSRF token is fresh
      await initializeCSRF()
      
      const token = localStorage.getItem('auth_token')
      
      const config = {
        method: 'GET',
        credentials: 'include', // Important for CSRF cookies
        headers: {          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
          ...(token && { Authorization: `Bearer ${token}` }),
          ...options.headers
        },
        ...options
      }

      const response = await fetch(url, config)
      
      // Handle CSRF token mismatch
      if (response.status === 419) {
        notification.error('Token de seguridad expirado. Recargando página...')
        setTimeout(() => window.location.reload(), 2000)
        throw new Error('CSRF token mismatch')
      }
      
      // Handle authentication errors
      if (response.status === 401) {
        auth.logout()
        window.location.href = '/login'
        notification.error('Tu sesión ha expirado. Por favor, inicia sesión nuevamente.')
        throw new Error('Session expired')
      }

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}))
        throw new Error(errorData.message || `HTTP ${response.status}: ${response.statusText}`)
      }

      const data = await response.json()
      
      // Show success message if provided
      if (options.successMessage) {
        notification.success(options.successMessage)
      }
      
      return data
    } catch (err) {
      error.value = err.message
      
      // Show error message unless explicitly disabled
      if (options.showError !== false) {
        notification.error(err.message || 'Ha ocurrido un error inesperado')
      }
      
      throw err
    } finally {
      isLoading.value = false
      loading.stopLoading()
    }
  }

  const get = (url, options = {}) => {
    return makeRequest(url, { ...options, method: 'GET' })
  }

  const post = (url, data = {}, options = {}) => {
    return makeRequest(url, {
      ...options,
      method: 'POST',
      body: JSON.stringify(data)
    })
  }

  const put = (url, data = {}, options = {}) => {
    return makeRequest(url, {
      ...options,
      method: 'PUT',
      body: JSON.stringify(data)
    })
  }

  const patch = (url, data = {}, options = {}) => {
    return makeRequest(url, {
      ...options,
      method: 'PATCH',
      body: JSON.stringify(data)
    })
  }

  const deleteRequest = (url, options = {}) => {
    return makeRequest(url, { ...options, method: 'DELETE' })
  }

  return {
    isLoading,
    error,
    get,
    post,
    put,
    patch,
    delete: deleteRequest,
    makeRequest
  }
}
