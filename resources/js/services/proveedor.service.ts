import apiService from './api';
import type { 
  Proveedor, 
  PagoProveedor,
  CreateProveedorRequest, 
  UpdateProveedorRequest,
  CreatePagoProveedorRequest,
  UpdatePagoProveedorRequest
} from '@/types';

export interface ProveedorFilters {
  page?: number;
  per_page?: number;
  search?: string;
  activo?: boolean;
}

export interface PagoFilters {
  page?: number;
  per_page?: number;
  search?: string;
  proveedor_id?: number;
  fecha_desde?: string;
  fecha_hasta?: string;
}

class ProveedorService {
  private basePath = '/proveedores';

  // CRUD Proveedores
  async getProveedores(filters?: ProveedorFilters) {
    const params = new URLSearchParams();
    
    if (filters?.page) params.append('page', filters.page.toString());
    if (filters?.per_page) params.append('per_page', filters.per_page.toString());
    if (filters?.search) params.append('search', filters.search);
    if (filters?.activo !== undefined) params.append('activo', filters.activo.toString());

    const queryString = params.toString();
    const url = queryString ? `${this.basePath}?${queryString}` : this.basePath;
    
    return await apiService.get<Proveedor[]>(url);
  }

  async getProveedor(id: number) {
    return await apiService.get<Proveedor>(`${this.basePath}/${id}`);
  }

  async createProveedor(data: CreateProveedorRequest) {
    return await apiService.post<Proveedor>(this.basePath, data);
  }

  async updateProveedor(id: number, data: UpdateProveedorRequest) {
    return await apiService.put<Proveedor>(`${this.basePath}/${id}`, data);
  }

  async deleteProveedor(id: number) {
    return await apiService.delete(`${this.basePath}/${id}`);
  }

  async restoreProveedor(id: number) {
    return await apiService.put<Proveedor>(`${this.basePath}/${id}/restore`);
  }

  async getTrashedProveedores() {
    return await apiService.get<Proveedor[]>(`${this.basePath}/trashed`);
  }

  // Gestión de deudas
  async getDeudaProveedor(id: number) {
    return await apiService.get(`${this.basePath}/${id}/deuda`);
  }

  async getPagosProveedor(id: number) {
    return await apiService.get<PagoProveedor[]>(`${this.basePath}/${id}/pagos`);
  }

  // Gestión de pagos (todos los proveedores)
  async getAllPagos(filters?: PagoFilters) {
    const params = new URLSearchParams();
    
    if (filters?.page) params.append('page', filters.page.toString());
    if (filters?.per_page) params.append('per_page', filters.per_page.toString());
    if (filters?.search) params.append('search', filters.search);
    if (filters?.proveedor_id) params.append('proveedor_id', filters.proveedor_id.toString());
    if (filters?.fecha_desde) params.append('fecha_desde', filters.fecha_desde);
    if (filters?.fecha_hasta) params.append('fecha_hasta', filters.fecha_hasta);

    const queryString = params.toString();
    const url = queryString ? `${this.basePath}/pagos?${queryString}` : `${this.basePath}/pagos`;
    
    return await apiService.get<PagoProveedor[]>(url);
  }

  async createPago(data: CreatePagoProveedorRequest) {
    return await apiService.post<PagoProveedor>(`${this.basePath}/pagos`, data);
  }

  async getPago(pagoId: number) {
    return await apiService.get<PagoProveedor>(`${this.basePath}/pagos/${pagoId}`);
  }

  async updatePago(pagoId: number, data: UpdatePagoProveedorRequest) {
    return await apiService.put<PagoProveedor>(`${this.basePath}/pagos/${pagoId}`, data);
  }

  async deletePago(pagoId: number) {
    return await apiService.delete(`${this.basePath}/pagos/${pagoId}`);
  }

  async getMetricasPagos() {
    return await apiService.get(`${this.basePath}/pagos/metricas`);
  }
}

export default new ProveedorService();
