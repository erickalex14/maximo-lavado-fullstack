import apiClient from './api';

export class ProductoDespensaService {
  /**
   * Obtener todos los productos de despensa
   */
  static async getAll(params = {}) {
    const response = await apiClient.get('/productos-despensa', { params });
    return response.data;
  }

  /**
   * Obtener un producto de despensa por ID
   */
  static async getById(id) {
    const response = await apiClient.get(`/productos-despensa/${id}`);
    return response.data;
  }

  /**
   * Crear un nuevo producto de despensa
   */
  static async create(data) {
    const response = await apiClient.post('/productos-despensa', data);
    return response.data;
  }

  /**
   * Actualizar un producto de despensa
   */
  static async update(id, data) {
    const response = await apiClient.put(`/productos-despensa/${id}`, data);
    return response.data;
  }

  /**
   * Eliminar un producto de despensa
   */
  static async delete(id) {
    const response = await apiClient.delete(`/productos-despensa/${id}`);
    return response.data;
  }

  /**
   * Actualizar stock del producto
   */
  static async updateStock(id, data) {
    const response = await apiClient.patch(`/productos-despensa/${id}/stock`, data);
    return response.data;
  }

  /**
   * Obtener productos con stock bajo
   */
  static async getStockBajo() {
    const response = await apiClient.get('/productos-despensa/stock-bajo');
    return response.data;
  }

  /**
   * Obtener categorías disponibles
   */
  static async getCategorias() {
    const response = await apiClient.get('/productos-despensa/categorias');
    return response.data;
  }
}

export default ProductoDespensaService;
