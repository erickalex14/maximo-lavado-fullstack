<template>
  <div class="report-content">
    <!-- Resumen -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
      <div class="metric-box">
        <div class="metric-label">Total Productos</div>
        <div class="metric-value">{{ reporte.resumen.total_productos }}</div>
      </div>
      <div class="metric-box">
        <div class="metric-label">Agotados</div>
        <div class="metric-value text-red-600">{{ reporte.resumen.productos_agotados }}</div>
      </div>
      <div class="metric-box">
        <div class="metric-label">Valor Inventario</div>
        <div class="metric-value">${{ formatCurrency(reporte.resumen.valor_inventario) }}</div>
      </div>
    </div>

    <!-- Productos más vendidos -->
    <div class="mb-6">
      <h4 class="text-lg font-semibold text-gray-900 mb-3">Productos Más Vendidos</h4>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="table-header">Producto</th>
              <th class="table-header">Categoría</th>
              <th class="table-header">Vendidos</th>
              <th class="table-header">Stock</th>
              <th class="table-header">Valor</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="producto in reporte.productos_mas_vendidos" :key="producto.producto">
              <td class="table-cell font-medium">{{ producto.producto }}</td>
              <td class="table-cell">{{ producto.categoria }}</td>
              <td class="table-cell">{{ producto.cantidad_vendida }}</td>
              <td class="table-cell" :class="producto.stock_actual <= 5 ? 'text-red-600' : 'text-green-600'">
                {{ producto.stock_actual }}
              </td>
              <td class="table-cell font-semibold">${{ formatCurrency(producto.valor_total) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Productos bajo stock -->
    <div v-if="reporte.productos_bajo_stock.length > 0" class="mb-6">
      <h4 class="text-lg font-semibold text-gray-900 mb-3">Productos Bajo Stock</h4>
      <div class="space-y-3">
        <div 
          v-for="producto in reporte.productos_bajo_stock" 
          :key="producto.producto"
          class="flex items-center justify-between p-3 bg-red-50 border border-red-200 rounded-lg"
        >
          <div>
            <div class="font-medium text-gray-900">{{ producto.producto }}</div>
            <div class="text-sm text-gray-600">{{ producto.categoria }}</div>
          </div>
          <div class="text-right">
            <div class="text-lg font-semibold text-red-600">{{ producto.stock_actual }}</div>
            <div class="text-sm text-gray-600">Mín: {{ producto.stock_minimo }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { ReporteProductos } from '@/types';

interface Props {
  reporte: ReporteProductos;
}

defineProps<Props>();

function formatCurrency(value: number): string {
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
}
</script>

<style scoped>
.report-content {
  padding: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

.metric-box {
  text-align: center;
  padding: 1rem;
  background-color: #f9fafb;
  border-radius: 0.5rem;
  border: 1px solid #e5e7eb;
}

.metric-label {
  font-size: 0.875rem;
  color: #6b7280;
  margin-bottom: 0.5rem;
}

.metric-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
}

.table-header {
  padding: 0.75rem 1.5rem;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 500;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.table-cell {
  padding: 1rem 1.5rem;
  white-space: nowrap;
  font-size: 0.875rem;
  color: #111827;
}
</style>
