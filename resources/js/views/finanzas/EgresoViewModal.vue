<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
      <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">
          Detalles del Egreso
        </h3>
      </div>

      <div class="px-6 py-4 space-y-4">
        <!-- Fecha -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Fecha
          </label>
          <p class="text-gray-900 font-medium">{{ formatDate(egreso?.fecha) }}</p>
        </div>

        <!-- Tipo -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Tipo
          </label>
          <p class="text-gray-900 font-medium">{{ getTypeLabel(egreso?.tipo) || 'N/A' }}</p>
        </div>

        <!-- Monto -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Monto
          </label>
          <div class="flex items-center gap-2">
            <span class="text-gray-900 font-medium text-lg text-red-600">
              ${{ formatCurrency(egreso?.monto || 0) }}
            </span>
          </div>
        </div>

        <!-- Descripción -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Descripción
          </label>
          <p class="text-gray-900">{{ egreso?.descripcion || 'Sin descripción' }}</p>
        </div>

        <!-- Fechas del sistema -->
        <div class="grid grid-cols-1 gap-4 pt-4 border-t">
          <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">
              Fecha de Registro
            </label>
            <p class="text-gray-900">
              {{ formatDateTime(egreso?.created_at) }}
            </p>
          </div>
          <div v-if="egreso?.updated_at && egreso.updated_at !== egreso.created_at">
            <label class="block text-sm font-medium text-gray-500 mb-1">
              Última Actualización
            </label>
            <p class="text-gray-900">
              {{ formatDateTime(egreso?.updated_at) }}
            </p>
          </div>
        </div>

        <!-- Información adicional -->
        <div v-if="showStats" class="pt-4 border-t">
          <h4 class="text-sm font-medium text-gray-500 mb-2">Información Adicional</h4>
          <div class="grid grid-cols-1 gap-4 text-sm">
            <div class="text-center p-2 bg-red-50 rounded">
              <p class="text-gray-500">ID Egreso</p>
              <p class="font-medium font-mono">{{ egreso?.egreso_id || 'N/A' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Botones -->
      <div class="px-6 py-4 border-t flex justify-end gap-3">
        <button
          @click="$emit('close')"
          class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg"
        >
          Cerrar
        </button>
        <button
          v-if="egreso"
          @click="$emit('edit', egreso)"
          class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg"
        >
          Editar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Egreso } from '@/types';

interface Props {
  isOpen: boolean;
  egreso?: Egreso | null;
  showStats?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  showStats: true
});

const emit = defineEmits<{
  close: [];
  edit: [egreso: Egreso];
}>();

// Métodos
const formatDate = (date?: string): string => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('es-CO');
};

const formatDateTime = (date?: string): string => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleString('es-CO');
};

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount);
};

const getTypeLabel = (tipo?: string): string => {
  const labels: Record<string, string> = {
    salario: 'Salario',
    proveedor: 'Proveedor',
    gasto_general: 'Gasto General'
  };
  return labels[tipo || ''] || 'N/A';
};
</script>
