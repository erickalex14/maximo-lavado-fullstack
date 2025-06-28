<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Balance Financiero</h1>
      <button
        @click="exportBalance"
        class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Exportar
      </button>
    </div>

    <!-- Contenido temporal -->
    <div class="bg-white rounded-lg shadow-sm p-8 text-center">
      <div class="max-w-md mx-auto">
        <svg class="mx-auto h-24 w-24 text-blue-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Vista de Balance en Desarrollo</h3>
        <p class="text-gray-500 mb-4">
          Esta sección estará disponible próximamente. Aquí podrás ver el balance financiero completo.
        </p>
        <div class="text-sm text-gray-400">
          Funcionalidades planificadas:
          <ul class="mt-2 space-y-1">
            <li>• Balance general</li>
            <li>• Estado de resultados</li>
            <li>• Flujo de caja</li>
            <li>• Indicadores financieros</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useNotificationStore } from '@/stores/notification';

const notificationStore = useNotificationStore();

const exportBalance = () => {
  notificationStore.addNotification({
    type: 'info',
    title: 'Función en desarrollo',
    message: 'La exportación de balance se implementará próximamente'
  });
};
</script>
