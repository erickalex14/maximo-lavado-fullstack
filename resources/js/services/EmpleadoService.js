import { BaseService } from './BaseService';

/**
 * Servicio para gestionar empleados
 */
class EmpleadoService extends BaseService {
  constructor() {
    super('/empleados');
  }

  // Los métodos CRUD básicos ya están heredados del BaseService:

  // Métodos específicos para lavados por empleado

  // Obtener lavados por día de un empleado específico
  async getLavadosPorDia(empleadoId, fecha, params = {}) {
    try {
      const response = await this.customAction(`lavados/dia/${fecha}`, {
        id: empleadoId,
        method: 'GET',
        data: params,
        useParams: true
      });
      return response;
    } catch (error) {
      throw error;
    }
  }

  // Obtener lavados por semana de un empleado específico
  async getLavadosPorSemana(empleadoId, fecha, params = {}) {
    try {
      const response = await this.customAction(`lavados/semana/${fecha}`, {
        id: empleadoId,
        method: 'GET',
        data: params,
        useParams: true
      });
      return response;
    } catch (error) {
      throw error;
    }
  }

  // Obtener lavados por mes de un empleado específico
  async getLavadosPorMes(empleadoId, anio, mes, params = {}) {
    try {
      const response = await this.customAction(`lavados/mes/${anio}/${mes}`, {
        id: empleadoId,
        method: 'GET',
        data: params,
        useParams: true
      });
      return response;
    } catch (error) {
      throw error;
    }
  }

  // Métodos de conveniencia adicionales

  // Obtener empleados activos
  async getActiveEmpleados(params = {}) {
    return this.index({ ...params, activo: true });
  }

  // Obtener todos los lavados de un empleado (sin filtro temporal)
  async getAllLavadosEmpleado(empleadoId, params = {}) {
    try {
      const response = await this.customAction('lavados', {
        id: empleadoId,
        method: 'GET',
        data: params,
        useParams: true
      });
      return response;
    } catch (error) {
      throw error;
    }
  }

  // Obtener estadísticas de rendimiento de un empleado
  async getEstadisticasEmpleado(empleadoId, params = {}) {
    try {
      const response = await this.customAction('estadisticas', {
        id: empleadoId,
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
const empleadoService = new EmpleadoService();

export default empleadoService;
