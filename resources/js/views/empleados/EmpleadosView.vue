<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">
          Empleados
        </h1>
        <p class="text-gray-600 mt-1">
          Gestionar empleados y su información
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
          :to="{ name: 'CrearEmpleado' }"
          class="material-button-filled"
        >
          <PlusIcon class="h-4 w-4 mr-2" />
          Nuevo Empleado
        </router-link>
      </div>
    </div>

    <!-- Filtros -->
    <div class="material-card">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="form-label">Buscar</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Nombre, cédula, teléfono..."
              class="material-input"
              @input="debouncedSearch"
            />
          </div>
          
          <div>
            <label class="form-label">Tipo de Salario</label>
            <select v-model="filters.tipo_salario" class="material-input">
              <option value="">Todos</option>
              <option value="mensual">Mensual</option>
              <option value="quincenal">Quincenal</option>
              <option value="semanal">Semanal</option>
              <option value="diario">Diario</option>
            </select>
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

    <!-- Tabla de empleados -->
    <div class="material-card">
      <div class="card-header">
        <h3 class="text-lg font-semibold text-gray-900">
          Lista de Empleados ({{ pagination.total || 0 }})
        </h3>
      </div>
      
      <div class="card-body p-0">
        <div v-if="isLoading" class="p-6">
          <div class="space-y-4">
            <div v-for="i in 5" :key="i" class="skeleton-loader h-16"></div>
          </div>
        </div>
        
        <div v-else-if="empleados.length === 0" class="p-6 text-center text-gray-500">
          <UsersIcon class="w-12 h-12 mx-auto mb-4 text-gray-300" />
          <p>No se encontraron empleados</p>
          <p class="text-sm mt-2">
            {{ filters.search ? 'Intenta con diferentes términos de búsqueda' : 'Comienza agregando tu primer empleado' }}
          </p>
        </div>
        
        <div v-else class="overflow-x-auto">
          <table class="data-table">
            <thead class="data-table-header">
              <tr>
                <th>Empleado</th>
                <th>Cédula</th>
                <th>Teléfono</th>
                <th>Tipo Salario</th>
                <th>Salario</th>
                <th>Estado</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody class="data-table-body">
              <tr v-for="empleado in empleados" :key="empleado.id">
                <td>
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center mr-3">
                      <span class="text-primary-600 font-medium text-sm">
                        {{ getInitials(empleado.nombres, empleado.apellidos) }}
                      </span>
                    </div>
                    <div>
                      <div class="font-medium text-gray-900">
                        {{ empleado.nombres }} {{ empleado.apellidos }}
                      </div>
                      <div class="text-sm text-gray-500">
                        ID: {{ empleado.id }}
                      </div>
                    </div>
                  </div>
                </td>
                <td>{{ empleado.cedula }}</td>
                <td>{{ empleado.telefono }}</td>
                <td>
                  <span class="status-badge status-info">
                    {{ empleado.tipo_salario }}
                  </span>
                </td>
                <td class="font-medium">
                  {{ formatCurrency(empleado.salario) }}
                </td>
                <td>
                  <span 
                    :class="[
                      'status-badge',
                      empleado.activo ? 'status-success' : 'status-error'
                    ]"
                  >
                    {{ empleado.activo ? 'Activo' : 'Inactivo' }}
                  </span>
                </td>
                <td>
                  <div class="flex items-center space-x-2">
                    <router-link
                      :to="{ name: 'DetalleEmpleado', params: { id: empleado.id } }"
                      class="text-primary-600 hover:text-primary-700"
                      title="Ver detalles"
                    >
                      <EyeIcon class="w-4 h-4" />
                    </router-link>
                    <router-link
                      :to="{ name: 'EditarEmpleado', params: { id: empleado.id } }"
                      class="text-blue-600 hover:text-blue-700"
                      title="Editar"
                    >
                      <PencilIcon class="w-4 h-4" />
                    </router-link>
                    <button
                      @click="confirmDelete(empleado)"
                      class="text-red-600 hover:text-red-700"
                      title="Eliminar"
                    >
                      <TrashIcon class="w-4 h-4" />
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
            Mostrando {{ pagination.from }}-{{ pagination.to }} de {{ pagination.total }} empleados
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
import EmpleadoService from '@/services/EmpleadoService';
import {
  PlusIcon,
  ArrowPathIcon,
  UsersIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon
} from '@heroicons/vue/24/outline';

// Composables
const router = useRouter();
const notificationStore = useNotificationStore();

// Estado
const empleados = ref([]);
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
  tipo_salario: '',
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
const fetchEmpleados = async (page = 1) => {
  try {
    isLoading.value = true;
    
    const params = {
      page,
      per_page: pagination.per_page,
      ...filters
    };
    
    const response = await EmpleadoService.getEmpleados(params);
    
    empleados.value = response.data || [];
    Object.assign(pagination, {
      current_page: response.current_page || 1,
      last_page: response.last_page || 1,
      per_page: response.per_page || 15,
      total: response.total || 0,
      from: response.from || 0,
      to: response.to || 0
    });
    
  } catch (error) {
    console.error('Error al cargar empleados:', error);
    notificationStore.error('Error al cargar los empleados');
  } finally {
    isLoading.value = false;
  }
};

const refreshData = async () => {
  isRefreshing.value = true;
  await fetchEmpleados(pagination.current_page);
  isRefreshing.value = false;
  notificationStore.success('Datos actualizados');
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.last_page) {
    fetchEmpleados(page);
  }
};

const clearFilters = () => {
  filters.search = '';
  filters.tipo_salario = '';
  filters.activo = '';
  fetchEmpleados(1);
};

// Debounced search
let searchTimeout;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchEmpleados(1);
  }, 500);
};

const confirmDelete = (empleado) => {
  if (confirm(`¿Está seguro que desea eliminar al empleado ${empleado.nombres} ${empleado.apellidos}?`)) {
    deleteEmpleado(empleado.id);
  }
};

const deleteEmpleado = async (id) => {
  try {
    await EmpleadoService.deleteEmpleado(id);
    notificationStore.success('Empleado eliminado correctamente');
    fetchEmpleados(pagination.current_page);
  } catch (error) {
    console.error('Error al eliminar empleado:', error);
    notificationStore.error('Error al eliminar el empleado');
  }
};

const getInitials = (nombres, apellidos) => {
  const n = nombres ? nombres.charAt(0) : '';
  const a = apellidos ? apellidos.charAt(0) : '';
  return (n + a).toUpperCase();
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: 'USD'
  }).format(amount || 0);
};

// Lifecycle
onMounted(() => {
  fetchEmpleados();
});
</script>
