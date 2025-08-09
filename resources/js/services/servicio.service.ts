import apiService from './api';
import type { 
  ApiResponse, 
  PaginatedResponse,
  Servicio,
  ServicioPrecio,
  CreateServicioRequest,
  UpdateServicioRequest
} from '@/types';

/**
 * Servicio para Servicios - Sistema V2
 * Consume las rutas /api/servicios
 */
class ServicioService {
  private readonly BASE_URL = '/servicios';

  /**
   * Obtener todos los servicios con paginación
   * GET /api/servicios
   */
  async getServicios(params?: any): Promise<PaginatedResponse<Servicio>> {
    const raw: any = await apiService.get<any>(this.BASE_URL, params);
    const candidate =
      (raw && 'current_page' in raw && Array.isArray(raw.data)) ? raw :
      (raw && raw.data && 'current_page' in raw.data && Array.isArray(raw.data.data)) ? raw.data :
      null;
    if (candidate) return candidate as PaginatedResponse<Servicio>;
    const arrayData: Servicio[] = Array.isArray(raw?.data) ? raw.data : (Array.isArray(raw) ? raw : []);
    return { data: arrayData, current_page: 1, last_page: 1, per_page: arrayData.length || 15, total: arrayData.length, from: arrayData.length ? 1 : 0, to: arrayData.length };
  }

  /**
   * Obtener todos los servicios sin paginación
   * GET /api/servicios/all
   */
  async getAllServicios(): Promise<ApiResponse<Servicio[]>> {
    return await apiService.get(`${this.BASE_URL}/all`);
  }

  /**
   * Obtener solo servicios activos
   * GET /api/servicios/activos
   */
  async getServiciosActivos(): Promise<ApiResponse<Servicio[]>> {
    return await apiService.get(`${this.BASE_URL}/activos`);
  }

  /**
   * Obtener estadísticas de servicios
   * GET /api/servicios/stats
   */
  async getStats(): Promise<ApiResponse<any>> {
    return await apiService.get(`${this.BASE_URL}/stats`);
  }

  /**
   * Crear un nuevo servicio
   * POST /api/servicios
   */
  async createServicio(data: CreateServicioRequest): Promise<ApiResponse<Servicio>> {
    return await apiService.post(this.BASE_URL, data);
  }

  /**
   * Obtener un servicio específico
   * GET /api/servicios/{id}
   */
  async getServicioById(id: number): Promise<ApiResponse<Servicio>> {
    return await apiService.get(`${this.BASE_URL}/${id}`);
  }

  /**
   * Actualizar un servicio
   * PUT /api/servicios/{id}
   */
  async updateServicio(id: number, data: UpdateServicioRequest): Promise<ApiResponse<Servicio>> {
    return await apiService.put(`${this.BASE_URL}/${id}`, data);
  }

  /**
   * Alternar el estado activo de un servicio
   * PATCH /api/servicios/{id}/toggle-activo
   */
  async toggleActivo(id: number): Promise<ApiResponse<Servicio>> {
    return await apiService.patch(`${this.BASE_URL}/${id}/toggle-activo`);
  }

  /**
   * Eliminar un servicio (soft delete)
   * DELETE /api/servicios/{id}
   */
  async deleteServicio(id: number): Promise<ApiResponse<void>> {
    return await apiService.delete(`${this.BASE_URL}/${id}`);
  }

  /**
   * Restaurar un servicio eliminado
   * PUT /api/servicios/{id}/restore
   */
  async restoreServicio(id: number): Promise<ApiResponse<Servicio>> {
    return await apiService.put(`${this.BASE_URL}/${id}/restore`);
  }

  /**
   * Obtener servicios eliminados
   * GET /api/servicios/trashed/list
   */
  async getTrashedServicios(): Promise<ApiResponse<Servicio[]>> {
    return await apiService.get(`${this.BASE_URL}/trashed/list`);
  }

  // === GESTIÓN DE PRECIOS ===

  /**
   * Obtener precios de un servicio por tipo de vehículo
   * GET /api/servicios/{id}/precios
   */
  async getPrecios(servicioId: number): Promise<ApiResponse<ServicioPrecio[]>> {
    return await apiService.get(`${this.BASE_URL}/${servicioId}/precios`);
  }

  /**
   * Actualizar precio de un servicio para un tipo de vehículo
   * PUT /api/servicios/{id}/precios/{tipoVehiculoId}
   */
  async updatePrecio(servicioId: number, tipoVehiculoId: number, precio: number): Promise<ApiResponse<ServicioPrecio>> {
    return await apiService.put(`${this.BASE_URL}/${servicioId}/precios/${tipoVehiculoId}`, { precio });
  }

  /**
   * Eliminar precio de un servicio para un tipo de vehículo
   * DELETE /api/servicios/{id}/precios/{tipoVehiculoId}
   */
  async deletePrecio(servicioId: number, tipoVehiculoId: number): Promise<ApiResponse<void>> {
    return await apiService.delete(`${this.BASE_URL}/${servicioId}/precios/${tipoVehiculoId}`);
  }
}

export default new ServicioService();
