import apiClient from './api';

export class ProductoService {
  /**
   * Obtener productos por tipo con paginación y filtros
   * @param {string} tipo - 'automotriz' o 'despensa'
   * @param {object} params - Parámetros de filtrado y paginación
   */
  static async getProductos(tipo = null, params = {}) {
    const endpoint = tipo ? `/productos/${tipo}` : '/productos';
    const response = await apiClient.get(endpoint, { params });
    return response.data;
  }

  /**
   * Obtener todos los productos (sin paginación)
   * @param {string} tipo - 'automotriz' o 'despensa' (opcional)
   */
  static async getAll(tipo = null) {
    const endpoint = tipo ? `/productos/${tipo}/all` : '/productos/all';
    const response = await apiClient.get(endpoint);
    return response.data;
  }

  /**
   * Obtener producto por ID
   * @param {number} id - ID del producto
   * @param {string} tipo - 'automotriz' o 'despensa' (opcional para compatibilidad)
   */
  static async getById(id, tipo = null) {
    const endpoint = tipo ? `/productos/${tipo}/${id}` : `/productos/${id}`;
    const response = await apiClient.get(endpoint);
    return response.data;
  }

  /**
   * Crear nuevo producto
   * @param {object} data - Datos del producto
   * @param {string} tipo - 'automotriz' o 'despensa'
   */
  static async create(data, tipo) {
    const endpoint = `/productos/${tipo}`;
    const response = await apiClient.post(endpoint, data);
    return response.data;
  }

  /**
   * Actualizar producto
   * @param {number} id - ID del producto
   * @param {object} data - Datos a actualizar
   * @param {string} tipo - 'automotriz' o 'despensa' (opcional)
   */
  static async update(id, data, tipo = null) {
    const endpoint = tipo ? `/productos/${tipo}/${id}` : `/productos/${id}`;
    const response = await apiClient.put(endpoint, data);
    return response.data;
  }

  /**
   * Eliminar producto
   * @param {number} id - ID del producto
   * @param {string} tipo - 'automotriz' o 'despensa' (opcional)
   */
  static async delete(id, tipo = null) {
    const endpoint = tipo ? `/productos/${tipo}/${id}` : `/productos/${id}`;
    const response = await apiClient.delete(endpoint);
    return response.data;
  }

  /**
   * Actualizar stock de producto
   * @param {number} id - ID del producto
   * @param {object} data - {cantidad, tipo_movimiento, observaciones}
   */
  static async updateStock(id, data) {
    const response = await apiClient.patch(`/productos/${id}/stock`, data);
    return response.data;
  }

  /**
   * Alternar estado activo de producto
   * @param {number} id - ID del producto
   */
  static async toggleActivo(id) {
    const response = await apiClient.patch(`/productos/${id}/toggle-activo`);
    return response.data;
  }

  /**
   * Obtener métricas de productos
   * @param {string} tipo - 'automotriz' o 'despensa' (opcional)
   */
  static async getMetricas(tipo = null) {
    const endpoint = tipo ? `/productos/${tipo}/metricas` : '/productos/metricas';
    const response = await apiClient.get(endpoint);
    return response.data;
  }

  /**
   * Obtener productos con stock bajo
   * @param {string} tipo - 'automotriz' o 'despensa' (opcional)
   */
  static async getStockBajo(tipo = null) {
    const endpoint = tipo ? `/productos/${tipo}/stock-bajo` : '/productos/stock-bajo';
    const response = await apiClient.get(endpoint);
    return response.data;
  }

  /**
   * Buscar productos
   * @param {string} query - Término de búsqueda
   * @param {string} tipo - 'automotriz' o 'despensa' (opcional)
   */
  static async search(query, tipo = null) {
    const endpoint = tipo ? `/productos/${tipo}/search` : '/productos/search';
    const response = await apiClient.get(endpoint, { params: { q: query } });
    return response.data;
  }

  /**
   * Obtener categorías disponibles
   * @param {string} tipo - 'automotriz' o 'despensa'
   */
  static async getCategorias(tipo) {
    const response = await apiClient.get(`/productos/${tipo}/categorias`);
    return response.data;
  }

  // =======================================
  // MÉTODOS DE COMPATIBILIDAD (deprecated)
  // =======================================
  
  /**
   * @deprecated Usar getProductos('automotriz', params) en su lugar
   */
  static async getProductosAutomotrices(params = {}) {
    return this.getProductos('automotriz', params);
  }

  /**
   * @deprecated Usar getProductos('despensa', params) en su lugar
   */
  static async getProductosDespensa(params = {}) {
    return this.getProductos('despensa', params);
  }

  /**
   * @deprecated Usar create(data, 'automotriz') en su lugar
   */
  static async createProductoAutomotriz(data) {
    return this.create(data, 'automotriz');
  }

  /**
   * @deprecated Usar create(data, 'despensa') en su lugar
   */
  static async createProductoDespensa(data) {
    return this.create(data, 'despensa');
  }

  /**
   * @deprecated Usar getById(id, 'automotriz') en su lugar
   */
  static async getProductoAutomotriz(id) {
    return this.getById(id, 'automotriz');
  }

  /**
   * @deprecated Usar update(id, data, 'automotriz') en su lugar
   */
  static async updateProductoAutomotriz(id, data) {
    return this.update(id, data, 'automotriz');
  }

  /**
   * @deprecated Usar delete(id, 'automotriz') en su lugar
   */
  static async deleteProductoAutomotriz(id) {
    return this.delete(id, 'automotriz');
  }
}

export default ProductoService;
