import apiClient from './api';

export class LavadoService {
  /**
   * Obtener todos los lavados con paginación
   */
  static async getLavados(params = {}) {
    const response = await apiClient.get('/lavados', { params });
    return response.data;
  }

  /**
   * Obtener lavado por ID
   */
  static async getLavado(id) {
    const response = await apiClient.get(`/lavados/${id}`);
    return response.data;
  }

  /**
   * Crear nuevo lavado
   */
  static async createLavado(data) {
    const response = await apiClient.post('/lavados', data);
    return response.data;
  }

  /**
   * Actualizar lavado
   */
  static async updateLavado(id, data) {
    const response = await apiClient.put(`/lavados/${id}`, data);
    return response.data;
  }

  /**
   * Eliminar lavado
   */
  static async deleteLavado(id) {
    const response = await apiClient.delete(`/lavados/${id}`);
    return response.data;
  }
}

export default LavadoService;
