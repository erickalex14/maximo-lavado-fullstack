<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
      <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">
          Detalles del Ingreso
        </h3>
      </div>

      <div class="px-6 py-4 space-y-4">
        <!-- Fecha -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Fecha
          </label>
          <p class="text-gray-900 font-medium">{{ formatDate(ingreso?.fecha) }}</p>
        </div>

        <!-- Tipo -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Tipo
          </label>
          <p class="text-gray-900 font-medium">{{ getTypeLabel(ingreso?.tipo) || 'N/A' }}</p>
        </div>

        <!-- Monto -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Monto
          </label>
          <div class="flex items-center gap-2">
            <span class="text-gray-900 font-medium text-lg text-green-600">
              ${{ formatCurrency(ingreso?.monto || 0) }}
            </span>
          </div>
        </div>

        <!-- Descripción -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Descripción
          </label>
          <p class="text-gray-900">{{ ingreso?.descripcion || 'Sin descripción' }}</p>
        </div>

        <!-- Fechas del sistema -->
        <div class="grid grid-cols-1 gap-4 pt-4 border-t">
          <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">
              Fecha de Registro
            </label>
            <p class="text-gray-900">
              {{ formatDateTime(ingreso?.created_at) }}
            </p>
          </div>
          <div v-if="ingreso?.updated_at && ingreso.updated_at !== ingreso.created_at">
            <label class="block text-sm font-medium text-gray-500 mb-1">
              Última Actualización
            </label>
            <p class="text-gray-900">
              {{ formatDateTime(ingreso?.updated_at) }}
            </p>
          </div>
        </div>

        <!-- Información adicional -->
        <div v-if="showStats" class="pt-4 border-t">
          <h4 class="text-sm font-medium text-gray-500 mb-2">Información Adicional</h4>
          <div class="grid grid-cols-1 gap-4 text-sm">
            <div class="text-center p-2 bg-green-50 rounded">
              <p class="text-gray-500">ID Ingreso</p>
              <p class="font-medium font-mono">{{ ingreso?.ingreso_id || 'N/A' }}</p>
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
          v-if="ingreso"
          @click="$emit('edit', ingreso)"
          class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg"
        >
          Editar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Ingreso } from '@/types';

interface Props {
  isOpen: boolean;
  ingreso?: Ingreso | null;
  showStats?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  showStats: true
});

const emit = defineEmits<{
  close: [];
  edit: [ingreso: Ingreso];
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
    lavado: 'Lavado',
    producto_automotriz: 'Producto Automotriz',
    producto_despensa: 'Producto de Despensa'
  };
  return labels[tipo || ''] || 'N/A';
};
</script>
