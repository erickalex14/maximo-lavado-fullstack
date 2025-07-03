<template>
  <div id="app" class="min-h-screen bg-surface-50">
    <router-view />
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

onMounted(async () => {
  // Intentar obtener el usuario autenticado al iniciar la app
  await authStore.fetchUser();
});
</script>

<style>
/* Estilos globales adicionales si son necesarios */
</style>
