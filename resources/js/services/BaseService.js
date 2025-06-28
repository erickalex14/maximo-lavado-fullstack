import apiClient from './api';

/**
 * Servicio base genérico que implementa operaciones CRUD estándar
 * Otros servicios pueden extender esta clase para evitar duplicación de código
 */
export class BaseService {
  constructor(endpoint) {
    this.endpoint = endpoint;
  }

  /**
   * Obtener todos los registros con paginación y filtros
   * @param {object} params - Parámetros de consulta
   */
  async getAll(params = {}) {
    const response = await apiClient.get(this.endpoint, { params });
    return response.data;
  }

  /**
   * Obtener registro por ID
   * @param {number} id - ID del registro
   */
  async getById(id) {
    const response = await apiClient.get(`${this.endpoint}/${id}`);
    return response.data;
  }

  /**
   * Crear nuevo registro
   * @param {object} data - Datos del registro
   */
  async create(data) {
    const response = await apiClient.post(this.endpoint, data);
    return response.data;
  }

  /**
   * Actualizar registro
   * @param {number} id - ID del registro
   * @param {object} data - Datos a actualizar
   */
  async update(id, data) {
    const response = await apiClient.put(`${this.endpoint}/${id}`, data);
    return response.data;
  }

  /**
   * Actualizar parcialmente un registro
   * @param {number} id - ID del registro
   * @param {object} data - Datos a actualizar
   */
  async patch(id, data) {
    const response = await apiClient.patch(`${this.endpoint}/${id}`, data);
    return response.data;
  }

  /**
   * Eliminar registro
   * @param {number} id - ID del registro
   */
  async delete(id) {
    const response = await apiClient.delete(`${this.endpoint}/${id}`);
    return response.data;
  }

  /**
   * Buscar registros
   * @param {string} query - Término de búsqueda
   * @param {object} params - Parámetros adicionales
   */
  async search(query, params = {}) {
    const response = await apiClient.get(`${this.endpoint}/search`, {
      params: { q: query, ...params }
    });
    return response.data;
  }

  /**
   * Obtener estadísticas/métricas
   */
  async getStats() {
    const response = await apiClient.get(`${this.endpoint}/stats`);
    return response.data;
  }

  /**
   * Realizar una acción personalizada
   * @param {string} action - Nombre de la acción
   * @param {number} id - ID del registro (opcional)
   * @param {object} data - Datos para la acción (opcional)
   * @param {string} method - Método HTTP (default: POST)
   */
  async customAction(action, id = null, data = null, method = 'POST') {
    const url = id ? `${this.endpoint}/${id}/${action}` : `${this.endpoint}/${action}`;
    
    const config = {
      method: method.toLowerCase(),
      url
    };

    if (data && ['post', 'put', 'patch'].includes(method.toLowerCase())) {
      config.data = data;
    } else if (data) {
      config.params = data;
    }

    const response = await apiClient(config);
    return response.data;
  }
}

/**
 * Factory para crear servicios basados en BaseService
 * @param {string} endpoint - Endpoint base del servicio
 * @returns {BaseService} Instancia del servicio
 */
export const createService = (endpoint) => {
  return new BaseService(endpoint);
};

export default BaseService;
