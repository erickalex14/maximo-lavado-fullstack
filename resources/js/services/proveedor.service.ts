import apiService from './api';
import type { 
  ApiResponse,
  PaginatedResponse,
  Proveedor, 
  PagoProveedor,
  CreateProveedorRequest, 
  UpdateProveedorRequest,
  CreatePagoProveedorRequest,
  UpdatePagoProveedorRequest,
  ProveedorFilters
} from '@/types';

/**
 * Servicio para Proveedores - Sistema Legacy
 * Consume las rutas /api/proveedores
 */
class ProveedorService {
  private readonly BASE_URL = '/proveedores';

  // ===== CRUD PROVEEDORES =====

  /**
   * Obtener todos los proveedores con paginación y filtros
   * GET /api/proveedores
   */
  async getProveedores(filters?: ProveedorFilters): Promise<PaginatedResponse<Proveedor>> {
    const params = new URLSearchParams();
    
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }

    const url = `${this.BASE_URL}${params.toString() ? `?${params.toString()}` : ''}`;
    const response = await apiService.get<PaginatedResponse<Proveedor>>(url);
    return response.data || { data: [], current_page: 1, last_page: 1, per_page: 15, total: 0, from: 0, to: 0 };
  }

  /**
   * Obtener proveedores eliminados
   * GET /api/proveedores/trashed
   */
  async getTrashedProveedores(): Promise<ApiResponse<Proveedor[]>> {
    return await apiService.get(`${this.BASE_URL}/trashed`);
  }

  /**
   * Obtener todos los pagos
   * GET /api/proveedores/pagos
   */
  async getAllPagos(): Promise<ApiResponse<PagoProveedor[]>> {
    return await apiService.get(`${this.BASE_URL}/pagos`);
  }

  /**
   * Obtener métricas de pagos
   * GET /api/proveedores/pagos/metricas
   */
  async getMetricasPagos(): Promise<ApiResponse<any>> {
    return await apiService.get(`${this.BASE_URL}/pagos/metricas`);
  }

  /**
   * Crear un nuevo proveedor
   * POST /api/proveedores
   */
  async createProveedor(data: CreateProveedorRequest): Promise<ApiResponse<Proveedor>> {
    return await apiService.post(this.BASE_URL, data);
  }

  /**
   * Crear un nuevo pago
   * POST /api/proveedores/pagos
   */
  async createPago(data: CreatePagoProveedorRequest): Promise<ApiResponse<PagoProveedor>> {
    return await apiService.post(`${this.BASE_URL}/pagos`, data);
  }

  /**
   * Obtener un proveedor específico
   * GET /api/proveedores/{id}
   */
  async getProveedor(id: number): Promise<ApiResponse<Proveedor>> {
    return await apiService.get(`${this.BASE_URL}/${id}`);
  }

  /**
   * Actualizar un proveedor
   * PUT /api/proveedores/{id}
   */
  async updateProveedor(id: number, data: UpdateProveedorRequest): Promise<ApiResponse<Proveedor>> {
    return await apiService.put(`${this.BASE_URL}/${id}`, data);
  }

  /**
   * Eliminar un proveedor (soft delete)
   * DELETE /api/proveedores/{id}
   */
  async deleteProveedor(id: number): Promise<ApiResponse<void>> {
    return await apiService.delete(`${this.BASE_URL}/${id}`);
  }

  /**
   * Restaurar un proveedor eliminado
   * PUT /api/proveedores/{id}/restore
   */
  async restoreProveedor(id: number): Promise<ApiResponse<Proveedor>> {
    return await apiService.put(`${this.BASE_URL}/${id}/restore`);
  }

  // ===== GESTIÓN DE DEUDAS Y PAGOS =====

  /**
   * Ver deuda de un proveedor
   * GET /api/proveedores/{id}/deuda
   */
  async verDeuda(id: number): Promise<ApiResponse<any>> {
    return await apiService.get(`${this.BASE_URL}/${id}/deuda`);
  }

  /**
   * Obtener pagos de un proveedor
   * GET /api/proveedores/{id}/pagos
   */
  async getPagosByProveedor(id: number): Promise<ApiResponse<PagoProveedor[]>> {
    return await apiService.get(`${this.BASE_URL}/${id}/pagos`);
  }

  /**
   * Obtener un pago específico
   * GET /api/proveedores/pagos/{pagoId}
   */
  async getPago(pagoId: number): Promise<ApiResponse<PagoProveedor>> {
    return await apiService.get(`${this.BASE_URL}/pagos/${pagoId}`);
  }

  /**
   * Actualizar un pago
   * PUT /api/proveedores/pagos/{pagoId}
   */
  async updatePago(pagoId: number, data: UpdatePagoProveedorRequest): Promise<ApiResponse<PagoProveedor>> {
    return await apiService.put(`${this.BASE_URL}/pagos/${pagoId}`, data);
  }

  /**
   * Eliminar un pago
   * DELETE /api/proveedores/pagos/{pagoId}
   */
  async deletePago(pagoId: number): Promise<ApiResponse<void>> {
    return await apiService.delete(`${this.BASE_URL}/pagos/${pagoId}`);
  }
}

export default new ProveedorService();
