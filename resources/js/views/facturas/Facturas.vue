<template>
  <div class="facturas-container">
    <!-- Header con métricas -->
    <div class="page-header">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">
            Facturación
          </h1>
          <p class="text-gray-600 mt-1">
            Gestión de facturas y facturación de servicios
          </p>
        </div>
        
        <div class="flex gap-4">
          <button
            @click="showModal = true"
            class="btn btn-primary"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nueva Factura
          </button>
        </div>
      </div>
    </div>

    <!-- Métricas Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" v-if="metricas">
      <div class="metric-card bg-blue-50 border-blue-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-blue-600 text-sm font-medium">Total Facturas</p>
            <p class="text-2xl font-bold text-blue-900">{{ metricas.total_facturas }}</p>
          </div>
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="metric-card bg-green-50 border-green-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-green-600 text-sm font-medium">Total Facturado</p>
            <p class="text-2xl font-bold text-green-900">${{ formatCurrency(metricas.total_facturado) }}</p>
          </div>
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
            </svg>
          </div>
        </div>
      </div>

      <div class="metric-card bg-yellow-50 border-yellow-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-yellow-600 text-sm font-medium">Este Mes</p>
            <p class="text-2xl font-bold text-yellow-900">{{ metricas.facturas_mes_actual }}</p>
          </div>
          <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="metric-card bg-purple-50 border-purple-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-purple-600 text-sm font-medium">Promedio</p>
            <p class="text-2xl font-bold text-purple-900">${{ formatCurrency(metricas.promedio_facturacion) }}</p>
          </div>
          <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="filters-section mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Buscar
          </label>
          <input
            v-model="searchTerm"
            type="text"
            placeholder="Buscar por número o cliente..."
            class="form-input"
            @input="debouncedSearch"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Cliente
          </label>
          <select v-model="selectedCliente" class="form-select" @change="applyFilters">
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
          <label class="block text-sm font-medium text-gray-700 mb-2">
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
          <label class="block text-sm font-medium text-gray-700 mb-2">
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

    <!-- Tabla de facturas -->
    <div class="table-container">
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>Número</th>
              <th>Cliente</th>
              <th>Fecha</th>
              <th>Descripción</th>
              <th>Total</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading && facturas.length === 0">
              <td colspan="6" class="text-center py-8">
                <div class="flex items-center justify-center">
                  <svg class="animate-spin w-6 h-6 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Cargando facturas...
                </div>
              </td>
            </tr>
            
            <tr v-else-if="!loading && facturas.length === 0">
              <td colspan="6" class="text-center py-8 text-gray-500">
                <div class="flex flex-col items-center">
                  <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  <p class="text-lg font-medium">No hay facturas registradas</p>
                  <p class="text-sm">Crea tu primera factura para comenzar</p>
                </div>
              </td>
            </tr>
            
            <tr
              v-else
              v-for="factura in filteredFacturas"
              :key="factura.factura_id"
              class="hover:bg-gray-50"
            >
              <td class="font-medium">{{ factura.numero_factura }}</td>
              <td>{{ factura.cliente?.nombre || 'N/A' }}</td>
              <td>{{ formatDate(factura.fecha) }}</td>
              <td>{{ factura.descripcion || 'Sin descripción' }}</td>
              <td class="font-semibold">${{ formatCurrency(factura.total) }}</td>
              <td>
                <div class="flex items-center space-x-2">
                  <button
                    @click="viewFactura(factura)"
                    class="action-btn action-btn-view"
                    title="Ver detalles"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  
                  <button
                    @click="editFactura(factura)"
                    class="action-btn action-btn-edit"
                    title="Editar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  
                  <button
                    @click="deleteFactura(factura)"
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
      <div v-if="pagination.last_page > 1" class="pagination-container">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Mostrando {{ ((pagination.current_page - 1) * pagination.per_page) + 1 }} a 
            {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} de 
            {{ pagination.total }} resultados
          </div>
          
          <div class="flex items-center space-x-2">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="pagination-btn"
            >
              Anterior
            </button>
            
            <span class="text-sm text-gray-700">
              Página {{ pagination.current_page }} de {{ pagination.last_page }}
            </span>
            
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="pagination-btn"
            >
              Siguiente
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de creación/edición -->
    <FacturaModal
      v-model:visible="showModal"
      :mode="modalMode"
      :factura="selectedFactura"
      @saved="handleFacturaSaved"
    />

    <!-- Modal de vista -->
    <FacturaViewModal
      v-model:visible="showViewModal"
      :factura="selectedFactura"
      @edit="handleEditFromView"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useFacturaStore } from '@/stores/factura';
import FacturaModal from './FacturaModal.vue';
import FacturaViewModal from './FacturaViewModal.vue';
import type { Factura } from '@/types';

// Store
const facturaStore = useFacturaStore();

// State
const searchTerm = ref('');
const selectedCliente = ref<number | ''>('');
const fechaInicio = ref('');
const fechaFin = ref('');
const showModal = ref(false);
const showViewModal = ref(false);
const modalMode = ref<'create' | 'edit'>('create');
const selectedFactura = ref<Factura | null>(null);

// Computed
const loading = computed(() => facturaStore.loading);
const facturas = computed(() => facturaStore.facturas);
const clientes = computed(() => facturaStore.clientes);
const metricas = computed(() => facturaStore.metricas);
const pagination = computed(() => facturaStore.pagination);

// Filtros aplicados
const filteredFacturas = computed(() => {
  let result = facturas.value;

  if (searchTerm.value) {
    const term = searchTerm.value.toLowerCase();
    result = result.filter(f => 
      f.numero_factura.toLowerCase().includes(term) ||
      f.cliente?.nombre?.toLowerCase().includes(term) ||
      f.descripcion?.toLowerCase().includes(term)
    );
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

function debouncedSearch() {
  // En una implementación real, aquí se aplicaría debounce
  applyFilters();
}

async function applyFilters() {
  await facturaStore.applyFilters({
    search: searchTerm.value,
    cliente_id: selectedCliente.value || undefined,
    fecha_inicio: fechaInicio.value,
    fecha_fin: fechaFin.value
  });
}

function clearFilters() {
  searchTerm.value = '';
  selectedCliente.value = '';
  fechaInicio.value = '';
  fechaFin.value = '';
  facturaStore.clearFilters();
}

async function refreshData() {
  await facturaStore.refreshAll();
}

function viewFactura(factura: Factura) {
  selectedFactura.value = factura;
  showViewModal.value = true;
}

function editFactura(factura: Factura) {
  selectedFactura.value = factura;
  modalMode.value = 'edit';
  showModal.value = true;
}

function handleEditFromView(factura: Factura) {
  showViewModal.value = false;
  selectedFactura.value = factura;
  modalMode.value = 'edit';
  showModal.value = true;
}

async function deleteFactura(factura: Factura) {
  if (!confirm(`¿Estás seguro de que deseas eliminar la factura ${factura.numero_factura}?`)) {
    return;
  }

  try {
    await facturaStore.deleteFactura(factura.factura_id);
  } catch (error) {
    console.error('Error al eliminar factura:', error);
  }
}

async function changePage(page: number) {
  await facturaStore.changePage(page);
}

function handleFacturaSaved() {
  showModal.value = false;
  selectedFactura.value = null;
  modalMode.value = 'create';
}

// Lifecycle
onMounted(async () => {
  await facturaStore.refreshAll();
});
</script>

<style scoped>
.facturas-container {
  min-height: 100vh;
  background-color: #f9fafb;
  padding: 1.5rem;
}

.page-header {
  margin-bottom: 2rem;
}

.metric-card {
  padding: 1.5rem;
  border-radius: 0.5rem;
  border: 1px solid #e5e7eb;
  background-color: white;
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
  border: none;
  cursor: pointer;
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

.pagination-container {
  padding: 1rem 1.5rem;
  background-color: #f9fafb;
  border-top: 1px solid #e5e7eb;
}

.pagination-btn {
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  border: 1px solid #d1d5db;
  background-color: white;
  color: #374151;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.2s;
  cursor: pointer;
}

.pagination-btn:hover:not(:disabled) {
  background-color: #f9fafb;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
