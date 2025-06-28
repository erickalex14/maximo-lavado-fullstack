<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Gestión de Proveedores</h1>
      <button
        @click="showCreateModal = true"
        class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Nuevo Proveedor
      </button>
    </div>

    <!-- Contenido temporal -->
    <div class="bg-white rounded-lg shadow-sm p-8 text-center">
      <div class="max-w-md mx-auto">
        <svg class="mx-auto h-24 w-24 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Vista de Proveedores en Desarrollo</h3>
        <p class="text-gray-500 mb-4">
          Esta sección estará disponible próximamente. Aquí podrás gestionar todos los proveedores.
        </p>
        <div class="text-sm text-gray-400">
          Funcionalidades planificadas:
          <ul class="mt-2 space-y-1">
            <li>• Registro de proveedores</li>
            <li>• Información de contacto</li>
            <li>• Historial de compras</li>
            <li>• Evaluación de proveedores</li>
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
