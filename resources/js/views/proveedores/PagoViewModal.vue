<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">
            Detalles del Pago #{{ pago?.id_pago_proveedor }}
          </h3>
          <button
            @click="closeModal"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Content -->
      <div v-if="pago" class="p-6 space-y-6">
        <!-- Información del Proveedor -->
        <div class="bg-gray-50 rounded-lg p-4">
          <h4 class="text-sm font-semibold text-gray-900 mb-3">Información del Proveedor</h4>
          <div class="grid grid-cols-1 gap-3">
            <div>
              <span class="text-sm font-medium text-gray-500">Nombre:</span>
              <span class="text-sm text-gray-900 ml-2">{{ pago.proveedor?.nombre || 'N/A' }}</span>
            </div>
            <div v-if="pago.proveedor?.email">
              <span class="text-sm font-medium text-gray-500">Email:</span>
              <span class="text-sm text-gray-900 ml-2">{{ pago.proveedor.email }}</span>
            </div>
            <div v-if="pago.proveedor?.telefono">
              <span class="text-sm font-medium text-gray-500">Teléfono:</span>
              <span class="text-sm text-gray-900 ml-2">{{ pago.proveedor.telefono }}</span>
            </div>
          </div>
        </div>

        <!-- Detalles del Pago -->
        <div class="grid grid-cols-1 gap-4">
          <div class="flex justify-between">
            <span class="text-sm font-medium text-gray-500">Monto:</span>
            <span class="text-sm font-semibold text-gray-900">${{ formatCurrency(pago.monto) }}</span>
          </div>
          
          <div class="flex justify-between">
            <span class="text-sm font-medium text-gray-500">Fecha de Pago:</span>
            <span class="text-sm text-gray-900">{{ formatDate(pago.fecha) }}</span>
          </div>
          
          <div v-if="pago.descripcion" class="flex flex-col">
            <span class="text-sm font-medium text-gray-500 mb-1">Descripción:</span>
            <span class="text-sm text-gray-900">{{ pago.descripcion }}</span>
          </div>
        </div>

        <!-- Metadatos -->
        <div class="border-t border-gray-200 pt-4">
          <h4 class="text-sm font-semibold text-gray-900 mb-3">Información del Sistema</h4>
          <div class="grid grid-cols-1 gap-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-500">Fecha de Creación:</span>
              <span class="text-gray-900">{{ formatDateTime(pago.created_at) }}</span>
            </div>
            <div v-if="pago.updated_at !== pago.created_at" class="flex justify-between">
              <span class="text-gray-500">Última Actualización:</span>
              <span class="text-gray-900">{{ formatDateTime(pago.updated_at) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg">
        <div class="flex justify-end space-x-3">
          <button
            v-if="pago"
            @click="$emit('edit', pago)"
            class="px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            Editar
          </button>
          <button
            @click="closeModal"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            Cerrar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { PagoProveedor } from '@/types';

interface Props {
  isOpen: boolean;
  pago?: PagoProveedor | null;
}

interface Emits {
  (e: 'close'): void;
  (e: 'edit', pago: PagoProveedor): void;
}

const props = withDefaults(defineProps<Props>(), {
  pago: null
});

const emit = defineEmits<Emits>();

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
};

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('es-CO');
};

const formatDateTime = (date: string): string => {
  return new Date(date).toLocaleString('es-CO');
};

const closeModal = () => {
  emit('close');
};
</script>
