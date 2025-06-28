<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Sistema de Facturación</h1>
      <button
        @click="createInvoice"
        class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Nueva Factura
      </button>
    </div>

    <!-- Contenido temporal -->
    <div class="bg-white rounded-lg shadow-sm p-8 text-center">
      <div class="max-w-md mx-auto">
        <svg class="mx-auto h-24 w-24 text-indigo-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Vista de Facturación en Desarrollo</h3>
        <p class="text-gray-500 mb-4">
          Esta sección estará disponible próximamente. Aquí podrás gestionar toda la facturación.
        </p>
        <div class="text-sm text-gray-400">
          Funcionalidades planificadas:
          <ul class="mt-2 space-y-1">
            <li>• Creación de facturas</li>
            <li>• Gestión de pagos</li>
            <li>• Historial de facturación</li>
            <li>• Reportes fiscales</li>
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

const createInvoice = () => {
  notificationStore.addNotification({
    type: 'info',
    title: 'Función en desarrollo',
    message: 'La creación de facturas se implementará próximamente'
  });
};
</script>
