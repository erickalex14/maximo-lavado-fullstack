<template>
  <div class="ventas-despensa-tab">
    <!-- Filtros específicos para despensa -->
    <div class="bg-white dark:bg-surface-800 rounded-lg shadow-sm border border-surface-200 dark:border-surface-700 p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">
            Buscar
          </label>
          <input
            v-model="searchTerm"
            type="text"
            placeholder="Buscar por producto..."
            class="w-full px-3 py-2 border border-surface-300 dark:border-surface-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-surface-700 dark:text-surface-100"
            @input="applyFilters"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">
            Cliente
          </label>
          <select 
            v-model="selectedCliente" 
            class="w-full px-3 py-2 border border-surface-300 dark:border-surface-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-surface-700 dark:text-surface-100"
            @change="applyFilters"
          >
            <option value="">Todos los clientes</option>
            <option
              v-for="cliente in clientes"
              :key="cliente.cliente_id"
              :value="cliente.cliente_id"
            >
              {{ cliente.nombre }}
            </option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">
            Fecha Inicio
          </label>
          <input
            v-model="fechaInicio"
            type="date"
            class="w-full px-3 py-2 border border-surface-300 dark:border-surface-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-surface-700 dark:text-surface-100"
            @change="applyFilters"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">
            Fecha Fin
          </label>
          <input
            v-model="fechaFin"
            type="date"
            class="w-full px-3 py-2 border border-surface-300 dark:border-surface-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-surface-700 dark:text-surface-100"
            @change="applyFilters"
          />
        </div>
      </div>
      
      <div class="flex justify-between items-center mt-4">
        <div class="text-sm text-surface-600 dark:text-surface-400">
          Total: {{ pagination.total }} ventas de despensa
        </div>
        
        <div class="flex gap-2">
          <button
            @click="clearFilters"
            class="px-4 py-2 border border-surface-300 text-surface-700 hover:bg-surface-50 dark:border-surface-600 dark:text-surface-300 dark:hover:bg-surface-700 rounded-lg font-medium transition-colors"
          >
            Limpiar Filtros
          </button>
          <button
            @click="refreshData"
            class="px-4 py-2 bg-primary-500 text-white hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg font-medium transition-colors flex items-center"
            :disabled="loading"
          >
            <svg v-if="loading" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Actualizar
          </button>
        </div>
      </div>
    </div>

    <!-- Tabla de ventas de despensa -->
    <div class="bg-white dark:bg-surface-800 rounded-lg shadow-sm border border-surface-200 dark:border-surface-700">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-surface-200 dark:divide-surface-700">
          <thead>
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider bg-surface-50 dark:bg-surface-700">
                ID
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider bg-surface-50 dark:bg-surface-700">
                Producto
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider bg-surface-50 dark:bg-surface-700">
                Cliente
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider bg-surface-50 dark:bg-surface-700">
                Cantidad
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider bg-surface-50 dark:bg-surface-700">
                Precio Unit.
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider bg-surface-50 dark:bg-surface-700">
                Total
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider bg-surface-50 dark:bg-surface-700">
                Fecha
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-surface-500 dark:text-surface-400 uppercase tracking-wider bg-surface-50 dark:bg-surface-700">
                Acciones
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-surface-200 dark:divide-surface-700">
            <tr v-if="loading && ventas.length === 0">
              <td colspan="8" class="px-6 py-8 text-center text-surface-500 dark:text-surface-400">
                <div class="flex items-center justify-center">
                  <svg class="animate-spin w-6 h-6 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Cargando ventas de despensa...
                </div>
              </td>
            </tr>
            
            <tr v-else-if="!loading && filteredVentas.length === 0">
              <td colspan="8" class="px-6 py-8 text-center text-surface-500 dark:text-surface-400">
                <div class="flex flex-col items-center">
                  <svg class="w-12 h-12 mb-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                  </svg>
                  <p class="text-lg font-medium">No hay ventas de despensa</p>
                  <p class="text-sm">No se encontraron resultados con los filtros aplicados</p>
                </div>
              </td>
            </tr>
            
            <tr
              v-else
              v-for="venta in filteredVentas"
              :key="venta.id"
              class="hover:bg-surface-50 dark:hover:bg-surface-800 transition-colors"
            >
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-surface-900 dark:text-surface-100">
                #{{ venta.id }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-surface-900 dark:text-surface-100">
                    {{ venta.producto_despensa?.nombre }}
                  </div>
                  <div v-if="venta.producto_despensa?.descripcion" class="text-sm text-surface-500 dark:text-surface-400">
                    {{ venta.producto_despensa.descripcion }}
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-surface-900 dark:text-surface-100">
                {{ venta.cliente?.nombre || 'Cliente general' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-surface-900 dark:text-surface-100">
                {{ venta.cantidad }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-surface-900 dark:text-surface-100">
                ${{ formatCurrency(venta.precio_unitario) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-surface-900 dark:text-surface-100">
                ${{ formatCurrency(venta.cantidad * venta.precio_unitario) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-surface-900 dark:text-surface-100">
                {{ formatDate(venta.fecha) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center space-x-2">
                  <button
                    @click="viewVenta(venta)"
                    class="p-2 text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20 rounded-md transition-colors"
                    title="Ver detalles"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  
                  <button
                    @click="editVenta(venta)"
                    class="p-2 text-green-600 hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/20 rounded-md transition-colors"
                    title="Editar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  
                  <button
                    @click="deleteVenta(venta)"
                    class="p-2 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20 rounded-md transition-colors"
                    title="Eliminar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
      <div v-if="pagination.total > 0" class="px-6 py-4 border-t border-surface-200 dark:border-surface-700">
        <TablePagination
          :current-page="pagination.current_page"
          :total-pages="pagination.last_page"
          :per-page="pagination.per_page"
          :total="pagination.total"
          @page-change="handlePageChange"
          @per-page-change="handlePerPageChange"
        />
      </div>
    </div>

    <!-- Modal -->
    <VentaDespensaModal
      v-model:visible="showModal"
      :mode="modalMode"
      :venta="selectedVenta"
      @saved="handleVentaSaved"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useVentaStore } from '@/stores/venta';
import VentaDespensaModal from '../VentaDespensaModal.vue';
import TablePagination from '@/components/ui/TablePagination.vue';
import type { VentaProductoDespensa } from '@/types';

// Store
const ventaStore = useVentaStore();

// State
const searchTerm = ref('');
const selectedCliente = ref('');
const fechaInicio = ref('');
const fechaFin = ref('');
const showModal = ref(false);
const modalMode = ref<'view' | 'edit'>('view');
const selectedVenta = ref<VentaProductoDespensa | null>(null);

// Computed
const loading = computed(() => ventaStore.loading);
const ventas = computed(() => ventaStore.ventasDespensa);
const pagination = computed(() => ventaStore.ventasDespensaPagination);
const clientes = computed(() => ventaStore.clientes);

// Filtros aplicados
const filteredVentas = computed(() => {
  let result = ventas.value;

  // Filtro por búsqueda
  if (searchTerm.value) {
    const term = searchTerm.value.toLowerCase();
    result = result.filter(v => 
      v.producto_despensa?.nombre?.toLowerCase().includes(term)
    );
  }

  // Filtro por cliente
  if (selectedCliente.value) {
    result = result.filter(v => v.cliente_id === Number(selectedCliente.value));
  }

  // Filtro por fecha
  if (fechaInicio.value) {
    result = result.filter(v => v.fecha >= fechaInicio.value);
  }
  
  if (fechaFin.value) {
    result = result.filter(v => v.fecha <= fechaFin.value);
  }

  return result;
});

// Methods
function formatCurrency(value: number): string {
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('es-CO');
}

function applyFilters() {
  const filters = {
    search: searchTerm.value,
    cliente_id: selectedCliente.value ? Number(selectedCliente.value) : undefined,
    fecha_inicio: fechaInicio.value || undefined,
    fecha_fin: fechaFin.value || undefined,
    page: 1 // Reset a primera página cuando se aplican filtros
  };
  
  ventaStore.fetchVentasDespensa(filters);
}

function clearFilters() {
  searchTerm.value = '';
  selectedCliente.value = '';
  fechaInicio.value = '';
  fechaFin.value = '';
  ventaStore.clearFilters();
  ventaStore.fetchVentasDespensa();
}

async function refreshData() {
  await ventaStore.fetchVentasDespensa();
}

function viewVenta(venta: VentaProductoDespensa) {
  selectedVenta.value = venta;
  modalMode.value = 'view';
  showModal.value = true;
}

function editVenta(venta: VentaProductoDespensa) {
  selectedVenta.value = venta;
  modalMode.value = 'edit';
  showModal.value = true;
}

async function deleteVenta(venta: VentaProductoDespensa) {
  if (!confirm(`¿Estás seguro de que deseas eliminar esta venta de ${venta.producto_despensa?.nombre}?`)) {
    return;
  }

  await ventaStore.deleteVentaDespensa(venta.id);
}

function handleVentaSaved() {
  showModal.value = false;
  selectedVenta.value = null;
  ventaStore.fetchVentasDespensa();
}

function handlePageChange(page: number) {
  ventaStore.fetchVentasDespensa({ page });
}

function handlePerPageChange(perPage: number) {
  ventaStore.fetchVentasDespensa({ per_page: perPage, page: 1 });
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    ventaStore.fetchVentasDespensa(),
    ventaStore.fetchClientes()
  ]);
});
</script>
