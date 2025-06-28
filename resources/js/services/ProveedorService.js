import apiClient from './api';

export class ProveedorService {
  /**
   * Obtener todos los proveedores
   */
  static async getAll(params = {}) {
    const response = await apiClient.get('/proveedores', { params });
    return response.data;
  }

  /**
   * Obtener un proveedor por ID
   */
  static async getById(id) {
    const response = await apiClient.get(`/proveedores/${id}`);
    return response.data;
  }

  /**
   * Crear un nuevo proveedor
   */
  static async create(data) {
    const response = await apiClient.post('/proveedores', data);
    return response.data;
  }

  /**
   * Actualizar un proveedor
   */
  static async update(id, data) {
    const response = await apiClient.put(`/proveedores/${id}`, data);
    return response.data;
  }

  /**
   * Eliminar un proveedor
   */
  static async delete(id) {
    const response = await apiClient.delete(`/proveedores/${id}`);
    return response.data;
  }

  /**
   * Obtener historial de compras del proveedor
   */
  static async getHistorialCompras(id, params = {}) {
    const response = await apiClient.get(`/proveedores/${id}/compras`, { params });
    return response.data;
  }

  /**
   * Obtener productos del proveedor
   */
  static async getProductos(id, params = {}) {
    const response = await apiClient.get(`/proveedores/${id}/productos`, { params });
    return response.data;
  }
}

export default ProveedorService;
