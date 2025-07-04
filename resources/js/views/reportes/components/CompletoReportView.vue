<template>
  <div class="report-content">
    <!-- Información del período -->
    <div class="mb-6 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
      <h4 class="text-lg font-semibold text-indigo-900 mb-2">Período del Reporte</h4>
      <p class="text-indigo-700">
        Desde {{ formatDate(reporte.periodo.fecha_inicio) }} hasta {{ formatDate(reporte.periodo.fecha_fin) }}
      </p>
    </div>

    <!-- Resumen General -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
      <div class="metric-box bg-blue-50 border-blue-200">
        <div class="metric-label">Ventas Totales</div>
        <div class="metric-value text-blue-700">${{ formatCurrency(reporte.ventas.resumen.total_ventas) }}</div>
      </div>
      <div class="metric-box bg-cyan-50 border-cyan-200">
        <div class="metric-label">Lavados</div>
        <div class="metric-value text-cyan-700">{{ reporte.lavados.resumen.total_lavados }}</div>
      </div>
      <div class="metric-box bg-green-50 border-green-200">
        <div class="metric-label">Utilidad</div>
        <div class="metric-value text-green-700">${{ formatCurrency(reporte.financiero.resumen.utilidad_neta) }}</div>
      </div>
      <div class="metric-box bg-purple-50 border-purple-200">
        <div class="metric-label">Clientes</div>
        <div class="metric-value text-purple-700">{{ reporte.clientes.resumen.total_clientes }}</div>
      </div>
    </div>

    <!-- Secciones de reportes -->
    <div class="space-y-8">
      <!-- Ventas -->
      <div class="section">
        <h3 class="section-title">📊 Resumen de Ventas</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="summary-card">
            <div class="summary-label">Total Unidades</div>
            <div class="summary-value">{{ reporte.ventas.resumen.total_unidades }}</div>
          </div>
          <div class="summary-card">
            <div class="summary-label">Ticket Promedio</div>
            <div class="summary-value">${{ formatCurrency(reporte.ventas.resumen.ticket_promedio) }}</div>
          </div>
          <div class="summary-card">
            <div class="summary-label">Crecimiento</div>
            <div class="summary-value" :class="getGrowthClass(reporte.ventas.resumen.crecimiento_periodo)">
              {{ reporte.ventas.resumen.crecimiento_periodo >= 0 ? '+' : '' }}{{ reporte.ventas.resumen.crecimiento_periodo.toFixed(1) }}%
            </div>
          </div>
        </div>
      </div>

      <!-- Lavados -->
      <div class="section">
        <h3 class="section-title">🚿 Servicios de Lavado</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="summary-card">
            <div class="summary-label">Ingresos por Lavados</div>
            <div class="summary-value">${{ formatCurrency(reporte.lavados.resumen.total_ingresos) }}</div>
          </div>
          <div class="summary-card">
            <div class="summary-label">Promedio por Lavado</div>
            <div class="summary-value">${{ formatCurrency(reporte.lavados.resumen.lavado_promedio) }}</div>
          </div>
          <div class="summary-card">
            <div class="summary-label">Crecimiento</div>
            <div class="summary-value" :class="getGrowthClass(reporte.lavados.resumen.crecimiento_periodo)">
              {{ reporte.lavados.resumen.crecimiento_periodo >= 0 ? '+' : '' }}{{ reporte.lavados.resumen.crecimiento_periodo.toFixed(1) }}%
            </div>
          </div>
        </div>
      </div>

      <!-- Financiero -->
      <div class="section">
        <h3 class="section-title">💰 Estado Financiero</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="summary-card">
            <div class="summary-label">Ingresos</div>
            <div class="summary-value text-green-600">${{ formatCurrency(reporte.financiero.resumen.total_ingresos) }}</div>
          </div>
          <div class="summary-card">
            <div class="summary-label">Egresos</div>
            <div class="summary-value text-red-600">${{ formatCurrency(reporte.financiero.resumen.total_egresos) }}</div>
          </div>
          <div class="summary-card">
            <div class="summary-label">Utilidad Neta</div>
            <div class="summary-value" :class="reporte.financiero.resumen.utilidad_neta >= 0 ? 'text-green-600' : 'text-red-600'">
              ${{ formatCurrency(reporte.financiero.resumen.utilidad_neta) }}
            </div>
          </div>
          <div class="summary-card">
            <div class="summary-label">Margen</div>
            <div class="summary-value">{{ reporte.financiero.resumen.margen_ganancia.toFixed(1) }}%</div>
          </div>
        </div>
      </div>

      <!-- Top Productos y Clientes -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Top Productos -->
        <div class="section">
          <h3 class="section-title">🏆 Top Productos</h3>
          <div class="space-y-2">
            <div 
              v-for="(producto, index) in reporte.ventas.productos_mas_vendidos.slice(0, 5)" 
              :key="producto.producto"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
            >
              <div class="flex items-center">
                <div class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-sm font-bold mr-3">
                  {{ index + 1 }}
                </div>
                <div>
                  <div class="font-medium">{{ producto.producto }}</div>
                  <div class="text-sm text-gray-600">{{ producto.cantidad }} unidades</div>
                </div>
              </div>
              <div class="font-semibold">${{ formatCurrency(producto.total) }}</div>
            </div>
          </div>
        </div>

        <!-- Top Clientes -->
        <div class="section">
          <h3 class="section-title">👑 Top Clientes</h3>
          <div class="space-y-2">
            <div 
              v-for="(cliente, index) in reporte.clientes.clientes_top.slice(0, 5)" 
              :key="cliente.cliente"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
            >
              <div class="flex items-center">
                <div class="w-6 h-6 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center text-sm font-bold mr-3">
                  {{ index + 1 }}
                </div>
                <div>
                  <div class="font-medium">{{ cliente.cliente }}</div>
                  <div class="text-sm text-gray-600">{{ cliente.visitas }} visitas</div>
                </div>
              </div>
              <div class="font-semibold">${{ formatCurrency(cliente.total_gastado) }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { ReporteCompleto } from '@/types';

interface Props {
  reporte: ReporteCompleto;
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
  border-radius: 0.5rem;
  border: 1px solid;
}

.metric-label {
  font-size: 0.875rem;
  color: #6b7280;
  margin-bottom: 0.5rem;
}

.metric-value {
  font-size: 1.25rem;
  font-weight: 700;
}

.section {
  background-color: white;
  border-radius: 0.5rem;
  padding: 1.5rem;
  border: 1px solid #e5e7eb;
}

.section-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.summary-card {
  text-align: center;
  padding: 1rem;
  background-color: #f9fafb;
  border-radius: 0.375rem;
  border: 1px solid #e5e7eb;
}

.summary-label {
  font-size: 0.875rem;
  color: #6b7280;
  margin-bottom: 0.5rem;
}

.summary-value {
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
}
</style>
