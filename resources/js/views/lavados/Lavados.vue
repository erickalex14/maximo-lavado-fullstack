<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Lavados</h1>
        <p class="text-gray-600 mt-1">Gestiona todos los servicios de lavado</p>
      </div>
      <button
        @click="openCreateModal"
        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center gap-2"
      >
        <PlusIcon class="h-5 w-5" />
        Nuevo Lavado
      </button>
    </div>

    <!-- Filtros -->
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Búsqueda -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
          <input
            v-model="searchTerm"
            @input="handleSearch"
            type="text"
            placeholder="Buscar lavados..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>

        <!-- Tipo de Vehículo -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Vehículo</label>
          <select
            v-model="filters.tipo_vehiculo"
            @change="applyFilters"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">Todos</option>
            <option value="moto">Moto</option>
            <option value="camioneta">Camioneta</option>
            <option value="auto_pequeno">Auto Pequeño</option>
            <option value="auto_mediano">Auto Mediano</option>
          </select>
        </div>

        <!-- Empleado -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Empleado</label>
          <select
            v-model="filters.empleado_id"
            @change="applyFilters"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">Todos</option>
            <option v-for="empleado in empleados" :key="empleado.empleado_id" :value="empleado.empleado_id">
              {{ empleado.nombres }} {{ empleado.apellidos }}
            </option>
          </select>
        </div>

        <!-- Fecha -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Fecha</label>
          <input
            v-model="filters.fecha_inicio"
            @change="applyFilters"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>
      </div>

      <!-- Botón limpiar filtros -->
      <div class="mt-4 flex justify-end">
        <button
          @click="clearFilters"
          class="text-gray-600 hover:text-gray-800 font-medium"
        >
          Limpiar filtros
        </button>
      </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <TruckIcon class="h-8 w-8 text-blue-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Lavados Hoy</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.lavados_hoy || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <CurrencyDollarIcon class="h-8 w-8 text-green-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Ingresos Hoy</p>
            <p class="text-2xl font-bold text-gray-900">${{ stats.ingresos_hoy || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <CalendarDaysIcon class="h-8 w-8 text-purple-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Lavados Este Mes</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.lavados_mes || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <ChartBarIcon class="h-8 w-8 text-orange-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Promedio Diario</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.promedio_diario || 0 }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla de lavados -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">
          Lista de Lavados ({{ totalLavados }})
        </h3>
      </div>

      <!-- Loading state -->
      <div v-if="isLoading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>

      <!-- Error state -->
      <div v-else-if="hasError" class="text-center py-12">
        <div class="text-red-600 mb-4">
          <ExclamationTriangleIcon class="h-12 w-12 mx-auto" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Error al cargar lavados</h3>
        <p class="text-gray-600 mb-4">{{ error }}</p>
        <button
          @click="loadLavados"
          class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg"
        >
          Reintentar
        </button>
      </div>

      <!-- Empty state -->
      <div v-else-if="!hasLavados" class="text-center py-12">
        <TruckIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">No hay lavados</h3>
        <p class="text-gray-600 mb-4">Comienza registrando tu primer lavado</p>
        <button
          @click="openCreateModal"
          class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg"
        >
          Crear Lavado
        </button>
      </div>

      <!-- Tabla -->
      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Fecha
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Vehículo
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Cliente
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Empleado
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Tipo Lavado
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Precio
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Pulverizado
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Acciones
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="lavado in lavados" :key="lavado.lavado_id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(lavado.fecha) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  {{ formatTipoVehiculo(lavado.vehiculo?.tipo || '') }}
                </div>
                <div class="text-sm text-gray-500">
                  <span v-if="lavado.vehiculo?.matricula">{{ lavado.vehiculo.matricula }}</span>
                  <span v-else class="text-gray-400">Sin matrícula</span>
                  <span v-if="lavado.vehiculo?.descripcion"> - {{ lavado.vehiculo.descripcion }}</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  {{ lavado.vehiculo?.cliente?.nombre }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ lavado.vehiculo?.cliente?.telefono }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  {{ lavado.empleado?.nombres }} {{ lavado.empleado?.apellidos }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                      :class="getTipoLavadoClass(lavado.tipo_lavado)">
                  {{ formatTipoLavado(lavado.tipo_lavado) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                ${{ lavado.precio.toFixed(2) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span v-if="lavado.pulverizado" class="text-green-600">
                  <CheckIcon class="h-5 w-5" />
                </span>
                <span v-else class="text-gray-400">
                  <XMarkIcon class="h-5 w-5" />
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end gap-2">
                  <button
                    @click="viewLavado(lavado)"
                    class="text-blue-600 hover:text-blue-900"
                    title="Ver detalles"
                  >
                    <EyeIcon class="h-5 w-5" />
                  </button>
                  <button
                    @click="editLavado(lavado)"
                    class="text-yellow-600 hover:text-yellow-900"
                    title="Editar"
                  >
                    <PencilIcon class="h-5 w-5" />
                  </button>
                  <button
                    @click="confirmDelete(lavado)"
                    class="text-red-600 hover:text-red-900"
                    title="Eliminar"
                  >
                    <TrashIcon class="h-5 w-5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div v-if="hasLavados" class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Mostrando {{ ((currentPage - 1) * filters.per_page) + 1 }} a 
            {{ Math.min(currentPage * filters.per_page, totalLavados) }} de {{ totalLavados }} resultados
          </div>
          
          <div class="flex items-center gap-2">
            <button
              @click="changePage(currentPage - 1)"
              :disabled="currentPage <= 1"
              class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Anterior
            </button>
            
            <span class="px-3 py-1 text-sm">
              Página {{ currentPage }} de {{ lastPage }}
            </span>
            
            <button
              @click="changePage(currentPage + 1)"
              :disabled="currentPage >= lastPage"
              class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Siguiente
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modales -->
    <LavadoModal
      v-if="showCreateModal || showEditModal"
      :lavado="selectedLavado"
      :is-editing="showEditModal"
      @close="closeModals"
      @saved="handleLavadoSaved"
    />

    <LavadoViewModal
      v-if="showViewModal"
      :lavado="selectedLavado"
      @close="closeModals"
      @edit="editLavado"
      @delete="confirmDelete"
    />

    <!-- Modal de confirmación de eliminación -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Confirmar eliminación</h3>
        <p class="text-gray-600 mb-6">
          ¿Estás seguro de que deseas eliminar este lavado? Esta acción no se puede deshacer.
        </p>
        <div class="flex justify-end gap-3">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Cancelar
          </button>
          <button
            @click="deleteLavado"
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
import { useLavadoStore } from '@/stores/lavado';
import { useEmpleadoStore } from '@/stores/empleado';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import type { Lavado } from '@/types';

// Componentes
import LavadoModal from './LavadoModal.vue';
import LavadoViewModal from './LavadoViewModal.vue';

// Iconos
import {
  PlusIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  TruckIcon,
  CurrencyDollarIcon,
  CalendarDaysIcon,
  ChartBarIcon,
  ExclamationTriangleIcon,
  CheckIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline';

// Stores
const lavadoStore = useLavadoStore();
const empleadoStore = useEmpleadoStore();

// Estado local
const searchTerm = ref('');
const selectedLavado = ref<Lavado | null>(null);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showViewModal = ref(false);
const showDeleteModal = ref(false);
const stats = ref<any>({});

// Filtros
const filters = ref({
  page: 1,
  per_page: 15,
  search: '',
  tipo_vehiculo: '',
  empleado_id: undefined as number | undefined,
  fecha_inicio: '',
});

// Computed
const lavados = computed(() => lavadoStore.lavados);
const hasLavados = computed(() => lavadoStore.hasLavados);
const isLoading = computed(() => lavadoStore.isLoading);
const hasError = computed(() => lavadoStore.hasError);
const error = computed(() => lavadoStore.error);
const totalLavados = computed(() => lavadoStore.totalLavados);
const currentPage = computed(() => lavadoStore.currentPage);
const lastPage = computed(() => lavadoStore.lastPage);
const empleados = computed(() => empleadoStore.empleados);

// Métodos
const loadLavados = async () => {
  await lavadoStore.fetchLavados(filters.value);
};

const loadEmpleados = async () => {
  await empleadoStore.fetchEmpleados({ per_page: 100 });
};

const loadStats = async () => {
  try {
    stats.value = await lavadoStore.fetchStats();
  } catch (error) {
    console.error('Error loading stats:', error);
  }
};

const handleSearch = () => {
  filters.value.search = searchTerm.value;
  filters.value.page = 1;
  loadLavados();
};

const applyFilters = () => {
  filters.value.page = 1;
  loadLavados();
};

const clearFilters = () => {
  searchTerm.value = '';
  filters.value = {
    page: 1,
    per_page: 15,
    search: '',
    tipo_vehiculo: '',
    empleado_id: undefined,
    fecha_inicio: '',
  };
  loadLavados();
};

const changePage = async (page: number) => {
  filters.value.page = page;
  await loadLavados();
};

// Modales
const openCreateModal = () => {
  selectedLavado.value = null;
  showCreateModal.value = true;
};

const viewLavado = (lavado: Lavado) => {
  selectedLavado.value = lavado;
  showViewModal.value = true;
};

const editLavado = (lavado: Lavado) => {
  selectedLavado.value = lavado;
  showEditModal.value = true;
  showViewModal.value = false;
};

const confirmDelete = (lavado: Lavado) => {
  selectedLavado.value = lavado;
  showDeleteModal.value = true;
  showViewModal.value = false;
};

const deleteLavado = async () => {
  if (selectedLavado.value) {
    try {
      await lavadoStore.deleteLavado(selectedLavado.value.lavado_id);
      showDeleteModal.value = false;
      selectedLavado.value = null;
    } catch (error) {
      console.error('Error deleting lavado:', error);
    }
  }
};

const closeModals = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  showViewModal.value = false;
  selectedLavado.value = null;
};

const handleLavadoSaved = () => {
  closeModals();
  loadLavados();
  loadStats();
};

// Formatters
const formatDate = (date: string) => {
  return format(new Date(date), 'dd/MM/yyyy', { locale: es });
};

const formatTipoLavado = (tipo: string) => {
  const tipos = {
    'completo': 'Completo',
    'solo_fuera': 'Solo Por Fuera',
    'solo_por_dentro': 'Solo Por Dentro'
  };
  return tipos[tipo as keyof typeof tipos] || tipo;
};

const getTipoLavadoClass = (tipo: string) => {
  const classes = {
    'completo': 'bg-green-100 text-green-800',
    'solo_fuera': 'bg-blue-100 text-blue-800',
    'solo_por_dentro': 'bg-yellow-100 text-yellow-800'
  };
  return classes[tipo as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};

const formatTipoVehiculo = (tipo: string) => {
  const tipos = {
    'moto': 'Moto',
    'camioneta': 'Camioneta',
    'auto_pequeno': 'Auto Pequeño',
    'auto_mediano': 'Auto Mediano'
  };
  return tipos[tipo as keyof typeof tipos] || tipo;
};

// Lifecycle
onMounted(async () => {
  await Promise.all([
    loadLavados(),
    loadEmpleados(),
    loadStats()
  ]);
});
</script>
