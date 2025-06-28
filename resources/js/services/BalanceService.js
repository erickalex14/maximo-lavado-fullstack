import apiClient from './api';

export class BalanceService {
  /**
   * Obtener balance general
   */
  static async getBalanceGeneral(params = {}) {
    const response = await apiClient.get('/balance/general', { params });
    return response.data;
  }

  /**
   * Obtener estado de resultados
   */
  static async getEstadoResultados(params = {}) {
    const response = await apiClient.get('/balance/estado-resultados', { params });
    return response.data;
  }

  /**
   * Obtener flujo de caja
   */
  static async getFlujoCaja(params = {}) {
    const response = await apiClient.get('/balance/flujo-caja', { params });
    return response.data;
  }

  /**
   * Obtener indicadores financieros
   */
  static async getIndicadoresFinancieros(params = {}) {
    const response = await apiClient.get('/balance/indicadores', { params });
    return response.data;
  }

  /**
   * Obtener resumen financiero
   */
  static async getResumenFinanciero(params = {}) {
    const response = await apiClient.get('/balance/resumen', { params });
    return response.data;
  }

  /**
   * Exportar balance en PDF
   */
  static async exportarPDF(params = {}) {
    const response = await apiClient.get('/balance/exportar/pdf', { 
      params,
      responseType: 'blob'
    });
    return response.data;
  }

  /**
   * Exportar balance en Excel
   */
  static async exportarExcel(params = {}) {
    const response = await apiClient.get('/balance/exportar/excel', { 
      params,
      responseType: 'blob'
    });
    return response.data;
  }
}

export default BalanceService;
