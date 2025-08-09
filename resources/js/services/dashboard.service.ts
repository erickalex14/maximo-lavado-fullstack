import apiService from './api';
import type { ApiResponse, DashboardMetrics, DashboardChartData } from '@/types';

class DashboardService {
  /**
   * Obtener datos principales del dashboard
  * GET /api/dashboard/datos
   */
  async getData(): Promise<ApiResponse<any>> {
   return await apiService.get('/dashboard/datos');
  }

  /**
   * Obtener métricas principales
   * GET /api/dashboard/metricas
   */
  async getMetricas(): Promise<ApiResponse<DashboardMetrics>> {
    return await apiService.get('/dashboard/metricas');
  }

  /**
   * Obtener actividad reciente
   * GET /api/dashboard/actividad
   */
  async getActividadReciente(limite?: number): Promise<ApiResponse<any[]>> {
    const params = limite ? { limite } : {};
    return await apiService.get('/dashboard/actividad', params);
  }

  /**
   * Obtener próximas citas
   * GET /api/dashboard/citas
   */
  async getProximasCitas(limite?: number): Promise<ApiResponse<any[]>> {
    const params = limite ? { limite } : {};
    return await apiService.get('/dashboard/citas', params);
  }

  /**
   * Obtener datos para gráficos
   * GET /api/dashboard/charts
   */
  async getChartData(): Promise<ApiResponse<DashboardChartData>> {
    return await apiService.get('/dashboard/charts');
  }

  /**
   * Obtener alertas del sistema
   * GET /api/dashboard/alertas
   */
  async getAlertas(): Promise<ApiResponse<any[]>> {
    return await apiService.get('/dashboard/alertas');
  }

  /**
   * Obtener estadísticas generales
   * GET /api/dashboard/estadisticas
   */
  async getEstadisticas(): Promise<ApiResponse<any>> {
    return await apiService.get('/dashboard/estadisticas');
  }

  /**
   * Obtener análisis financiero
   * GET /api/dashboard/analisis-financiero
   */
  async getAnalisisFinanciero(): Promise<ApiResponse<any>> {
    return await apiService.get('/dashboard/analisis-financiero');
  }

  /**
   * Obtener rendimiento operativo
   * GET /api/dashboard/rendimiento-operativo
   */
  async getRendimientoOperativo(): Promise<ApiResponse<any>> {
    return await apiService.get('/dashboard/rendimiento-operativo');
  }

  /**
   * Obtener resumen completo
   * GET /api/dashboard/resumen-completo
   */
  async getResumenCompleto(): Promise<ApiResponse<any>> {
    return await apiService.get('/dashboard/resumen-completo');
  }
}

export default new DashboardService();
