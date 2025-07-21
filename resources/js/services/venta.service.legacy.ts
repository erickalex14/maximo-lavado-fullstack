import apiService from './api';
import type {
  PaginatedResponse,
  VentaProductoAutomotriz,
  VentaProductoDespensa,
  VentaMetricas,
  ProductoVentaOption,
  Cliente,
  CreateVentaAutomotrizRequest,
  CreateVentaDespensaRequest,
  UpdateVentaAutomotrizRequest,
  UpdateVentaDespensaRequest,
  VentaAutomotrizFilters,
  VentaDespensaFilters
} from '@/types';

export const ventaService = {
  // === ENDPOINTS GENERALES ===
  
  /**
   * Obtener todas las ventas con filtros opcionales
   */
  async getVentas(params?: any): Promise<PaginatedResponse<VentaProductoAutomotriz | VentaProductoDespensa>> {
    const response = await apiService.get('/ventas', { params });
    return response.data;
  },

  /**
   * Obtener métricas generales de ventas
   */
  async getMetricas(): Promise<VentaMetricas> {
    const response = await apiService.get('/ventas/metricas');
    return response.data;
  },

  /**
   * Obtener productos disponibles para venta
   */
  async getProductosDisponibles(): Promise<ProductoVentaOption[]> {
    const response = await apiService.get('/ventas/productos-disponibles');
    return response.data;
  },

  /**
   * Obtener clientes para ventas
   */
  async getClientes(): Promise<Cliente[]> {
    const response = await apiService.get('/ventas/clientes');
    return response.data.clientes;
  },

  /**
   * Obtener ventas eliminadas
   */
  async getTrashedVentas(): Promise<(VentaProductoAutomotriz | VentaProductoDespensa)[]> {
    const response = await apiService.get('/ventas/trashed');
    return response.data.ventas;
  },

  // === VENTAS AUTOMOTRICES ===

  /**
   * Obtener ventas de productos automotrices
   */
  async getVentasAutomotrices(filters?: VentaAutomotrizFilters): Promise<PaginatedResponse<VentaProductoAutomotriz>> {
    const response = await apiService.get('/ventas/automotrices', { params: filters });
    return response.data;
  },

  /**
   * Crear una venta de producto automotriz
   */
  async createVentaAutomotriz(ventaData: CreateVentaAutomotrizRequest): Promise<VentaProductoAutomotriz> {
    const response = await apiService.post('/ventas/automotrices', ventaData);
    return response.data.data;
  },

  /**
   * Obtener una venta automotriz específica
   */
  async getVentaAutomotriz(id: number): Promise<VentaProductoAutomotriz> {
    const response = await apiService.get(`/ventas/automotrices/${id}`);
    return response.data.data;
  },

  /**
   * Actualizar una venta automotriz
   */
  async updateVentaAutomotriz(id: number, ventaData: UpdateVentaAutomotrizRequest): Promise<VentaProductoAutomotriz> {
    const response = await apiService.put(`/ventas/automotrices/${id}`, ventaData);
    return response.data.data;
  },

  /**
   * Eliminar una venta automotriz
   */
  async deleteVentaAutomotriz(id: number): Promise<void> {
    await apiService.delete(`/ventas/automotrices/${id}`);
  },

  /**
   * Restaurar una venta automotriz eliminada
   */
  async restoreVentaAutomotriz(id: number): Promise<void> {
    await apiService.put(`/ventas/automotrices/${id}/restore`);
  },

  /**
   * Obtener ventas automotrices eliminadas
   */
  async getTrashedVentasAutomotrices(): Promise<VentaProductoAutomotriz[]> {
    const response = await apiService.get('/ventas/automotrices/trashed');
    return response.data.ventas_automotrices_eliminadas;
  },

  /**
   * Obtener métricas de ventas automotrices
   */
  async getMetricasAutomotrices(filters?: any): Promise<any> {
    const response = await apiService.get('/ventas/automotrices/metricas', { params: filters });
    return response.data.data;
  },

  // === VENTAS DE DESPENSA ===

  /**
   * Obtener ventas de productos de despensa
   */
  async getVentasDespensa(filters?: VentaDespensaFilters): Promise<PaginatedResponse<VentaProductoDespensa>> {
    const response = await apiService.get('/ventas/despensa', { params: filters });
    return response.data;
  },

  /**
   * Crear una venta de producto de despensa
   */
  async createVentaDespensa(ventaData: CreateVentaDespensaRequest): Promise<VentaProductoDespensa> {
    const response = await apiService.post('/ventas/despensa', ventaData);
    return response.data.data;
  },

  /**
   * Obtener una venta de despensa específica
   */
  async getVentaDespensa(id: number): Promise<VentaProductoDespensa> {
    const response = await apiService.get(`/ventas/despensa/${id}`);
    return response.data.data;
  },

  /**
   * Actualizar una venta de despensa
   */
  async updateVentaDespensa(id: number, ventaData: UpdateVentaDespensaRequest): Promise<VentaProductoDespensa> {
    const response = await apiService.put(`/ventas/despensa/${id}`, ventaData);
    return response.data.data;
  },

  /**
   * Eliminar una venta de despensa
   */
  async deleteVentaDespensa(id: number): Promise<void> {
    await apiService.delete(`/ventas/despensa/${id}`);
  },

  /**
   * Restaurar una venta de despensa eliminada
   */
  async restoreVentaDespensa(id: number): Promise<void> {
    await apiService.put(`/ventas/despensa/${id}/restore`);
  },

  /**
   * Obtener ventas de despensa eliminadas
   */
  async getTrashedVentasDespensa(): Promise<VentaProductoDespensa[]> {
    const response = await apiService.get('/ventas/despensa/trashed');
    return response.data.ventas_despensa_eliminadas;
  },

  /**
   * Obtener métricas de ventas de despensa
   */
  async getMetricasDespensa(filters?: any): Promise<any> {
    const response = await apiService.get('/ventas/despensa/metricas', { params: filters });
    return response.data.data;
  }
};
