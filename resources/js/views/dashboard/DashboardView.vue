<template>
  <div class="space-y-6">
    <!-- Header del Dashboard -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">
          Dashboard
        </h1>
        <p class="text-gray-600 mt-1">
          Bienvenido, {{ authStore.userName }}
        </p>
      </div>
      
      <div class="flex items-center space-x-3 mt-4 lg:mt-0">
        <button 
          @click="refreshData"
          :disabled="isRefreshing"
          class="material-button-outlined"
        >
          <ArrowPathIcon 
            :class="['h-4 w-4 mr-2', { 'animate-spin': isRefreshing }]" 
          />
          Actualizar
        </button>
        
        <div class="text-sm text-gray-500">
          Última actualización: {{ lastUpdatedText }}
        </div>
      </div>
    </div>

    <!-- Métricas principales -->
    <div v-if="dashboardStore.metricas" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <MetricCard
        v-for="metric in metricsCards"
        :key="metric.key"
        :title="metric.title"
        :value="metric.value"
        :change="metric.change"
        :icon="metric.icon"
        :color="metric.color"
      />
    </div>

    <!-- Alertas del sistema -->
    <div v-if="dashboardStore.alertasUrgentes.length > 0" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
      <div class="flex items-center">
        <ExclamationTriangleIcon class="h-5 w-5 text-yellow-400 mr-2" />
        <h3 class="text-sm font-medium text-yellow-800">
          Alertas del Sistema ({{ dashboardStore.alertasUrgentes.length }})
        </h3>
      </div>
      <div class="mt-2 space-y-1">
        <p 
          v-for="alerta in dashboardStore.alertasUrgentes.slice(0, 3)"
          :key="alerta.id"
          class="text-sm text-yellow-700"
        >
          • {{ alerta.mensaje }}
        </p>
        <router-link 
          v-if="dashboardStore.alertasUrgentes.length > 3"
          to="/dashboard/alertas"
          class="text-sm text-yellow-600 hover:text-yellow-700 font-medium"
        >
          Ver todas las alertas →
        </router-link>
      </div>
    </div>

    <!-- Gráficos y actividad -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Gráfico de ingresos -->
      <div class="material-card">
        <div class="card-header">
          <h3 class="text-lg font-semibold text-gray-900">
            Ingresos vs Egresos
          </h3>
        </div>
        <div class="card-body">
          <ChartComponent 
            v-if="dashboardStore.chartData"
            :data="dashboardStore.chartData.ingresos_egresos"
            type="line"
          />
          <div v-else class="skeleton-loader h-64"></div>
        </div>
      </div>

      <!-- Actividad reciente -->
      <div class="material-card">
        <div class="card-header">
          <h3 class="text-lg font-semibold text-gray-900">
            Actividad Reciente
          </h3>
        </div>
        <div class="card-body">
          <div v-if="dashboardStore.actividadReciente.length > 0" class="space-y-3">
            <div 
              v-for="actividad in dashboardStore.actividadReciente.slice(0, 5)"
              :key="actividad.id"
              class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded"
            >
              <div class="flex-shrink-0">
                <div 
                  class="w-2 h-2 rounded-full"
                  :class="getActivityColor(actividad.tipo)"
                ></div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-900 truncate">
                  {{ actividad.descripcion }}
                </p>
                <p class="text-xs text-gray-500">
                  {{ formatDate(actividad.fecha) }}
                </p>
              </div>
            </div>
            <router-link 
              to="/dashboard/actividad"
              class="block text-center text-sm text-primary-600 hover:text-primary-700 font-medium pt-2"
            >
              Ver toda la actividad →
            </router-link>
          </div>
          <div v-else class="text-center text-gray-500 py-8">
            No hay actividad reciente
          </div>
        </div>
      </div>
    </div>

    <!-- Próximas citas y estadísticas -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Próximas citas -->
      <div class="material-card">
        <div class="card-header">
          <h3 class="text-lg font-semibold text-gray-900">
            Próximas Citas
          </h3>
        </div>
        <div class="card-body">
          <div v-if="dashboardStore.proximasCitas.length > 0" class="space-y-2">
            <div 
              v-for="cita in dashboardStore.proximasCitas.slice(0, 4)"
              :key="cita.id"
              class="text-sm"
            >
              <div class="font-medium text-gray-900">{{ cita.cliente }}</div>
              <div class="text-gray-500">{{ formatDateTime(cita.fecha_hora) }}</div>
            </div>
          </div>
          <div v-else class="text-center text-gray-500 py-4 text-sm">
            No hay citas programadas
          </div>
        </div>
      </div>

      <!-- Estadísticas adicionales -->
      <div class="lg:col-span-2 material-card">
        <div class="card-header">
          <h3 class="text-lg font-semibold text-gray-900">
            Estadísticas del Mes
          </h3>
        </div>
        <div class="card-body">
          <div v-if="dashboardStore.estadisticas" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center">
              <div class="text-2xl font-bold text-primary-600">
                {{ dashboardStore.estadisticas.lavados_mes || 0 }}
              </div>
              <div class="text-sm text-gray-500">Lavados</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-green-600">
                {{ dashboardStore.estadisticas.clientes_nuevos || 0 }}
              </div>
              <div class="text-sm text-gray-500">Clientes Nuevos</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-blue-600">
                {{ dashboardStore.estadisticas.ventas_productos || 0 }}
              </div>
              <div class="text-sm text-gray-500">Ventas Productos</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-purple-600">
                {{ formatCurrency(dashboardStore.estadisticas.ingreso_promedio || 0) }}
              </div>
              <div class="text-sm text-gray-500">Ingreso Promedio</div>
            </div>
          </div>
          <div v-else class="skeleton-loader h-16"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useDashboardStore } from '@/stores/dashboard';
import { useNotificationStore } from '@/stores/notification';
import { 
  ArrowPathIcon, 
  ExclamationTriangleIcon,
  CurrencyDollarIcon,
  UserGroupIcon,
  TruckIcon,
  ChartBarIcon
} from '@heroicons/vue/24/outline';

import MetricCard from '@/components/dashboard/MetricCard.vue';
import ChartComponent from '@/components/charts/ChartComponent.vue';

// Stores
const authStore = useAuthStore();
const dashboardStore = useDashboardStore();
const notificationStore = useNotificationStore();

// Estado
const isRefreshing = ref(false);

// Computed
const lastUpdatedText = computed(() => {
  if (!dashboardStore.lastUpdated) return 'Nunca';
  
  const date = new Date(dashboardStore.lastUpdated);
  return date.toLocaleString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
});

const metricsCards = computed(() => {
  if (!dashboardStore.metricas) return [];

  return [
    {
      key: 'ingresos_hoy',
      title: 'Ingresos Hoy',
      value: formatCurrency(dashboardStore.metricas.ingresos_hoy || 0),
      change: dashboardStore.metricas.cambio_ingresos_hoy,
      icon: CurrencyDollarIcon,
      color: 'green'
    },
    {
      key: 'lavados_hoy',
      title: 'Lavados Hoy',
      value: dashboardStore.metricas.lavados_hoy || 0,
      change: dashboardStore.metricas.cambio_lavados_hoy,
      icon: TruckIcon,
      color: 'blue'
    },
    {
      key: 'clientes_activos',
      title: 'Clientes Activos',
      value: dashboardStore.metricas.clientes_activos || 0,
      change: dashboardStore.metricas.cambio_clientes,
      icon: UserGroupIcon,
      color: 'purple'
    },
    {
      key: 'ventas_mes',
      title: 'Ventas del Mes',
      value: formatCurrency(dashboardStore.metricas.ventas_mes || 0),
      change: dashboardStore.metricas.cambio_ventas_mes,
      icon: ChartBarIcon,
      color: 'orange'
    }
  ];
});

// Métodos
const refreshData = async () => {
  try {
    isRefreshing.value = true;
    await dashboardStore.refreshData();
  } finally {
    isRefreshing.value = false;
  }
};

const getActivityColor = (tipo) => {
  const colors = {
    lavado: 'bg-blue-500',
    venta: 'bg-green-500',
    cliente: 'bg-purple-500',
    empleado: 'bg-yellow-500',
    default: 'bg-gray-500'
  };
  return colors[tipo] || colors.default;
};

const formatDate = (fecha) => {
  if (!fecha) return '';
  return new Date(fecha).toLocaleDateString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const formatDateTime = (fecha) => {
  if (!fecha) return '';
  return new Date(fecha).toLocaleString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: 'EUR'
  }).format(amount);
};

// Lifecycle
onMounted(async () => {
  if (!dashboardStore.hasData) {
    await dashboardStore.fetchDashboardData();
  }
});
</script>
