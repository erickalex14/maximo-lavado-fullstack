import apiClient from './api';

export class VentaService {
  /**
   * Obtener todas las ventas
   */
  static async getAll(params = {}) {
    const response = await apiClient.get('/ventas', { params });
    return response.data;
  }

  /**
   * Obtener una venta por ID
   */
  static async getById(id) {
    const response = await apiClient.get(`/ventas/${id}`);
    return response.data;
  }

  /**
   * Crear una nueva venta
   */
  static async create(data) {
    const response = await apiClient.post('/ventas', data);
    return response.data;
  }

  /**
   * Actualizar una venta
   */
  static async update(id, data) {
    const response = await apiClient.put(`/ventas/${id}`, data);
    return response.data;
  }

  /**
   * Eliminar una venta
   */
  static async delete(id) {
    const response = await apiClient.delete(`/ventas/${id}`);
    return response.data;
  }

  /**
   * Obtener ventas de productos automotrices
   */
  static async getVentasProductosAutomotrices(params = {}) {
    const response = await apiClient.get('/ventas/productos-automotrices', { params });
    return response.data;
  }

  /**
   * Obtener ventas de productos de despensa
   */
  static async getVentasProductosDespensa(params = {}) {
    const response = await apiClient.get('/ventas/productos-despensa', { params });
    return response.data;
  }

  /**
   * Crear venta de producto automotriz
   */
  static async crearVentaProductoAutomotriz(data) {
    const response = await apiClient.post('/ventas/productos-automotrices', data);
    return response.data;
  }

  /**
   * Crear venta de producto de despensa
   */
  static async crearVentaProductoDespensa(data) {
    const response = await apiClient.post('/ventas/productos-despensa', data);
    return response.data;
  }

  /**
   * Obtener resumen de ventas por período
   */
  static async getResumenPorPeriodo(params = {}) {
    const response = await apiClient.get('/ventas/resumen', { params });
    return response.data;
  }

  /**
   * Obtener top productos vendidos
   */
  static async getTopProductos(params = {}) {
    const response = await apiClient.get('/ventas/top-productos', { params });
    return response.data;
  }
}

export default VentaService;
