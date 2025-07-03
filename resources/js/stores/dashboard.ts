import { defineStore } from 'pinia';
import { ref, computed, readonly } from 'vue';
import dashboardService from '@/services/dashboard.service';
import type { DashboardMetrics, DashboardChartData } from '@/types';

export const useDashboardStore = defineStore('dashboard', () => {
  // Estado
  const metricas = ref<DashboardMetrics | null>(null);
  const chartData = ref<DashboardChartData | null>(null);
  const actividadReciente = ref<any[]>([]);
  const proximasCitas = ref<any[]>([]);
  const alertas = ref<any[]>([]);
  const estadisticas = ref<any | null>(null);
  const analisisFinanciero = ref<any | null>(null);
  const rendimientoOperativo = ref<any | null>(null);
  
  const loading = ref(false);
  const error = ref<string | null>(null);

  // Getters
  const hasData = computed(() => !!metricas.value);
  const totalLavadosHoy = computed(() => metricas.value?.total_lavados_hoy || 0);
  const ingresosHoy = computed(() => metricas.value?.ingresos_hoy || 0);
  const totalClientes = computed(() => metricas.value?.total_clientes || 0);
  const totalEmpleados = computed(() => metricas.value?.total_empleados || 0);
  const lavadosPendientes = computed(() => metricas.value?.lavados_pendientes || 0);
  const facturasPendientes = computed(() => metricas.value?.facturas_pendientes || 0);

  // Actions
  async function fetchMetricas(): Promise<void> {
    loading.value = true;
    error.value = null;

    try {
      const response = await dashboardService.getMetricas();
      
      if (response.success && response.data) {
        metricas.value = response.data;
      } else {
        error.value = response.message || 'Error al cargar métricas';
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Error al cargar métricas';
    } finally {
      loading.value = false;
    }
  }

  async function fetchChartData(): Promise<void> {
    try {
      const response = await dashboardService.getChartData();
      
      if (response.success && response.data) {
        chartData.value = response.data;
      }
    } catch (err: any) {
      console.error('Error al cargar datos de gráficos:', err);
    }
  }

  async function fetchActividadReciente(limite = 5): Promise<void> {
    try {
      const response = await dashboardService.getActividadReciente(limite);
      
      if (response.success && response.data) {
        actividadReciente.value = response.data;
      }
    } catch (err: any) {
      console.error('Error al cargar actividad reciente:', err);
    }
  }

  async function fetchProximasCitas(limite = 5): Promise<void> {
    try {
      const response = await dashboardService.getProximasCitas(limite);
      
      if (response.success && response.data) {
        proximasCitas.value = response.data;
      }
    } catch (err: any) {
      console.error('Error al cargar próximas citas:', err);
    }
  }

  async function fetchAlertas(): Promise<void> {
    try {
      const response = await dashboardService.getAlertas();
      
      if (response.success && response.data) {
        alertas.value = response.data;
      }
    } catch (err: any) {
      console.error('Error al cargar alertas:', err);
    }
  }

  async function fetchEstadisticas(): Promise<void> {
    try {
      const response = await dashboardService.getEstadisticas();
      
      if (response.success && response.data) {
        estadisticas.value = response.data;
      }
    } catch (err: any) {
      console.error('Error al cargar estadísticas:', err);
    }
  }

  async function fetchAnalisisFinanciero(): Promise<void> {
    try {
      const response = await dashboardService.getAnalisisFinanciero();
      
      if (response.success && response.data) {
        analisisFinanciero.value = response.data;
      }
    } catch (err: any) {
      console.error('Error al cargar análisis financiero:', err);
    }
  }

  async function fetchRendimientoOperativo(): Promise<void> {
    try {
      const response = await dashboardService.getRendimientoOperativo();
      
      if (response.success && response.data) {
        rendimientoOperativo.value = response.data;
      }
    } catch (err: any) {
      console.error('Error al cargar rendimiento operativo:', err);
    }
  }

  async function fetchAllData(): Promise<void> {
    await Promise.all([
      fetchMetricas(),
      fetchChartData(),
      fetchActividadReciente(),
      fetchProximasCitas(),
      fetchAlertas(),
      fetchEstadisticas(),
      fetchAnalisisFinanciero(),
      fetchRendimientoOperativo(),
    ]);
  }

  function clearError(): void {
    error.value = null;
  }

  function $reset(): void {
    metricas.value = null;
    chartData.value = null;
    actividadReciente.value = [];
    proximasCitas.value = [];
    alertas.value = [];
    estadisticas.value = null;
    analisisFinanciero.value = null;
    rendimientoOperativo.value = null;
    loading.value = false;
    error.value = null;
  }

  return {
    // Estado
    metricas: readonly(metricas),
    chartData: readonly(chartData),
    actividadReciente: readonly(actividadReciente),
    proximasCitas: readonly(proximasCitas),
    alertas: readonly(alertas),
    estadisticas: readonly(estadisticas),
    analisisFinanciero: readonly(analisisFinanciero),
    rendimientoOperativo: readonly(rendimientoOperativo),
    loading: readonly(loading),
    error: readonly(error),
    
    // Getters
    hasData,
    totalLavadosHoy,
    ingresosHoy,
    totalClientes,
    totalEmpleados,
    lavadosPendientes,
    facturasPendientes,
    
    // Actions
    fetchMetricas,
    fetchChartData,
    fetchActividadReciente,
    fetchProximasCitas,
    fetchAlertas,
    fetchEstadisticas,
    fetchAnalisisFinanciero,
    fetchRendimientoOperativo,
    fetchAllData,
    clearError,
    $reset,
  };
});
