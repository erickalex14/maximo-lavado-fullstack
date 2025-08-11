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
        // Normalización de claves heterogéneas entre backend y frontend
        const raw: any = response.data;
        metricas.value = {
          total_lavados_hoy: raw.total_lavados_hoy ?? raw.lavados_hoy ?? 0,
            ingresos_hoy: raw.ingresos_hoy ?? 0,
            total_clientes: raw.total_clientes ?? raw.clientes_total ?? 0,
            total_empleados: raw.total_empleados ?? raw.empleados_activos ?? 0,
            lavados_pendientes: raw.lavados_pendientes ?? raw.lavados_en_proceso ?? 0,
            facturas_pendientes: raw.facturas_pendientes ?? raw.facturas_hoy ?? 0,
        };
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
        const raw: any = response.data;
        // Si el backend ya envía el formato esperado, úsalo directamente
        if (raw.lavados_por_dia || raw.ingresos_por_mes) {
          // Asegurar que existan arrays no vacíos para que el componente pinte o muestre placeholder correctamente
          const safe = { ...raw };
          if (!Array.isArray(safe.lavados_por_dia) || safe.lavados_por_dia.length === 0) {
            const today = new Date();
            const days: any[] = [];
            for (let i = 6; i >= 0; i--) {
              const d = new Date(today);
              d.setDate(d.getDate() - i);
              days.push({ fecha: d.toLocaleDateString('es-EC', { month: 'short', day: 'numeric' }), cantidad: 0 });
            }
            safe.lavados_por_dia = days;
          }
          if (!Array.isArray(safe.ingresos_por_mes) || safe.ingresos_por_mes.length === 0) {
            safe.ingresos_por_mes = safe.lavados_por_dia.map((d: any) => ({ mes: d.fecha, monto: 0 }));
          }
          chartData.value = safe;
        } else {
          // Adaptar formato semanal (labels/datasets/lavados) al esperado por la vista
          const labels: string[] = raw.labels || [];
          const lavadosSerie: number[] = raw.lavados?.data || [];
          // Buscar dataset de ingresos
            const ingresosDataset = Array.isArray(raw.datasets)
              ? raw.datasets.find((d: any) => (d.label || '').toLowerCase().includes('ingreso'))
              : null;
          const ingresosSerie: number[] = ingresosDataset?.data || [];

          const lavados_por_dia = labels.map((lbl, i) => ({ fecha: lbl, cantidad: lavadosSerie[i] ?? 0 }));
          // Reutilizamos el nombre ingresos_por_mes aunque sean últimos días (mejora futura: renombrar vista)
          const ingresos_por_mes = labels.map((lbl, i) => ({ mes: lbl, monto: ingresosSerie[i] ?? 0 }));

          chartData.value = {
            lavados_por_dia,
            ingresos_por_mes,
            productos_mas_vendidos: [] // placeholder hasta implementar
          };
        }
        // Log de depuración opcional (desactivar en prod)
  // Evitar ReferenceError: process is not defined en build ESM / navegador
  const envMode = (import.meta as any)?.env?.MODE || (import.meta as any)?.env?.NODE_ENV;
  if (envMode !== 'production') {
          // eslint-disable-next-line no-console
          console.debug('[Dashboard] chartData normalizada:', chartData.value);
        }
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
        // Adaptar a formato con descripcion usado en la vista
        proximasCitas.value = response.data.map((cita: any) => ({
          ...cita,
          descripcion: cita.descripcion
            ? cita.descripcion
            : `${cita.cliente_nombre ?? 'Cliente'} - ${cita.vehiculo ?? ''} ${cita.hora ? '· ' + cita.hora : ''}`.trim()
        }));
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
