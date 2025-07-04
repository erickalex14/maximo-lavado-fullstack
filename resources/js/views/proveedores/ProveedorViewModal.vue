<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
      <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">
          Detalles del Proveedor
        </h3>
      </div>

      <div class="px-6 py-4 space-y-4">
        <!-- Nombre -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Nombre
          </label>
          <p class="text-gray-900 font-medium">{{ proveedor?.nombre || 'N/A' }}</p>
        </div>

        <!-- Email -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Email
          </label>
          <p class="text-gray-900">{{ proveedor?.email || 'No especificado' }}</p>
        </div>

        <!-- Teléfono -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Teléfono
          </label>
          <p class="text-gray-900">{{ proveedor?.telefono || 'No especificado' }}</p>
        </div>

        <!-- Descripción -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Descripción
          </label>
          <p class="text-gray-900">{{ proveedor?.descripcion || 'Sin descripción' }}</p>
        </div>

        <!-- Deuda Pendiente -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Deuda Pendiente
          </label>
          <div class="flex items-center gap-2">
            <span class="text-gray-900 font-medium text-lg">
              ${{ (proveedor?.deuda_pendiente || 0).toLocaleString('es-CO', { minimumFractionDigits: 2 }) }}
            </span>
            <span
              :class="{
                'bg-green-100 text-green-800': (proveedor?.deuda_pendiente || 0) === 0,
                'bg-yellow-100 text-yellow-800': (proveedor?.deuda_pendiente || 0) > 0 && (proveedor?.deuda_pendiente || 0) <= 100000,
                'bg-red-100 text-red-800': (proveedor?.deuda_pendiente || 0) > 100000
              }"
              class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
            >
              {{ getDeudaStatus(proveedor?.deuda_pendiente) }}
            </span>
          </div>
        </div>

        <!-- Fechas -->
        <div class="grid grid-cols-1 gap-4 pt-4 border-t">
          <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">
              Fecha de Registro
            </label>
            <p class="text-gray-900">
              {{ proveedor?.created_at ? new Date(proveedor.created_at).toLocaleDateString('es-CO') : 'N/A' }}
            </p>
          </div>
          <div v-if="proveedor?.updated_at && proveedor.updated_at !== proveedor.created_at">
            <label class="block text-sm font-medium text-gray-500 mb-1">
              Última Actualización
            </label>
            <p class="text-gray-900">
              {{ new Date(proveedor.updated_at).toLocaleDateString('es-CO') }}
            </p>
          </div>
        </div>

        <!-- Estadísticas -->
        <div v-if="showStats" class="pt-4 border-t">
          <h4 class="text-sm font-medium text-gray-500 mb-2">Información Adicional</h4>
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div class="text-center p-2 bg-gray-50 rounded">
              <p class="text-gray-500">ID Proveedor</p>
              <p class="font-medium font-mono">{{ proveedor?.proveedor_id || 'N/A' }}</p>
            </div>
            <div class="text-center p-2 bg-gray-50 rounded">
              <p class="text-gray-500">Estado</p>
              <p class="font-medium">
                {{ (proveedor?.deuda_pendiente || 0) > 0 ? 'Con Deuda' : 'Al Día' }}
              </p>
            </div>
          </div>
        </div>

        <!-- Pagos recientes -->
        <div v-if="proveedor?.pagos && proveedor.pagos.length > 0" class="pt-4 border-t">
          <h4 class="text-sm font-medium text-gray-500 mb-2">Pagos Recientes</h4>
          <div class="space-y-2 max-h-32 overflow-y-auto">
            <div
              v-for="pago in proveedor.pagos.slice(0, 3)"
              :key="pago.id_pago_proveedor"
              class="flex justify-between items-center p-2 bg-gray-50 rounded text-sm"
            >
              <span>${{ pago.monto.toLocaleString('es-CO', { minimumFractionDigits: 2 }) }}</span>
              <span class="text-gray-500">{{ new Date(pago.fecha).toLocaleDateString('es-CO') }}</span>
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
          v-if="proveedor"
          @click="$emit('edit', proveedor)"
          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg"
        >
          Editar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { Proveedor } from '@/types';

interface Props {
  isOpen: boolean;
  proveedor?: Proveedor | null;
  showStats?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  showStats: true
});

const emit = defineEmits<{
  close: [];
  edit: [proveedor: Proveedor];
}>();

// Función para obtener el estado de la deuda
const getDeudaStatus = (deuda?: number): string => {
  if (!deuda || deuda === 0) return 'Al Día';
  if (deuda <= 100000) return 'Deuda Baja';
  return 'Deuda Alta';
};
</script>
