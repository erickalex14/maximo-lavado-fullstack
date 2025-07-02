import { BaseService } from './BaseService';

/**
 * Servicio para gestionar clientes
 */
class ClienteService extends BaseService {
  constructor() {
    super('/clientes');
  }

  // Los métodos CRUD básicos ya están heredados del BaseService:

  // Métodos de conveniencia adicionales

  // Obtener solo clientes activos
  async getActiveClientes(params = {}) {
    return this.index({ ...params, activo: true });
  }

  // Obtener solo clientes inactivos
  async getInactiveClientes(params = {}) {
    return this.index({ ...params, activo: false });
  }

  // Obtener cliente con sus vehículos (si existe endpoint)
  async getClienteWithVehiculos(id) {
    try {
      const response = await this.customAction('vehiculos', {
        id,
        method: 'GET'
      });
      return response;
    } catch (error) {
      throw error;
    }
  }

  // Obtener historial de lavados del cliente (si existe endpoint)
  async getClienteLavados(id, params = {}) {
    try {
      const response = await this.customAction('lavados', {
        id,
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
const clienteService = new ClienteService();

export default clienteService;