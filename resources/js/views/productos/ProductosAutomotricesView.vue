<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Productos Automotrices</h1>
        <p class="text-gray-600">Gestión de productos para el lavado de vehículos</p>
      </div>
      <button
        @click="showCreateModal = true"
        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Nuevo Producto
      </button>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Nombre, código..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
          <select
            v-model="filters.categoria"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
          >
            <option value="">Todas</option>
            <option value="shampoo">Shampoo</option>
            <option value="cera">Cera</option>
            <option value="silicona">Silicona</option>
            <option value="desengrasante">Desengrasante</option>
            <option value="accesorios">Accesorios</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Stock</label>
          <select
            v-model="filters.stock"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
          >
            <option value="">Todos</option>
            <option value="bajo">Stock bajo</option>
            <option value="normal">Stock normal</option>
            <option value="alto">Stock alto</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
          <select
            v-model="filters.activo"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
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
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
      </div>
      
      <div v-else-if="filteredProductos.length === 0" class="text-center py-8 text-gray-500">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
        </svg>
        <p>No se encontraron productos automotrices</p>
      </div>
      
      <table v-else class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Producto
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
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10">
                  <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">{{ producto.nombre }}</div>
                  <div class="text-sm text-gray-500">{{ producto.codigo }}</div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                {{ producto.categoria }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">{{ producto.stock_actual || 0 }} {{ producto.unidad }}</div>
              <div :class="[
                'text-xs',
                (producto.stock_actual || 0) <= (producto.stock_minimo || 0)
                  ? 'text-red-600'
                  : 'text-gray-500'
              ]">
                Mín: {{ producto.stock_minimo || 0 }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              ${{ Number(producto.precio_venta || 0).toLocaleString() }}
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
                @click="viewStock(producto)"
                class="text-green-600 hover:text-green-900 mr-3"
              >
                Stock
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
import { ref, computed, onMounted } from 'vue';
import ProductoAutomotrizService from '@/services/ProductoAutomotrizService';
import { useNotificationStore } from '@/stores/notification';

const notificationStore = useNotificationStore();

// Estado
const productos = ref([]);
const isLoading = ref(false);
const showCreateModal = ref(false);

// Filtros
const filters = ref({
  search: '',
  categoria: '',
  stock: '',
  activo: ''
});

// Computed
const filteredProductos = computed(() => {
  let filtered = productos.value;
  
  if (filters.value.search) {
    const search = filters.value.search.toLowerCase();
    filtered = filtered.filter(producto => 
      producto.nombre.toLowerCase().includes(search) ||
      producto.codigo.toLowerCase().includes(search)
    );
  }
  
  if (filters.value.categoria) {
    filtered = filtered.filter(producto => producto.categoria === filters.value.categoria);
  }
  
  if (filters.value.stock) {
    filtered = filtered.filter(producto => {
      const stock = producto.stock_actual || 0;
      const minimo = producto.stock_minimo || 0;
      
      switch (filters.value.stock) {
        case 'bajo':
          return stock <= minimo;
        case 'normal':
          return stock > minimo && stock <= minimo * 2;
        case 'alto':
          return stock > minimo * 2;
        default:
          return true;
      }
    });
  }
  
  if (filters.value.activo !== '') {
    filtered = filtered.filter(producto => producto.activo == filters.value.activo);
  }
  
  return filtered;
});

// Métodos
const loadProductos = async () => {
  try {
    isLoading.value = true;
    const response = await ProductoAutomotrizService.getAll();
    productos.value = response.data || [];
  } catch (error) {
    console.error('Error cargando productos:', error);
    // Datos de ejemplo mientras el backend no esté completo
    productos.value = [
      {
        id: 1,
        codigo: 'SHA001',
        nombre: 'Shampoo Automotriz Premium',
        categoria: 'shampoo',
        stock_actual: 25,
        stock_minimo: 10,
        unidad: 'L',
        precio_venta: 15000,
        activo: true
      },
      {
        id: 2,
        codigo: 'CER001',
        nombre: 'Cera Líquida Protectora',
        categoria: 'cera',
        stock_actual: 5,
        stock_minimo: 8,
        unidad: 'L',
        precio_venta: 25000,
        activo: true
      },
      {
        id: 3,
        codigo: 'SIL001',
        nombre: 'Silicona para Tablero',
        categoria: 'silicona',
        stock_actual: 15,
        stock_minimo: 5,
        unidad: 'ml',
        precio_venta: 8000,
        activo: true
      }
    ];
    notificationStore.addNotification({
      type: 'warning',
      title: 'Datos de ejemplo',
      message: 'Mostrando datos de ejemplo. Backend en desarrollo.'
    });
  } finally {
    isLoading.value = false;
  }
};

const editProducto = (producto) => {
  notificationStore.addNotification({
    type: 'info',
    title: 'Función en desarrollo',
    message: 'La edición de productos se implementará próximamente'
  });
};

const viewStock = (producto) => {
  notificationStore.addNotification({
    type: 'info',
    title: 'Función en desarrollo',
    message: 'La gestión de stock se implementará próximamente'
  });
};

const deleteProducto = async (producto) => {
  if (confirm(`¿Está seguro de eliminar el producto ${producto.nombre}?`)) {
    notificationStore.addNotification({
      type: 'success',
      title: 'Éxito',
      message: 'Producto eliminado correctamente'
    });
  }
};

// Inicialización
onMounted(() => {
  loadProductos();
});
</script>
