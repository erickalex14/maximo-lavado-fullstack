import apiClient from './api';

export class IngresoService {
  /**
   * Obtener todos los ingresos
   */
  static async getAll(params = {}) {
    const response = await apiClient.get('/ingresos', { params });
    return response.data;
  }

  /**
   * Obtener un ingreso por ID
   */
  static async getById(id) {
    const response = await apiClient.get(`/ingresos/${id}`);
    return response.data;
  }

  /**
   * Crear un nuevo ingreso
   */
  static async create(data) {
    const response = await apiClient.post('/ingresos', data);
    return response.data;
  }

  /**
   * Actualizar un ingreso
   */
  static async update(id, data) {
    const response = await apiClient.put(`/ingresos/${id}`, data);
    return response.data;
  }

  /**
   * Eliminar un ingreso
   */
  static async delete(id) {
    const response = await apiClient.delete(`/ingresos/${id}`);
    return response.data;
  }

  /**
   * Obtener resumen de ingresos por período
   */
  static async getResumenPorPeriodo(params = {}) {
    const response = await apiClient.get('/ingresos/resumen', { params });
    return response.data;
  }

  /**
   * Obtener ingresos por categoría
   */
  static async getPorCategoria(params = {}) {
    const response = await apiClient.get('/ingresos/por-categoria', { params });
    return response.data;
  }

  /**
   * Obtener categorías de ingresos
   */
  static async getCategorias() {
    const response = await apiClient.get('/ingresos/categorias');
    return response.data;
  }
}

export default IngresoService;
