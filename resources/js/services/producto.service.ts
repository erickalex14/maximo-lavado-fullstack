import apiService from './api';
import type { 
  ApiResponse,
  ProductoAutomotriz, 
  ProductoDespensa, 
  PaginatedResponse, 
  CreateProductoAutomotrizRequest, 
  UpdateProductoAutomotrizRequest, 
  CreateProductoDespensaRequest, 
  UpdateProductoDespensaRequest,
  ProductoFilters
} from '@/types';

/**
 * Servicio para Productos Unificados - Sistema Legacy
 * Consume las rutas /api/productos
 */
class ProductoService {
  private readonly BASE_URL = '/productos';

  // ===== MÉTODOS GENERALES =====
  
  /**
   * Obtener métricas generales de productos
   * GET /api/productos/metricas
   */
  async getMetricas(): Promise<ApiResponse<any>> {
    return await apiService.get(`${this.BASE_URL}/metricas`);
  }

  // ===== PRODUCTOS AUTOMOTRICES =====
  
  /**
   * Obtener productos automotrices con paginación y filtros
   * GET /api/productos/automotrices
   */
  async getProductosAutomotrices(filters?: ProductoFilters): Promise<PaginatedResponse<ProductoAutomotriz>> {
    const params = new URLSearchParams();
    
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }

    const url = `${this.BASE_URL}/automotrices${params.toString() ? `?${params.toString()}` : ''}`;
    const response = await apiService.get<PaginatedResponse<ProductoAutomotriz>>(url);
    return response.data || { data: [], current_page: 1, last_page: 1, per_page: 15, total: 0, from: 0, to: 0 };
  }

  /**
   * Obtener productos automotrices eliminados
   * GET /api/productos/automotrices/trashed
   */
  async getTrashedAutomotrices(): Promise<ApiResponse<ProductoAutomotriz[]>> {
    return await apiService.get(`${this.BASE_URL}/automotrices/trashed`);
  }

  /**
   * Crear producto automotriz
   * POST /api/productos/automotrices
   */
  async createProductoAutomotriz(data: CreateProductoAutomotrizRequest): Promise<ApiResponse<ProductoAutomotriz>> {
    return await apiService.post(`${this.BASE_URL}/automotrices`, data);
  }

  /**
   * Obtener producto automotriz por ID
   * GET /api/productos/automotrices/{id}
   */
  async getProductoAutomotrizById(id: number): Promise<ApiResponse<ProductoAutomotriz>> {
    return await apiService.get(`${this.BASE_URL}/automotrices/${id}`);
  }

  /**
   * Actualizar producto automotriz
   * PUT /api/productos/automotrices/{id}
   */
  async updateProductoAutomotriz(id: number, data: UpdateProductoAutomotrizRequest): Promise<ApiResponse<ProductoAutomotriz>> {
    return await apiService.put(`${this.BASE_URL}/automotrices/${id}`, data);
  }

  /**
   * Restaurar producto automotriz eliminado
   * PUT /api/productos/automotrices/{id}/restore
   */
  async restoreProductoAutomotriz(id: number): Promise<ApiResponse<ProductoAutomotriz>> {
    return await apiService.put(`${this.BASE_URL}/automotrices/${id}/restore`);
  }

  /**
   * Actualizar stock de producto automotriz
   * PUT /api/productos/automotrices/{id}/stock
   */
  async updateStockAutomotriz(id: number, stock: number): Promise<ApiResponse<ProductoAutomotriz>> {
    return await apiService.put(`${this.BASE_URL}/automotrices/${id}/stock`, { stock });
  }

  /**
   * Eliminar producto automotriz
   * DELETE /api/productos/automotrices/{id}
   */
  async deleteProductoAutomotriz(id: number): Promise<ApiResponse<void>> {
    return await apiService.delete(`${this.BASE_URL}/automotrices/${id}`);
  }

  // ===== PRODUCTOS DE DESPENSA =====
  
  /**
   * Obtener productos de despensa con paginación y filtros
   * GET /api/productos/despensa
   */
  async getProductosDespensa(filters?: ProductoFilters): Promise<PaginatedResponse<ProductoDespensa>> {
    const params = new URLSearchParams();
    
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }

    const url = `${this.BASE_URL}/despensa${params.toString() ? `?${params.toString()}` : ''}`;
    const response = await apiService.get<PaginatedResponse<ProductoDespensa>>(url);
    return response.data || { data: [], current_page: 1, last_page: 1, per_page: 15, total: 0, from: 0, to: 0 };
  }

  /**
   * Obtener productos de despensa eliminados
   * GET /api/productos/despensa/trashed
   */
  async getTrashedDespensa(): Promise<ApiResponse<ProductoDespensa[]>> {
    return await apiService.get(`${this.BASE_URL}/despensa/trashed`);
  }

  /**
   * Crear producto de despensa
   * POST /api/productos/despensa
   */
  async createProductoDespensa(data: CreateProductoDespensaRequest): Promise<ApiResponse<ProductoDespensa>> {
    return await apiService.post(`${this.BASE_URL}/despensa`, data);
  }

  /**
   * Obtener producto de despensa por ID
   * GET /api/productos/despensa/{id}
   */
  async getProductoDespensaById(id: number): Promise<ApiResponse<ProductoDespensa>> {
    return await apiService.get(`${this.BASE_URL}/despensa/${id}`);
  }

  /**
   * Actualizar producto de despensa
   * PUT /api/productos/despensa/{id}
   */
  async updateProductoDespensa(id: number, data: UpdateProductoDespensaRequest): Promise<ApiResponse<ProductoDespensa>> {
    return await apiService.put(`${this.BASE_URL}/despensa/${id}`, data);
  }

  /**
   * Restaurar producto de despensa eliminado
   * PUT /api/productos/despensa/{id}/restore
   */
  async restoreProductoDespensa(id: number): Promise<ApiResponse<ProductoDespensa>> {
    return await apiService.put(`${this.BASE_URL}/despensa/${id}/restore`);
  }

  /**
   * Actualizar stock de producto de despensa
   * PUT /api/productos/despensa/{id}/stock
   */
  async updateStockDespensa(id: number, stock: number): Promise<ApiResponse<ProductoDespensa>> {
    return await apiService.put(`${this.BASE_URL}/despensa/${id}/stock`, { stock });
  }

  /**
   * Eliminar producto de despensa
   * DELETE /api/productos/despensa/{id}
   */
  async deleteProductoDespensa(id: number): Promise<ApiResponse<void>> {
    return await apiService.delete(`${this.BASE_URL}/despensa/${id}`);
  }
}

export default new ProductoService();
