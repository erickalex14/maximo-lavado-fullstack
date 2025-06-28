import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useLoadingStore = defineStore('loading', () => {
  // Estado
  const isLoading = ref(false);
  const loadingMessage = ref('');
  const activeRequests = ref(new Set());

  // Actions
  const setLoading = (loading, message = '') => {
    isLoading.value = loading;
    loadingMessage.value = message;
  };

  const startLoading = (requestId = null, message = 'Cargando...') => {
    if (requestId) {
      activeRequests.value.add(requestId);
    }
    isLoading.value = true;
    loadingMessage.value = message;
  };

  const stopLoading = (requestId = null) => {
    if (requestId) {
      activeRequests.value.delete(requestId);
      // Solo parar el loading si no hay más requests activos
      if (activeRequests.value.size === 0) {
        isLoading.value = false;
        loadingMessage.value = '';
      }
    } else {
      isLoading.value = false;
      loadingMessage.value = '';
      activeRequests.value.clear();
    }
  };

  return {
    // Estado
    isLoading,
    loadingMessage,
    // Actions
    setLoading,
    startLoading,
    stopLoading
  };
});
