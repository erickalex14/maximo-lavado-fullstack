import apiClient from './api';

export class ClienteService {
  /**
   * Obtener todos los clientes con paginación y filtros
   */
  static async getClientes(params = {}) {
    const response = await apiClient.get('/clientes', { params });
    return response.data;
  }

  /**
   * Obtener todos los clientes (para selects)
   */
  static async getAllClientes() {
    const response = await apiClient.get('/clientes/all');
    return response.data;
  }

  /**
   * Buscar clientes
   */
  static async searchClientes(query) {
    const response = await apiClient.get('/clientes/search', { 
      params: { q: query } 
    });
    return response.data;
  }

  /**
   * Obtener estadísticas de clientes
   */
  static async getStats() {
    const response = await apiClient.get('/clientes/stats');
    return response.data;
  }

  /**
   * Obtener cliente por ID
   */
  static async getCliente(id) {
    const response = await apiClient.get(`/clientes/${id}`);
    return response.data;
  }

  /**
   * Crear nuevo cliente
   */
  static async createCliente(data) {
    const response = await apiClient.post('/clientes', data);
    return response.data;
  }

  /**
   * Actualizar cliente
   */
  static async updateCliente(id, data) {
    const response = await apiClient.put(`/clientes/${id}`, data);
    return response.data;
  }

  /**
   * Alternar estado activo del cliente
   */
  static async toggleActivo(id) {
    const response = await apiClient.patch(`/clientes/${id}/toggle-activo`);
    return response.data;
  }

  /**
   * Eliminar cliente
   */
  static async deleteCliente(id) {
    const response = await apiClient.delete(`/clientes/${id}`);
    return response.data;
  }
}

export default ClienteService;
