import api from './api';

/**
 * Servicio para gestión de balance y análisis financiero
 * Consume la API real del backend sin datos de ejemplo
 * Endpoints disponibles según routes/api.php:
 * - GET /balance/general - Obtener balance general
 * - GET /balance/categorias - Balance por categoría
 * - GET /balance/mensual - Balance por mes
 * - GET /balance/trimestral - Balance por trimestre
 * - GET /balance/anual - Balance anual
 * - GET /balance/flujo-caja - Flujo de caja
 * - GET /balance/indicadores - Indicadores financieros
 * - GET /balance/comparativo - Comparativo mensual
 * - GET /balance/proyeccion - Proyección financiera
 * - GET /balance/resumen - Resumen completo
 */
class BalanceServiceClass {
  /**
   * Obtener balance general
   * @param {object} params - Parámetros de consulta (fechas, filtros, etc.)
   * @returns {Promise} Respuesta de la API
   */
  async getBalanceGeneral(params = {}) {
    const response = await api.get('/balance/general', { params });
    return response.data;
  }

  /**
   * Obtener balance por categoría
   * @param {object} params - Parámetros de consulta
   * @returns {Promise} Respuesta de la API
   */
  async getBalancePorCategoria(params = {}) {
    const response = await api.get('/balance/categorias', { params });
    return response.data;
  }

  /**
   * Obtener balance por mes
   * @param {object} params - Parámetros de consulta
   * @returns {Promise} Respuesta de la API
   */
  async getBalancePorMes(params = {}) {
    const response = await api.get('/balance/mensual', { params });
    return response.data;
  }

  /**
   * Obtener balance por trimestre
   * @param {object} params - Parámetros de consulta
   * @returns {Promise} Respuesta de la API
   */
  async getBalancePorTrimestre(params = {}) {
    const response = await api.get('/balance/trimestral', { params });
    return response.data;
  }

  /**
   * Obtener balance anual
   * @param {object} params - Parámetros de consulta
   * @returns {Promise} Respuesta de la API
   */
  async getBalanceAnual(params = {}) {
    const response = await api.get('/balance/anual', { params });
    return response.data;
  }

  /**
   * Obtener flujo de caja
   * @param {object} params - Parámetros de consulta
   * @returns {Promise} Respuesta de la API
   */
  async getFlujoCaja(params = {}) {
    const response = await api.get('/balance/flujo-caja', { params });
    return response.data;
  }

  /**
   * Obtener indicadores financieros
   * @param {object} params - Parámetros de consulta
   * @returns {Promise} Respuesta de la API
   */
  async getIndicadoresFinancieros(params = {}) {
    const response = await api.get('/balance/indicadores', { params });
    return response.data;
  }

  /**
   * Obtener comparativo mensual
   * @param {object} params - Parámetros de consulta
   * @returns {Promise} Respuesta de la API
   */
  async getComparativoMensual(params = {}) {
    const response = await api.get('/balance/comparativo', { params });
    return response.data;
  }

  /**
   * Obtener proyección financiera
   * @param {object} params - Parámetros de consulta
   * @returns {Promise} Respuesta de la API
   */
  async getProyeccion(params = {}) {
    const response = await api.get('/balance/proyeccion', { params });
    return response.data;
  }

  /**
   * Obtener resumen completo
   * @param {object} params - Parámetros de consulta
   * @returns {Promise} Respuesta de la API
   */
  async getResumenCompleto(params = {}) {
    const response = await api.get('/balance/resumen', { params });
    return response.data;
  }
}

// Crear instancia única del servicio
export const balanceService = new BalanceServiceClass();
export default balanceService;
