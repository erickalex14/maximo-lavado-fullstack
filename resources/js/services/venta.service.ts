import apiService from './api';
import type { 
  ApiResponse, 
  PaginatedResponse,
  Venta,
  CreateVentaRequest,
  UpdateVentaRequest,
  VentaFilters
} from '@/types';

/**
 * Servicio para el Sistema Unificado V2 de Ventas
 * Consume las rutas /api/ventas del ControladorVenta unificado
 */
class VentaService {
  private readonly BASE_URL = '/ventas';

  /**
   * Obtener todas las ventas con paginación y filtros
   * GET /api/ventas
   */
  async getVentas(filters?: VentaFilters): Promise<PaginatedResponse<Venta>> {
    const params = new URLSearchParams();
    
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `${this.BASE_URL}${params.toString() ? `?${params.toString()}` : ''}`;
    const response = await apiService.get<PaginatedResponse<Venta>>(url);
    return response.data || { data: [], current_page: 1, last_page: 1, per_page: 15, total: 0, from: 0, to: 0 };
  }

  /**
   * Obtener todas las ventas sin paginación
   * GET /api/ventas/all
   */
  async getAllVentas(): Promise<ApiResponse<Venta[]>> {
    return await apiService.get(`${this.BASE_URL}/all`);
  }

  /**
   * Obtener estadísticas de ventas
   * GET /api/ventas/stats
   */
  async getStats(): Promise<ApiResponse<any>> {
    return await apiService.get(`${this.BASE_URL}/stats`);
  }

  /**
   * Obtener ventas del día actual
   * GET /api/ventas/del-dia
   */
  async getVentasDelDia(): Promise<ApiResponse<Venta[]>> {
    return await apiService.get(`${this.BASE_URL}/del-dia`);
  }

  /**
   * Crear una nueva venta
   * POST /api/ventas
   */
  async createVenta(data: CreateVentaRequest): Promise<ApiResponse<Venta>> {
    return await apiService.post(this.BASE_URL, data);
  }

  /**
   * Obtener una venta específica por ID
   * GET /api/ventas/{id}
   */
  async getVentaById(id: number): Promise<ApiResponse<Venta>> {
    return await apiService.get(`${this.BASE_URL}/${id}`);
  }

  /**
   * Actualizar una venta
   * PUT /api/ventas/{id}
   */
  async updateVenta(id: number, data: UpdateVentaRequest): Promise<ApiResponse<Venta>> {
    return await apiService.put(`${this.BASE_URL}/${id}`, data);
  }

  /**
   * Eliminar una venta (soft delete)
   * DELETE /api/ventas/{id}
   */
  async deleteVenta(id: number): Promise<ApiResponse<void>> {
    return await apiService.delete(`${this.BASE_URL}/${id}`);
  }

  /**
   * Restaurar una venta eliminada
   * PUT /api/ventas/{id}/restore
   */
  async restoreVenta(id: number): Promise<ApiResponse<Venta>> {
    return await apiService.put(`${this.BASE_URL}/${id}/restore`);
  }

  /**
   * Obtener ventas eliminadas
   * GET /api/ventas/trashed/list
   */
  async getTrashedVentas(): Promise<ApiResponse<Venta[]>> {
    return await apiService.get(`${this.BASE_URL}/trashed/list`);
  }

  // === CONSULTAS ESPECÍFICAS ===

  /**
   * Obtener ventas por cliente
   * GET /api/ventas/cliente/{clienteId}
   */
  async getVentasByCliente(clienteId: number): Promise<ApiResponse<Venta[]>> {
    return await apiService.get(`${this.BASE_URL}/cliente/${clienteId}`);
  }

  /**
   * Obtener ventas por empleado
   * GET /api/ventas/empleado/{empleadoId}
   */
  async getVentasByEmpleado(empleadoId: number): Promise<ApiResponse<Venta[]>> {
    return await apiService.get(`${this.BASE_URL}/empleado/${empleadoId}`);
  }

  /**
   * Obtener ventas por fecha específica
   * GET /api/ventas/fecha/{fecha}
   */
  async getVentasByFecha(fecha: string): Promise<ApiResponse<Venta[]>> {
    return await apiService.get(`${this.BASE_URL}/fecha/${fecha}`);
  }

  /**
   * Obtener ventas por rango de fechas
   * GET /api/ventas/rango-fechas
   */
  async getVentasByRangoFechas(fechaInicio: string, fechaFin: string): Promise<ApiResponse<Venta[]>> {
    const params = new URLSearchParams({
      fecha_inicio: fechaInicio,
      fecha_fin: fechaFin
    });
    return await apiService.get(`${this.BASE_URL}/rango-fechas?${params.toString()}`);
  }

  // === ANÁLISIS Y REPORTES ===

  /**
   * Obtener productos más vendidos
   * GET /api/ventas/productos-mas-vendidos
   */
  async getProductosMasVendidos(limite?: number): Promise<ApiResponse<any[]>> {
    const params = limite ? `?limite=${limite}` : '';
    return await apiService.get(`${this.BASE_URL}/productos-mas-vendidos${params}`);
  }

  /**
   * Obtener servicios más vendidos
   * GET /api/ventas/servicios-mas-vendidos
   */
  async getServiciosMasVendidos(limite?: number): Promise<ApiResponse<any[]>> {
    const params = limite ? `?limite=${limite}` : '';
    return await apiService.get(`${this.BASE_URL}/servicios-mas-vendidos${params}`);
  }

  /**
   * Debug endpoint para validar lavados
   * POST /api/ventas/debug-lavados
   */
  async debugLavados(data: any): Promise<ApiResponse<any>> {
    return await apiService.post(`${this.BASE_URL}/debug-lavados`, data);
  }
}

export default new VentaService();
