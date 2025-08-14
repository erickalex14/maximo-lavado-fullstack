<template>
  <div class="ventas-container">
    <!-- Header con métricas -->
    <div class="page-header">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-surface-900">
            Ventas
          </h1>
          <p class="text-surface-600 mt-1">
            Gestión unificada de ventas automotrices y de despensa
          </p>
        </div>
        
        <div class="flex gap-4">
          <RouterLink
            :to="{ name: 'ventas.create' }"
            class="btn btn-primary"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Crear Venta
          </RouterLink>
        </div>
      </div>
    </div>

    <!-- Métricas Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" v-if="metricas">
      <div class="metric-card bg-primary-50 border-primary-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-primary-600 text-sm font-medium">Total Ventas</p>
            <p class="text-2xl font-bold text-primary-900">{{ metricas.total_ventas }}</p>
          </div>
          <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="metric-card bg-green-50 border-green-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-green-600 text-sm font-medium">Total Ingresos</p>
            <p class="text-2xl font-bold text-green-900">${{ formatCurrency(metricas.total_ingresos) }}</p>
          </div>
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="metric-card bg-blue-50 border-blue-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-blue-600 text-sm font-medium">Ventas Automotrices</p>
            <p class="text-2xl font-bold text-blue-900">{{ metricas.ventas_automotrices?.total ?? 0 }}</p>
          </div>
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </div>
        </div>
      </div>

      <div class="metric-card bg-orange-50 border-orange-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-orange-600 text-sm font-medium">Ventas Despensa</p>
            <p class="text-2xl font-bold text-orange-900">{{ metricas.ventas_despensa?.total ?? 0 }}</p>
          </div>
          <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Servicios (si aplica) -->
      <div v-if="metricas.servicios" class="metric-card bg-purple-50 border-purple-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-purple-600 text-sm font-medium">Servicios</p>
            <p class="text-2xl font-bold text-purple-900">{{ metricas.servicios?.total ?? 0 }}</p>
          </div>
          <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2v-5H3v5a2 2 0 002 2z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs de navegación -->
  <div class="bg-white rounded-lg shadow-sm border border-surface-200 p-6">
      <VentasGeneralTab />
    </div>

    <!-- Modales legacy temporalmente deshabilitados; usar página Crear Venta -->
    <!--
    <VentaAutomotrizModal
      v-model:visible="showCreateAutomotrizModal"
      :mode="'create'"
      @saved="handleVentaAutomotrizSaved"
    />

    <VentaDespensaModal
      v-model:visible="showCreateDespensaModal"
      :mode="'create'"
      @saved="handleVentaDespensaSaved"
    />
    -->
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useVentaStore } from '@/stores/venta';
import VentasGeneralTab from './components/VentasGeneralTab.vue';
import type { Venta } from '@/types';

// Store
const ventaStore = useVentaStore();

// State
// Tabs eliminadas: vista unificada

// Computed
const metricas = computed(() => ventaStore.metricas);
const loading = computed(() => ventaStore.loading || ventaStore.loadingMetricas);

// Configuración de tabs removida

// Methods
function formatCurrency(value: number): string {
  const num = typeof value === 'number' && !isNaN(value) ? value : 0;
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(num);
}

// function handleVentaAutomotrizSaved(venta: Venta) {
//   showCreateAutomotrizModal.value = false;
//   ventaStore.fetchMetricas();
// }
// function handleVentaDespensaSaved(venta: Venta) {
//   showCreateDespensaModal.value = false;
//   ventaStore.fetchMetricas();
// }

// Lifecycle
onMounted(async () => {
  await Promise.all([
    ventaStore.fetchMetricas(),
    ventaStore.fetchProductosDisponibles(),
    ventaStore.fetchClientes()
  ]);
});
</script>

<style scoped>
.ventas-container {
  min-height: 100vh;
  background-color: #f9fafb;
  padding: 1.5rem;
}

.page-header {
  margin-bottom: 2rem;
}

.metric-card {
  padding: 1.5rem;
  border-radius: 0.5rem;
  border: 1px solid #e5e7eb;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-weight: 500;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  border: none;
  cursor: pointer;
}

.btn-primary {
  background-color: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background-color: #2563eb;
}

.btn-secondary {
  background-color: #f59e0b;
  color: white;
}

.btn-secondary:hover {
  background-color: #d97706;
}

/* Modo claro forzado: se removieron estilos dark */
</style>
