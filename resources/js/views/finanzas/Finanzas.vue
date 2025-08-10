<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Finanzas</h1>
        <p class="text-gray-600 mt-1">Gestiona ingresos, egresos y balance general</p>
      </div>
      <div class="flex gap-3">
        <button
          @click="openCreateIngresoModal"
          class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center gap-2"
        >
          <PlusIcon class="h-5 w-5" />
          Nuevo Ingreso
        </button>
        <button
          @click="openCreateEgresoModal"
          class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center gap-2"
        >
          <MinusIcon class="h-5 w-5" />
          Nuevo Egreso
        </button>
        <button
          @click="openCreateGastoModal"
          class="bg-orange-600 hover:bg-orange-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center gap-2"
        >
          <DocumentTextIcon class="h-5 w-5" />
          Nuevo Gasto
        </button>
      </div>
    </div>

    <!-- Métricas Principales -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <ArrowTrendingUpIcon class="h-8 w-8 text-green-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Ingresos</p>
            <p class="text-2xl font-bold text-gray-900">
              ${{ formatCurrency(totalMontosIngresos) }}
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
            <p class="text-2xl font-bold text-gray-900">
              ${{ formatCurrency(totalMontosEgresos) }}
            </p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <DocumentTextIcon class="h-8 w-8 text-orange-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Gastos Generales</p>
            <p class="text-2xl font-bold text-gray-900">
              ${{ formatCurrency(totalMontosGastosGenerales) }}
            </p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <CalculatorIcon class="h-8 w-8" :class="balanceNeto >= 0 ? 'text-green-600' : 'text-red-600'" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Balance Neto</p>
            <p class="text-2xl font-bold" :class="balanceNeto >= 0 ? 'text-green-900' : 'text-red-900'">
              ${{ formatCurrency(balanceNeto) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtros Generales -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Fecha</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Inicio</label>
          <input
            v-model="globalFilters.fecha_inicio"
            @change="applyGlobalFilters"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Fin</label>
          <input
            v-model="globalFilters.fecha_fin"
            @change="applyGlobalFilters"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>
        <div class="flex items-end">
          <button
            @click="clearGlobalFilters"
            class="text-gray-600 hover:text-gray-800 font-medium"
          >
            Limpiar filtros
          </button>
        </div>
      </div>
    </div>

    <!-- Pestañas -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex">
          <button
            @click="activeTab = 'ingresos'"
            :class="[
              'py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200',
              activeTab === 'ingresos'
                ? 'border-green-500 text-green-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Ingresos ({{ totalIngresos }})
          </button>
          <button
            @click="activeTab = 'egresos'"
            :class="[
              'py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200',
              activeTab === 'egresos'
                ? 'border-red-500 text-red-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Egresos ({{ totalEgresos }})
          </button>
          <button
            @click="activeTab = 'gastos'"
            :class="[
              'py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200',
              activeTab === 'gastos'
                ? 'border-orange-500 text-orange-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Gastos Generales ({{ totalGastosGenerales }})
          </button>
          <button
            @click="activeTab = 'balance'"
            :class="[
              'py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200',
              activeTab === 'balance'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Balance y Reportes
          </button>
        </nav>
      </div>

      <!-- Contenido de las pestañas -->
      <div class="p-6">
        <!-- Pestaña Ingresos -->
        <div v-if="activeTab === 'ingresos'">
          <IngresosTab />
        </div>

        <!-- Pestaña Egresos -->
        <div v-if="activeTab === 'egresos'">
          <EgresosTab />
        </div>

        <!-- Pestaña Gastos Generales -->
        <div v-if="activeTab === 'gastos'">
          <GastosGeneralesTab />
        </div>

        <!-- Pestaña Balance -->
        <div v-if="activeTab === 'balance'">
          <BalanceTab />
        </div>
      </div>
    </div>

    <!-- Modales (se implementarán después) -->
    <!-- Modal de confirmación de eliminación -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Confirmar eliminación</h3>
        <p class="text-gray-600 mb-6">
          ¿Estás seguro de que deseas eliminar este {{ deleteType }}? Esta acción no se puede deshacer.
        </p>
        <div class="flex justify-end gap-3">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Cancelar
          </button>
          <button
            @click="confirmDelete"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
          >
            Eliminar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useFinanzasStore } from '@/stores/finanzas';

// Componentes
import IngresosTab from './components/IngresosTab.vue';
import EgresosTab from './components/EgresosTab.vue';
import GastosGeneralesTab from './components/GastosGeneralesTab.vue';
import BalanceTab from './components/BalanceTab.vue';
import IngresoModal from './IngresoModal.vue';
import EgresoModal from './EgresoModal.vue';
import GastoGeneralModal from './GastoGeneralModal.vue';

// Iconos
import {
  PlusIcon,
  MinusIcon,
  DocumentTextIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
  CalculatorIcon
} from '@heroicons/vue/24/outline';

// Store
const finanzasStore = useFinanzasStore();

// Estado local
const activeTab = ref<'ingresos' | 'egresos' | 'gastos' | 'balance'>('ingresos');
const showDeleteModal = ref(false);
const deleteType = ref<'ingreso' | 'egreso' | 'gasto'>('ingreso');

// Estados modales creación
const showIngresoModal = ref(false);
const showEgresoModal = ref(false);
const showGastoModal = ref(false);

// Filtros globales
const globalFilters = ref({
  fecha_inicio: '',
  fecha_fin: '',
});

// Computed
const totalIngresos = computed(() => finanzasStore.totalIngresos);
const totalEgresos = computed(() => finanzasStore.totalEgresos);
const totalGastosGenerales = computed(() => finanzasStore.totalGastosGenerales);
const totalMontosIngresos = computed(() => finanzasStore.totalMontosIngresos);
const totalMontosEgresos = computed(() => finanzasStore.totalMontosEgresos);
const totalMontosGastosGenerales = computed(() => finanzasStore.totalMontosGastosGenerales);
const balanceNeto = computed(() => finanzasStore.balanceNeto);
const isLoading = computed(() => finanzasStore.isLoading);

// Métodos
const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
};

const loadAllData = async () => {
  await Promise.all([
    finanzasStore.fetchIngresos(),
    finanzasStore.fetchEgresos(),
    finanzasStore.fetchGastosGenerales(),
    finanzasStore.fetchMetricas()
  ]);
};

const applyGlobalFilters = () => {
  const filters = {
    fecha_inicio: globalFilters.value.fecha_inicio,
    fecha_fin: globalFilters.value.fecha_fin,
  };
  
  // Aplicar filtros a todos los tabs
  finanzasStore.filtersIngresos = { ...finanzasStore.filtersIngresos, ...filters };
  finanzasStore.filtersEgresos = { ...finanzasStore.filtersEgresos, ...filters };
  finanzasStore.filtersGastosGenerales = { ...finanzasStore.filtersGastosGenerales, ...filters };
  
  loadAllData();
};

const clearGlobalFilters = () => {
  globalFilters.value = {
    fecha_inicio: '',
    fecha_fin: '',
  };
  
  // Limpiar filtros de los stores
  finanzasStore.filtersIngresos = {
    page: 1,
    per_page: 15,
    search: '',
  };
  
  finanzasStore.filtersEgresos = {
    page: 1,
    per_page: 15,
    search: '',
  };
  
  finanzasStore.filtersGastosGenerales = {
    page: 1,
    per_page: 15,
    search: '',
  };
  
  loadAllData();
};

// Modales (placeholder - se implementarán con componentes separados)
const openCreateIngresoModal = () => { showIngresoModal.value = true; };
const openCreateEgresoModal = () => { showEgresoModal.value = true; };
const openCreateGastoModal = () => { showGastoModal.value = true; };

const handleSaved = async () => {
  showIngresoModal.value = false;
  showEgresoModal.value = false;
  showGastoModal.value = false;
  await loadAllData();
};

const confirmDelete = async () => {
  try {
    // Implementar lógica de eliminación
    showDeleteModal.value = false;
  } catch (error) {
    console.error('Error deleting:', error);
  }
};

// Lifecycle
onMounted(async () => {
  await loadAllData();
});
</script>

<!-- Modales de creación -->
<IngresoModal :isOpen="showIngresoModal" @close="showIngresoModal=false" @save="handleSaved" />
<EgresoModal :isOpen="showEgresoModal" @close="showEgresoModal=false" @save="handleSaved" />
<GastoGeneralModal :isOpen="showGastoModal" @close="showGastoModal=false" @save="handleSaved" />
