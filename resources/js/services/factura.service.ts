import apiService from './api';
import type { 
  Factura, 
  FacturaDetalle, 
  Cliente, 
  PaginatedResponse
} from '@/types';

// Tipos específicos para facturación
export interface FacturaFilters {
  page?: number;
  per_page?: number;
  search?: string;
  cliente_id?: number;
  fecha_inicio?: string;
  fecha_fin?: string;
}

export interface CreateFacturaRequest {
  numero_factura: string;
  cliente_id: number;
  fecha: string;
  descripcion?: string;
  total: number;
  detalles?: CreateFacturaDetalleRequest[];
}

export interface CreateFacturaDetalleRequest {
  lavado_id?: number;
  venta_producto_automotriz_id?: number;
  venta_producto_despensa_id?: number;
  cantidad: number;
  precio_unitario: number;
  subtotal: number;
}

export interface UpdateFacturaRequest extends Partial<CreateFacturaRequest> {
  factura_id?: number;
}

export interface FacturaMetricas {
  total_facturas: number;
  facturas_mes_actual: number;
  total_facturado: number;
  facturado_mes_actual: number;
  facturas_pendientes: number;
  promedio_facturacion: number;
}

class FacturaService {
  private readonly BASE_URL = '/facturas';

  // Obtener todas las facturas con paginación y filtros
  async getFacturas(filters: FacturaFilters = {}): Promise<PaginatedResponse<Factura>> {
    const params = new URLSearchParams();
    
    if (filters.page) params.append('page', filters.page.toString());
    if (filters.per_page) params.append('per_page', filters.per_page.toString());
    if (filters.search) params.append('search', filters.search);
    if (filters.cliente_id) params.append('cliente_id', filters.cliente_id.toString());
    if (filters.fecha_inicio) params.append('fecha_inicio', filters.fecha_inicio);
    if (filters.fecha_fin) params.append('fecha_fin', filters.fecha_fin);
    
    const response = await apiService.get(`${this.BASE_URL}?${params.toString()}`);
    return response.data;
  }

  // Obtener factura por ID
  async getFacturaById(id: number): Promise<Factura> {
    const response = await apiService.get(`${this.BASE_URL}/${id}`);
    return response.data.data;
  }

  // Crear nueva factura
  async createFactura(data: CreateFacturaRequest): Promise<Factura> {
    const response = await apiService.post(this.BASE_URL, data);
    return response.data.data;
  }

  // Actualizar factura
  async updateFactura(id: number, data: UpdateFacturaRequest): Promise<Factura> {
    const response = await apiService.put(`${this.BASE_URL}/${id}`, data);
    return response.data.data;
  }

  // Eliminar factura
  async deleteFactura(id: number): Promise<void> {
    await apiService.delete(`${this.BASE_URL}/${id}`);
  }

  // Obtener facturas eliminadas
  async getTrashedFacturas(): Promise<Factura[]> {
    const response = await apiService.get(`${this.BASE_URL}/trashed`);
    return response.data.data;
  }

  // Restaurar factura eliminada
  async restoreFactura(id: number): Promise<Factura> {
    const response = await apiService.put(`${this.BASE_URL}/${id}/restore`);
    return response.data.data;
  }

  // Buscar factura por número
  async findByNumero(numeroFactura: string): Promise<Factura | null> {
    try {
      const response = await apiService.get(`${this.BASE_URL}/numero/${numeroFactura}`);
      return response.data.data;
    } catch (error) {
      return null;
    }
  }

  // Obtener métricas de facturas
  async getMetricas(): Promise<FacturaMetricas> {
    const response = await apiService.get(`${this.BASE_URL}/metricas`);
    return response.data.data;
  }

  // Obtener clientes para formularios
  async getClientes(): Promise<Cliente[]> {
    const response = await apiService.get('/clientes');
    return response.data.data;
  }
}

export const facturaService = new FacturaService();
