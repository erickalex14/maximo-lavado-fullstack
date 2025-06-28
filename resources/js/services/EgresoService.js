import apiClient from './api';

export class EgresoService {
  /**
   * Obtener todos los egresos
   */
  static async getAll(params = {}) {
    const response = await apiClient.get('/egresos', { params });
    return response.data;
  }

  /**
   * Obtener un egreso por ID
   */
  static async getById(id) {
    const response = await apiClient.get(`/egresos/${id}`);
    return response.data;
  }

  /**
   * Crear un nuevo egreso
   */
  static async create(data) {
    const response = await apiClient.post('/egresos', data);
    return response.data;
  }

  /**
   * Actualizar un egreso
   */
  static async update(id, data) {
    const response = await apiClient.put(`/egresos/${id}`, data);
    return response.data;
  }

  /**
   * Eliminar un egreso
   */
  static async delete(id) {
    const response = await apiClient.delete(`/egresos/${id}`);
    return response.data;
  }

  /**
   * Obtener resumen de egresos por período
   */
  static async getResumenPorPeriodo(params = {}) {
    const response = await apiClient.get('/egresos/resumen', { params });
    return response.data;
  }

  /**
   * Obtener egresos por categoría
   */
  static async getPorCategoria(params = {}) {
    const response = await apiClient.get('/egresos/por-categoria', { params });
    return response.data;
  }

  /**
   * Obtener categorías de egresos
   */
  static async getCategorias() {
    const response = await apiClient.get('/egresos/categorias');
    return response.data;
  }
}

export default EgresoService;
