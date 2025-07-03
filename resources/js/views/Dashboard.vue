<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-surface-900">Dashboard</h1>
        <p class="text-surface-600">Resumen de actividades del sistema</p>
      </div>
      <div class="flex items-center space-x-3">
        <button
          @click="refreshData"
          :disabled="dashboardStore.loading"
          class="btn-secondary flex items-center"
        >
          <svg
            class="w-4 h-4 mr-2"
            :class="{ 'animate-spin': dashboardStore.loading }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Actualizar
        </button>
        <span class="text-sm text-surface-500">
          Última actualización: {{ lastUpdated }}
        </span>
      </div>
    </div>

    <!-- Error state -->
    <div v-if="dashboardStore.error" class="bg-red-50 border border-red-200 rounded-lg p-4">
      <div class="flex">
        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
        </svg>
        <div class="ml-3">
          <p class="text-sm text-red-700">{{ dashboardStore.error }}</p>
        </div>
      </div>
    </div>

    <!-- Loading state -->
    <div v-if="dashboardStore.loading && !dashboardStore.hasData" class="flex items-center justify-center py-12">
      <div class="text-center">
        <LoadingSpinner class="mx-auto mb-4" />
        <p class="text-surface-600">Cargando datos del dashboard...</p>
      </div>
    </div>

    <!-- Main content -->
    <div v-else class="space-y-6">
      <!-- Metrics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <MetricCard
          title="Lavados Hoy"
          :value="dashboardStore.totalLavadosHoy"
          icon="lavado"
          color="primary"
        />
        <MetricCard
          title="Ingresos Hoy"
          :value="formatCurrency(dashboardStore.ingresosHoy)"
          icon="dinero"
          color="green"
        />
        <MetricCard
          title="Total Clientes"
          :value="dashboardStore.totalClientes"
          icon="clientes"
          color="blue"
        />
        <MetricCard
          title="Total Empleados"
          :value="dashboardStore.totalEmpleados"
          icon="empleados"
          color="purple"
        />
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Lavados por día chart -->
        <div class="card">
          <div class="card-header">
            <h3 class="text-lg font-semibold text-surface-900">Lavados por Día</h3>
          </div>
          <div class="card-body">
            <ChartComponent
              v-if="dashboardStore.chartData?.lavados_por_dia"
              type="line"
              :data="chartLavadosData"
              :options="chartOptions"
            />
            <div v-else class="flex items-center justify-center h-64 text-surface-500">
              Sin datos disponibles
            </div>
          </div>
        </div>

        <!-- Ingresos por mes chart -->
        <div class="card">
          <div class="card-header">
            <h3 class="text-lg font-semibold text-surface-900">Ingresos por Mes</h3>
          </div>
          <div class="card-body">
            <ChartComponent
              v-if="dashboardStore.chartData?.ingresos_por_mes"
              type="bar"
              :data="chartIngresosData"
              :options="chartOptions"
            />
            <div v-else class="flex items-center justify-center h-64 text-surface-500">
              Sin datos disponibles
            </div>
          </div>
        </div>
      </div>

      <!-- Content Row -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Actividad Reciente -->
        <div class="card">
          <div class="card-header">
            <h3 class="text-lg font-semibold text-surface-900">Actividad Reciente</h3>
          </div>
          <div class="card-body">
            <div v-if="dashboardStore.actividadReciente.length > 0" class="space-y-3">
              <div
                v-for="actividad in dashboardStore.actividadReciente"
                :key="actividad.id"
                class="flex items-start space-x-3 p-3 bg-surface-50 rounded-lg"
              >
                <div class="w-2 h-2 bg-primary-500 rounded-full mt-2 flex-shrink-0"></div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm text-surface-900 truncate">{{ actividad.descripcion }}</p>
                  <p class="text-xs text-surface-500">{{ formatDate(actividad.fecha) }}</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-surface-500">
              No hay actividad reciente
            </div>
          </div>
        </div>

        <!-- Próximas Citas -->
        <div class="card">
          <div class="card-header">
            <h3 class="text-lg font-semibold text-surface-900">Próximas Citas</h3>
          </div>
          <div class="card-body">
            <div v-if="dashboardStore.proximasCitas.length > 0" class="space-y-3">
              <div
                v-for="cita in dashboardStore.proximasCitas"
                :key="cita.id"
                class="flex items-start space-x-3 p-3 bg-surface-50 rounded-lg"
              >
                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm text-surface-900 truncate">{{ cita.descripcion }}</p>
                  <p class="text-xs text-surface-500">{{ formatDate(cita.fecha) }}</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-surface-500">
              No hay citas programadas
            </div>
          </div>
        </div>

        <!-- Alertas del Sistema -->
        <div class="card">
          <div class="card-header">
            <h3 class="text-lg font-semibold text-surface-900">Alertas</h3>
          </div>
          <div class="card-body">
            <div v-if="dashboardStore.alertas.length > 0" class="space-y-3">
              <div
                v-for="alerta in dashboardStore.alertas"
                :key="alerta.id"
                class="flex items-start space-x-3 p-3 rounded-lg"
                :class="getAlertClass(alerta.tipo)"
              >
                <div
                  class="w-2 h-2 rounded-full mt-2 flex-shrink-0"
                  :class="getAlertDotClass(alerta.tipo)"
                ></div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm truncate" :class="getAlertTextClass(alerta.tipo)">
                    {{ alerta.mensaje }}
                  </p>
                  <p class="text-xs opacity-75">{{ formatDate(alerta.fecha) }}</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-surface-500">
              No hay alertas activas
            </div>
          </div>
        </div>
      </div>

      <!-- Pendientes Row -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Lavados Pendientes -->
        <div class="card">
          <div class="card-header flex items-center justify-between">
            <h3 class="text-lg font-semibold text-surface-900">Lavados Pendientes</h3>
            <span class="badge badge-warning">{{ dashboardStore.lavadosPendientes }}</span>
          </div>
          <div class="card-body">
            <p class="text-surface-600">
              Hay {{ dashboardStore.lavadosPendientes }} lavados pendientes de procesar.
            </p>
            <router-link to="/lavados" class="btn-primary mt-3 inline-flex items-center">
              Ver todos los lavados
              <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </router-link>
          </div>
        </div>

        <!-- Facturas Pendientes -->
        <div class="card">
          <div class="card-header flex items-center justify-between">
            <h3 class="text-lg font-semibold text-surface-900">Facturas Pendientes</h3>
            <span class="badge badge-danger">{{ dashboardStore.facturasPendientes }}</span>
          </div>
          <div class="card-body">
            <p class="text-surface-600">
              Hay {{ dashboardStore.facturasPendientes }} facturas pendientes de pago.
            </p>
            <router-link to="/facturas" class="btn-primary mt-3 inline-flex items-center">
              Ver todas las facturas
              <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useDashboardStore } from '@/stores/dashboard';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';
import MetricCard from '@/components/common/MetricCard.vue';
import ChartComponent from '@/components/common/ChartComponent.vue';

const dashboardStore = useDashboardStore();

// Estado local
const lastUpdated = ref('');

// Computed properties
const chartLavadosData = computed(() => {
  if (!dashboardStore.chartData?.lavados_por_dia) return null;
  
  return {
    labels: dashboardStore.chartData.lavados_por_dia.map(item => item.fecha),
    datasets: [{
      label: 'Lavados',
      data: dashboardStore.chartData.lavados_por_dia.map(item => item.cantidad),
      borderColor: '#4caf50',
      backgroundColor: 'rgba(76, 175, 80, 0.1)',
      tension: 0.4,
    }]
  };
});

const chartIngresosData = computed(() => {
  if (!dashboardStore.chartData?.ingresos_por_mes) return null;
  
  return {
    labels: dashboardStore.chartData.ingresos_por_mes.map(item => item.mes),
    datasets: [{
      label: 'Ingresos',
      data: dashboardStore.chartData.ingresos_por_mes.map(item => item.monto),
      backgroundColor: '#2196f3',
    }]
  };
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false,
    },
  },
  scales: {
    y: {
      beginAtZero: true,
    },
  },
};

// Methods
const refreshData = async () => {
  await dashboardStore.fetchAllData();
  lastUpdated.value = new Date().toLocaleTimeString();
};

const formatCurrency = (value: number): string => {
  return new Intl.NumberFormat('es-EC', {
    style: 'currency',
    currency: 'USD',
  }).format(value);
};

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('es-EC', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const getAlertClass = (tipo: string): string => {
  const classes = {
    warning: 'bg-yellow-50',
    error: 'bg-red-50',
    info: 'bg-blue-50',
    success: 'bg-green-50',
  };
  return classes[tipo as keyof typeof classes] || 'bg-surface-50';
};

const getAlertDotClass = (tipo: string): string => {
  const classes = {
    warning: 'bg-yellow-500',
    error: 'bg-red-500',
    info: 'bg-blue-500',
    success: 'bg-green-500',
  };
  return classes[tipo as keyof typeof classes] || 'bg-surface-500';
};

const getAlertTextClass = (tipo: string): string => {
  const classes = {
    warning: 'text-yellow-900',
    error: 'text-red-900',
    info: 'text-blue-900',
    success: 'text-green-900',
  };
  return classes[tipo as keyof typeof classes] || 'text-surface-900';
};

// Lifecycle
onMounted(async () => {
  await refreshData();
});
</script>
