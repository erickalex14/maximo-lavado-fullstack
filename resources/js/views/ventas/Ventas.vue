<template>
  <div class="ventas-container">
    <!-- Header con métricas -->
    <div class="page-header">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-50">
            Ventas
          </h1>
          <p class="text-surface-600 dark:text-surface-400 mt-1">
            Gestión unificada de ventas automotrices y de despensa
          </p>
        </div>
        
        <div class="flex gap-4">
          <button
            @click="showCreateAutomotrizModal = true"
            class="btn btn-primary"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Venta Automotriz
          </button>
          
          <button
            @click="showCreateDespensaModal = true"
            class="btn btn-secondary"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Venta Despensa
          </button>
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
            <p class="text-2xl font-bold text-blue-900">{{ metricas.ventas_automotrices.total }}</p>
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
            <p class="text-2xl font-bold text-orange-900">{{ metricas.ventas_despensa.total }}</p>
          </div>
          <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs de navegación -->
    <div class="bg-white dark:bg-surface-800 rounded-lg shadow-sm border border-surface-200 dark:border-surface-700">
      <!-- Tab headers -->
      <div class="border-b border-surface-200 dark:border-surface-700">
        <nav class="flex space-x-8 px-6">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="activeTab = tab.key"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
              activeTab === tab.key
                ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                : 'border-transparent text-surface-500 hover:text-surface-700 hover:border-surface-300 dark:text-surface-400 dark:hover:text-surface-300'
            ]"
          >
            <span class="flex items-center">
              <span v-html="tab.icon" class="w-5 h-5 mr-2"></span>
              {{ tab.label }}
            </span>
          </button>
        </nav>
      </div>

      <!-- Tab content -->
      <div class="p-6">
        <!-- Vista General (Unificada) -->
        <div v-if="activeTab === 'general'">
          <VentasGeneralTab />
        </div>

        <!-- Ventas Automotrices -->
        <div v-if="activeTab === 'automotrices'">
          <VentasAutomotricesTab />
        </div>

        <!-- Ventas de Despensa -->
        <div v-if="activeTab === 'despensa'">
          <VentasDespensaTab />
        </div>
      </div>
    </div>

    <!-- Modales -->
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
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useVentaStore } from '@/stores/venta';
import VentasGeneralTab from './components/VentasGeneralTab.vue';
import VentasAutomotricesTab from './components/VentasAutomotricesTab.vue';
import VentasDespensaTab from './components/VentasDespensaTab.vue';
import VentaAutomotrizModal from './VentaAutomotrizModal.vue';
import VentaDespensaModal from './VentaDespensaModal.vue';
import type { VentaProductoAutomotriz, VentaProductoDespensa } from '@/types';

// Store
const ventaStore = useVentaStore();

// State
const activeTab = ref('general');
const showCreateAutomotrizModal = ref(false);
const showCreateDespensaModal = ref(false);

// Computed
const metricas = computed(() => ventaStore.metricas);
const loading = computed(() => ventaStore.loading || ventaStore.loadingMetricas);

// Tabs configuration
const tabs = [
  {
    key: 'general',
    label: 'Vista General',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
    </svg>`
  },
  {
    key: 'automotrices',
    label: 'Automotrices',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
    </svg>`
  },
  {
    key: 'despensa',
    label: 'Despensa',
    icon: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
    </svg>`
  }
];

// Methods
function formatCurrency(value: number): string {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
}

function handleVentaAutomotrizSaved(venta: VentaProductoAutomotriz) {
  showCreateAutomotrizModal.value = false;
  // Las métricas se actualizarán automáticamente cuando se recargue la lista en el store
  ventaStore.fetchMetricas();
}

function handleVentaDespensaSaved(venta: VentaProductoDespensa) {
  showCreateDespensaModal.value = false;
  // Las métricas se actualizarán automáticamente cuando se recargue la lista en el store
  ventaStore.fetchMetricas();
}

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

/* Dark mode styles - if needed */
@media (prefers-color-scheme: dark) {
  .ventas-container {
    background-color: #111827;
  }

  .metric-card {
    border-color: #374151;
  }
}
</style>
