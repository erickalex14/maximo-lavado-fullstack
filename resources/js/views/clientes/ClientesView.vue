<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">
          Clientes
        </h1>
        <p class="text-gray-600 mt-1">
          Gestionar clientes y su información
        </p>
      </div>
      
      <div class="flex items-center space-x-3 mt-4 lg:mt-0">
        <button 
          @click="refreshData"
          :disabled="isRefreshing"
          class="material-button-outlined"
        >
          <ArrowPathIcon 
            :class="['h-4 w-4 mr-2', { 'animate-spin': isRefreshing }]" 
          />
          Actualizar
        </button>
        
        <router-link 
          :to="{ name: 'CrearCliente' }"
          class="material-button-filled"
        >
          <PlusIcon class="h-4 w-4 mr-2" />
          Nuevo Cliente
        </router-link>
      </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="material-card">
        <div class="card-body">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <UserGroupIcon class="w-6 h-6 text-blue-600" />
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Clientes</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.total || 0 }}</p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="material-card">
        <div class="card-body">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                <CheckCircleIcon class="w-6 h-6 text-green-600" />
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Activos</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.activos || 0 }}</p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="material-card">
        <div class="card-body">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                <CalendarIcon class="w-6 h-6 text-purple-600" />
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Nuevos este mes</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.nuevos_mes || 0 }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="material-card">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="form-label">Buscar</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Nombre, email, cédula..."
              class="material-input"
              @input="debouncedSearch"
            />
          </div>
          
          <div>
            <label class="form-label">Estado</label>
            <select v-model="filters.activo" class="material-input">
              <option value="">Todos</option>
              <option value="1">Activos</option>
              <option value="0">Inactivos</option>
            </select>
          </div>
          
          <div class="flex items-end">
            <button 
              @click="clearFilters"
              class="material-button-outlined w-full"
            >
              Limpiar Filtros
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla de clientes -->
    <div class="material-card">
      <div class="card-header">
        <h3 class="text-lg font-semibold text-gray-900">
          Lista de Clientes ({{ pagination.total || 0 }})
        </h3>
      </div>
      
      <div class="card-body p-0">
        <div v-if="isLoading" class="p-6">
          <div class="space-y-4">
            <div v-for="i in 5" :key="i" class="skeleton-loader h-16"></div>
          </div>
        </div>
        
        <div v-else-if="clientes.length === 0" class="p-6 text-center text-gray-500">
          <UserGroupIcon class="w-12 h-12 mx-auto mb-4 text-gray-300" />
          <p>No se encontraron clientes</p>
          <p class="text-sm mt-2">
            {{ filters.search ? 'Intenta con diferentes términos de búsqueda' : 'Comienza agregando tu primer cliente' }}
          </p>
        </div>
        
        <div v-else class="overflow-x-auto">
          <table class="data-table">
            <thead class="data-table-header">
              <tr>
                <th>Cliente</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Cédula</th>
                <th>Estado</th>
                <th>Registro</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody class="data-table-body">
              <tr v-for="cliente in clientes" :key="cliente.id">
                <td>
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center mr-3">
                      <span class="text-primary-600 font-medium text-sm">
                        {{ getInitials(cliente.nombre) }}
                      </span>
                    </div>
                    <div>
                      <div class="font-medium text-gray-900">
                        {{ cliente.nombre }}
                      </div>
                      <div class="text-sm text-gray-500">
                        ID: {{ cliente.id }}
                      </div>
                    </div>
                  </div>
                </td>
                <td>{{ cliente.email || 'N/A' }}</td>
                <td>{{ cliente.telefono || 'N/A' }}</td>
                <td>{{ cliente.cedula || 'N/A' }}</td>
                <td>
                  <span 
                    :class="[
                      'status-badge',
                      cliente.activo ? 'status-success' : 'status-error'
                    ]"
                  >
                    {{ cliente.activo ? 'Activo' : 'Inactivo' }}
                  </span>
                </td>
                <td class="text-sm text-gray-500">
                  {{ formatDate(cliente.created_at) }}
                </td>
                <td>
                  <div class="flex items-center space-x-2">
                    <router-link
                      :to="{ name: 'DetalleCliente', params: { id: cliente.id } }"
                      class="text-primary-600 hover:text-primary-700"
                      title="Ver detalles"
                    >
                      <EyeIcon class="w-4 h-4" />
                    </router-link>
                    <router-link
                      :to="{ name: 'EditarCliente', params: { id: cliente.id } }"
                      class="text-blue-600 hover:text-blue-700"
                      title="Editar"
                    >
                      <PencilIcon class="w-4 h-4" />
                    </router-link>
                    <button
                      @click="toggleActivo(cliente)"
                      :class="[
                        'hover:text-gray-700',
                        cliente.activo ? 'text-yellow-600' : 'text-green-600'
                      ]"
                      :title="cliente.activo ? 'Desactivar' : 'Activar'"
                    >
                      <component :is="cliente.activo ? LockClosedIcon : LockOpenIcon" class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      
      <!-- Paginación -->
      <div v-if="pagination.total > 0" class="card-footer">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-500">
            Mostrando {{ pagination.from }}-{{ pagination.to }} de {{ pagination.total }} clientes
          </div>
          
          <div class="flex items-center space-x-2">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page <= 1"
              class="material-button-outlined text-sm py-1 px-3"
            >
              Anterior
            </button>
            
            <div class="flex items-center space-x-1">
              <button
                v-for="page in visiblePages"
                :key="page"
                @click="changePage(page)"
                :class="[
                  'w-8 h-8 text-sm rounded flex items-center justify-center',
                  page === pagination.current_page
                    ? 'bg-primary-500 text-white'
                    : 'text-gray-500 hover:bg-gray-100'
                ]"
              >
                {{ page }}
              </button>
            </div>
            
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page >= pagination.last_page"
              class="material-button-outlined text-sm py-1 px-3"
            >
              Siguiente
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useNotificationStore } from '@/stores/notification';
import ClienteService from '@/services/ClienteService';
import {
  PlusIcon,
  ArrowPathIcon,
  UserGroupIcon,
  CheckCircleIcon,
  CalendarIcon,
  EyeIcon,
  PencilIcon,
  LockClosedIcon,
  LockOpenIcon
} from '@heroicons/vue/24/outline';

// Composables
const router = useRouter();
const notificationStore = useNotificationStore();

// Estado
const clientes = ref([]);
const stats = ref({});
const isLoading = ref(false);
const isRefreshing = ref(false);

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
});

const filters = reactive({
  search: '',
  activo: ''
});

// Computed
const visiblePages = computed(() => {
  const current = pagination.current_page;
  const last = pagination.last_page;
  const pages = [];
  
  let start = Math.max(1, current - 2);
  let end = Math.min(last, current + 2);
  
  if (end - start < 4) {
    if (start === 1) {
      end = Math.min(last, start + 4);
    } else {
      start = Math.max(1, end - 4);
    }
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  
  return pages;
});

// Methods
const fetchClientes = async (page = 1) => {
  try {
    isLoading.value = true;
    
    const params = {
      page,
      per_page: pagination.per_page,
      ...filters
    };
    
    const response = await ClienteService.getClientes(params);
    
    clientes.value = response.data || [];
    Object.assign(pagination, {
      current_page: response.current_page || 1,
      last_page: response.last_page || 1,
      per_page: response.per_page || 15,
      total: response.total || 0,
      from: response.from || 0,
      to: response.to || 0
    });
    
  } catch (error) {
    console.error('Error al cargar clientes:', error);
    notificationStore.error('Error al cargar los clientes');
  } finally {
    isLoading.value = false;
  }
};

const fetchStats = async () => {
  try {
    const response = await ClienteService.getStats();
    stats.value = response;
  } catch (error) {
    console.error('Error al cargar estadísticas:', error);
  }
};

const refreshData = async () => {
  isRefreshing.value = true;
  await Promise.all([
    fetchClientes(pagination.current_page),
    fetchStats()
  ]);
  isRefreshing.value = false;
  notificationStore.success('Datos actualizados');
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.last_page) {
    fetchClientes(page);
  }
};

const clearFilters = () => {
  filters.search = '';
  filters.activo = '';
  fetchClientes(1);
};

// Debounced search
let searchTimeout;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchClientes(1);
  }, 500);
};

const toggleActivo = async (cliente) => {
  try {
    await ClienteService.toggleActivo(cliente.id);
    cliente.activo = !cliente.activo;
    notificationStore.success(
      `Cliente ${cliente.activo ? 'activado' : 'desactivado'} correctamente`
    );
    await fetchStats(); // Actualizar estadísticas
  } catch (error) {
    console.error('Error al cambiar estado del cliente:', error);
    notificationStore.error('Error al cambiar el estado del cliente');
  }
};

const getInitials = (nombre) => {
  if (!nombre) return 'N/A';
  const names = nombre.split(' ');
  if (names.length >= 2) {
    return (names[0][0] + names[1][0]).toUpperCase();
  }
  return nombre.substring(0, 2).toUpperCase();
};

const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('es-ES');
};

// Lifecycle
onMounted(() => {
  Promise.all([
    fetchClientes(),
    fetchStats()
  ]);
});
</script>
