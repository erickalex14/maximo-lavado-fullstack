<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Proveedores</h1>
        <p class="text-gray-600 mt-1">Gestiona proveedores y pagos</p>
      </div>
      <button
        @click="openCreateModal"
        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center gap-2"
      >
        <PlusIcon class="h-5 w-5" />
        Nuevo Proveedor
      </button>
    </div>

    <!-- Métricas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <UsersIcon class="h-8 w-8 text-blue-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Proveedores</p>
            <p class="text-2xl font-bold text-gray-900">{{ totalProveedores || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <ExclamationTriangleIcon class="h-8 w-8 text-red-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Con Deuda</p>
            <p class="text-2xl font-bold text-gray-900">{{ proveedoresConDeuda.length || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <CurrencyDollarIcon class="h-8 w-8 text-green-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Deuda Total</p>
            <p class="text-2xl font-bold text-gray-900">
              ${{ totalDeuda.toLocaleString('es-CO', { minimumFractionDigits: 2 }) }}
            </p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <DocumentIcon class="h-8 w-8 text-purple-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Pagos Este Mes</p>
            <p class="text-2xl font-bold text-gray-900">{{ metricas.pagos_mes || 0 }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Pestañas -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex">
          <button
            @click="activeTab = 'proveedores'"
            :class="[
              'py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200',
              activeTab === 'proveedores'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Proveedores
          </button>
          <button
            @click="activeTab = 'pagos'"
            :class="[
              'py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200',
              activeTab === 'pagos'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Pagos
          </button>
        </nav>
      </div>

      <!-- Contenido de las pestañas -->
      <div class="p-6">
        <!-- Pestaña Proveedores -->
        <div v-if="activeTab === 'proveedores'">
          <!-- Filtros para proveedores -->
          <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
              <input
                v-model="searchTermProveedores"
                @input="handleSearchProveedores"
                type="text"
                placeholder="Buscar proveedores..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Con Deuda</label>
              <select
                v-model="filterProveedores.conDeuda"
                @change="applyFiltersProveedores"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Todos</option>
                <option value="true">Con deuda</option>
                <option value="false">Sin deuda</option>
              </select>
            </div>
            <div class="flex items-end">
              <button
                @click="clearFiltersProveedores"
                class="text-gray-600 hover:text-gray-800 font-medium"
              >
                Limpiar filtros
              </button>
            </div>
          </div>

          <!-- Tabla de proveedores -->
          <ProveedoresTable
            :proveedores="proveedores"
            :loading="isLoading"
            :pagination="paginationProveedores"
            @view="viewProveedor"
            @edit="editProveedor"
            @delete="confirmDeleteProveedor"
            @changePage="changePageProveedores"
          />
        </div>

        <!-- Pestaña Pagos -->
        <div v-if="activeTab === 'pagos'">
          <!-- Filtros para pagos -->
          <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
              <input
                v-model="searchTermPagos"
                @input="handleSearchPagos"
                type="text"
                placeholder="Buscar pagos..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Proveedor</label>
              <select
                v-model="filterPagos.proveedor_id"
                @change="applyFiltersPagos"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Todos los proveedores</option>
                <option v-for="proveedor in proveedores" :key="proveedor.proveedor_id" :value="proveedor.proveedor_id">
                  {{ proveedor.nombre }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Fecha desde</label>
              <input
                v-model="filterPagos.fecha_desde"
                @change="applyFiltersPagos"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div class="flex items-end">
              <button
                @click="clearFiltersPagos"
                class="text-gray-600 hover:text-gray-800 font-medium"
              >
                Limpiar filtros
              </button>
            </div>
          </div>

          <!-- Botón para nuevo pago -->
          <div class="mb-4 flex justify-end">
            <button
              @click="openCreatePagoModal"
              class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center gap-2"
            >
              <PlusIcon class="h-5 w-5" />
              Nuevo Pago
            </button>
          </div>

          <!-- Tabla de pagos -->
          <PagosTable
            :pagos="pagos"
            :loading="isLoading"
            :pagination="paginationPagos"
            @view="viewPago"
            @edit="editPago"
            @delete="confirmDeletePago"
            @page-change="changePagePagos"
          />
        </div>
      </div>
    </div>

    <!-- Modales -->
    <ProveedorModal
      :isOpen="showCreateModal || showEditModal"
      :proveedor="selectedProveedor"
      :loading="isLoading"
      @close="closeModals"
      @submit="handleProveedorSubmit"
    />

    <ProveedorViewModal
      :isOpen="showViewModal"
      :proveedor="selectedProveedor"
      @close="closeModals"
      @edit="editProveedor"
    />

    <PagoModal
      :isOpen="showCreatePagoModal || showEditPagoModal"
      :pago="selectedPago"
      :proveedores="proveedores"
      :loading="isLoading"
      @close="closePagoModals"
      @submit="handlePagoSubmit"
    />

    <PagoViewModal
      :isOpen="showViewPagoModal"
      :pago="selectedPago"
      @close="closePagoModals"
      @edit="editPago"
    />

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
import { useProveedorStore } from '@/stores/proveedor';
import type { Proveedor, PagoProveedor } from '@/types';

// Componentes
import ProveedoresTable from './components/ProveedoresTable.vue';
import PagosTable from './components/PagosTable.vue';
import ProveedorModal from './ProveedorModal.vue';
import ProveedorViewModal from './ProveedorViewModal.vue';
import PagoModal from './PagoModal.vue';
import PagoViewModal from './PagoViewModal.vue';

// Iconos
import {
  PlusIcon,
  UsersIcon,
  ExclamationTriangleIcon,
  CurrencyDollarIcon,
  DocumentIcon
} from '@heroicons/vue/24/outline';

// Store
const proveedorStore = useProveedorStore();

// Estado local
const activeTab = ref<'proveedores' | 'pagos'>('proveedores');
const searchTermProveedores = ref('');
const searchTermPagos = ref('');
const selectedProveedor = ref<Proveedor | null>(null);
const selectedPago = ref<PagoProveedor | null>(null);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showViewModal = ref(false);
const showCreatePagoModal = ref(false);
const showEditPagoModal = ref(false);
const showViewPagoModal = ref(false);
const showDeleteModal = ref(false);
const deleteType = ref<'proveedor' | 'pago'>('proveedor');

// Filtros
const filterProveedores = ref({
  page: 1,
  per_page: 15,
  search: '',
  conDeuda: '',
});

const filterPagos = ref({
  page: 1,
  per_page: 15,
  search: '',
  proveedor_id: undefined as number | undefined,
  fecha_desde: '',
  fecha_hasta: '',
});

// Computed
const proveedores = computed(() => proveedorStore.proveedores);
const pagos = computed(() => proveedorStore.pagos);
const isLoading = computed(() => proveedorStore.isLoading);
const metricas = computed(() => proveedorStore.metricas);
const paginationProveedores = computed(() => proveedorStore.paginationProveedores);
const paginationPagos = computed(() => proveedorStore.paginationPagos);
const totalProveedores = computed(() => proveedorStore.totalProveedores);
const proveedoresConDeuda = computed(() => proveedorStore.proveedoresConDeuda);

// Total deuda (asegurando conversión numérica para evitar concatenación de strings)
const totalDeuda = computed(() => {
  return proveedores.value.reduce((sum, p) => {
    const val = Number((p as any).deuda_pendiente ?? 0);
    return sum + (isNaN(val) ? 0 : val);
  }, 0);
});

// Métodos
const loadProveedores = async () => {
  await proveedorStore.fetchProveedores(filterProveedores.value);
};

const loadPagos = async () => {
  await proveedorStore.fetchPagos(filterPagos.value);
};

const loadMetricas = async () => {
  await proveedorStore.fetchMetricas();
};

const handleSearchProveedores = () => {
  filterProveedores.value.search = searchTermProveedores.value;
  filterProveedores.value.page = 1;
  loadProveedores();
};

const handleSearchPagos = () => {
  filterPagos.value.search = searchTermPagos.value;
  filterPagos.value.page = 1;
  loadPagos();
};

const applyFiltersProveedores = () => {
  filterProveedores.value.page = 1;
  loadProveedores();
};

const applyFiltersPagos = () => {
  filterPagos.value.page = 1;
  loadPagos();
};

const clearFiltersProveedores = () => {
  searchTermProveedores.value = '';
  filterProveedores.value = {
    page: 1,
    per_page: 15,
    search: '',
    conDeuda: '',
  };
  loadProveedores();
};

const clearFiltersPagos = () => {
  searchTermPagos.value = '';
  filterPagos.value = {
    page: 1,
    per_page: 15,
    search: '',
    proveedor_id: undefined,
    fecha_desde: '',
    fecha_hasta: '',
  };
  loadPagos();
};

const changePageProveedores = async (page: number) => {
  filterProveedores.value.page = page;
  await loadProveedores();
};

const changePagePagos = async (page: number) => {
  filterPagos.value.page = page;
  await loadPagos();
};

// Modales de proveedores
const openCreateModal = () => {
  selectedProveedor.value = null;
  showCreateModal.value = true;
};

const viewProveedor = (proveedor: Proveedor) => {
  selectedProveedor.value = proveedor;
  showViewModal.value = true;
};

const editProveedor = (proveedor: Proveedor) => {
  selectedProveedor.value = proveedor;
  showEditModal.value = true;
  showViewModal.value = false;
};

const confirmDeleteProveedor = (proveedor: Proveedor) => {
  selectedProveedor.value = proveedor;
  deleteType.value = 'proveedor';
  showDeleteModal.value = true;
  showViewModal.value = false;
};

// Modales de pagos
const openCreatePagoModal = () => {
  selectedPago.value = null;
  showCreatePagoModal.value = true;
};

const viewPago = (pago: PagoProveedor) => {
  selectedPago.value = pago;
  showViewPagoModal.value = true;
};

const editPago = (pago: PagoProveedor) => {
  selectedPago.value = pago;
  showEditPagoModal.value = true;
  showViewPagoModal.value = false;
};

const confirmDeletePago = (pago: PagoProveedor) => {
  selectedPago.value = pago;
  deleteType.value = 'pago';
  showDeleteModal.value = true;
  showViewPagoModal.value = false;
};

const confirmDelete = async () => {
  try {
    if (deleteType.value === 'proveedor' && selectedProveedor.value) {
      await proveedorStore.deleteProveedor(selectedProveedor.value.proveedor_id);
    } else if (deleteType.value === 'pago' && selectedPago.value) {
      await proveedorStore.deletePago(selectedPago.value.id_pago_proveedor);
    }
    showDeleteModal.value = false;
    selectedProveedor.value = null;
    selectedPago.value = null;
  } catch (error) {
    console.error('Error deleting:', error);
  }
};

const closeModals = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  showViewModal.value = false;
  selectedProveedor.value = null;
};

const closePagoModals = () => {
  showCreatePagoModal.value = false;
  showEditPagoModal.value = false;
  showViewPagoModal.value = false;
  selectedPago.value = null;
};

const handleProveedorSaved = () => {
  closeModals();
  loadProveedores();
  loadMetricas();
};

const handleProveedorSubmit = async (data: any) => {
  try {
    if (selectedProveedor.value) {
      await proveedorStore.updateProveedor(selectedProveedor.value.proveedor_id, data);
    } else {
      await proveedorStore.createProveedor(data);
    }
    handleProveedorSaved();
  } catch (error) {
    console.error('Error submitting proveedor:', error);
  }
};

const handlePagoSaved = () => {
  closePagoModals();
  loadPagos();
  loadProveedores(); // Recargar para actualizar deudas
  loadMetricas();
};

const handlePagoSubmit = async (data: any) => {
  try {
    if (selectedPago.value) {
      // TODO: Implement updatePago when backend endpoint is ready
      console.log('Update pago:', data);
    } else {
      await proveedorStore.createPago(data);
    }
    handlePagoSaved();
  } catch (error) {
    console.error('Error submitting pago:', error);
  }
};

// Lifecycle
onMounted(async () => {
  await Promise.all([
    loadProveedores(),
    loadPagos(),
    loadMetricas()
  ]);
});
</script>
