<template>
  <div class="reportes-container">
    <!-- Header -->
    <div class="page-header">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">
            Reportes
          </h1>
          <p class="text-gray-600 mt-1">
            Análisis y reportes del negocio
          </p>
        </div>
        
        <div class="flex gap-4">
          <button
            @click="clearAllReports"
            class="btn btn-outline-secondary"
            :disabled="!hasData"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Limpiar Reportes
          </button>
        </div>
      </div>
    </div>

    <!-- Filtros de fecha generales -->
    <div class="filters-section mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Fecha Inicio
          </label>
          <input
            v-model="fechaInicio"
            type="date"
            class="form-input"
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
          />
        </div>
        
        <div class="flex items-end">
          <button
            @click="setDefaultDates"
            class="btn btn-outline-secondary w-full"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Último Mes
          </button>
        </div>
      </div>
    </div>

    <!-- Tipos de Reportes -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
      <!-- Reporte de Ventas -->
      <div class="report-card">
        <div class="report-header">
          <div class="flex items-center">
            <div class="report-icon bg-blue-100 text-blue-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-lg font-semibold text-gray-900">Reporte de Ventas</h3>
              <p class="text-sm text-gray-600">Análisis de ventas por período</p>
            </div>
          </div>
        </div>
        
        <div class="report-actions">
          <button
            @click="generateVentasReport"
            :disabled="loading || !isValidDateRange"
            class="btn btn-primary w-full"
          >
            <svg v-if="loading" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Generar Reporte
          </button>
          
          <div v-if="reporteVentas" class="flex gap-2 mt-2">
            <button
              @click="downloadPdf('ventas')"
              :disabled="downloadingPdf"
              class="btn btn-outline-primary flex-1"
            >
              PDF
            </button>
            <button
              @click="downloadExcel('ventas')"
              :disabled="downloadingPdf"
              class="btn btn-outline-success flex-1"
            >
              Excel
            </button>
          </div>
        </div>
        
        <VentasReportView v-if="reporteVentas" :reporte="reporteVentas" />
      </div>

      <!-- Reporte de Lavados -->
      <div class="report-card">
        <div class="report-header">
          <div class="flex items-center">
            <div class="report-icon bg-cyan-100 text-cyan-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-lg font-semibold text-gray-900">Reporte de Lavados</h3>
              <p class="text-sm text-gray-600">Performance de servicios</p>
            </div>
          </div>
        </div>
        
        <div class="report-actions">
          <button
            @click="generateLavadosReport"
            :disabled="loading || !isValidDateRange"
            class="btn btn-primary w-full"
          >
            <svg v-if="loading" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Generar Reporte
          </button>
          
          <div v-if="reporteLavados" class="flex gap-2 mt-2">
            <button
              @click="downloadPdf('lavados')"
              :disabled="downloadingPdf"
              class="btn btn-outline-primary flex-1"
            >
              PDF
            </button>
            <button
              @click="downloadExcel('lavados')"
              :disabled="downloadingPdf"
              class="btn btn-outline-success flex-1"
            >
              Excel
            </button>
          </div>
        </div>
        
        <LavadosReportView v-if="reporteLavados" :reporte="reporteLavados" />
      </div>

      <!-- Reporte Financiero -->
      <div class="report-card">
        <div class="report-header">
          <div class="flex items-center">
            <div class="report-icon bg-green-100 text-green-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-lg font-semibold text-gray-900">Reporte Financiero</h3>
              <p class="text-sm text-gray-600">Estado financiero general</p>
            </div>
          </div>
        </div>
        
        <div class="report-actions">
          <button
            @click="generateFinancieroReport"
            :disabled="loading || !isValidDateRange"
            class="btn btn-primary w-full"
          >
            <svg v-if="loading" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Generar Reporte
          </button>
          
          <div v-if="reporteFinanciero" class="flex gap-2 mt-2">
            <button
              @click="downloadPdf('financiero')"
              :disabled="downloadingPdf"
              class="btn btn-outline-primary flex-1"
            >
              PDF
            </button>
            <button
              @click="downloadExcel('financiero')"
              :disabled="downloadingPdf"
              class="btn btn-outline-success flex-1"
            >
              Excel
            </button>
          </div>
        </div>
        
        <FinancieroReportView v-if="reporteFinanciero" :reporte="reporteFinanciero" />
      </div>

      <!-- Reporte de Clientes -->
      <div class="report-card">
        <div class="report-header">
          <div class="flex items-center">
            <div class="report-icon bg-purple-100 text-purple-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-lg font-semibold text-gray-900">Reporte de Clientes</h3>
              <p class="text-sm text-gray-600">Análisis de clientela</p>
            </div>
          </div>
        </div>
        
        <div class="report-actions">
          <button
            @click="generateClientesReport"
            :disabled="loading"
            class="btn btn-primary w-full"
          >
            <svg v-if="loading" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Generar Reporte
          </button>
          
          <div v-if="reporteClientes" class="flex gap-2 mt-2">
            <button
              @click="downloadPdf('clientes')"
              :disabled="downloadingPdf"
              class="btn btn-outline-primary flex-1"
            >
              PDF
            </button>
            <button
              @click="downloadExcel('clientes')"
              :disabled="downloadingPdf"
              class="btn btn-outline-success flex-1"
            >
              Excel
            </button>
          </div>
        </div>
        
        <ClientesReportView v-if="reporteClientes" :reporte="reporteClientes" />
      </div>

      <!-- Reporte de Productos -->
      <div class="report-card">
        <div class="report-header">
          <div class="flex items-center">
            <div class="report-icon bg-orange-100 text-orange-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-lg font-semibold text-gray-900">Reporte de Productos</h3>
              <p class="text-sm text-gray-600">Inventario y rotación</p>
            </div>
          </div>
        </div>
        
        <div class="report-actions">
          <button
            @click="generateProductosReport"
            :disabled="loading"
            class="btn btn-primary w-full"
          >
            <svg v-if="loading" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Generar Reporte
          </button>
          
          <div v-if="reporteProductos" class="flex gap-2 mt-2">
            <button
              @click="downloadPdf('productos')"
              :disabled="downloadingPdf"
              class="btn btn-outline-primary flex-1"
            >
              PDF
            </button>
            <button
              @click="downloadExcel('productos')"
              :disabled="downloadingPdf"
              class="btn btn-outline-success flex-1"
            >
              Excel
            </button>
          </div>
        </div>
        
        <ProductosReportView v-if="reporteProductos" :reporte="reporteProductos" />
      </div>

      <!-- Reporte Completo -->
      <div class="report-card lg:col-span-2 xl:col-span-3">
        <div class="report-header">
          <div class="flex items-center">
            <div class="report-icon bg-indigo-100 text-indigo-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-lg font-semibold text-gray-900">Reporte Completo</h3>
              <p class="text-sm text-gray-600">Análisis integral del negocio</p>
            </div>
          </div>
        </div>
        
        <div class="report-actions">
          <div class="flex gap-4">
            <button
              @click="generateCompletoReport"
              :disabled="loading || !isValidDateRange"
              class="btn btn-primary flex-1"
            >
              <svg v-if="loading" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Generar Reporte Completo
            </button>
            
            <div v-if="reporteCompleto" class="flex gap-2">
              <button
                @click="downloadPdf('completo')"
                :disabled="downloadingPdf"
                class="btn btn-outline-primary"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                PDF
              </button>
              <button
                @click="downloadExcel('completo')"
                :disabled="downloadingPdf"
                class="btn btn-outline-success"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Excel
              </button>
            </div>
          </div>
        </div>
        
        <CompletoReportView v-if="reporteCompleto" :reporte="reporteCompleto" />
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="mt-6 bg-red-50 border border-red-200 rounded-lg p-4">
      <div class="flex">
        <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">Error</h3>
          <div class="mt-2 text-sm text-red-700">
            {{ error }}
          </div>
        </div>
        <div class="ml-auto pl-3">
          <div class="-mx-1.5 -my-1.5">
            <button
              @click="clearError"
              class="inline-flex bg-red-50 rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-red-50 focus:ring-red-600"
            >
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useReporteStore } from '@/stores/reporte';

// Componentes de reportes (se crearán después)
import VentasReportView from './components/VentasReportView.vue';
import LavadosReportView from './components/LavadosReportView.vue';
import FinancieroReportView from './components/FinancieroReportView.vue';
import ClientesReportView from './components/ClientesReportView.vue';
import ProductosReportView from './components/ProductosReportView.vue';
import CompletoReportView from './components/CompletoReportView.vue';

// Store
const reporteStore = useReporteStore();

// State
const fechaInicio = ref('');
const fechaFin = ref('');

// Computed
const loading = computed(() => reporteStore.loading);
const downloadingPdf = computed(() => reporteStore.downloadingPdf);
const error = computed(() => reporteStore.error);
const hasData = computed(() => reporteStore.hasData);

const reporteVentas = computed(() => reporteStore.reporteVentas);
const reporteLavados = computed(() => reporteStore.reporteLavados);
const reporteFinanciero = computed(() => reporteStore.reporteFinanciero);
const reporteClientes = computed(() => reporteStore.reporteClientes);
const reporteProductos = computed(() => reporteStore.reporteProductos);
const reporteCompleto = computed(() => reporteStore.reporteCompleto);

const isValidDateRange = computed(() => {
  return fechaInicio.value && fechaFin.value && fechaInicio.value <= fechaFin.value;
});

// Methods
function setDefaultDates() {
  const fechaFinDate = new Date();
  const fechaInicioDate = new Date();
  fechaInicioDate.setMonth(fechaFinDate.getMonth() - 1);

  fechaFin.value = fechaFinDate.toISOString().split('T')[0];
  fechaInicio.value = fechaInicioDate.toISOString().split('T')[0];
}

function getReportParams() {
  return {
    fecha_inicio: fechaInicio.value,
    fecha_fin: fechaFin.value,
    formato: 'json' as const
  };
}

async function generateVentasReport() {
  await reporteStore.generateReporteVentas(getReportParams());
}

async function generateLavadosReport() {
  await reporteStore.generateReporteLavados(getReportParams());
}

async function generateFinancieroReport() {
  await reporteStore.generateReporteFinanciero(getReportParams());
}

async function generateClientesReport() {
  await reporteStore.generateReporteClientes();
}

async function generateProductosReport() {
  await reporteStore.generateReporteProductos();
}

async function generateCompletoReport() {
  await reporteStore.generateReporteCompleto(getReportParams());
}

async function downloadPdf(tipo: string) {
  await reporteStore.downloadReportePdf(tipo, getReportParams());
}

async function downloadExcel(tipo: string) {
  await reporteStore.downloadReporteExcel(tipo, getReportParams());
}

function clearAllReports() {
  reporteStore.clearReporte();
}

function clearError() {
  reporteStore.clearError();
}

// Lifecycle
onMounted(() => {
  setDefaultDates();
});
</script>

<style scoped>
.reportes-container {
  min-height: 100vh;
  background-color: #f9fafb;
  padding: 1.5rem;
}

.page-header {
  margin-bottom: 2rem;
}

.filters-section {
  background-color: white;
  padding: 1.5rem;
  border-radius: 0.5rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  border: 1px solid #e5e7eb;
}

.form-input {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  background-color: white;
  color: #111827;
}

.form-input:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
  border-color: #3b82f6;
}

.report-card {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  border: 1px solid #e5e7eb;
  overflow: hidden;
}

.report-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.report-icon {
  width: 3rem;
  height: 3rem;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.report-actions {
  padding: 1.5rem;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-weight: 500;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  border: none;
  cursor: pointer;
}

.btn-primary {
  background-color: #3b82f6;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background-color: #2563eb;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-outline-primary {
  border: 1px solid #3b82f6;
  color: #3b82f6;
  background-color: white;
}

.btn-outline-primary:hover:not(:disabled) {
  background-color: #dbeafe;
}

.btn-outline-success {
  border: 1px solid #059669;
  color: #059669;
  background-color: white;
}

.btn-outline-success:hover:not(:disabled) {
  background-color: #d1fae5;
}

.btn-outline-secondary {
  border: 1px solid #d1d5db;
  color: #374151;
  background-color: white;
}

.btn-outline-secondary:hover:not(:disabled) {
  background-color: #f9fafb;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
