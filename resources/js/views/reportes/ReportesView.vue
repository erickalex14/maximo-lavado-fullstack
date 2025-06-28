<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Reportes</h1>
      <button
        @click="generateReport"
        class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Generar Reporte
      </button>
    </div>

    <!-- Contenido temporal -->
    <div class="bg-white rounded-lg shadow-sm p-8 text-center">
      <div class="max-w-md mx-auto">
        <svg class="mx-auto h-24 w-24 text-purple-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 13v-1m4 1v-3m4 3V8M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Vista de Reportes en Desarrollo</h3>
        <p class="text-gray-500 mb-4">
          Esta sección estará disponible próximamente. Aquí podrás generar reportes detallados.
        </p>
        <div class="text-sm text-gray-400">
          Funcionalidades planificadas:
          <ul class="mt-2 space-y-1">
            <li>• Reportes de ventas</li>
            <li>• Reportes financieros</li>
            <li>• Análisis de clientes</li>
            <li>• Reportes operativos</li>
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

const generateReport = () => {
  notificationStore.addNotification({
    type: 'info',
    title: 'Función en desarrollo',
    message: 'La generación de reportes se implementará próximamente'
  });
};
</script>
