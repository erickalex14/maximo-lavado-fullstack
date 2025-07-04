<template>
  <div class="report-content">
    <!-- Resumen -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="metric-box">
        <div class="metric-label">Total Ventas</div>
        <div class="metric-value">${{ formatCurrency(reporte.resumen.total_ventas) }}</div>
      </div>
      <div class="metric-box">
        <div class="metric-label">Unidades</div>
        <div class="metric-value">{{ reporte.resumen.total_unidades }}</div>
      </div>
      <div class="metric-box">
        <div class="metric-label">Ticket Promedio</div>
        <div class="metric-value">${{ formatCurrency(reporte.resumen.ticket_promedio) }}</div>
      </div>
      <div class="metric-box">
        <div class="metric-label">Crecimiento</div>
        <div class="metric-value" :class="getGrowthClass(reporte.resumen.crecimiento_periodo)">
          {{ reporte.resumen.crecimiento_periodo >= 0 ? '+' : '' }}{{ reporte.resumen.crecimiento_periodo.toFixed(1) }}%
        </div>
      </div>
    </div>

    <!-- Ventas por día -->
    <div class="mb-6">
      <h4 class="text-lg font-semibold text-gray-900 mb-3">Ventas por Día</h4>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="table-header">Fecha</th>
              <th class="table-header">Total</th>
              <th class="table-header">Cantidad</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="venta in reporte.ventas_por_dia" :key="venta.fecha">
              <td class="table-cell">{{ formatDate(venta.fecha) }}</td>
              <td class="table-cell font-semibold">${{ formatCurrency(venta.total) }}</td>
              <td class="table-cell">{{ venta.cantidad }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Productos más vendidos -->
    <div class="mb-6">
      <h4 class="text-lg font-semibold text-gray-900 mb-3">Productos Más Vendidos</h4>
      <div class="space-y-3">
        <div 
          v-for="producto in reporte.productos_mas_vendidos" 
          :key="producto.producto"
          class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
        >
          <div>
            <div class="font-medium text-gray-900">{{ producto.producto }}</div>
            <div class="text-sm text-gray-600">{{ producto.cantidad }} unidades</div>
          </div>
          <div class="text-lg font-semibold text-gray-900">
            ${{ formatCurrency(producto.total) }}
          </div>
        </div>
      </div>
    </div>

    <!-- Ventas por categoría -->
    <div>
      <h4 class="text-lg font-semibold text-gray-900 mb-3">Ventas por Categoría</h4>
      <div class="space-y-3">
        <div 
          v-for="categoria in reporte.ventas_por_categoria" 
          :key="categoria.categoria"
          class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
        >
          <div class="flex items-center">
            <div class="mr-3">
              <div class="font-medium text-gray-900">{{ categoria.categoria }}</div>
              <div class="text-sm text-gray-600">{{ categoria.porcentaje }}% del total</div>
            </div>
          </div>
          <div class="text-lg font-semibold text-gray-900">
            ${{ formatCurrency(categoria.total) }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { ReporteVentas } from '@/types';

// Props
interface Props {
  reporte: ReporteVentas;
}

defineProps<Props>();

// Methods
function formatCurrency(value: number): string {
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('es-CO');
}

function getGrowthClass(growth: number): string {
  if (growth > 0) return 'text-green-600';
  if (growth < 0) return 'text-red-600';
  return 'text-gray-600';
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
