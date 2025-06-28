<template>
  <div id="app" class="min-h-screen bg-surface-50">
    <!-- Loading inicial mientras se verifica autenticación -->
    <div v-if="!authStore.isInitialized" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-primary-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Verificando sesión...</p>
      </div>
    </div>
    
    <!-- App principal una vez inicializada -->
    <template v-else>
      <!-- Loading overlay global -->
      <LoadingOverlay v-if="loadingStore.isLoading" />
      
      <!-- Notification system -->
      <NotificationSystem />
      
      <!-- Router view para renderizar las páginas -->
      <router-view />
    </template>
  </div>
</template>

<script setup>
import { useLoadingStore } from '@/stores/loading';
import { useAuthStore } from '@/stores/auth';
import LoadingOverlay from '@/components/common/LoadingOverlay.vue';
import NotificationSystem from '@/components/common/NotificationSystem.vue';

const loadingStore = useLoadingStore();
const authStore = useAuthStore();
</script>

<style>
/* Estilos globales adicionales si son necesarios */
</style>
