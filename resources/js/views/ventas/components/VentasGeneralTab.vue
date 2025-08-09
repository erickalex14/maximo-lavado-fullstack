<template>
  <div class="ventas-general-tab">
    <!-- Filtros -->
    <div class="filters-section mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-surface-700 mb-2">
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
          <label class="block text-sm font-medium text-surface-700 mb-2">
            Tipo de Venta
          </label>
          <select v-model="selectedTipo" class="form-select" @change="applyFilters">
            <option value="">Todos</option>
            <option value="automotriz">Automotriz</option>
            <option value="despensa">Despensa</option>
            <option value="servicio">Servicio</option>
            <option value="mixta">Mixta</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-surface-700 mb-2">
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
          <label class="block text-sm font-medium text-surface-700 mb-2">
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
              <th>Referencia</th>
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
              class="hover:bg-surface-50"
            >
              <td>{{ venta.id }}</td>
              <td>
                <span :class="badgeClass(venta.tipo)">{{ labelTipo(venta.tipo) }}</span>
              </td>
              <td class="font-medium">{{ nombreReferencia(venta) }}</td>
              <td>{{ venta.cliente?.nombre || '' }}</td>
              <td>{{ safeCantidad(venta) }}</td>
              <td>${{ formatCurrency(venta.precio_unitario) }}</td>
              <td class="font-semibold">${{ formatCurrency(venta.total) }}</td>
              <td>{{ formatDate(venta.fecha) }}</td>
              <td>
                <div class="flex items-center space-x-2">
                  <button
                    @click="duplicarVenta(venta)"
                    class="action-btn action-btn-edit"
                    title="Duplicar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16h8M8 12h8m-6 8h6a2 2 0 002-2V8a2 2 0 00-2-2h-5l-2-2H8a2 2 0 00-2 2v12a2 2 0 002 2z" />
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
      <!-- Paginación -->
      <div v-if="pagination.total > 0" class="px-6 py-4 border-t border-surface-200">
        <TablePagination :pagination="pagination" @changePage="handlePageChange" />
      </div>
    </div>

    <!-- Modales de vista y edición -->
  <!-- Modales legacy eliminados en flujo unificado -->
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useToast } from '@/composables/useToast';
import { useVentaStore } from '@/stores/venta';
import type { Venta } from '@/types';
import TablePagination from '@/components/ui/TablePagination.vue';

// Store
const ventaStore = useVentaStore();
const { push: toast } = useToast();

// State
const searchTerm = ref('');
const selectedTipo = ref('');
const fechaInicio = ref('');
const fechaFin = ref('');
// Modales legacy eliminados: no se usan showEditModal/editMode/selectedVenta

// Computed
const loading = computed(() => ventaStore.loading);
const ventasUnificadas = computed(() => ventaStore.ventasUnificadas);
const pagination = computed(() => ventaStore.ventasPagination);

// Filtros aplicados
const filteredVentas = computed(() => {
  let ventas = ventasUnificadas.value;

  // Filtro por tipo
  if (selectedTipo.value) {
    if (selectedTipo.value === 'automotriz') ventas = ventas.filter(v => v.tipo === 'producto_automotriz' || (v.tipo==='mixta' && v.detalles_tipos?.includes('producto_automotriz')));
    else if (selectedTipo.value === 'despensa') ventas = ventas.filter(v => v.tipo === 'producto_despensa' || (v.tipo==='mixta' && v.detalles_tipos?.includes('producto_despensa')));
    else if (selectedTipo.value === 'servicio') ventas = ventas.filter(v => v.tipo === 'servicio' || (v.tipo==='mixta' && v.detalles_tipos?.includes('servicio')));
    else if (selectedTipo.value === 'mixta') ventas = ventas.filter(v => v.tipo === 'mixta');
  }

  // Filtro por búsqueda
  if (searchTerm.value) {
    const term = searchTerm.value.toLowerCase();
    ventas = ventas.filter(v => {
      const nombre = nombreReferencia(v).toLowerCase();
      const cliente = (v.cliente?.nombre || '').toLowerCase();
      return nombre.includes(term) || cliente.includes(term);
    });
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
function formatCurrency(value: number | undefined | null): string {
  const num = typeof value === 'number' && !isNaN(value) ? value : 0;
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(num);
}

function safeCantidad(v: Venta) {
  return (typeof v.cantidad === 'number' && !isNaN(v.cantidad)) ? v.cantidad : 0;
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

function handlePageChange(page: number) {
  ventaStore.fetchVentas({ page });
}

function badgeClass(tipo: Venta['tipo']) {
  switch (tipo) {
    case 'producto_automotriz': return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800';
    case 'producto_despensa': return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800';
  case 'servicio': return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800';
  case 'mixta': return 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800';
  }
}

function labelTipo(tipo: Venta['tipo']) {
  if (tipo === 'producto_automotriz') return 'Automotriz';
  if (tipo === 'producto_despensa') return 'Despensa';
  if (tipo === 'servicio') return 'Servicio';
  if (tipo === 'mixta') return 'Mixta';
  return tipo;
}

function nombreReferencia(v: Venta) {
  if (v.referencia_compuesta) return v.referencia_compuesta;
  if (v.tipo === 'producto_automotriz') return v.producto_automotriz?.nombre || `#${v.referencia_id}`;
  if (v.tipo === 'producto_despensa') return v.producto_despensa?.nombre || `#${v.referencia_id}`;
  if (v.tipo === 'servicio') return v.servicio?.nombre || `Servicio ${v.referencia_id}`;
  return `Ref ${v.referencia_id}`;
}

async function duplicarVenta(v: Venta) {
  if (v.tipo === 'mixta') {
    toast({ type: 'info', text: 'Duplicación rápida no soportada para ventas mixtas' });
    return;
  }
  const nueva = await ventaStore.createVentaGenerica({
    tipo: v.tipo as any,
    referencia_id: v.referencia_id,
    cantidad: v.cantidad,
    precio_unitario: v.precio_unitario,
    cliente_id: v.cliente_id || undefined,
  });
  if (nueva) toast({ type: 'success', text: 'Venta duplicada' });
}

async function deleteVenta(v: Venta) {
  if (!window.confirm('¿Eliminar esta venta?')) return;
  const ok = await ventaStore.deleteVentaGenerica(v.id);
  if (ok) toast({ type: 'success', text: 'Venta eliminada' }); else toast({ type: 'error', text: 'No se pudo eliminar' });
}

// Lifecycle
onMounted(async () => {
  await ventaStore.fetchVentas();
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

/* Estilos dark eliminados para modo claro consistente */
</style>
