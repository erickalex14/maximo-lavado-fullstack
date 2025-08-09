import apiService from './api';
import type { 
  ApiResponse,
  Empleado, 
  PaginatedResponse, 
  CreateEmpleadoRequest, 
  UpdateEmpleadoRequest,
  EmpleadoFilters
} from '@/types';

/**
 * Servicio para Empleados - Sistema Legacy
 * Consume las rutas /api/empleados
 */
class EmpleadoService {
  private readonly BASE_URL = '/empleados';

  /**
   * Obtener todos los empleados con paginación y filtros
   * GET /api/empleados
   */
  async getEmpleados(filters: EmpleadoFilters = {}): Promise<PaginatedResponse<Empleado>> {
    const params = new URLSearchParams();
    
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.append(key, value.toString());
      }
    });

    const url = `${this.BASE_URL}${params.toString() ? `?${params.toString()}` : ''}`;
    const response = await apiService.get<PaginatedResponse<Empleado>>(url);
    return response.data || { data: [], current_page: 1, last_page: 1, per_page: 15, total: 0, from: 0, to: 0 };
  }

  /**
   * Obtener empleados eliminados
   * GET /api/empleados/trashed
   */
  async getTrashedEmpleados(): Promise<ApiResponse<Empleado[]>> {
    return await apiService.get(`${this.BASE_URL}/trashed`);
  }

  /**
   * Crear un nuevo empleado
   * POST /api/empleados
   */
  async createEmpleado(data: CreateEmpleadoRequest): Promise<ApiResponse<Empleado>> {
    return await apiService.post(this.BASE_URL, data);
  }

  /**
   * Obtener un empleado específico
   * GET /api/empleados/{empleado}
   */
  async getEmpleado(id: number): Promise<ApiResponse<Empleado>> {
    return await apiService.get(`${this.BASE_URL}/${id}`);
  }

  /**
   * Actualizar un empleado
   * PUT /api/empleados/{empleado}
   */
  async updateEmpleado(id: number, data: UpdateEmpleadoRequest): Promise<ApiResponse<Empleado>> {
    return await apiService.put(`${this.BASE_URL}/${id}`, data);
  }

  /**
   * Restaurar un empleado eliminado
   * PUT /api/empleados/{empleado}/restore
   */
  async restoreEmpleado(id: number): Promise<ApiResponse<Empleado>> {
    return await apiService.put(`${this.BASE_URL}/${id}/restore`);
  }

  /**
   * Eliminar un empleado (soft delete)
   * DELETE /api/empleados/{empleado}
   */
  async deleteEmpleado(id: number): Promise<ApiResponse<void>> {
    return await apiService.delete(`${this.BASE_URL}/${id}`);
  }

  // === CONSULTAS DE LAVADOS POR EMPLEADO ===

  /**
   * Obtener lavados de un empleado por día
   * GET /api/empleados/{empleado}/lavados/dia/{fecha}
   */
  async getLavadosPorDia(empleadoId: number, fecha: string): Promise<ApiResponse<any[]>> {
    return await apiService.get(`${this.BASE_URL}/${empleadoId}/lavados/dia/${fecha}`);
  }

  /**
   * Obtener lavados de un empleado por semana
   * GET /api/empleados/{empleado}/lavados/semana/{fecha}
   */
  async getLavadosPorSemana(empleadoId: number, fecha: string): Promise<ApiResponse<any[]>> {
    return await apiService.get(`${this.BASE_URL}/${empleadoId}/lavados/semana/${fecha}`);
  }

  /**
   * Obtener lavados de un empleado por mes
   * GET /api/empleados/{empleado}/lavados/mes/{anio}/{mes}
   */
  async getLavadosPorMes(empleadoId: number, anio: number, mes: number): Promise<ApiResponse<any[]>> {
    return await apiService.get(`${this.BASE_URL}/${empleadoId}/lavados/mes/${anio}/${mes}`);
  }
}

export default new EmpleadoService();
