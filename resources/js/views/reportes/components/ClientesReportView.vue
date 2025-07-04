<template>
  <div class="report-content">
    <!-- Resumen -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="metric-box">
        <div class="metric-label">Total Clientes</div>
        <div class="metric-value">{{ reporte.resumen.total_clientes }}</div>
      </div>
      <div class="metric-box">
        <div class="metric-label">Activos</div>
        <div class="metric-value">{{ reporte.resumen.clientes_activos }}</div>
      </div>
      <div class="metric-box">
        <div class="metric-label">Ticket Promedio</div>
        <div class="metric-value">${{ formatCurrency(reporte.resumen.ticket_promedio) }}</div>
      </div>
      <div class="metric-box">
        <div class="metric-label">Frecuencia</div>
        <div class="metric-value">{{ reporte.resumen.frecuencia_promedio.toFixed(1) }}</div>
      </div>
    </div>

    <!-- Clientes Top -->
    <div class="mb-6">
      <h4 class="text-lg font-semibold text-gray-900 mb-3">Top Clientes</h4>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="table-header">Cliente</th>
              <th class="table-header">Total Gastado</th>
              <th class="table-header">Visitas</th>
              <th class="table-header">Última Visita</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="cliente in reporte.clientes_top" :key="cliente.cliente">
              <td class="table-cell font-medium">{{ cliente.cliente }}</td>
              <td class="table-cell font-semibold">${{ formatCurrency(cliente.total_gastado) }}</td>
              <td class="table-cell">{{ cliente.visitas }}</td>
              <td class="table-cell">{{ formatDate(cliente.ultima_visita) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Segmentación -->
    <div>
      <h4 class="text-lg font-semibold text-gray-900 mb-3">Segmentación</h4>
      <div class="space-y-3">
        <div 
          v-for="segmento in reporte.segmentacion" 
          :key="segmento.segmento"
          class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
        >
          <div>
            <div class="font-medium text-gray-900">{{ segmento.segmento }}</div>
            <div class="text-sm text-gray-600">{{ segmento.porcentaje }}% del total</div>
          </div>
          <div class="text-lg font-semibold text-gray-900">
            {{ segmento.cantidad }} clientes
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { ReporteClientes } from '@/types';

interface Props {
  reporte: ReporteClientes;
}

defineProps<Props>();

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
