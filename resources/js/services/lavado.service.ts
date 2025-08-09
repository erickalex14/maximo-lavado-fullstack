import apiService from './api';
import type { 
  ApiResponse,
  Lavado, 
  PaginatedResponse, 
  CreateLavadoRequest, 
  UpdateLavadoRequest,
  LavadoFilters
} from '@/types';

export interface LavadoDateFilters {
  fecha?: string;
  anio?: number;
  mes?: number;
}

/**
 * Servicio para Lavados - Sistema de auditoría simple
 * Consume las rutas /api/lavados
 */
class LavadoService {
  private readonly BASE_URL = '/lavados';

  /**
   * Obtener todos los lavados con paginación y filtros
   * GET /api/lavados
   */
  async getLavados(filters: LavadoFilters = {}): Promise<PaginatedResponse<Lavado>> {
    const params = new URLSearchParams();
    
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.append(key, value.toString());
      }
    });

    const url = `${this.BASE_URL}${params.toString() ? `?${params.toString()}` : ''}`;
    const response = await apiService.get<PaginatedResponse<Lavado>>(url);
    return response.data || { data: [], current_page: 1, last_page: 1, per_page: 15, total: 0, from: 0, to: 0 };
  }

  /**
   * Obtener todos los lavados sin paginación
   * GET /api/lavados/all
   */
  async getAllLavados(): Promise<ApiResponse<Lavado[]>> {
    return await apiService.get(`${this.BASE_URL}/all`);
  }

  /**
   * Obtener estadísticas de lavados
   * GET /api/lavados/stats
   */
  async getStats(): Promise<ApiResponse<any>> {
    return await apiService.get(`${this.BASE_URL}/stats`);
  }

  /**
   * Obtener lavados recientes
   * GET /api/lavados/recientes
   */
  async getRecientes(limite?: number): Promise<ApiResponse<Lavado[]>> {
    const params = limite ? `?limite=${limite}` : '';
    return await apiService.get(`${this.BASE_URL}/recientes${params}`);
  }

  /**
   * Crear un nuevo lavado
   * POST /api/lavados
   */
  async createLavado(data: CreateLavadoRequest): Promise<ApiResponse<Lavado>> {
    return await apiService.post(this.BASE_URL, data);
  }

  /**
   * Obtener un lavado específico
   * GET /api/lavados/{id}
   */
  async getLavado(id: number): Promise<ApiResponse<Lavado>> {
    return await apiService.get(`${this.BASE_URL}/${id}`);
  }

  /**
   * Actualizar un lavado
   * PUT /api/lavados/{id}
   */
  async updateLavado(id: number, data: UpdateLavadoRequest): Promise<ApiResponse<Lavado>> {
    return await apiService.put(`${this.BASE_URL}/${id}`, data);
  }

  /**
   * Eliminar un lavado (soft delete)
   * DELETE /api/lavados/{id}
   */
  async deleteLavado(id: number): Promise<ApiResponse<void>> {
    return await apiService.delete(`${this.BASE_URL}/${id}`);
  }

  /**
   * Restaurar un lavado eliminado
   * PUT /api/lavados/{id}/restore
   */
  async restoreLavado(id: number): Promise<ApiResponse<Lavado>> {
    return await apiService.put(`${this.BASE_URL}/${id}/restore`);
  }

  /**
   * Obtener lavados eliminados
   * GET /api/lavados/trashed/list
   */
  async getTrashedLavados(): Promise<ApiResponse<Lavado[]>> {
    return await apiService.get(`${this.BASE_URL}/trashed/list`);
  }

  // === CONSULTAS POR ENTIDAD ===

  /**
   * Obtener lavados por cliente
   * GET /api/lavados/cliente/{clienteId}
   */
  async getByCliente(clienteId: number): Promise<ApiResponse<Lavado[]>> {
    return await apiService.get(`${this.BASE_URL}/cliente/${clienteId}`);
  }

  /**
   * Obtener lavados por empleado
   * GET /api/lavados/empleado/{empleadoId}
   */
  async getByEmpleado(empleadoId: number): Promise<ApiResponse<Lavado[]>> {
    return await apiService.get(`${this.BASE_URL}/empleado/${empleadoId}`);
  }

  /**
   * Obtener lavados por vehículo
   * GET /api/lavados/vehiculo/{vehiculoId}
   */
  async getByVehiculo(vehiculoId: number): Promise<ApiResponse<Lavado[]>> {
    return await apiService.get(`${this.BASE_URL}/vehiculo/${vehiculoId}`);
  }

  /**
   * Obtener lavados por servicio
   * GET /api/lavados/servicio/{servicioId}
   */
  async getByServicio(servicioId: number): Promise<ApiResponse<Lavado[]>> {
    return await apiService.get(`${this.BASE_URL}/servicio/${servicioId}`);
  }

  // === CONSULTAS POR FECHA ===

  /**
   * Obtener lavados por día
   * GET /api/lavados/dia/{fecha}
   */
  async getByDay(fecha: string): Promise<ApiResponse<Lavado[]>> {
    return await apiService.get(`${this.BASE_URL}/dia/${fecha}`);
  }

  /**
   * Obtener lavados por semana
   * GET /api/lavados/semana/{fecha}
   */
  async getByWeek(fecha: string): Promise<ApiResponse<Lavado[]>> {
    return await apiService.get(`${this.BASE_URL}/semana/${fecha}`);
  }

  /**
   * Obtener lavados por mes
   * GET /api/lavados/mes/{anio}/{mes}
   */
  async getByMonth(anio: number, mes: number): Promise<ApiResponse<Lavado[]>> {
    return await apiService.get(`${this.BASE_URL}/mes/${anio}/${mes}`);
  }

  /**
   * Obtener lavados por año
   * GET /api/lavados/anio/{anio}
   */
  async getByYear(anio: number): Promise<ApiResponse<Lavado[]>> {
    return await apiService.get(`${this.BASE_URL}/anio/${anio}`);
  }

  /**
   * Obtener lavados por rango de fechas
   * GET /api/lavados/rango-fechas
   */
  async getByRangoFechas(fechaInicio: string, fechaFin: string): Promise<ApiResponse<Lavado[]>> {
    const params = new URLSearchParams({
      fecha_inicio: fechaInicio,
      fecha_fin: fechaFin
    });
    return await apiService.get(`${this.BASE_URL}/rango-fechas?${params.toString()}`);
  }
}

export default new LavadoService();
