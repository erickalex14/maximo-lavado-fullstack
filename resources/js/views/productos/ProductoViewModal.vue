<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
      <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">
          Detalles del {{ tipoLabel }}
        </h3>
      </div>

      <div class="px-6 py-4 space-y-4">
        <!-- Código (solo para automotriz) -->
        <div v-if="tipo === 'automotriz' && producto && 'codigo' in producto">
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Código
          </label>
          <p class="text-gray-900 font-mono">{{ producto.codigo || 'N/A' }}</p>
        </div>

        <!-- Nombre -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Nombre
          </label>
          <p class="text-gray-900 font-medium">{{ producto?.nombre || 'N/A' }}</p>
        </div>

        <!-- Descripción -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Descripción
          </label>
          <p class="text-gray-900">{{ producto?.descripcion || 'Sin descripción' }}</p>
        </div>

        <!-- Precio de Venta -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Precio de Venta
          </label>
          <p class="text-gray-900 font-medium text-lg">
            {{ producto?.precio_venta ? `$${producto.precio_venta.toLocaleString('es-CO', { minimumFractionDigits: 2 })}` : 'N/A' }}
          </p>
        </div>

        <!-- Stock -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Stock Disponible
          </label>
          <div class="flex items-center gap-2">
            <span class="text-gray-900 font-medium">{{ producto?.stock ?? 0 }}</span>
            <span
              :class="{
                'bg-green-100 text-green-800': (producto?.stock ?? 0) > 10,
                'bg-yellow-100 text-yellow-800': (producto?.stock ?? 0) > 0 && (producto?.stock ?? 0) <= 10,
                'bg-red-100 text-red-800': (producto?.stock ?? 0) === 0
              }"
              class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
            >
              {{ getStockStatus(producto?.stock) }}
            </span>
          </div>
        </div>

        <!-- Estado -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Estado
          </label>
          <span
            :class="{
              'bg-green-100 text-green-800': producto?.activo,
              'bg-red-100 text-red-800': !producto?.activo
            }"
            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
          >
            {{ producto?.activo ? 'Activo' : 'Inactivo' }}
          </span>
        </div>

        <!-- Información de Tipo -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Tipo de Producto
          </label>
          <span
            :class="{
              'bg-blue-100 text-blue-800': tipo === 'automotriz',
              'bg-purple-100 text-purple-800': tipo === 'despensa'
            }"
            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
          >
            {{ tipoLabel }}
          </span>
        </div>

        <!-- Fechas -->
        <div class="grid grid-cols-1 gap-4 pt-4 border-t">
          <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">
              Fecha de Registro
            </label>
            <p class="text-gray-900">
              {{ producto?.created_at ? new Date(producto.created_at).toLocaleDateString('es-CO') : 'N/A' }}
            </p>
          </div>
          <div v-if="producto?.updated_at && producto.updated_at !== producto.created_at">
            <label class="block text-sm font-medium text-gray-500 mb-1">
              Última Actualización
            </label>
            <p class="text-gray-900">
              {{ new Date(producto.updated_at).toLocaleDateString('es-CO') }}
            </p>
          </div>
        </div>

        <!-- Estadísticas adicionales -->
        <div v-if="showStats" class="pt-4 border-t">
          <h4 class="text-sm font-medium text-gray-500 mb-2">Estadísticas</h4>
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div class="text-center p-2 bg-gray-50 rounded">
              <p class="text-gray-500">Valor en Stock</p>
              <p class="font-medium">
                ${{ ((producto?.stock ?? 0) * (producto?.precio_venta ?? 0)).toLocaleString('es-CO', { minimumFractionDigits: 2 }) }}
              </p>
            </div>
            <div class="text-center p-2 bg-gray-50 rounded">
              <p class="text-gray-500">ID</p>
              <p class="font-medium font-mono">
                {{ 
                  tipo === 'automotriz' && producto && 'producto_automotriz_id' in producto
                    ? producto.producto_automotriz_id 
                    : tipo === 'despensa' && producto && 'producto_despensa_id' in producto
                    ? producto.producto_despensa_id
                    : 'N/A' 
                }}
              </p>
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
          @click="$emit('edit')"
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
import type { ProductoAutomotriz, ProductoDespensa } from '@/types';

interface Props {
  isOpen: boolean;
  tipo: 'automotriz' | 'despensa';
  producto?: ProductoAutomotriz | ProductoDespensa | null;
  showStats?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  showStats: true
});

const emit = defineEmits<{
  close: [];
  edit: [];
}>();

// Computed properties
const tipoLabel = computed(() => 
  props.tipo === 'automotriz' ? 'Producto Automotriz' : 'Producto de Despensa'
);

// Función para obtener el estado del stock
const getStockStatus = (stock?: number): string => {
  if (!stock || stock === 0) return 'Sin Stock';
  if (stock <= 10) return 'Stock Bajo';
  return 'Stock Disponible';
};
</script>
