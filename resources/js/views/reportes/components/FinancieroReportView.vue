<template>
  <div class="report-content">
    <!-- Resumen -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="metric-box">
        <div class="metric-label">Ingresos</div>
        <div class="metric-value text-green-600">${{ formatCurrency(reporte.resumen.total_ingresos) }}</div>
      </div>
      <div class="metric-box">
        <div class="metric-label">Egresos</div>
        <div class="metric-value text-red-600">${{ formatCurrency(reporte.resumen.total_egresos) }}</div>
      </div>
      <div class="metric-box">
        <div class="metric-label">Utilidad Neta</div>
        <div class="metric-value" :class="reporte.resumen.utilidad_neta >= 0 ? 'text-green-600' : 'text-red-600'">
          ${{ formatCurrency(reporte.resumen.utilidad_neta) }}
        </div>
      </div>
      <div class="metric-box">
        <div class="metric-label">Margen</div>
        <div class="metric-value">{{ reporte.resumen.margen_ganancia.toFixed(1) }}%</div>
      </div>
    </div>

    <!-- Flujo de caja -->
    <div class="mb-6">
      <h4 class="text-lg font-semibold text-gray-900 mb-3">Flujo de Caja</h4>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="table-header">Fecha</th>
              <th class="table-header">Ingresos</th>
              <th class="table-header">Egresos</th>
              <th class="table-header">Saldo</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="flujo in reporte.flujo_caja" :key="flujo.fecha">
              <td class="table-cell">{{ formatDate(flujo.fecha) }}</td>
              <td class="table-cell text-green-600">${{ formatCurrency(flujo.ingresos) }}</td>
              <td class="table-cell text-red-600">${{ formatCurrency(flujo.egresos) }}</td>
              <td class="table-cell font-semibold" :class="flujo.saldo >= 0 ? 'text-green-600' : 'text-red-600'">
                ${{ formatCurrency(flujo.saldo) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Principales gastos -->
    <div>
      <h4 class="text-lg font-semibold text-gray-900 mb-3">Principales Gastos</h4>
      <div class="space-y-3">
        <div 
          v-for="gasto in reporte.principales_gastos" 
          :key="gasto.categoria"
          class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
        >
          <div>
            <div class="font-medium text-gray-900">{{ gasto.categoria }}</div>
            <div class="text-sm text-gray-600">{{ gasto.porcentaje }}% del total</div>
          </div>
          <div class="text-lg font-semibold text-red-600">
            ${{ formatCurrency(gasto.total) }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { ReporteFinanciero } from '@/types';

// Props
interface Props {
  reporte: ReporteFinanciero;
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
