import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAppStore = defineStore('app', () => {
  // State
  const loading = ref(false)
  const alerts = ref([])
  const sidebarCollapsed = ref(false)
  const theme = ref('light')
  const locale = ref('es')
  
  let nextAlertId = 1

  // Getters
  const isLoading = computed(() => loading.value)
  const isDarkMode = computed(() => theme.value === 'dark')
  const activeAlerts = computed(() => alerts.value.filter(alert => !alert.dismissed))

  // Actions
  const setLoading = (value) => {
    loading.value = !!value
  }

  const addAlert = (alert) => {
    const id = nextAlertId++
    const newAlert = {
      id,
      variant: alert.variant || 'info',
      title: alert.title || '',
      message: alert.message || '',
      dismissible: alert.dismissible !== false,
      duration: alert.duration || 0,
      dismissed: false,
      timestamp: Date.now()
    }

    alerts.value.push(newAlert)

    // Auto-dismiss if duration is set
    if (newAlert.duration > 0) {
      setTimeout(() => {
        removeAlert(id)
      }, newAlert.duration)
    }

    return id
  }

  const removeAlert = (id) => {
    const index = alerts.value.findIndex(alert => alert.id === id)
    if (index > -1) {
      alerts.value.splice(index, 1)
    }
  }

  const clearAlerts = () => {
    alerts.value = []
  }

  const setSidebarCollapsed = (collapsed) => {
    sidebarCollapsed.value = collapsed
  }

  const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value
  }

  const setTheme = (newTheme) => {
    theme.value = newTheme
    // Apply theme to document
    document.documentElement.setAttribute('data-theme', newTheme)
    // Save to localStorage
    localStorage.setItem('app-theme', newTheme)
  }

  const toggleTheme = () => {
    setTheme(theme.value === 'light' ? 'dark' : 'light')
  }

  const setLocale = (newLocale) => {
    locale.value = newLocale
    // Save to localStorage
    localStorage.setItem('app-locale', newLocale)
  }

  // Convenience alert methods
  const showSuccess = (message, title = 'Éxito', options = {}) => {
    return addAlert({
      variant: 'success',
      title,
      message,
      ...options
    })
  }

  const showError = (message, title = 'Error', options = {}) => {
    return addAlert({
      variant: 'danger',
      title,
      message,
      ...options
    })
  }

  const showWarning = (message, title = 'Advertencia', options = {}) => {
    return addAlert({
      variant: 'warning',
      title,
      message,
      ...options
    })
  }

  const showInfo = (message, title = 'Información', options = {}) => {
    return addAlert({
      variant: 'info',
      title,
      message,
      ...options
    })
  }

  // Initialize from localStorage
  const initializeFromStorage = () => {
    const savedTheme = localStorage.getItem('app-theme')
    if (savedTheme) {
      setTheme(savedTheme)
    }

    const savedLocale = localStorage.getItem('app-locale')
    if (savedLocale) {
      setLocale(savedLocale)
    }

    const savedSidebarState = localStorage.getItem('sidebar-collapsed')
    if (savedSidebarState !== null) {
      setSidebarCollapsed(JSON.parse(savedSidebarState))
    }
  }

  return {
    // State
    loading,
    alerts,
    sidebarCollapsed,
    theme,
    locale,

    // Getters
    isLoading,
    isDarkMode,
    activeAlerts,

    // Actions
    setLoading,
    addAlert,
    removeAlert,
    clearAlerts,
    setSidebarCollapsed,
    toggleSidebar,
    setTheme,
    toggleTheme,
    setLocale,
    
    // Convenience methods
    showSuccess,
    showError,
    showWarning,
    showInfo,
    
    // Initialize
    initializeFromStorage
  }
})
