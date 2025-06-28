import apiClient from './api';

export class ProductoAutomotrizService {
  /**
   * Obtener todos los productos automotrices
   */
  static async getAll(params = {}) {
    const response = await apiClient.get('/productos-automotrices', { params });
    return response.data;
  }

  /**
   * Obtener un producto automotriz por ID
   */
  static async getById(id) {
    const response = await apiClient.get(`/productos-automotrices/${id}`);
    return response.data;
  }

  /**
   * Crear un nuevo producto automotriz
   */
  static async create(data) {
    const response = await apiClient.post('/productos-automotrices', data);
    return response.data;
  }

  /**
   * Actualizar un producto automotriz
   */
  static async update(id, data) {
    const response = await apiClient.put(`/productos-automotrices/${id}`, data);
    return response.data;
  }

  /**
   * Eliminar un producto automotriz
   */
  static async delete(id) {
    const response = await apiClient.delete(`/productos-automotrices/${id}`);
    return response.data;
  }

  /**
   * Actualizar stock del producto
   */
  static async updateStock(id, data) {
    const response = await apiClient.patch(`/productos-automotrices/${id}/stock`, data);
    return response.data;
  }

  /**
   * Obtener productos con stock bajo
   */
  static async getStockBajo() {
    const response = await apiClient.get('/productos-automotrices/stock-bajo');
    return response.data;
  }

  /**
   * Obtener categorías disponibles
   */
  static async getCategorias() {
    const response = await apiClient.get('/productos-automotrices/categorias');
    return response.data;
  }
}

export default ProductoAutomotrizService;
