<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Gestión de Ingresos</h1>
      <button
        @click="showCreateModal = true"
        class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Nuevo Ingreso
      </button>
    </div>

    <!-- Contenido temporal -->
    <div class="bg-white rounded-lg shadow-sm p-8 text-center">
      <div class="max-w-md mx-auto">
        <svg class="mx-auto h-24 w-24 text-green-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Vista de Ingresos en Desarrollo</h3>
        <p class="text-gray-500 mb-4">
          Esta sección estará disponible próximamente. Aquí podrás gestionar todos los ingresos.
        </p>
        <div class="text-sm text-gray-400">
          Funcionalidades planificadas:
          <ul class="mt-2 space-y-1">
            <li>• Registro de ingresos</li>
            <li>• Categorización</li>
            <li>• Reportes de ingresos</li>
            <li>• Análisis financiero</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const showCreateModal = ref(false);
</script>
