<template>
  <div class="ventas-general-tab">
    <!-- Filtros -->
    <div class="filters-section mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">
            Buscar
          </label>
          <input
            v-model="searchTerm"
            type="text"
            placeholder="Buscar por producto o cliente..."
            class="form-input"
            @input="debouncedSearch"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">
            Tipo de Venta
          </label>
          <select v-model="selectedTipo" class="form-select" @change="applyFilters">
            <option value="">Todos</option>
            <option value="automotriz">Automotriz</option>
            <option value="despensa">Despensa</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2">
            Fecha Inicio
          </label>
          <input
            v-model="fechaInicio"
            type="date"
            class="form-input"
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
            class="form-input"
            @change="applyFilters"
          />
        </div>
      </div>
      
      <div class="flex justify-end mt-4">
        <button
          @click="clearFilters"
          class="btn btn-outline-secondary mr-2"
        >
          Limpiar Filtros
        </button>
        <button
          @click="refreshData"
          class="btn btn-primary"
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

    <!-- Tabla de ventas unificadas -->
    <div class="table-container">
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Tipo</th>
              <th>Producto</th>
              <th>Cliente</th>
              <th>Cantidad</th>
              <th>Precio Unit.</th>
              <th>Total</th>
              <th>Fecha</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading && ventasUnificadas.length === 0">
              <td colspan="9" class="text-center py-8">
                <div class="flex items-center justify-center">
                  <svg class="animate-spin w-6 h-6 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Cargando ventas...
                </div>
              </td>
            </tr>
            
            <tr v-else-if="!loading && ventasUnificadas.length === 0">
              <td colspan="9" class="text-center py-8 text-surface-500">
                <div class="flex flex-col items-center">
                  <svg class="w-12 h-12 mb-4 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                  </svg>
                  <p class="text-lg font-medium">No hay ventas registradas</p>
                  <p class="text-sm">Agrega tu primera venta para comenzar</p>
                </div>
              </td>
            </tr>
            
            <tr
              v-else
              v-for="venta in filteredVentas"
              :key="`${venta.tipo}-${venta.id}`"
              class="hover:bg-surface-50 dark:hover:bg-surface-800"
            >
              <td>{{ venta.id }}</td>
              <td>
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    venta.tipo === 'automotriz'
                      ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
                      : 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200'
                  ]"
                >
                  {{ venta.tipo === 'automotriz' ? 'Automotriz' : 'Despensa' }}
                </span>
              </td>
              <td class="font-medium">{{ venta.producto_nombre }}</td>
              <td>{{ venta.cliente_nombre }}</td>
              <td>{{ venta.cantidad }}</td>
              <td>${{ formatCurrency(venta.precio_unitario) }}</td>
              <td class="font-semibold">${{ formatCurrency(venta.total) }}</td>
              <td>{{ formatDate(venta.fecha) }}</td>
              <td>
                <div class="flex items-center space-x-2">
                  <button
                    @click="viewVenta(venta)"
                    class="action-btn action-btn-view"
                    title="Ver detalles"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  
                  <button
                    @click="editVenta(venta)"
                    class="action-btn action-btn-edit"
                    title="Editar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  
                  <button
                    @click="deleteVenta(venta)"
                    class="action-btn action-btn-delete"
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
    </div>

    <!-- Modales de vista y edición -->
    <VentaAutomotrizModal
      v-if="selectedVenta && selectedVenta.tipo === 'automotriz'"
      v-model:visible="showEditModal"
      :mode="editMode"
      :venta="selectedVenta.original_data as VentaProductoAutomotriz"
      @saved="handleVentaSaved"
    />

    <VentaDespensaModal
      v-if="selectedVenta && selectedVenta.tipo === 'despensa'"
      v-model:visible="showEditModal"
      :mode="editMode"
      :venta="selectedVenta.original_data as VentaProductoDespensa"
      @saved="handleVentaSaved"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useVentaStore } from '@/stores/venta';
import VentaAutomotrizModal from '../VentaAutomotrizModal.vue';
import VentaDespensaModal from '../VentaDespensaModal.vue';
import type { VentaUnificada, VentaProductoAutomotriz, VentaProductoDespensa } from '@/types';

// Store
const ventaStore = useVentaStore();

// State
const searchTerm = ref('');
const selectedTipo = ref('');
const fechaInicio = ref('');
const fechaFin = ref('');
const showEditModal = ref(false);
const editMode = ref<'view' | 'edit'>('view');
const selectedVenta = ref<VentaUnificada | null>(null);

// Computed
const loading = computed(() => ventaStore.loading);
const ventasUnificadas = computed(() => ventaStore.ventasUnificadas);

// Filtros aplicados
const filteredVentas = computed(() => {
  let ventas = ventasUnificadas.value;

  // Filtro por tipo
  if (selectedTipo.value) {
    ventas = ventas.filter(v => v.tipo === selectedTipo.value);
  }

  // Filtro por búsqueda
  if (searchTerm.value) {
    const term = searchTerm.value.toLowerCase();
    ventas = ventas.filter(v => 
      v.producto_nombre.toLowerCase().includes(term) ||
      v.cliente_nombre?.toLowerCase().includes(term)
    );
  }

  // Filtro por fecha
  if (fechaInicio.value) {
    ventas = ventas.filter(v => v.fecha >= fechaInicio.value);
  }
  
  if (fechaFin.value) {
    ventas = ventas.filter(v => v.fecha <= fechaFin.value);
  }

  return ventas;
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

function debouncedSearch() {
  // En una implementación real, aquí se aplicaría debounce
  applyFilters();
}

function applyFilters() {
  // Los filtros se aplican automáticamente a través del computed filteredVentas
}

function clearFilters() {
  searchTerm.value = '';
  selectedTipo.value = '';
  fechaInicio.value = '';
  fechaFin.value = '';
  ventaStore.clearFilters();
}

async function refreshData() {
  await ventaStore.refreshAll();
}

function viewVenta(venta: VentaUnificada) {
  selectedVenta.value = venta;
  editMode.value = 'view';
  showEditModal.value = true;
}

function editVenta(venta: VentaUnificada) {
  selectedVenta.value = venta;
  editMode.value = 'edit';
  showEditModal.value = true;
}

async function deleteVenta(venta: VentaUnificada) {
  if (!confirm(`¿Estás seguro de que deseas eliminar esta venta de ${venta.producto_nombre}?`)) {
    return;
  }

  try {
    if (venta.tipo === 'automotriz') {
      await ventaStore.deleteVentaAutomotriz(venta.id);
    } else {
      await ventaStore.deleteVentaDespensa(venta.id);
    }
  } catch (error) {
    console.error('Error al eliminar venta:', error);
  }
}

function handleVentaSaved() {
  showEditModal.value = false;
  selectedVenta.value = null;
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    ventaStore.fetchVentasAutomotrices(),
    ventaStore.fetchVentasDespensa()
  ]);
});
</script>

<style scoped>
.ventas-general-tab {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.filters-section {
  background-color: white;
  padding: 1.5rem;
  border-radius: 0.5rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  border: 1px solid #e5e7eb;
}

.form-input, .form-select {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  background-color: white;
  color: #111827;
}

.form-input:focus, .form-select:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
  border-color: #3b82f6;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-weight: 500;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-primary {
  background-color: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background-color: #2563eb;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-outline-secondary {
  border: 1px solid #d1d5db;
  color: #374151;
  background-color: white;
}

.btn-outline-secondary:hover {
  background-color: #f9fafb;
}

.table-container {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  border: 1px solid #e5e7eb;
  overflow: hidden;
}

.data-table {
  min-width: 100%;
  border-collapse: collapse;
}

.data-table thead th {
  padding: 0.75rem 1.5rem;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 500;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  background-color: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.data-table tbody tr {
  border-bottom: 1px solid #e5e7eb;
}

.data-table tbody tr:hover {
  background-color: #f9fafb;
}

.data-table tbody td {
  padding: 1rem 1.5rem;
  white-space: nowrap;
  font-size: 0.875rem;
  color: #111827;
}

.action-btn {
  padding: 0.5rem;
  border-radius: 0.375rem;
  transition: all 0.2s;
  border: none;
  background: none;
  cursor: pointer;
}

.action-btn-view {
  color: #2563eb;
}

.action-btn-view:hover {
  background-color: #dbeafe;
}

.action-btn-edit {
  color: #059669;
}

.action-btn-edit:hover {
  background-color: #d1fae5;
}

.action-btn-delete {
  color: #dc2626;
}

.action-btn-delete:hover {
  background-color: #fee2e2;
}

/* Dark mode styles - if needed */
@media (prefers-color-scheme: dark) {
  .filters-section,
  .table-container {
    background-color: #1f2937;
    border-color: #374151;
  }

  .form-input, .form-select {
    background-color: #1f2937;
    border-color: #374151;
    color: #f9fafb;
  }

  .btn-outline-secondary {
    border-color: #374151;
    color: #d1d5db;
    background-color: #1f2937;
  }

  .btn-outline-secondary:hover {
    background-color: #374151;
  }

  .data-table thead th {
    background-color: #374151;
    color: #9ca3af;
  }

  .data-table tbody tr:hover {
    background-color: #374151;
  }

  .data-table tbody td {
    color: #f9fafb;
  }

  .data-table tbody tr {
    border-color: #374151;
  }
}
</style>
