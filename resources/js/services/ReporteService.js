import apiClient from './api';

export class ReporteService {
  /**
   * Obtener reporte de ventas
   */
  static async getReporteVentas(params = {}) {
    const response = await apiClient.get('/reportes/ventas', { params });
    return response.data;
  }

  /**
   * Obtener reporte financiero
   */
  static async getReporteFinanciero(params = {}) {
    const response = await apiClient.get('/reportes/financiero', { params });
    return response.data;
  }

  /**
   * Obtener reporte de clientes
   */
  static async getReporteClientes(params = {}) {
    const response = await apiClient.get('/reportes/clientes', { params });
    return response.data;
  }

  /**
   * Obtener reporte de empleados
   */
  static async getReporteEmpleados(params = {}) {
    const response = await apiClient.get('/reportes/empleados', { params });
    return response.data;
  }

  /**
   * Obtener reporte de productos
   */
  static async getReporteProductos(params = {}) {
    const response = await apiClient.get('/reportes/productos', { params });
    return response.data;
  }

  /**
   * Obtener reporte de servicios (lavados)
   */
  static async getReporteServicios(params = {}) {
    const response = await apiClient.get('/reportes/servicios', { params });
    return response.data;
  }

  /**
   * Obtener reporte de inventario
   */
  static async getReporteInventario(params = {}) {
    const response = await apiClient.get('/reportes/inventario', { params });
    return response.data;
  }

  /**
   * Exportar reporte en PDF
   */
  static async exportarPDF(tipo, params = {}) {
    const response = await apiClient.get(`/reportes/${tipo}/pdf`, {
      params,
      responseType: 'blob'
    });
    return response.data;
  }

  /**
   * Exportar reporte en Excel
   */
  static async exportarExcel(tipo, params = {}) {
    const response = await apiClient.get(`/reportes/${tipo}/excel`, {
      params,
      responseType: 'blob'
    });
    return response.data;
  }

  /**
   * Obtener datos para gráficos del dashboard
   */
  static async getDatosGraficos(params = {}) {
    const response = await apiClient.get('/reportes/graficos', { params });
    return response.data;
  }

  /**
   * Obtener métricas principales
   */
  static async getMetricasPrincipales(params = {}) {
    const response = await apiClient.get('/reportes/metricas', { params });
    return response.data;
  }
}

export default ReporteService;
