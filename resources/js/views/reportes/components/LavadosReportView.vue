<template>
  <div class="report-content">
    <!-- Resumen -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="metric-box">
        <div class="metric-label">Total Lavados</div>
        <div class="metric-value">{{ reporte.resumen.total_lavados }}</div>
      </div>
      <div class="metric-box">
        <div class="metric-label">Ingresos</div>
        <div class="metric-value">${{ formatCurrency(reporte.resumen.total_ingresos) }}</div>
      </div>
      <div class="metric-box">
        <div class="metric-label">Promedio</div>
        <div class="metric-value">${{ formatCurrency(reporte.resumen.lavado_promedio) }}</div>
      </div>
      <div class="metric-box">
        <div class="metric-label">Crecimiento</div>
        <div class="metric-value" :class="getGrowthClass(reporte.resumen.crecimiento_periodo)">
          {{ reporte.resumen.crecimiento_periodo >= 0 ? '+' : '' }}{{ reporte.resumen.crecimiento_periodo.toFixed(1) }}%
        </div>
      </div>
    </div>

    <!-- Lavados por día -->
    <div class="mb-6">
      <h4 class="text-lg font-semibold text-gray-900 mb-3">Lavados por Día</h4>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="table-header">Fecha</th>
              <th class="table-header">Cantidad</th>
              <th class="table-header">Total</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="lavado in reporte.lavados_por_dia" :key="lavado.fecha">
              <td class="table-cell">{{ formatDate(lavado.fecha) }}</td>
              <td class="table-cell">{{ lavado.cantidad }}</td>
              <td class="table-cell font-semibold">${{ formatCurrency(lavado.total) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Tipos más solicitados -->
    <div class="mb-6">
      <h4 class="text-lg font-semibold text-gray-900 mb-3">Tipos Más Solicitados</h4>
      <div class="space-y-3">
        <div 
          v-for="tipo in reporte.tipos_mas_solicitados" 
          :key="tipo.tipo_lavado"
          class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
        >
          <div>
            <div class="font-medium text-gray-900">{{ tipo.tipo_lavado }}</div>
            <div class="text-sm text-gray-600">{{ tipo.cantidad }} servicios</div>
          </div>
          <div class="text-lg font-semibold text-gray-900">
            ${{ formatCurrency(tipo.total) }}
          </div>
        </div>
      </div>
    </div>

    <!-- Performance de empleados -->
    <div>
      <h4 class="text-lg font-semibold text-gray-900 mb-3">Performance de Empleados</h4>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="table-header">Empleado</th>
              <th class="table-header">Lavados</th>
              <th class="table-header">Total Generado</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="empleado in reporte.empleados_performance" :key="empleado.empleado">
              <td class="table-cell font-medium">{{ empleado.empleado }}</td>
              <td class="table-cell">{{ empleado.lavados_realizados }}</td>
              <td class="table-cell font-semibold">${{ formatCurrency(empleado.total_generado) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { ReporteLavados } from '@/types';

// Props
interface Props {
  reporte: ReporteLavados;
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
