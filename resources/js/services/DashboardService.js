import api from './api';

/**
 * Servicio para gestión del Dashboard
 * Consume la API del backend DashboardController
 * 
 * Aplica principios SOLID:
 * - Single Responsibility: Solo maneja datos del dashboard
 * - Open/Closed: Extensible para nuevos tipos de métricas
 * - Liskov Substitution: Métodos consistentes
 * - Interface Segregation: Métodos específicos por funcionalidad
 * - Dependency Inversion: Depende de la abstracción API
 * 
 * Endpoints disponibles:
 * - GET /api/dashboard/data - Datos principales
 * - GET /api/dashboard/metricas - Métricas principales
 * - GET /api/dashboard/actividad - Actividad reciente
 * - GET /api/dashboard/citas - Próximas citas
 * - GET /api/dashboard/charts - Datos para gráficos
 * - GET /api/dashboard/alertas - Alertas del sistema
 * - GET /api/dashboard/estadisticas - Estadísticas generales
 * - GET /api/dashboard/analisis-financiero - Análisis financiero
 * - GET /api/dashboard/rendimiento-operativo - Rendimiento operativo
 * - GET /api/dashboard/resumen-completo - Resumen completo
 */
class DashboardServiceClass {
  
  // =================================================================
  // MÉTODOS PRINCIPALES DEL DASHBOARD
  // =================================================================

  /**
   * Obtener datos completos del dashboard
   * @param {object} params - Parámetros opcionales
   * @returns {Promise} Respuesta de la API
   */
  async getDashboardData(params = {}) {
    return this.makeApiCall('/dashboard/data', params);
  }

  /**
   * Obtener métricas principales del dashboard
   * @param {object} params - Parámetros opcionales
   * @returns {Promise} Respuesta de la API
   */
  async getMetricas(params = {}) {
    return this.makeApiCall('/dashboard/metricas', params);
  }

  /**
   * Obtener actividad reciente
   * @param {number} limite - Límite de resultados (opcional)
   * @returns {Promise} Respuesta de la API
   */
  async getActividadReciente(limite = 5) {
    return this.makeApiCall('/dashboard/actividad', { limite });
  }

  /**
   * Obtener próximas citas
   * @param {number} limite - Límite de resultados (opcional)
   * @returns {Promise} Respuesta de la API
   */
  async getProximasCitas(limite = 5) {
    return this.makeApiCall('/dashboard/citas', { limite });
  }

  /**
   * Obtener datos para gráficos
   * @param {object} params - Parámetros opcionales
   * @returns {Promise} Respuesta de la API
   */
  async getChartData(params = {}) {
    return this.makeApiCall('/dashboard/charts', params);
  }

  /**
   * Obtener alertas del sistema
   * @param {object} params - Parámetros opcionales
   * @returns {Promise} Respuesta de la API
   */
  async getAlertas(params = {}) {
    return this.makeApiCall('/dashboard/alertas', params);
  }

  /**
   * Obtener estadísticas generales
   * @param {object} params - Parámetros opcionales
   * @returns {Promise} Respuesta de la API
   */
  async getEstadisticas(params = {}) {
    return this.makeApiCall('/dashboard/estadisticas', params);
  }

  /**
   * Obtener análisis financiero
   * @param {object} params - Parámetros opcionales (fechas, filtros)
   * @returns {Promise} Respuesta de la API
   */
  async getAnalisisFinanciero(params = {}) {
    return this.makeApiCall('/dashboard/analisis-financiero', params);
  }

  /**
   * Obtener rendimiento operativo
   * @param {object} params - Parámetros opcionales
   * @returns {Promise} Respuesta de la API
   */
  async getRendimientoOperativo(params = {}) {
    return this.makeApiCall('/dashboard/rendimiento-operativo', params);
  }

  /**
   * Obtener resumen completo del dashboard
   * @param {object} params - Parámetros opcionales
   * @returns {Promise} Respuesta de la API
   */
  async getResumenCompleto(params = {}) {
    return this.makeApiCall('/dashboard/resumen-completo', params);
  }

  // =================================================================
  // MÉTODOS UTILITARIOS
  // =================================================================

  /**
   * Obtener datos del dashboard por rango de fechas
   * @param {string} fechaInicio - Fecha de inicio (YYYY-MM-DD)
   * @param {string} fechaFin - Fecha de fin (YYYY-MM-DD)
   * @returns {Promise} Respuesta de la API
   */
  async getDashboardDataByDateRange(fechaInicio, fechaFin) {
    return this.getDashboardData({ fecha_inicio: fechaInicio, fecha_fin: fechaFin });
  }

  /**
   * Obtener métricas por período
   * @param {string} periodo - Período (hoy, semana, mes, año)
   * @returns {Promise} Respuesta de la API
   */
  async getMetricasByPeriodo(periodo = 'hoy') {
    return this.getMetricas({ periodo });
  }

  /**
   * Refrescar todos los datos del dashboard
   * @returns {Promise} Objeto con todos los datos del dashboard
   */
  async refreshAllData() {
    try {
      const [
        dashboardData,
        metricas,
        actividad,
        citas,
        chartData,
        alertas
      ] = await Promise.all([
        this.getDashboardData(),
        this.getMetricas(),
        this.getActividadReciente(),
        this.getProximasCitas(),
        this.getChartData(),
        this.getAlertas()
      ]);

      return {
        dashboardData,
        metricas,
        actividad,
        citas,
        chartData,
        alertas
      };
    } catch (error) {
      console.error('Error al refrescar datos del dashboard:', error);
      throw this.processApiError(error);
    }
  }

  // =================================================================
  // MÉTODOS PRIVADOS PARA APLICAR DRY
  // =================================================================

  /**
   * Método privado para hacer llamadas a la API (DRY)
   * @param {string} endpoint - Endpoint de la API
   * @param {object} params - Parámetros de consulta
   * @returns {Promise} Respuesta de la API
   */
  async makeApiCall(endpoint, params = {}) {
    try {
      const response = await api.get(endpoint, { params });
      return response.data;
    } catch (error) {
      console.error(`Error en ${endpoint}:`, error);
      throw this.processApiError(error);
    }
  }

  /**
   * Procesar errores de la API de forma centralizada
   * @param {Error} error - Error de la API
   * @returns {Error} Error procesado
   */
  processApiError(error) {
    if (error.response && error.response.data && error.response.data.message) {
      return new Error(error.response.data.message);
    }
    
    if (error.response && error.response.status === 401) {
      return new Error('No autorizado. Inicie sesión nuevamente.');
    }
    
    if (error.response && error.response.status === 403) {
      return new Error('No tiene permisos para acceder a esta información.');
    }
    
    if (error.response && error.response.status === 500) {
      return new Error('Error interno del servidor. Contacte al administrador.');
    }
    
    return new Error('Error desconocido en el dashboard.');
  }

  /**
   * Formatear datos para mostrar en la UI
   * @param {object} data - Datos del dashboard
   * @returns {object} Datos formateados
   */
  formatDashboardData(data) {
    if (!data || !data.data) {
      return null;
    }

    return {
      ...data.data,
      // Formatear números como moneda
      ingresos_formatted: data.data.ingresos_hoy ? 
        new Intl.NumberFormat('es-ES', {
          style: 'currency',
          currency: 'EUR'
        }).format(data.data.ingresos_hoy) : '€0',
      
      // Formatear fechas
      fecha_formatted: new Date().toLocaleDateString('es-ES'),
      
      // Calcular porcentajes de cambio
      porcentaje_cambio: data.data.comparacion_anterior ? 
        ((data.data.valor_actual - data.data.valor_anterior) / data.data.valor_anterior * 100).toFixed(1) : 0
    };
  }
}

// Crear instancia única del servicio
export const dashboardService = new DashboardServiceClass();
export default dashboardService;
