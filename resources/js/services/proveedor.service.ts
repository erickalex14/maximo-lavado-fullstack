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
          // Mapear nombres si backend usa snake_case
          const mapped = key === 'conDeuda' ? 'con_deuda' : key;
          params.append(mapped, value.toString());
        }
      });
    }
    const url = `${this.BASE_URL}${params.toString() ? `?${params.toString()}` : ''}`;
    const raw: any = await apiService.get<any>(url);
    // Posibles formatos:
    // 1) { success:true, data:{ data:[...], current_page:1,... } }
    // 2) { data:[...], current_page:1,... }
    // 3) PaginatedResponse directamente
    const candidate = raw?.data && raw.data.data && Array.isArray(raw.data.data) ? raw.data : (raw && Array.isArray(raw.data) && 'current_page' in raw ? raw : null);
    if (candidate) {
      return {
        data: candidate.data || [],
        current_page: candidate.current_page || 1,
        last_page: candidate.last_page || 1,
        per_page: candidate.per_page || (candidate.data?.length ?? 15),
        total: candidate.total || (candidate.data?.length ?? 0),
        from: candidate.from ?? (candidate.data?.length ? 1 : 0),
        to: candidate.to ?? (candidate.data?.length ?? 0),
      };
    }
    // Si viene array plano
    const arr: Proveedor[] = Array.isArray(raw?.data) ? raw.data : (Array.isArray(raw) ? raw : []);
    return { data: arr, current_page: 1, last_page: 1, per_page: arr.length || 15, total: arr.length, from: arr.length ? 1 : 0, to: arr.length };
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
   * Obtener pagos con filtros/paginación (si el backend lo soporta)
   * GET /api/proveedores/pagos?proveedor_id=&search=&fecha_desde=&fecha_hasta=&page=&per_page=
   */
  async getPagos(filters?: Record<string, any>): Promise<PaginatedResponse<PagoProveedor>> { // retornamos paginado normalizado
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([k, v]) => {
        if (v !== undefined && v !== null && v !== '') {
          const mapped = k === 'proveedor_id' ? 'proveedor_id' : (k === 'fecha_desde' ? 'fecha_desde' : (k === 'fecha_hasta' ? 'fecha_hasta' : k));
          params.append(mapped, v.toString());
        }
      });
    }
    const url = `${this.BASE_URL}/pagos${params.toString() ? `?${params.toString()}` : ''}`;
    const raw: any = await apiService.get<any>(url);
    const candidate = raw?.data && raw.data.data && Array.isArray(raw.data.data) ? raw.data : (raw && Array.isArray(raw.data) && 'current_page' in raw ? raw : null);
    if (candidate) {
      return {
        data: candidate.data || [],
        current_page: candidate.current_page || 1,
        last_page: candidate.last_page || 1,
        per_page: candidate.per_page || (candidate.data?.length ?? 15),
        total: candidate.total || (candidate.data?.length ?? 0),
        from: candidate.from ?? (candidate.data?.length ? 1 : 0),
        to: candidate.to ?? (candidate.data?.length ?? 0),
      };
    }
    const arr: PagoProveedor[] = Array.isArray(raw?.data) ? raw.data : (Array.isArray(raw) ? raw : []);
    return { data: arr, current_page: 1, last_page: 1, per_page: arr.length || 15, total: arr.length, from: arr.length ? 1 : 0, to: arr.length };
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
