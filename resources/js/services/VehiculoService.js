import apiClient from './api';

export class VehiculoService {
  /**
   * Obtener todos los vehículos con paginación y filtros
   */
  static async getVehiculos(params = {}) {
    const response = await apiClient.get('/vehiculos', { params });
    return response.data;
  }

  /**
   * Obtener todos los vehículos (para selects)
   */
  static async getAllVehiculos() {
    const response = await apiClient.get('/vehiculos/all');
    return response.data;
  }

  /**
   * Obtener estadísticas de vehículos
   */
  static async getStats() {
    const response = await apiClient.get('/vehiculos/stats');
    return response.data;
  }

  /**
   * Obtener vehículos por cliente
   */
  static async getByCliente(clienteId) {
    const response = await apiClient.get(`/vehiculos/cliente/${clienteId}`);
    return response.data;
  }

  /**
   * Obtener vehículo por ID
   */
  static async getVehiculo(id) {
    const response = await apiClient.get(`/vehiculos/${id}`);
    return response.data;
  }

  /**
   * Crear nuevo vehículo
   */
  static async createVehiculo(data) {
    const response = await apiClient.post('/vehiculos', data);
    return response.data;
  }

  /**
   * Actualizar vehículo
   */
  static async updateVehiculo(id, data) {
    const response = await apiClient.put(`/vehiculos/${id}`, data);
    return response.data;
  }

  /**
   * Eliminar vehículo
   */
  static async deleteVehiculo(id) {
    const response = await apiClient.delete(`/vehiculos/${id}`);
    return response.data;
  }
}

export default VehiculoService;
