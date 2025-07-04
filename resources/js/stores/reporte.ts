import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { reporteService } from '@/services/reporte.service';
import type {
  ReporteDisponible,
  ReporteRequest,
  ReporteVentas,
  ReporteLavados,
  ReporteIngresos,
  ReporteEgresos,
  ReporteFacturas,
  ReporteEmpleados,
  ReporteProductos,
  ReporteClientes,
  ReporteFinanciero,
  ReporteBalance,
  ReporteCompleto
} from '@/types';

export const useReporteStore = defineStore('reporte', () => {
  // State
  const loading = ref(false);
  const downloadingPdf = ref(false);
  const reportesDisponibles = ref<ReporteDisponible[]>([]);
  
  // Reportes específicos
  const reporteVentas = ref<ReporteVentas | null>(null);
  const reporteLavados = ref<ReporteLavados | null>(null);
  const reporteIngresos = ref<ReporteIngresos | null>(null);
  const reporteEgresos = ref<ReporteEgresos | null>(null);
  const reporteFacturas = ref<ReporteFacturas | null>(null);
  const reporteEmpleados = ref<ReporteEmpleados | null>(null);
  const reporteProductos = ref<ReporteProductos | null>(null);
  const reporteClientes = ref<ReporteClientes | null>(null);
  const reporteFinanciero = ref<ReporteFinanciero | null>(null);
  const reporteBalance = ref<ReporteBalance | null>(null);
  const reporteCompleto = ref<ReporteCompleto | null>(null);

  // Parámetros actuales
  const parametrosActuales = ref<ReporteRequest>({
    fecha_inicio: '',
    fecha_fin: '',
    formato: 'json'
  });

  const error = ref<string | null>(null);

  // Computed
  const hasData = computed(() => {
    return !!(
      reporteVentas.value ||
      reporteLavados.value ||
      reporteIngresos.value ||
      reporteEgresos.value ||
      reporteFacturas.value ||
      reporteEmpleados.value ||
      reporteProductos.value ||
      reporteClientes.value ||
      reporteFinanciero.value ||
      reporteBalance.value ||
      reporteCompleto.value
    );
  });

  // Actions
  async function fetchReportesDisponibles() {
    try {
      loading.value = true;
      error.value = null;
      reportesDisponibles.value = await reporteService.getReportesDisponibles();
    } catch (err: any) {
      error.value = err.message || 'Error al cargar reportes disponibles';
      console.error('Error fetching reportes disponibles:', err);
    } finally {
      loading.value = false;
    }
  }

  async function generateReporteVentas(params: ReporteRequest) {
    try {
      loading.value = true;
      error.value = null;
      parametrosActuales.value = { ...params };
      reporteVentas.value = await reporteService.getReporteVentas(params);
    } catch (err: any) {
      error.value = err.message || 'Error al generar reporte de ventas';
      console.error('Error generating reporte ventas:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function generateReporteLavados(params: ReporteRequest) {
    try {
      loading.value = true;
      error.value = null;
      parametrosActuales.value = { ...params };
      reporteLavados.value = await reporteService.getReporteLavados(params);
    } catch (err: any) {
      error.value = err.message || 'Error al generar reporte de lavados';
      console.error('Error generating reporte lavados:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function generateReporteIngresos(params: ReporteRequest) {
    try {
      loading.value = true;
      error.value = null;
      parametrosActuales.value = { ...params };
      reporteIngresos.value = await reporteService.getReporteIngresos(params);
    } catch (err: any) {
      error.value = err.message || 'Error al generar reporte de ingresos';
      console.error('Error generating reporte ingresos:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function generateReporteEgresos(params: ReporteRequest) {
    try {
      loading.value = true;
      error.value = null;
      parametrosActuales.value = { ...params };
      reporteEgresos.value = await reporteService.getReporteEgresos(params);
    } catch (err: any) {
      error.value = err.message || 'Error al generar reporte de egresos';
      console.error('Error generating reporte egresos:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function generateReporteFacturas(params: ReporteRequest) {
    try {
      loading.value = true;
      error.value = null;
      parametrosActuales.value = { ...params };
      reporteFacturas.value = await reporteService.getReporteFacturas(params);
    } catch (err: any) {
      error.value = err.message || 'Error al generar reporte de facturas';
      console.error('Error generating reporte facturas:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function generateReporteEmpleados(params: ReporteRequest) {
    try {
      loading.value = true;
      error.value = null;
      parametrosActuales.value = { ...params };
      reporteEmpleados.value = await reporteService.getReporteEmpleados(params);
    } catch (err: any) {
      error.value = err.message || 'Error al generar reporte de empleados';
      console.error('Error generating reporte empleados:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function generateReporteProductos(params?: Partial<ReporteRequest>) {
    try {
      loading.value = true;
      error.value = null;
      if (params) parametrosActuales.value = { ...parametrosActuales.value, ...params };
      reporteProductos.value = await reporteService.getReporteProductos(params);
    } catch (err: any) {
      error.value = err.message || 'Error al generar reporte de productos';
      console.error('Error generating reporte productos:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function generateReporteClientes(params?: Partial<ReporteRequest>) {
    try {
      loading.value = true;
      error.value = null;
      if (params) parametrosActuales.value = { ...parametrosActuales.value, ...params };
      reporteClientes.value = await reporteService.getReporteClientes(params);
    } catch (err: any) {
      error.value = err.message || 'Error al generar reporte de clientes';
      console.error('Error generating reporte clientes:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function generateReporteFinanciero(params: ReporteRequest) {
    try {
      loading.value = true;
      error.value = null;
      parametrosActuales.value = { ...params };
      reporteFinanciero.value = await reporteService.getReporteFinanciero(params);
    } catch (err: any) {
      error.value = err.message || 'Error al generar reporte financiero';
      console.error('Error generating reporte financiero:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function generateReporteBalance(params: ReporteRequest) {
    try {
      loading.value = true;
      error.value = null;
      parametrosActuales.value = { ...params };
      reporteBalance.value = await reporteService.getReporteBalance(params);
    } catch (err: any) {
      error.value = err.message || 'Error al generar reporte de balance';
      console.error('Error generating reporte balance:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function generateReporteCompleto(params: ReporteRequest) {
    try {
      loading.value = true;
      error.value = null;
      parametrosActuales.value = { ...params };
      reporteCompleto.value = await reporteService.getReporteCompleto(params);
    } catch (err: any) {
      error.value = err.message || 'Error al generar reporte completo';
      console.error('Error generating reporte completo:', err);
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function downloadReportePdf(tipo: string, params: ReporteRequest) {
    try {
      downloadingPdf.value = true;
      error.value = null;
      
      const pdfParams = { ...params, formato: 'pdf' as const };
      const blob = await reporteService.descargarReporte(tipo, pdfParams);
      
      // Crear URL del blob y descargar
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `reporte-${tipo}-${params.fecha_inicio}-${params.fecha_fin}.pdf`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);
      
    } catch (err: any) {
      error.value = err.message || 'Error al descargar reporte PDF';
      console.error('Error downloading PDF:', err);
      throw err;
    } finally {
      downloadingPdf.value = false;
    }
  }

  async function downloadReporteExcel(tipo: string, params: ReporteRequest) {
    try {
      downloadingPdf.value = true;
      error.value = null;
      
      const excelParams = { ...params, formato: 'excel' as const };
      const blob = await reporteService.descargarReporte(tipo, excelParams);
      
      // Crear URL del blob y descargar
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `reporte-${tipo}-${params.fecha_inicio}-${params.fecha_fin}.xlsx`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);
      
    } catch (err: any) {
      error.value = err.message || 'Error al descargar reporte Excel';
      console.error('Error downloading Excel:', err);
      throw err;
    } finally {
      downloadingPdf.value = false;
    }
  }

  function clearReporte(tipo?: string) {
    if (!tipo) {
      // Limpiar todos los reportes
      reporteVentas.value = null;
      reporteLavados.value = null;
      reporteIngresos.value = null;
      reporteEgresos.value = null;
      reporteFacturas.value = null;
      reporteEmpleados.value = null;
      reporteProductos.value = null;
      reporteClientes.value = null;
      reporteFinanciero.value = null;
      reporteBalance.value = null;
      reporteCompleto.value = null;
    } else {
      // Limpiar reporte específico
      switch (tipo) {
        case 'ventas':
          reporteVentas.value = null;
          break;
        case 'lavados':
          reporteLavados.value = null;
          break;
        case 'ingresos':
          reporteIngresos.value = null;
          break;
        case 'egresos':
          reporteEgresos.value = null;
          break;
        case 'facturas':
          reporteFacturas.value = null;
          break;
        case 'empleados':
          reporteEmpleados.value = null;
          break;
        case 'productos':
          reporteProductos.value = null;
          break;
        case 'clientes':
          reporteClientes.value = null;
          break;
        case 'financiero':
          reporteFinanciero.value = null;
          break;
        case 'balance':
          reporteBalance.value = null;
          break;
        case 'completo':
          reporteCompleto.value = null;
          break;
      }
    }
    error.value = null;
  }

  function clearError() {
    error.value = null;
  }

  function setParametrosDefecto() {
    const fechaFin = new Date();
    const fechaInicio = new Date();
    fechaInicio.setMonth(fechaFin.getMonth() - 1);

    parametrosActuales.value = {
      fecha_inicio: fechaInicio.toISOString().split('T')[0],
      fecha_fin: fechaFin.toISOString().split('T')[0],
      formato: 'json'
    };
  }

  return {
    // State
    loading,
    downloadingPdf,
    error,
    reportesDisponibles,
    reporteVentas,
    reporteLavados,
    reporteIngresos,
    reporteEgresos,
    reporteFacturas,
    reporteEmpleados,
    reporteProductos,
    reporteClientes,
    reporteFinanciero,
    reporteBalance,
    reporteCompleto,
    parametrosActuales,
    
    // Computed
    hasData,
    
    // Actions
    fetchReportesDisponibles,
    generateReporteVentas,
    generateReporteLavados,
    generateReporteIngresos,
    generateReporteEgresos,
    generateReporteFacturas,
    generateReporteEmpleados,
    generateReporteProductos,
    generateReporteClientes,
    generateReporteFinanciero,
    generateReporteBalance,
    generateReporteCompleto,
    downloadReportePdf,
    downloadReporteExcel,
    clearReporte,
    clearError,
    setParametrosDefecto
  };
});
