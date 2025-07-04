<template>
  <div class="space-y-6">
    <!-- Filtros de período -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Período de Análisis</h3>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Inicio</label>
          <input
            v-model="balanceFilters.fecha_inicio"
            @change="loadBalance"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Fin</label>
          <input
            v-model="balanceFilters.fecha_fin"
            @change="loadBalance"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>
        <div class="flex items-end">
          <button
            @click="setQuickPeriod('mes')"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Este Mes
          </button>
        </div>
        <div class="flex items-end">
          <button
            @click="setQuickPeriod('año')"
            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Este Año
          </button>
        </div>
      </div>
    </div>

    <!-- Resumen Financiero -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <ArrowTrendingUpIcon class="h-8 w-8 text-green-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Ingresos</p>
            <p class="text-2xl font-bold text-green-600">
              ${{ formatCurrency(balanceData.total_ingresos || 0) }}
            </p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <ArrowTrendingDownIcon class="h-8 w-8 text-red-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Egresos</p>
            <p class="text-2xl font-bold text-red-600">
              ${{ formatCurrency((balanceData.total_egresos || 0) + (balanceData.total_gastos_generales || 0)) }}
            </p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <CalculatorIcon 
              class="h-8 w-8" 
              :class="balanceNeto >= 0 ? 'text-green-600' : 'text-red-600'" 
            />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Balance Neto</p>
            <p 
              class="text-2xl font-bold" 
              :class="balanceNeto >= 0 ? 'text-green-600' : 'text-red-600'"
            >
              ${{ formatCurrency(balanceNeto) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Desglose Detallado -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Desglose por Categorías -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Desglose por Categorías</h3>
        
        <!-- Ingresos por fuente -->
        <div class="mb-6">
          <h4 class="text-md font-medium text-green-600 mb-2">Ingresos por Fuente</h4>
          <div v-if="balanceData.ingresos_por_fuente && balanceData.ingresos_por_fuente.length > 0" class="space-y-2">
            <div 
              v-for="item in balanceData.ingresos_por_fuente" 
              :key="item.fuente"
              class="flex justify-between items-center p-2 bg-green-50 rounded"
            >
              <span class="text-sm text-gray-700">{{ item.fuente }}</span>
              <span class="text-sm font-medium text-green-600">
                ${{ formatCurrency(item.total) }}
              </span>
            </div>
          </div>
          <div v-else class="text-sm text-gray-500 italic">
            No hay datos de ingresos para el período seleccionado
          </div>
        </div>

        <!-- Egresos por destino -->
        <div class="mb-6">
          <h4 class="text-md font-medium text-red-600 mb-2">Egresos por Destino</h4>
          <div v-if="balanceData.egresos_por_destino && balanceData.egresos_por_destino.length > 0" class="space-y-2">
            <div 
              v-for="item in balanceData.egresos_por_destino" 
              :key="item.destino"
              class="flex justify-between items-center p-2 bg-red-50 rounded"
            >
              <span class="text-sm text-gray-700">{{ item.destino }}</span>
              <span class="text-sm font-medium text-red-600">
                ${{ formatCurrency(item.total) }}
              </span>
            </div>
          </div>
          <div v-else class="text-sm text-gray-500 italic">
            No hay datos de egresos para el período seleccionado
          </div>
        </div>

        <!-- Gastos por categoría -->
        <div>
          <h4 class="text-md font-medium text-orange-600 mb-2">Gastos por Categoría</h4>
          <div v-if="balanceData.gastos_por_categoria && balanceData.gastos_por_categoria.length > 0" class="space-y-2">
            <div 
              v-for="item in balanceData.gastos_por_categoria" 
              :key="item.categoria"
              class="flex justify-between items-center p-2 bg-orange-50 rounded"
            >
              <span class="text-sm text-gray-700">{{ item.categoria }}</span>
              <span class="text-sm font-medium text-orange-600">
                ${{ formatCurrency(item.total) }}
              </span>
            </div>
          </div>
          <div v-else class="text-sm text-gray-500 italic">
            No hay datos de gastos para el período seleccionado
          </div>
        </div>
      </div>

      <!-- Estadísticas Adicionales -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Estadísticas del Período</h3>
        
        <div class="space-y-4">
          <!-- Contadores -->
          <div class="grid grid-cols-2 gap-4">
            <div class="text-center p-3 bg-gray-50 rounded">
              <p class="text-sm text-gray-600">Total Ingresos</p>
              <p class="text-lg font-bold text-gray-900">{{ balanceData.conteo_ingresos || 0 }}</p>
            </div>
            <div class="text-center p-3 bg-gray-50 rounded">
              <p class="text-sm text-gray-600">Total Egresos</p>
              <p class="text-lg font-bold text-gray-900">
                {{ (balanceData.conteo_egresos || 0) + (balanceData.conteo_gastos_generales || 0) }}
              </p>
            </div>
          </div>

          <!-- Promedios -->
          <div class="border-t pt-4">
            <h4 class="text-md font-medium text-gray-700 mb-2">Promedios</h4>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Promedio por Ingreso:</span>
                <span class="text-sm font-medium">
                  ${{ formatCurrency(averageIngreso) }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Promedio por Egreso:</span>
                <span class="text-sm font-medium">
                  ${{ formatCurrency(averageEgreso) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Tendencia -->
          <div class="border-t pt-4">
            <h4 class="text-md font-medium text-gray-700 mb-2">Tendencia</h4>
            <div class="text-center p-4 rounded" :class="balanceNeto >= 0 ? 'bg-green-50' : 'bg-red-50'">
              <div class="flex items-center justify-center gap-2">
                <ArrowTrendingUpIcon v-if="balanceNeto >= 0" class="h-5 w-5 text-green-600" />
                <ArrowTrendingDownIcon v-else class="h-5 w-5 text-red-600" />
                <span :class="balanceNeto >= 0 ? 'text-green-600' : 'text-red-600'" class="font-medium">
                  {{ balanceNeto >= 0 ? 'Positiva' : 'Negativa' }}
                </span>
              </div>
              <p class="text-sm text-gray-600 mt-1">
                {{ balanceNeto >= 0 ? 'Los ingresos superan los egresos' : 'Los egresos superan los ingresos' }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useFinanzasStore } from '@/stores/finanzas';

// Iconos
import { 
  ArrowTrendingUpIcon, 
  ArrowTrendingDownIcon, 
  CalculatorIcon 
} from '@heroicons/vue/24/outline';

// Store
const finanzasStore = useFinanzasStore();

// Estado local
const balanceFilters = ref({
  fecha_inicio: '',
  fecha_fin: '',
});

// Computed
const balanceData = computed(() => finanzasStore.balance || {});
const isLoading = computed(() => finanzasStore.isLoading);

const balanceNeto = computed(() => {
  const ingresos = balanceData.value.total_ingresos || 0;
  const egresos = balanceData.value.total_egresos || 0;
  const gastos = balanceData.value.total_gastos_generales || 0;
  return ingresos - egresos - gastos;
});

const averageIngreso = computed(() => {
  const total = balanceData.value.total_ingresos || 0;
  const count = balanceData.value.conteo_ingresos || 0;
  return count > 0 ? total / count : 0;
});

const averageEgreso = computed(() => {
  const totalEgresos = balanceData.value.total_egresos || 0;
  const totalGastos = balanceData.value.total_gastos_generales || 0;
  const countEgresos = balanceData.value.conteo_egresos || 0;
  const countGastos = balanceData.value.conteo_gastos_generales || 0;
  const totalCount = countEgresos + countGastos;
  return totalCount > 0 ? (totalEgresos + totalGastos) / totalCount : 0;
});

// Métodos
const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
};

const setQuickPeriod = (period: 'mes' | 'año') => {
  const now = new Date();
  const currentYear = now.getFullYear();
  const currentMonth = now.getMonth();

  if (period === 'mes') {
    balanceFilters.value.fecha_inicio = new Date(currentYear, currentMonth, 1).toISOString().split('T')[0];
    balanceFilters.value.fecha_fin = new Date(currentYear, currentMonth + 1, 0).toISOString().split('T')[0];
  } else if (period === 'año') {
    balanceFilters.value.fecha_inicio = new Date(currentYear, 0, 1).toISOString().split('T')[0];
    balanceFilters.value.fecha_fin = new Date(currentYear, 11, 31).toISOString().split('T')[0];
  }

  loadBalance();
};

const loadBalance = async () => {
  try {
    await finanzasStore.fetchBalance(balanceFilters.value);
  } catch (error) {
    console.error('Error loading balance:', error);
  }
};

// Lifecycle
onMounted(() => {
  // Establecer período por defecto (este mes)
  setQuickPeriod('mes');
});
</script>
