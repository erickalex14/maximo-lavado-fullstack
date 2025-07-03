<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-surface-900">Clientes</h1>
        <p class="mt-1 text-sm text-surface-600">Gestiona los clientes del lavado de autos</p>
      </div>
      <div class="mt-4 sm:mt-0 flex items-center space-x-3">
        <button
          @click="showCreateModal = true"
          class="btn-primary flex items-center"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Nuevo Cliente
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <MetricCard
        title="Total Clientes"
        :value="stats.total_clientes || 0"
        icon="users"
        color="blue"
      />
      <MetricCard
        title="Clientes Activos"
        :value="stats.clientes_activos || 0"
        icon="user-check"
        color="green"
      />
      <MetricCard
        title="Nuevos Este Mes"
        :value="stats.nuevos_mes || 0"
        icon="user-plus"
        color="purple"
      />
      <MetricCard
        title="Con Vehículos"
        :value="stats.con_vehiculos || 0"
        icon="truck"
        color="blue"
      />
    </div>

    <!-- Filters -->
    <div class="card">
      <div class="card-body">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-surface-700 mb-1">Buscar</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Nombre, email o teléfono..."
              class="input-base"
              @input="debouncedSearch"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-surface-700 mb-1">Estado</label>
            <select v-model="filters.activo" class="input-base" @change="loadClientes">
              <option value="">Todos</option>
              <option value="1">Activos</option>
              <option value="0">Inactivos</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-surface-700 mb-1">Ordenar por</label>
            <select v-model="filters.sortBy" class="input-base" @change="loadClientes">
              <option value="nombre">Nombre</option>
              <option value="email">Email</option>
              <option value="created_at">Fecha de registro</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-surface-700 mb-1">Orden</label>
            <select v-model="filters.sortOrder" class="input-base" @change="loadClientes">
              <option value="asc">Ascendente</option>
              <option value="desc">Descendente</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="card">
      <div class="card-body p-0">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-surface-200">
            <thead class="bg-surface-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">
                  Cliente
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">
                  Contacto
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">
                  Vehículos
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">
                  Estado
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-surface-500 uppercase tracking-wider">
                  Registro
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-surface-500 uppercase tracking-wider">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-surface-200">
              <tr v-if="loading">
                <td colspan="6" class="px-6 py-12 text-center">
                  <LoadingSpinner class="mx-auto" />
                  <p class="mt-2 text-sm text-surface-500">Cargando clientes...</p>
                </td>
              </tr>
              <tr v-else-if="clientes.length === 0">
                <td colspan="6" class="px-6 py-12 text-center">
                  <svg class="mx-auto h-12 w-12 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  <p class="mt-2 text-sm text-surface-500">No hay clientes registrados</p>
                </td>
              </tr>
              <tr v-else v-for="cliente in clientes" :key="cliente.cliente_id" class="hover:bg-surface-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                        <span class="text-sm font-medium text-primary-800">
                          {{ cliente.nombre.charAt(0).toUpperCase() }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-surface-900">{{ cliente.nombre }}</div>
                      <div class="text-sm text-surface-500">{{ cliente.cedula }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-surface-900">{{ cliente.email }}</div>
                  <div class="text-sm text-surface-500">{{ cliente.telefono }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ cliente.vehiculos_count || 0 }} vehículos
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="!cliente.deleted_at ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                  >
                    {{ !cliente.deleted_at ? 'Activo' : 'Inactivo' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-surface-500">
                  {{ formatDate(cliente.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <button
                      @click="viewCliente(cliente)"
                      class="text-primary-600 hover:text-primary-900"
                      title="Ver detalles"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                    <button
                      @click="editCliente(cliente)"
                      class="text-blue-600 hover:text-blue-900"
                      title="Editar"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button
                      @click="toggleActive(cliente)"
                      :class="!cliente.deleted_at ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'"
                      :title="!cliente.deleted_at ? 'Desactivar' : 'Activar'"
                    >
                      <svg v-if="!cliente.deleted_at" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                      </svg>
                      <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total > pagination.per_page" class="px-6 py-3 border-t border-surface-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-surface-700">
              Mostrando {{ pagination.from }} a {{ pagination.to }} de {{ pagination.total }} resultados
            </div>
            <div class="flex items-center space-x-2">
              <button
                @click="changePage(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="btn-secondary btn-sm"
                :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === 1 }"
              >
                Anterior
              </button>
              <span class="text-sm text-surface-700">
                Página {{ pagination.current_page }} de {{ pagination.last_page }}
              </span>
              <button
                @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="btn-secondary btn-sm"
                :class="{ 'opacity-50 cursor-not-allowed': pagination.current_page === pagination.last_page }"
              >
                Siguiente
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <ClienteModal
      v-if="showCreateModal || showEditModal"
      :is-open="showCreateModal || showEditModal"
      :cliente="selectedCliente"
      :is-editing="showEditModal"
      @close="closeModal"
      @saved="onClienteSaved"
    />

    <!-- View Modal -->
    <ClienteViewModal
      v-if="showViewModal"
      :is-open="showViewModal"
      :cliente="selectedCliente"
      @close="showViewModal = false"
      @edit="editCliente"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue';
import { useClienteStore } from '@/stores/cliente';
import MetricCard from '@/components/common/MetricCard.vue';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';
import ClienteModal from './ClienteModal.vue';
import ClienteViewModal from './ClienteViewModal.vue';
import { debounce } from 'lodash-es';
import type { Cliente } from '@/types';

const clienteStore = useClienteStore();

// State
const loading = ref(false);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showViewModal = ref(false);
const selectedCliente = ref<Cliente | null>(null);

// Filters
const filters = reactive({
  search: '',
  activo: '',
  sortBy: 'nombre',
  sortOrder: 'asc',
  page: 1,
  per_page: 15
});

// Computed
const clientes = computed(() => clienteStore.clientes);
const stats = computed(() => clienteStore.estadisticas || {});
const pagination = computed(() => clienteStore.clientesPaginados || {
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0,
});

// Methods
const loadClientes = async () => {
  loading.value = true;
  try {
    await clienteStore.fetchClientes(filters);
  } catch (error) {
    console.error('Error loading clientes:', error);
  } finally {
    loading.value = false;
  }
};

const loadStats = async () => {
  try {
    await clienteStore.fetchEstadisticas();
  } catch (error) {
    console.error('Error loading stats:', error);
  }
};

const debouncedSearch = debounce(() => {
  filters.page = 1;
  loadClientes();
}, 300);

const changePage = (page: number) => {
  filters.page = page;
  loadClientes();
};

const viewCliente = (cliente: any) => {
  selectedCliente.value = cliente as Cliente;
  showViewModal.value = true;
};

const editCliente = (cliente: any) => {
  selectedCliente.value = cliente as Cliente;
  showViewModal.value = false;
  showEditModal.value = true;
};

const toggleActive = async (cliente: any) => {
  try {
    await clienteStore.toggleActivoCliente(cliente.cliente_id);
    await loadClientes();
  } catch (error) {
    console.error('Error toggling cliente status:', error);
  }
};

const closeModal = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  selectedCliente.value = null;
};

const onClienteSaved = () => {
  closeModal();
  loadClientes();
  loadStats();
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

// Lifecycle
onMounted(() => {
  loadClientes();
  loadStats();
});
</script>
