import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import DashboardService from '@/services/DashboardService';
import { useNotificationStore } from './notification';
import { useLoadingStore } from './loading';

export const useDashboardStore = defineStore('dashboard', () => {
  // Estado
  const metricas = ref(null);
  const actividadReciente = ref([]);
  const proximasCitas = ref([]);
  const chartData = ref(null);
  const alertas = ref([]);
  const estadisticas = ref(null);
  const analisisFinanciero = ref(null);
  const rendimientoOperativo = ref(null);
  const lastUpdated = ref(null);

  // Stores
  const notificationStore = useNotificationStore();
  const loadingStore = useLoadingStore();

  // Getters
  const hasData = computed(() => !!metricas.value);
  const totalAlertas = computed(() => alertas.value.length);
  const alertasUrgentes = computed(() => 
    alertas.value.filter(alerta => alerta.priority === 'high')
  );

  // Actions
  const fetchDashboardData = async () => {
    try {
      loadingStore.startLoading('dashboard', 'Cargando datos del dashboard...');
      
      const response = await DashboardService.getDashboardData();
      
      // Actualizar todos los datos del dashboard
      metricas.value = response.metricas || null;
      actividadReciente.value = response.actividad_reciente || [];
      proximasCitas.value = response.proximas_citas || [];
      chartData.value = response.chart_data || null;
      alertas.value = response.alertas || [];
      estadisticas.value = response.estadisticas || null;
      analisisFinanciero.value = response.analisis_financiero || null;
      rendimientoOperativo.value = response.rendimiento_operativo || null;
      lastUpdated.value = new Date();

    } catch (error) {
      console.error('Error al cargar datos del dashboard:', error);
      notificationStore.error('Error al cargar los datos del dashboard');
    } finally {
      loadingStore.stopLoading('dashboard');
    }
  };

  const fetchMetricas = async () => {
    try {
      const response = await DashboardService.getMetricas();
      metricas.value = response;
    } catch (error) {
      console.error('Error al cargar métricas:', error);
      notificationStore.error('Error al cargar las métricas');
    }
  };

  const fetchActividadReciente = async () => {
    try {
      const response = await DashboardService.getActividadReciente();
      actividadReciente.value = response;
    } catch (error) {
      console.error('Error al cargar actividad reciente:', error);
    }
  };

  const fetchProximasCitas = async () => {
    try {
      const response = await DashboardService.getProximasCitas();
      proximasCitas.value = response;
    } catch (error) {
      console.error('Error al cargar próximas citas:', error);
    }
  };

  const fetchChartData = async () => {
    try {
      const response = await DashboardService.getChartData();
      chartData.value = response;
    } catch (error) {
      console.error('Error al cargar datos de gráficos:', error);
    }
  };

  const fetchAlertas = async () => {
    try {
      const response = await DashboardService.getAlertas();
      alertas.value = response;
    } catch (error) {
      console.error('Error al cargar alertas:', error);
    }
  };

  const refreshData = async () => {
    await fetchDashboardData();
    notificationStore.success('Datos actualizados correctamente');
  };

  return {
    // Estado
    metricas,
    actividadReciente,
    proximasCitas,
    chartData,
    alertas,
    estadisticas,
    analisisFinanciero,
    rendimientoOperativo,
    lastUpdated,
    // Getters
    hasData,
    totalAlertas,
    alertasUrgentes,
    // Actions
    fetchDashboardData,
    fetchMetricas,
    fetchActividadReciente,
    fetchProximasCitas,
    fetchChartData,
    fetchAlertas,
    refreshData
  };
});
