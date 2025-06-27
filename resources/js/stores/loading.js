import { ref } from 'vue'
import { defineStore } from 'pinia'

export const useLoadingStore = defineStore('loading', () => {
  const isLoading = ref(false)
  const loadingMessage = ref('')
  const loadingCount = ref(0)

  const startLoading = (message = 'Cargando...') => {
    loadingCount.value++
    isLoading.value = true
    loadingMessage.value = message
  }

  const stopLoading = () => {
    if (loadingCount.value > 0) {
      loadingCount.value--
    }
    
    if (loadingCount.value === 0) {
      isLoading.value = false
      loadingMessage.value = ''
    }
  }

  const forceStopLoading = () => {
    loadingCount.value = 0
    isLoading.value = false
    loadingMessage.value = ''
  }

  return {
    isLoading,
    loadingMessage,
    startLoading,
    stopLoading,
    forceStopLoading
  }
})
