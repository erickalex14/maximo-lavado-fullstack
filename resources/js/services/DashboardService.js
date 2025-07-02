import { BaseService } from './BaseService';

/**
 * Servicio para gestionar datos del dashboard
 */
class DashboardService extends BaseService {
  constructor() {
    super('/dashboard');
  }

  // Datos principales del dashboard
  async getData(params = {}) {
    try {
      const response = await this.customAction('data', {
        method: 'GET',
        data: params,
        useParams: true
      });
      return response;
    } catch (error) {
      throw error;
    }
  }

  // Métricas generales
  async getMetricas(params = {}) {
    try {
      const response = await this.customAction('metricas', {
        method: 'GET',
        data: params,
        useParams: true
      });
      return response;
    } catch (error) {
      throw error;
    }
  }

  // Actividad reciente
  async getActividadReciente(params = {}) {
    try {
      const response = await this.customAction('actividad', {
        method: 'GET',
        data: params,
        useParams: true
      });
      return response;
    } catch (error) {
      throw error;
    }
  }

  // Próximas citas
  async getProximasCitas(params = {}) {
    try {
      const response = await this.customAction('citas', {
        method: 'GET',
        data: params,
        useParams: true
      });
      return response;
    } catch (error) {
      throw error;
    }
  }

  // Datos para gráficos
  async getChartData(params = {}) {
    try {
      const response = await this.customAction('charts', {
        method: 'GET',
        data: params,
        useParams: true
      });
      return response;
    } catch (error) {
      throw error;
    }
  }

  // Alertas del sistema
  async getAlertas(params = {}) {
    try {
      const response = await this.customAction('alertas', {
        method: 'GET',
        data: params,
        useParams: true
      });
      return response;
    } catch (error) {
      throw error;
    }
  }

  // Estadísticas generales
  async getEstadisticas(params = {}) {
    try {
      const response = await this.customAction('estadisticas', {
        method: 'GET',
        data: params,
        useParams: true
      });
      return response;
    } catch (error) {
      throw error;
    }
  }

  // Análisis financiero
  async getAnalisisFinanciero(params = {}) {
    try {
      const response = await this.customAction('analisis-financiero', {
        method: 'GET',
        data: params,
        useParams: true
      });
      return response;
    } catch (error) {
      throw error;
    }
  }

  // Rendimiento operativo
  async getRendimientoOperativo(params = {}) {
    try {
      const response = await this.customAction('rendimiento-operativo', {
        method: 'GET',
        data: params,
        useParams: true
      });
      return response;
    } catch (error) {
      throw error;
    }
  }

  // Resumen completo
  async getResumenCompleto(params = {}) {
    try {
      const response = await this.customAction('resumen-completo', {
        method: 'GET',
        data: params,
        useParams: true
      });
      return response;
    } catch (error) {
      throw error;
    }
  }
}

// Instancia única del servicio
const dashboardService = new DashboardService();

export default dashboardService;
