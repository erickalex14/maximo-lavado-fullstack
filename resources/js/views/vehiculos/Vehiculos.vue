<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Vehículos</h1>
        <p class="text-gray-600">Gestión de vehículos registrados</p>
      </div>
      <button
        @click="showCreateModal = true"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nuevo Vehículo
      </button>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow mb-6 p-4">
      <div class="flex gap-4">
        <div class="flex-1">
          <input
            v-model="searchTerm"
            @input="debouncedSearch"
            type="text"
            placeholder="Buscar vehículos..."
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
        <div class="w-48">
          <select
            v-model="tipoFilter"
            @change="loadVehiculos"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Todos los tipos</option>
            <option value="moto">Moto</option>
            <option value="camioneta">Camioneta</option>
            <option value="auto_pequeno">Auto Pequeño</option>
            <option value="auto_mediano">Auto Mediano</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div v-if="loading" class="p-8 text-center">
        <LoadingSpinner />
      </div>
      
      <div v-else-if="vehiculos.length === 0" class="p-8 text-center text-gray-500">
        No hay vehículos registrados
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Cliente
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Tipo
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Matrícula
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Descripción
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Acciones
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="vehiculo in vehiculos" :key="vehiculo.vehiculo_id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="font-medium text-gray-900">
                  {{ vehiculo.cliente?.nombre || 'Sin cliente' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="{
                    'bg-blue-100 text-blue-800': vehiculo.tipo === 'moto',
                    'bg-green-100 text-green-800': vehiculo.tipo === 'camioneta',
                    'bg-purple-100 text-purple-800': vehiculo.tipo === 'auto_pequeno',
                    'bg-yellow-100 text-yellow-800': vehiculo.tipo === 'auto_mediano',
                  }"
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                >
                  {{ formatTipoVehiculo(vehiculo.tipo) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                {{ vehiculo.matricula || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                {{ vehiculo.descripcion || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end gap-2">
                  <button
                    @click="viewVehiculo(vehiculo)"
                    class="text-blue-600 hover:text-blue-900"
                    title="Ver detalles"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button
                    @click="editVehiculo(vehiculo)"
                    class="text-indigo-600 hover:text-indigo-900"
                    title="Editar"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button
                    @click="deleteVehiculo(vehiculo)"
                    class="text-red-600 hover:text-red-900"
                    title="Eliminar"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div v-if="pagination.total > pagination.per_page" class="px-6 py-3 bg-gray-50 border-t">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Mostrando {{ pagination.from }} a {{ pagination.to }} de {{ pagination.total }} resultados
          </div>
          <div class="flex gap-2">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="px-3 py-1 text-sm border rounded disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Anterior
            </button>
            <span class="px-3 py-1 text-sm bg-blue-100 text-blue-800 rounded">
              {{ pagination.current_page }} de {{ pagination.last_page }}
            </span>
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="px-3 py-1 text-sm border rounded disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Siguiente
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Crear/Editar -->
    <VehiculoModal
      v-if="showCreateModal || showEditModal"
      :is-open="showCreateModal || showEditModal"
      :vehiculo="editingVehiculo"
      @close="closeModals"
      @success="handleSuccess"
    />

    <!-- Modal de Ver -->
    <VehiculoViewModal
      v-if="showViewModal"
      :is-open="showViewModal"
      :vehiculo="viewingVehiculo"
      @close="closeModals"
      @edit="editVehiculo"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';
import VehiculoModal from './VehiculoModal.vue';
import VehiculoViewModal from './VehiculoViewModal.vue';
import type { Vehiculo, PaginatedResponse } from '@/types';

// Estado reactivo
const vehiculos = ref<Vehiculo[]>([]);
const loading = ref(false);
const searchTerm = ref('');
const tipoFilter = ref('');

// Paginación
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
});

// Modales
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showViewModal = ref(false);
const editingVehiculo = ref<Vehiculo | null>(null);
const viewingVehiculo = ref<Vehiculo | null>(null);

// Busqueda con debounce
const debouncedSearch = () => {
  pagination.value.current_page = 1;
  loadVehiculos();
};

// Formatear tipo de vehículo
const formatTipoVehiculo = (tipo: string) => {
  switch (tipo) {
    case 'moto':
      return 'Moto';
    case 'camioneta':
      return 'Camioneta';
    case 'auto_pequeno':
      return 'Auto Pequeño';
    case 'auto_mediano':
      return 'Auto Mediano';
    default:
      return 'N/A';
  }
};

// Cargar vehículos
const loadVehiculos = async () => {
  try {
    loading.value = true;
    
    const params = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page,
      ...(searchTerm.value && { search: searchTerm.value }),
      ...(tipoFilter.value && { tipo: tipoFilter.value })
    };

    // Temporalmente usar datos vacíos hasta que se implemente el store
    const response = {
      data: [],
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0,
      from: 0,
      to: 0
    };
    
    vehiculos.value = response.data;
    pagination.value = {
      current_page: response.current_page,
      last_page: response.last_page,
      per_page: response.per_page,
      total: response.total,
      from: response.from,
      to: response.to
    };
  } catch (error) {
    console.error('Error loading vehiculos:', error);
  } finally {
    loading.value = false;
  }
};

// Cambiar página
const changePage = (page: number) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    pagination.value.current_page = page;
    loadVehiculos();
  }
};

// Acciones del modal
const editVehiculo = (vehiculo: Vehiculo) => {
  editingVehiculo.value = vehiculo;
  showEditModal.value = true;
  showViewModal.value = false;
};

const viewVehiculo = (vehiculo: Vehiculo) => {
  viewingVehiculo.value = vehiculo;
  showViewModal.value = true;
};

const deleteVehiculo = async (vehiculo: Vehiculo) => {
  if (confirm(`¿Estás seguro de que deseas eliminar el vehículo ${vehiculo.descripcion || 'seleccionado'}?`)) {
    try {
      // await vehiculoStore.deleteVehiculo(vehiculo.vehiculo_id);
      await loadVehiculos();
    } catch (error) {
      console.error('Error deleting vehiculo:', error);
    }
  }
};

const closeModals = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  showViewModal.value = false;
  editingVehiculo.value = null;
  viewingVehiculo.value = null;
};

const handleSuccess = () => {
  closeModals();
  loadVehiculos();
};

// Cargar datos al montar
onMounted(() => {
  loadVehiculos();
});
</script>
