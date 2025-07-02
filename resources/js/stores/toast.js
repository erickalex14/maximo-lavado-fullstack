import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useToastStore = defineStore('toast', () => {
  // State
  const toasts = ref([])
  let nextId = 1

  // Actions
  const add = (toast) => {
    const id = nextId++
    const newToast = {
      id,
      type: toast.type || 'info',
      title: toast.title || '',
      message: toast.message || '',
      duration: toast.duration || 5000,
      timestamp: Date.now()
    }

    toasts.value.push(newToast)

    // Auto-remove toast after duration
    if (newToast.duration > 0) {
      setTimeout(() => {
        remove(id)
      }, newToast.duration)
    }

    return id
  }

  const remove = (id) => {
    const index = toasts.value.findIndex(toast => toast.id === id)
    if (index > -1) {
      toasts.value.splice(index, 1)
    }
  }

  const clear = () => {
    toasts.value = []
  }

  // Convenience methods for different types
  const success = (message, title = 'Éxito', options = {}) => {
    return add({
      type: 'success',
      title,
      message,
      ...options
    })
  }

  const error = (message, title = 'Error', options = {}) => {
    return add({
      type: 'error',
      title,
      message,
      duration: 0, // Error toasts don't auto-dismiss by default
      ...options
    })
  }

  const warning = (message, title = 'Advertencia', options = {}) => {
    return add({
      type: 'warning',
      title,
      message,
      ...options
    })
  }

  const info = (message, title = 'Información', options = {}) => {
    return add({
      type: 'info',
      title,
      message,
      ...options
    })
  }

  return {
    // State
    toasts,

    // Actions
    add,
    remove,
    clear,

    // Convenience methods
    success,
    error,
    warning,
    info
  }
})
