import { ref } from 'vue'
import { defineStore } from 'pinia'

export const useNotificationStore = defineStore('notification', () => {
  const notifications = ref([])

  const addNotification = (notification) => {
    const id = Date.now() + Math.random()
    const newNotification = {
      id,
      type: notification.type || 'info', // 'success', 'error', 'warning', 'info'
      title: notification.title || '',
      message: notification.message || '',
      duration: notification.duration || 5000
    }
    
    notifications.value.push(newNotification)
    
    // Auto remove after duration
    if (newNotification.duration > 0) {
      setTimeout(() => {
        removeNotification(id)
      }, newNotification.duration)
    }
    
    return id
  }

  const removeNotification = (id) => {
    const index = notifications.value.findIndex(notification => notification.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
  }

  const clearAll = () => {
    notifications.value = []
  }

  // Helper methods
  const success = (message, title = 'Éxito') => {
    return addNotification({ type: 'success', title, message })
  }

  const error = (message, title = 'Error') => {
    return addNotification({ type: 'error', title, message, duration: 7000 })
  }

  const warning = (message, title = 'Advertencia') => {
    return addNotification({ type: 'warning', title, message })
  }

  const info = (message, title = 'Información') => {
    return addNotification({ type: 'info', title, message })
  }

  return {
    notifications,
    addNotification,
    removeNotification,
    clearAll,
    success,
    error,
    warning,
    info
  }
})
