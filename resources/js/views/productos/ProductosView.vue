<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Gestión de Productos</h1>
      <button
        @click="showCreateModal = true"
        class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Nuevo Producto
      </button>
    </div>

    <!-- Tabs para tipos de productos -->
    <div class="mb-6">
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
          <button
            @click="activeTab = 'automotriz'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === 'automotriz'
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Productos Automotrices
          </button>
          <button
            @click="activeTab = 'despensa'"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === 'despensa'
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            Productos de Despensa
          </button>
        </nav>
      </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Nombre, código..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
          <select
            v-model="filters.categoria"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <option value="">Todas las categorías</option>
            <option v-for="categoria in categorias" :key="categoria" :value="categoria">
              {{ categoria }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
          <select
            v-model="filters.activo"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <option value="">Todos</option>
            <option value="1">Activos</option>
            <option value="0">Inactivos</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Tabla de productos -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
      <div v-if="isLoading" class="flex justify-center items-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
      </div>
      
      <div v-else-if="filteredProductos.length === 0" class="text-center py-8 text-gray-500">
        No se encontraron productos
      </div>
      
      <table v-else class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Código
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Nombre
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Categoría
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Stock
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Precio
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Estado
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Acciones
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="producto in filteredProductos" :key="producto.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              {{ producto.codigo }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ producto.nombre }}</div>
              <div class="text-sm text-gray-500">{{ producto.descripcion }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ producto.categoria }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="[
                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                producto.stock_actual <= producto.stock_minimo
                  ? 'bg-red-100 text-red-800'
                  : 'bg-green-100 text-green-800'
              ]">
                {{ producto.stock_actual }} {{ producto.unidad }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              ${{ Number(producto.precio_venta).toLocaleString() }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="[
                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                producto.activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
              ]">
                {{ producto.activo ? 'Activo' : 'Inactivo' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button
                @click="editProducto(producto)"
                class="text-indigo-600 hover:text-indigo-900 mr-3"
              >
                Editar
              </button>
              <button
                @click="deleteProducto(producto)"
                class="text-red-600 hover:text-red-900"
              >
                Eliminar
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import ProductoService from '@/services/ProductoService';
import { useNotificationStore } from '@/stores/notification';

const notificationStore = useNotificationStore();

// Estado
const productos = ref([]);
const isLoading = ref(false);
const showCreateModal = ref(false);
const activeTab = ref('automotriz');

// Filtros
const filters = ref({
  search: '',
  categoria: '',
  activo: ''
});

// Computed
const filteredProductos = computed(() => {
  let filtered = productos.value;
  
  if (filters.value.search) {
    const search = filters.value.search.toLowerCase();
    filtered = filtered.filter(producto => 
      producto.nombre.toLowerCase().includes(search) ||
      producto.codigo.toLowerCase().includes(search) ||
      (producto.descripcion && producto.descripcion.toLowerCase().includes(search))
    );
  }
  
  if (filters.value.categoria) {
    filtered = filtered.filter(producto => producto.categoria === filters.value.categoria);
  }
  
  if (filters.value.activo !== '') {
    filtered = filtered.filter(producto => producto.activo == filters.value.activo);
  }
  
  return filtered;
});

const categorias = computed(() => {
  const cats = [...new Set(productos.value.map(p => p.categoria).filter(c => c))];
  return cats.sort();
});

// Métodos
const loadProductos = async () => {
  try {
    isLoading.value = true;
    const service = activeTab.value === 'automotriz' ? 'automotriz' : 'despensa';
    const response = await ProductoService.getAll(service);
    productos.value = response.data || [];
  } catch (error) {
    console.error('Error cargando productos:', error);
    notificationStore.addNotification({
      type: 'error',
      title: 'Error',
      message: 'No se pudieron cargar los productos'
    });
  } finally {
    isLoading.value = false;
  }
};

const editProducto = (producto) => {
  // TODO: Implementar modal de edición
  notificationStore.addNotification({
    type: 'info',
    title: 'Función en desarrollo',
    message: 'La edición de productos se implementará próximamente'
  });
};

const deleteProducto = async (producto) => {
  if (confirm(`¿Está seguro de eliminar el producto ${producto.nombre}?`)) {
    try {
      const service = activeTab.value === 'automotriz' ? 'automotriz' : 'despensa';
      await ProductoService.delete(producto.id, service);
      await loadProductos();
      notificationStore.addNotification({
        type: 'success',
        title: 'Éxito',
        message: 'Producto eliminado correctamente'
      });
    } catch (error) {
      console.error('Error eliminando producto:', error);
      notificationStore.addNotification({
        type: 'error',
        title: 'Error',
        message: 'No se pudo eliminar el producto'
      });
    }
  }
};

// Watchers
watch(activeTab, () => {
  loadProductos();
});

// Inicialización
onMounted(() => {
  loadProductos();
});
</script>
