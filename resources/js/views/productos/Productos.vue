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
        Nuevo Producto
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
            <p class="text-2xl font-bold text-gray-900">{{ metricas.total_productos || 0 }}</p>
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
            <p class="text-2xl font-bold text-gray-900">{{ metricas.productos_automotrices || 0 }}</p>
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
            <p class="text-2xl font-bold text-gray-900">{{ metricas.productos_despensa || 0 }}</p>
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
            <p class="text-2xl font-bold text-gray-900">{{ metricas.stock_bajo || 0 }}</p>
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
            :pagination="paginationAutomotriz"
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
            :pagination="paginationDespensa"
            @view="viewProductoDespensa"
            @edit="editProductoDespensa"
            @delete="confirmDeleteDespensa"
            @changePage="changePageDespensa"
          />
        </div>
      </div>
    </div>

    <!-- Modales -->
    <ProductoModal
      :is-open="showCreateModal || showEditModal"
      :tipo="selectedTipo"
      :producto="selectedProducto"
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
import type { ProductoAutomotriz, ProductoDespensa } from '@/types';

// Componentes
import ProductosAutomotricesTable from './components/ProductosAutomotricesTable.vue';
import ProductosDespensaTable from './components/ProductosDespensaTable.vue';
import ProductoModal from './ProductoModal.vue';
import ProductoViewModal from './ProductoViewModal.vue';

// Iconos
import {
  PlusIcon,
  CubeIcon,
  WrenchIcon,
  ShoppingBagIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';

// Store
const productoStore = useProductoStore();

// Estado local
const activeTab = ref<'automotrices' | 'despensa'>('automotrices');
const searchTermAutomotriz = ref('');
const searchTermDespensa = ref('');
const selectedProducto = ref<ProductoAutomotriz | ProductoDespensa | null>(null);
const selectedTipo = ref<'automotriz' | 'despensa'>('automotriz');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showViewModal = ref(false);
const showDeleteModal = ref(false);

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
  selectedProducto.value = null;
  selectedTipo.value = activeTab.value === 'automotrices' ? 'automotriz' : 'despensa';
  showCreateModal.value = true;
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
  showEditModal.value = true;
  showViewModal.value = false;
};

const editProductoDespensa = (producto: ProductoDespensa) => {
  selectedProducto.value = producto;
  selectedTipo.value = 'despensa';
  showEditModal.value = true;
  showViewModal.value = false;
};

const editProducto = (producto: ProductoAutomotriz | ProductoDespensa) => {
  if (selectedTipo.value === 'automotriz') {
    editProductoAutomotriz(producto as ProductoAutomotriz);
  } else {
    editProductoDespensa(producto as ProductoDespensa);
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
      if (selectedTipo.value === 'automotriz') {
        await productoStore.deleteProductoAutomotriz((selectedProducto.value as ProductoAutomotriz).producto_automotriz_id);
      } else {
        await productoStore.deleteProductoDespensa((selectedProducto.value as ProductoDespensa).producto_despensa_id);
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

// Lifecycle
onMounted(async () => {
  await Promise.all([
    loadProductosAutomotrices(),
    loadProductosDespensa(),
    loadMetricas()
  ]);
});
</script>
