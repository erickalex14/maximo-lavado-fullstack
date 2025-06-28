import apiClient from './api';

export class ProductoService {
  /**
   * Obtener todos los productos
   */
  static async getProductos(params = {}) {
    const response = await apiClient.get('/productos', { params });
    return response.data;
  }

  /**
   * Obtener métricas de productos
   */
  static async getMetricas() {
    const response = await apiClient.get('/productos/metricas');
    return response.data;
  }

  /**
   * Obtener productos automotrices
   */
  static async getProductosAutomotrices(params = {}) {
    const response = await apiClient.get('/productos/automotrices', { params });
    return response.data;
  }

  /**
   * Crear producto automotriz
   */
  static async createProductoAutomotriz(data) {
    const response = await apiClient.post('/productos/automotrices', data);
    return response.data;
  }

  /**
   * Obtener producto automotriz por ID
   */
  static async getProductoAutomotriz(id) {
    const response = await apiClient.get(`/productos/automotrices/${id}`);
    return response.data;
  }

  /**
   * Actualizar producto automotriz
   */
  static async updateProductoAutomotriz(id, data) {
    const response = await apiClient.put(`/productos/automotrices/${id}`, data);
    return response.data;
  }

  /**
   * Eliminar producto automotriz
   */
  static async deleteProductoAutomotriz(id) {
    const response = await apiClient.delete(`/productos/automotrices/${id}`);
    return response.data;
  }

  /**
   * Obtener productos de despensa
   */
  static async getProductosDespensa(params = {}) {
    const response = await apiClient.get('/productos/despensa', { params });
    return response.data;
  }

  /**
   * Crear producto de despensa
   */
  static async createProductoDespensa(data) {
    const response = await apiClient.post('/productos/despensa', data);
    return response.data;
  }

  /**
   * Actualizar stock de producto
   */
  static async updateStock(id, data) {
    const response = await apiClient.patch(`/productos/${id}/stock`, data);
    return response.data;
  }

  /**
   * Alternar estado activo de producto
   */
  static async toggleActivo(id) {
    const response = await apiClient.patch(`/productos/${id}/toggle-activo`);
    return response.data;
  }
}

export default ProductoService;
