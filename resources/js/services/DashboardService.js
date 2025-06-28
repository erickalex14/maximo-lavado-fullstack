import apiClient from './api';

export class DashboardService {
  /**
   * Obtener datos completos del dashboard
   */
  static async getDashboardData() {
    const response = await apiClient.get('/dashboard/data');
    return response.data;
  }

  /**
   * Obtener métricas del dashboard
   */
  static async getMetricas() {
    const response = await apiClient.get('/dashboard/metricas');
    return response.data;
  }

  /**
   * Obtener actividad reciente
   */
  static async getActividadReciente() {
    const response = await apiClient.get('/dashboard/actividad');
    return response.data;
  }

  /**
   * Obtener próximas citas
   */
  static async getProximasCitas() {
    const response = await apiClient.get('/dashboard/citas');
    return response.data;
  }

  /**
   * Obtener datos para gráficos
   */
  static async getChartData() {
    const response = await apiClient.get('/dashboard/charts');
    return response.data;
  }

  /**
   * Obtener alertas del sistema
   */
  static async getAlertas() {
    const response = await apiClient.get('/dashboard/alertas');
    return response.data;
  }

  /**
   * Obtener estadísticas generales
   */
  static async getEstadisticas() {
    const response = await apiClient.get('/dashboard/estadisticas');
    return response.data;
  }

  /**
   * Obtener análisis financiero
   */
  static async getAnalisisFinanciero() {
    const response = await apiClient.get('/dashboard/analisis-financiero');
    return response.data;
  }

  /**
   * Obtener rendimiento operativo
   */
  static async getRendimientoOperativo() {
    const response = await apiClient.get('/dashboard/rendimiento-operativo');
    return response.data;
  }

  /**
   * Obtener resumen completo
   */
  static async getResumenCompleto() {
    const response = await apiClient.get('/dashboard/resumen-completo');
    return response.data;
  }
}

export default DashboardService;
