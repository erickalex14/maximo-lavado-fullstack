<template>
  <div 
    v-if="loadingStore.isLoading"
    class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center"
  >
    <div class="bg-white rounded-lg p-6 flex flex-col items-center space-y-4 max-w-sm mx-4">
      <!-- Spinner -->
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-500"></div>
      
      <!-- Loading message -->
      <div class="text-center">
        <p class="text-gray-800 font-medium">
          {{ loadingStore.loadingMessage || 'Cargando...' }}
        </p>
        <p class="text-gray-500 text-sm mt-1">
          Por favor espere
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useLoadingStore } from '@/stores/loading';

const loadingStore = useLoadingStore();
</script>
