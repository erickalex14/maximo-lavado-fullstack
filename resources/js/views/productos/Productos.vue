<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Productos</h1>
        <p class="text-gray-600 mt-1">Gestiona productos automotrices y de despensa</p>
      </div>
      <button
        @click="openCreateModal"
        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center gap-2"
      >
  <PlusIcon class="h-5 w-5" />
  {{ activeTab === 'servicios' ? 'Nuevo Servicio' : 'Nuevo Producto' }}
      </button>
    </div>

    <!-- Métricas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <CubeIcon class="h-8 w-8 text-blue-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Productos</p>
            <p class="text-2xl font-bold text-gray-900">{{ metricas.total_productos || metricas.total || (metricas.automotrices_total + metricas.despensa_total) || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <WrenchIcon class="h-8 w-8 text-green-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Automotrices</p>
            <p class="text-2xl font-bold text-gray-900">{{ metricas.automotrices_total ?? metricas.automotrices?.total ?? 0 }}</p>
            <p class="text-xs text-gray-500" v-if="metricas.automotrices_stock_bajo || metricas.automotrices_sin_stock">SB: {{ metricas.automotrices_stock_bajo }} · SS: {{ metricas.automotrices_sin_stock }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <ShoppingBagIcon class="h-8 w-8 text-purple-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Despensa</p>
            <p class="text-2xl font-bold text-gray-900">{{ metricas.despensa_total ?? metricas.despensa?.total ?? 0 }}</p>
            <p class="text-xs text-gray-500" v-if="metricas.despensa_stock_bajo || metricas.despensa_sin_stock">SB: {{ metricas.despensa_stock_bajo }} · SS: {{ metricas.despensa_sin_stock }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <ExclamationTriangleIcon class="h-8 w-8 text-red-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Stock Bajo</p>
            <p class="text-2xl font-bold text-gray-900">{{ metricas.stock_bajo !== undefined ? metricas.stock_bajo : (metricas.automotrices_stock_bajo + metricas.despensa_stock_bajo) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Pestañas -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex">
          <button
            @click="activeTab = 'automotrices'"
            :class="[
              'py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200',
              activeTab === 'automotrices'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Productos Automotrices
          </button>
          <button
            @click="activeTab = 'despensa'"
            :class="[
              'py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200',
              activeTab === 'despensa'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Productos de Despensa
          </button>
          <!-- Eliminado botón duplicado de Servicios -->
          <button
            @click="activeTab = 'servicios'"
            :class="[
              'py-4 px-6 border-b-2 font-medium text-sm transition-colors duration-200',
              activeTab === 'servicios'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Servicios
          </button>
        </nav>
      </div>

      <!-- Contenido de las pestañas -->
      <div class="p-6">
        <!-- Pestaña Productos Automotrices -->
  <div v-if="activeTab === 'automotrices'">
          <!-- Filtros para automotrices -->
          <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
              <input
                v-model="searchTermAutomotriz"
                @input="handleSearchAutomotriz"
                type="text"
                placeholder="Buscar productos automotrices..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
              <select
                v-model="filtersAutomotriz.activo"
                @change="applyFiltersAutomotriz"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Todos</option>
                <option :value="true">Activos</option>
                <option :value="false">Inactivos</option>
              </select>
            </div>
            <div class="flex items-end">
              <button
                @click="clearFiltersAutomotriz"
                class="text-gray-600 hover:text-gray-800 font-medium"
              >
                Limpiar filtros
              </button>
            </div>
          </div>

          <!-- Tabla de productos automotrices -->
          <ProductosAutomotricesTable
            :productos="productosAutomotrices"
            :loading="isLoading"
            :pagination="paginationAutomotrizWithMeta"
            @view="viewProductoAutomotriz"
            @edit="editProductoAutomotriz"
            @delete="confirmDeleteAutomotriz"
            @changePage="changePageAutomotriz"
          />
        </div>

        <!-- Pestaña Productos de Despensa -->
        <div v-if="activeTab === 'despensa'">
          <!-- Filtros para despensa -->
          <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
              <input
                v-model="searchTermDespensa"
                @input="handleSearchDespensa"
                type="text"
                placeholder="Buscar productos de despensa..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
              <select
                v-model="filtersDespensa.activo"
                @change="applyFiltersDespensa"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Todos</option>
                <option :value="true">Activos</option>
                <option :value="false">Inactivos</option>
              </select>
            </div>
            <div class="flex items-end">
              <button
                @click="clearFiltersDespensa"
                class="text-gray-600 hover:text-gray-800 font-medium"
              >
                Limpiar filtros
              </button>
            </div>
          </div>

          <!-- Tabla de productos de despensa -->
          <ProductosDespensaTable
            :productos="productosDespensa"
            :loading="isLoading"
            :pagination="paginationDespensaWithMeta"
            @view="viewProductoDespensa"
            @edit="editProductoDespensa"
            @delete="confirmDeleteDespensa"
            @changePage="changePageDespensa"
          />
        </div>

        <!-- Pestaña Servicios -->
        <div v-if="activeTab === 'servicios'">
          <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
              <input
                v-model="searchTermServicio"
                @input="handleSearchServicio"
                type="text"
                placeholder="Buscar servicios..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
              <select
                v-model="filtersServicio.activo"
                @change="applyFiltersServicio"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Todos</option>
                <option :value="true">Activos</option>
                <option :value="false">Inactivos</option>
              </select>
            </div>
            <div class="flex items-end">
              <button
                @click="clearFiltersServicio"
                class="text-gray-600 hover:text-gray-800 font-medium"
              >
                Limpiar filtros
              </button>
            </div>
          </div>
          <ServiciosTable
            :servicios="servicios"
            :loading="isLoadingServicios"
            :pagination="paginationServiciosWithMeta"
            @view="viewServicio"
            @edit="editServicio"
            @delete="confirmDeleteServicio"
            @changePage="changePageServicio"
          />
        </div>
      </div>
    </div>

    <!-- Modales -->
    <ProductoModal
      :is-open="showCreateModal || showEditModal"
      :tipo="selectedTipo"
      :producto="selectedProducto"
      :modo="showEditModal ? 'edit' : 'create'"
      @close="closeModals"
      @success="handleProductoSaved"
    />

    <ProductoViewModal
      :is-open="showViewModal"
      :tipo="selectedTipo"
      :producto="selectedProducto"
      @close="closeModals"
      @edit="editProducto"
    />

    <!-- Modales Servicios -->
    <ServicioModal
      :is-open="showServicioCreateModal || showServicioEditModal"
      :servicio="selectedServicio"
      :modo="showServicioEditModal ? 'edit' : 'create'"
      @close="closeServicioModals"
      @success="handleServicioSaved"
    />
    <ServicioViewModal
      :is-open="showServicioViewModal"
      :servicio="selectedServicio"
      @close="closeServicioModals"
      @edit="editServicio"
    />

    <!-- Confirm delete servicio -->
    <div v-if="showServicioDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Confirmar eliminación</h3>
        <p class="text-gray-600 mb-6">
          ¿Estás seguro de que deseas eliminar este servicio? Esta acción no se puede deshacer.
        </p>
        <div class="flex justify-end gap-3">
          <button
            @click="showServicioDeleteModal = false"
            class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Cancelar
          </button>
          <button
            @click="deleteServicio"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
          >
            Eliminar
          </button>
        </div>
      </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Confirmar eliminación</h3>
        <p class="text-gray-600 mb-6">
          ¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer.
        </p>
        <div class="flex justify-end gap-3">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Cancelar
          </button>
          <button
            @click="deleteProducto"
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
import { useProductoStore } from '@/stores/producto';
import { useServicioStore } from '@/stores/servicio';
import type { ProductoAutomotriz, ProductoDespensa, Servicio } from '@/types';

// Componentes
import ProductosAutomotricesTable from './components/ProductosAutomotricesTable.vue';
import ProductosDespensaTable from './components/ProductosDespensaTable.vue';
import ProductoModal from './ProductoModal.vue';
import ProductoViewModal from './ProductoViewModal.vue';
import ServiciosTable from './components/ServiciosTable.vue';
import ServicioModal from './ServicioModal.vue';
import ServicioViewModal from './ServicioViewModal.vue';

// Iconos
import {
  PlusIcon,
  CubeIcon,
  WrenchIcon,
  ShoppingBagIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';

// Stores
const productoStore = useProductoStore();
const servicioStore = useServicioStore();

// Estado local
const activeTab = ref<'automotrices' | 'despensa' | 'servicios'>('automotrices');
const searchTermAutomotriz = ref('');
const searchTermDespensa = ref('');
const selectedProducto = ref<ProductoAutomotriz | ProductoDespensa | null>(null);
const selectedTipo = ref<'automotriz' | 'despensa'>('automotriz');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showViewModal = ref(false);
const showDeleteModal = ref(false);
// Servicios state
const searchTermServicio = ref('');
const selectedServicio = ref<Servicio | null>(null);
const showServicioCreateModal = ref(false);
const showServicioEditModal = ref(false);
const showServicioViewModal = ref(false);
const showServicioDeleteModal = ref(false);
const filtersServicio = ref<{ page: number; per_page: number; search: string; activo?: boolean | undefined }>({ page: 1, per_page: 15, search: '' });

// Filtros
const filtersAutomotriz = ref({
  page: 1,
  per_page: 15,
  search: '',
  activo: undefined as boolean | undefined,
});

const filtersDespensa = ref({
  page: 1,
  per_page: 15,
  search: '',
  activo: undefined as boolean | undefined,
});

// Computed
const productosAutomotrices = computed(() => productoStore.productosAutomotrices);
const productosDespensa = computed(() => productoStore.productosDespensa);
const isLoading = computed(() => productoStore.isLoading);
const metricas = computed(() => productoStore.metricas);
const paginationAutomotriz = computed(() => productoStore.paginationAutomotriz);
const paginationDespensa = computed(() => productoStore.paginationDespensa);
const paginationAutomotrizWithMeta = computed(() => {
  const p: any = paginationAutomotriz.value || { current_page: 1, per_page: 15, total: 0, last_page: 1 };
  const from = p.from ?? (p.total > 0 ? (p.current_page - 1) * p.per_page + 1 : 0);
  const to = p.to ?? Math.min(p.current_page * p.per_page, p.total);
  return { ...p, from, to };
});
const paginationDespensaWithMeta = computed(() => {
  const p: any = paginationDespensa.value || { current_page: 1, per_page: 15, total: 0, last_page: 1 };
  const from = p.from ?? (p.total > 0 ? (p.current_page - 1) * p.per_page + 1 : 0);
  const to = p.to ?? Math.min(p.current_page * p.per_page, p.total);
  return { ...p, from, to };
});
// Servicios computed
const servicios = computed(() => servicioStore.servicios);
const isLoadingServicios = computed(() => servicioStore.isLoading);
const paginationServiciosWithMeta = computed(() => {
  const p: any = servicioStore.pagination || { current_page: 1, per_page: 15, total: 0, last_page: 1 };
  const from = p.from ?? (p.total > 0 ? (p.current_page - 1) * p.per_page + 1 : 0);
  const to = p.to ?? Math.min(p.current_page * p.per_page, p.total);
  return { ...p, from, to };
});

// Métodos
const loadProductosAutomotrices = async () => {
  await productoStore.fetchProductosAutomotrices(filtersAutomotriz.value);
};

const loadProductosDespensa = async () => {
  await productoStore.fetchProductosDespensa(filtersDespensa.value);
};

const loadMetricas = async () => {
  await productoStore.fetchMetricas();
};

const handleSearchAutomotriz = () => {
  filtersAutomotriz.value.search = searchTermAutomotriz.value;
  filtersAutomotriz.value.page = 1;
  loadProductosAutomotrices();
};

const handleSearchDespensa = () => {
  filtersDespensa.value.search = searchTermDespensa.value;
  filtersDespensa.value.page = 1;
  loadProductosDespensa();
};

const applyFiltersAutomotriz = () => {
  filtersAutomotriz.value.page = 1;
  loadProductosAutomotrices();
};

const applyFiltersDespensa = () => {
  filtersDespensa.value.page = 1;
  loadProductosDespensa();
};

const clearFiltersAutomotriz = () => {
  searchTermAutomotriz.value = '';
  filtersAutomotriz.value = {
    page: 1,
    per_page: 15,
    search: '',
    activo: undefined,
  };
  loadProductosAutomotrices();
};

const clearFiltersDespensa = () => {
  searchTermDespensa.value = '';
  filtersDespensa.value = {
    page: 1,
    per_page: 15,
    search: '',
    activo: undefined,
  };
  loadProductosDespensa();
};

const changePageAutomotriz = async (page: number) => {
  filtersAutomotriz.value.page = page;
  await loadProductosAutomotrices();
};

const changePageDespensa = async (page: number) => {
  filtersDespensa.value.page = page;
  await loadProductosDespensa();
};

// Modales

const openCreateModal = () => {
  if (activeTab.value === 'servicios') {
    selectedServicio.value = null;
    showServicioCreateModal.value = true;
  } else {
    selectedProducto.value = null;
    selectedTipo.value = activeTab.value === 'automotrices' ? 'automotriz' : 'despensa';
    showEditModal.value = false;
    showCreateModal.value = true;
  }
};

const viewProductoAutomotriz = (producto: ProductoAutomotriz) => {
  selectedProducto.value = producto;
  selectedTipo.value = 'automotriz';
  showViewModal.value = true;
};

const viewProductoDespensa = (producto: ProductoDespensa) => {
  selectedProducto.value = producto;
  selectedTipo.value = 'despensa';
  showViewModal.value = true;
};


const editProductoAutomotriz = (producto: ProductoAutomotriz) => {
  selectedProducto.value = producto;
  selectedTipo.value = 'automotriz';
  showCreateModal.value = false;
  showEditModal.value = true;
  showViewModal.value = false;
};

const editProductoDespensa = (producto: ProductoDespensa) => {
  selectedProducto.value = producto;
  selectedTipo.value = 'despensa';
  showCreateModal.value = false;
  showEditModal.value = true;
  showViewModal.value = false;
};

const editProducto = (producto?: ProductoAutomotriz | ProductoDespensa) => {
  const target = producto || selectedProducto.value;
  if (!target) return;
  if (selectedTipo.value === 'automotriz') {
    editProductoAutomotriz(target as ProductoAutomotriz);
  } else {
    editProductoDespensa(target as ProductoDespensa);
  }
};

const confirmDeleteAutomotriz = (producto: ProductoAutomotriz) => {
  selectedProducto.value = producto;
  selectedTipo.value = 'automotriz';
  showDeleteModal.value = true;
  showViewModal.value = false;
};

const confirmDeleteDespensa = (producto: ProductoDespensa) => {
  selectedProducto.value = producto;
  selectedTipo.value = 'despensa';
  showDeleteModal.value = true;
  showViewModal.value = false;
};

const confirmDelete = (producto: ProductoAutomotriz | ProductoDespensa) => {
  if (selectedTipo.value === 'automotriz') {
    confirmDeleteAutomotriz(producto as ProductoAutomotriz);
  } else {
    confirmDeleteDespensa(producto as ProductoDespensa);
  }
};

const deleteProducto = async () => {
  if (selectedProducto.value) {
    try {
      // Obtener ID robusto según el tipo y posibles claves alternativas
      const getProductoId = (p: any, tipo: 'automotriz' | 'despensa'): number | null => {
        if (!p) return null;
        if (tipo === 'automotriz') {
          return (
            p.producto_automotriz_id ??
            p.id ??
            p.producto_id ??
            p.productoAutomotrizId ??
            p.productoId ??
            null
          );
        }
        return (
          p.producto_despensa_id ??
          p.id ??
          p.producto_id ??
          p.productoDespensaId ??
          p.productoId ??
          null
        );
      };

      const id = getProductoId(selectedProducto.value, selectedTipo.value);
      console.debug('deleteProducto -> tipo:', selectedTipo.value, 'id:', id, 'keys:', Object.keys(selectedProducto.value as any));
      if (!id) {
        console.error('No se pudo determinar el ID del producto a eliminar.');
        return;
      }

      if (selectedTipo.value === 'automotriz') {
        await productoStore.deleteProductoAutomotriz(id);
      } else {
        await productoStore.deleteProductoDespensa(id);
      }
      showDeleteModal.value = false;
      selectedProducto.value = null;
    } catch (error) {
      console.error('Error deleting producto:', error);
    }
  }
};

const closeModals = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  showViewModal.value = false;
  selectedProducto.value = null;
};

const handleProductoSaved = () => {
  closeModals();
  if (selectedTipo.value === 'automotriz') {
    loadProductosAutomotrices();
  } else {
    loadProductosDespensa();
  }
  loadMetricas();
};

// === Servicios methods ===
const loadServicios = async () => { await servicioStore.fetchServicios({ page: filtersServicio.value.page, per_page: filtersServicio.value.per_page, search: filtersServicio.value.search, activo: filtersServicio.value.activo }); };
const handleSearchServicio = () => { filtersServicio.value.search = searchTermServicio.value; filtersServicio.value.page = 1; loadServicios(); };
const applyFiltersServicio = () => { filtersServicio.value.page = 1; loadServicios(); };
const clearFiltersServicio = () => { searchTermServicio.value = ''; filtersServicio.value = { page: 1, per_page: 15, search: '' }; loadServicios(); };
const changePageServicio = (page: number) => { filtersServicio.value.page = page; loadServicios(); };
const viewServicio = (servicio: Servicio) => { selectedServicio.value = servicio; showServicioViewModal.value = true; };
const editServicio = (servicio?: Servicio) => { selectedServicio.value = servicio || selectedServicio.value; if (!selectedServicio.value) return; showServicioEditModal.value = true; showServicioViewModal.value = false; };
const confirmDeleteServicio = (servicio: Servicio) => { selectedServicio.value = servicio; showServicioDeleteModal.value = true; showServicioViewModal.value = false; };
const deleteServicio = async () => { if (!selectedServicio.value) return; await servicioStore.remove(selectedServicio.value.id); showServicioDeleteModal.value = false; selectedServicio.value = null; loadServicios(); };
const closeServicioModals = () => { showServicioCreateModal.value = false; showServicioEditModal.value = false; showServicioViewModal.value = false; selectedServicio.value = null; };
const handleServicioSaved = () => { closeServicioModals(); loadServicios(); };

// Lifecycle
onMounted(async () => {
  await Promise.all([
    loadProductosAutomotrices(),
    loadProductosDespensa(),
    loadMetricas()
  ]);
  loadServicios();
});
</script>
