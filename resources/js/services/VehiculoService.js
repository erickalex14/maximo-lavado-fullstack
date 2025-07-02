import { BaseService } from './BaseService';

/**
 * Servicio para gestionar vehículos
 */
class VehiculoService extends BaseService {
  constructor() {
    super('/vehiculos');
  }

  // Los métodos CRUD básicos ya están heredados del BaseService:

  // Métodos específicos de vehículos

  // Obtener vehículos por cliente
  async getByCliente(clienteId, params = {}) {
    try {
      const response = await this.customAction(`cliente/${clienteId}`, {
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

  // Obtener vehículos activos solamente
  async getActiveVehiculos(params = {}) {
    return this.index({ ...params, activo: true });
  }

  // Obtener vehículos por marca
  async getByMarca(marca, params = {}) {
    return this.index({ ...params, marca });
  }

  // Obtener vehículos por tipo
  async getByTipo(tipo, params = {}) {
    return this.index({ ...params, tipo });
  }

  // Obtener historial de lavados de un vehículo
  async getLavadosVehiculo(vehiculoId, params = {}) {
    try {
      const response = await this.customAction('lavados', {
        id: vehiculoId,
        method: 'GET',
        data: params,
        useParams: true
      });
      return response;
    } catch (error) {
      throw error;
    }
  }

  // Obtener estadísticas de un vehículo específico
  async getEstadisticasVehiculo(vehiculoId, params = {}) {
    try {
      const response = await this.customAction('estadisticas', {
        id: vehiculoId,
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
const vehiculoService = new VehiculoService();

export default vehiculoService;
