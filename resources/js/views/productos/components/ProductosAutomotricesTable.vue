<template>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Código
          </th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Producto
          </th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Precio
          </th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Stock
          </th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Estado
          </th>
          <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
            Valor Total
          </th>
          <th scope="col" class="relative px-6 py-3">
            <span class="sr-only">Acciones</span>
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr v-if="loading" class="animate-pulse">
          <td colspan="7" class="px-6 py-4 text-center text-gray-500">
            Cargando productos automotrices...
          </td>
        </tr>
        <tr v-else-if="!productos.length">
          <td colspan="7" class="px-6 py-4 text-center text-gray-500">
            No se encontraron productos automotrices
          </td>
        </tr>
        <tr 
          v-else
          v-for="producto in productos" 
          :key="producto.producto_automotriz_id"
          class="hover:bg-gray-50"
        >
          <!-- Código -->
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-mono text-gray-900">{{ producto.codigo }}</div>
          </td>

          <!-- Producto -->
          <td class="px-6 py-4">
            <div class="text-sm font-medium text-gray-900">{{ producto.nombre }}</div>
            <div class="text-sm text-gray-500 max-w-xs truncate">{{ producto.descripcion || 'Sin descripción' }}</div>
          </td>

          <!-- Precio -->
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-medium text-gray-900">
              ${{ producto.precio_venta.toLocaleString('es-CO', { minimumFractionDigits: 2 }) }}
            </div>
          </td>

          <!-- Stock -->
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="flex items-center gap-2">
              <span class="text-sm text-gray-900">{{ producto.stock }}</span>
              <span
                :class="{
                  'bg-green-100 text-green-800': producto.stock > 10,
                  'bg-yellow-100 text-yellow-800': producto.stock > 0 && producto.stock <= 10,
                  'bg-red-100 text-red-800': producto.stock === 0
                }"
                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
              >
                {{ getStockStatus(producto.stock) }}
              </span>
            </div>
          </td>

          <!-- Estado -->
          <td class="px-6 py-4 whitespace-nowrap">
            <span
              :class="{
                'bg-green-100 text-green-800': producto.activo,
                'bg-red-100 text-red-800': !producto.activo
              }"
              class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
            >
              {{ producto.activo ? 'Activo' : 'Inactivo' }}
            </span>
          </td>

          <!-- Valor Total en Stock -->
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-medium text-gray-900">
              ${{ (producto.stock * producto.precio_venta).toLocaleString('es-CO', { minimumFractionDigits: 2 }) }}
            </div>
          </td>

          <!-- Acciones -->
          <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <div class="flex justify-end gap-2">
              <button
                @click="$emit('view', producto)"
                class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50"
                title="Ver detalles"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
              <button
                @click="$emit('edit', producto)"
                class="text-indigo-600 hover:text-indigo-900 p-1 rounded hover:bg-indigo-50"
                title="Editar"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>
              <button
                @click="$emit('delete', producto)"
                class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50"
                title="Eliminar"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Paginación -->
    <div v-if="pagination && pagination.total > pagination.per_page" class="px-6 py-3 bg-gray-50 border-t">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Mostrando {{ pagination.from }} a {{ pagination.to }} de {{ pagination.total }} resultados
        </div>
        <div class="flex items-center gap-2">
          <button
            @click="$emit('changePage', pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="px-3 py-1 text-sm border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Anterior
          </button>
          <span class="text-sm text-gray-600">
            {{ pagination.current_page }} de {{ pagination.last_page }}
          </span>
          <button
            @click="$emit('changePage', pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="px-3 py-1 text-sm border rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Siguiente
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { ProductoAutomotriz } from '@/types';

interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

interface Props {
  productos: ProductoAutomotriz[];
  loading?: boolean;
  pagination?: PaginationMeta;
}

defineProps<Props>();

const emit = defineEmits<{
  view: [producto: ProductoAutomotriz];
  edit: [producto: ProductoAutomotriz];
  delete: [producto: ProductoAutomotriz];
  changePage: [page: number];
}>();

// Función para obtener el estado del stock
const getStockStatus = (stock: number): string => {
  if (stock === 0) return 'Sin Stock';
  if (stock <= 10) return 'Bajo';
  return 'Disponible';
};
</script>
