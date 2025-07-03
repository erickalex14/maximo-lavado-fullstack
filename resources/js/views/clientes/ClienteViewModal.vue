<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
      <div class="fixed inset-0 bg-black bg-opacity-25 transition-opacity" @click="$emit('close')"></div>

      <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
          <!-- Header -->
          <div class="sm:flex sm:items-start">
            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold leading-6 text-surface-900">
                  Detalles del Cliente
                </h3>
                <button
                  @click="cliente && $emit('edit', cliente)"
                  class="btn-secondary btn-sm"
                  :disabled="!cliente"
                >
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Editar
                </button>
              </div>

              <div v-if="cliente" class="mt-6">
                <!-- Cliente Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Avatar y nombre -->
                  <div class="col-span-full flex items-center space-x-4">
                    <div class="flex-shrink-0 h-16 w-16">
                      <div class="h-16 w-16 rounded-full bg-primary-100 flex items-center justify-center">
                        <span class="text-xl font-bold text-primary-800">
                          {{ cliente.nombre.charAt(0).toUpperCase() }}
                        </span>
                      </div>
                    </div>
                    <div>
                      <h4 class="text-xl font-bold text-surface-900">{{ cliente.nombre }}</h4>
                      <div class="flex items-center mt-1">
                        <span
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="cliente.activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                        >
                          {{ cliente.activo ? 'Activo' : 'Inactivo' }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Información de contacto -->
                  <div>
                    <h5 class="text-sm font-medium text-surface-700 mb-3">Información de Contacto</h5>
                    <div class="space-y-3">
                      <div class="flex items-center">
                        <svg class="w-5 h-5 text-surface-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span class="text-sm text-surface-900">{{ cliente.email }}</span>
                      </div>
                      <div class="flex items-center">
                        <svg class="w-5 h-5 text-surface-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span class="text-sm text-surface-900">{{ cliente.telefono }}</span>
                      </div>
                      <div v-if="cliente.direccion" class="flex items-center">
                        <svg class="w-5 h-5 text-surface-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-sm text-surface-900">{{ cliente.direccion }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Información personal -->
                  <div>
                    <h5 class="text-sm font-medium text-surface-700 mb-3">Información Personal</h5>
                    <div class="space-y-3">
                      <div class="flex items-center">
                        <svg class="w-5 h-5 text-surface-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V4a2 2 0 114 0v2m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                        </svg>
                        <span class="text-sm text-surface-900">{{ cliente.cedula }}</span>
                      </div>
                      <div class="flex items-center">
                        <svg class="w-5 h-5 text-surface-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 2v10a6 6 0 01-6-6V8a3 3 0 013-3h6a3 3 0 013 3v5a6 6 0 01-6 6z" />
                        </svg>
                        <span class="text-sm text-surface-900">
                          Registrado el {{ formatDate(cliente.created_at) }}
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Estadísticas -->
                  <div class="col-span-full">
                    <h5 class="text-sm font-medium text-surface-700 mb-3">Resumen</h5>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                      <div class="bg-blue-50 rounded-lg p-3">
                        <div class="flex items-center">
                          <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                          <div class="ml-3">
                            <p class="text-sm font-medium text-blue-900">Vehículos</p>
                            <p class="text-lg font-bold text-blue-600">{{ cliente.vehiculos_count || 0 }}</p>
                          </div>
                        </div>
                      </div>
                      
                      <div class="bg-green-50 rounded-lg p-3">
                        <div class="flex items-center">
                          <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>
                          <div class="ml-3">
                            <p class="text-sm font-medium text-green-900">Lavados</p>
                            <p class="text-lg font-bold text-green-600">0</p>
                          </div>
                        </div>
                      </div>

                      <div class="bg-purple-50 rounded-lg p-3">
                        <div class="flex items-center">
                          <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                          </svg>
                          <div class="ml-3">
                            <p class="text-sm font-medium text-purple-900">Facturas</p>
                            <p class="text-lg font-bold text-purple-600">0</p>
                          </div>
                        </div>
                      </div>

                      <div class="bg-orange-50 rounded-lg p-3">
                        <div class="flex items-center">
                          <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>
                          <div class="ml-3">
                            <p class="text-sm font-medium text-orange-900">Total Gastado</p>
                            <p class="text-lg font-bold text-orange-600">$0</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="bg-surface-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
          <button
            @click="$emit('close')"
            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-surface-900 shadow-sm ring-1 ring-inset ring-surface-300 hover:bg-surface-50 sm:mt-0 sm:w-auto"
          >
            Cerrar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Cliente } from '@/types';

interface Props {
  isOpen: boolean;
  cliente?: Cliente | null;
}

defineProps<Props>();

defineEmits<{
  close: [];
  edit: [cliente: Cliente];
}>();

// Methods
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};
</script>
