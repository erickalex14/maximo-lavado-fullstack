<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Gestión de Vehículos</h1>
      <button
        @click="showCreateModal = true"
        class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg flex items-center"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Nuevo Vehículo
      </button>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Placa, marca, modelo..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Cliente</label>
          <select
            v-model="filters.clienteId"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <option value="">Todos los clientes</option>
            <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">
              {{ cliente.nombre }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
          <select
            v-model="filters.tipo"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <option value="">Todos los tipos</option>
            <option value="auto">Auto</option>
            <option value="camioneta">Camioneta</option>
            <option value="moto">Moto</option>
            <option value="otro">Otro</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Tabla de vehículos -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
      <div v-if="isLoading" class="flex justify-center items-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
      </div>
      
      <div v-else-if="filteredVehiculos.length === 0" class="text-center py-8 text-gray-500">
        No se encontraron vehículos
      </div>
      
      <table v-else class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Placa
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Vehículo
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Cliente
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Tipo
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Color
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Acciones
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="vehiculo in filteredVehiculos" :key="vehiculo.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
              {{ vehiculo.placa }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ vehiculo.marca }} {{ vehiculo.modelo }}</div>
              <div class="text-sm text-gray-500">{{ vehiculo.año }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ vehiculo.cliente?.nombre }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                {{ vehiculo.tipo }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ vehiculo.color }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button
                @click="editVehiculo(vehiculo)"
                class="text-indigo-600 hover:text-indigo-900 mr-3"
              >
                Editar
              </button>
              <button
                @click="deleteVehiculo(vehiculo)"
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
import VehiculoService from '@/services/VehiculoService';
import ClienteService from '@/services/ClienteService';
import { useNotificationStore } from '@/stores/notification';

const notificationStore = useNotificationStore();

// Estado
const vehiculos = ref([]);
const clientes = ref([]);
const isLoading = ref(false);
const showCreateModal = ref(false);

// Filtros
const filters = ref({
  search: '',
  clienteId: '',
  tipo: ''
});

// Computed
const filteredVehiculos = computed(() => {
  let filtered = vehiculos.value;
  
  if (filters.value.search) {
    const search = filters.value.search.toLowerCase();
    filtered = filtered.filter(vehiculo => 
      vehiculo.placa.toLowerCase().includes(search) ||
      vehiculo.marca.toLowerCase().includes(search) ||
      vehiculo.modelo.toLowerCase().includes(search)
    );
  }
  
  if (filters.value.clienteId) {
    filtered = filtered.filter(vehiculo => vehiculo.cliente_id == filters.value.clienteId);
  }
  
  if (filters.value.tipo) {
    filtered = filtered.filter(vehiculo => vehiculo.tipo === filters.value.tipo);
  }
  
  return filtered;
});

// Métodos
const loadVehiculos = async () => {
  try {
    isLoading.value = true;
    const response = await VehiculoService.getAll();
    vehiculos.value = response.data || [];
  } catch (error) {
    console.error('Error cargando vehículos:', error);
    notificationStore.addNotification({
      type: 'error',
      title: 'Error',
      message: 'No se pudieron cargar los vehículos'
    });
  } finally {
    isLoading.value = false;
  }
};

const loadClientes = async () => {
  try {
    const response = await ClienteService.getAll();
    clientes.value = response.data || [];
  } catch (error) {
    console.error('Error cargando clientes:', error);
  }
};

const editVehiculo = (vehiculo) => {
  // TODO: Implementar modal de edición
  notificationStore.addNotification({
    type: 'info',
    title: 'Función en desarrollo',
    message: 'La edición de vehículos se implementará próximamente'
  });
};

const deleteVehiculo = async (vehiculo) => {
  if (confirm(`¿Está seguro de eliminar el vehículo ${vehiculo.placa}?`)) {
    try {
      await VehiculoService.delete(vehiculo.id);
      await loadVehiculos();
      notificationStore.addNotification({
        type: 'success',
        title: 'Éxito',
        message: 'Vehículo eliminado correctamente'
      });
    } catch (error) {
      console.error('Error eliminando vehículo:', error);
      notificationStore.addNotification({
        type: 'error',
        title: 'Error',
        message: 'No se pudo eliminar el vehículo'
      });
    }
  }
};

// Inicialización
onMounted(() => {
  loadVehiculos();
  loadClientes();
});
</script>
