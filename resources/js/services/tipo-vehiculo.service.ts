import apiService from './api';
import type { 
  ApiResponse, 
  PaginatedResponse,
  TipoVehiculo,
  CreateTipoVehiculoRequest,
  UpdateTipoVehiculoRequest
} from '@/types';

/**
 * Servicio para Tipos de Vehículos - Sistema V2
 * Consume las rutas /api/tipos-vehiculos
 */
class TipoVehiculoService {
  private readonly BASE_URL = '/tipos-vehiculos';

  /**
   * Obtener todos los tipos de vehículos con paginación
   * GET /api/tipos-vehiculos
   */
  async getTiposVehiculos(params?: any): Promise<PaginatedResponse<TipoVehiculo>> {
    const response = await apiService.get<PaginatedResponse<TipoVehiculo>>(this.BASE_URL, params);
    return response.data || { data: [], current_page: 1, last_page: 1, per_page: 15, total: 0, from: 0, to: 0 };
  }

  /**
   * Obtener todos los tipos de vehículos sin paginación
   * GET /api/tipos-vehiculos/all
   */
  async getAllTiposVehiculos(): Promise<ApiResponse<TipoVehiculo[]>> {
    return await apiService.get(`${this.BASE_URL}/all`);
  }

  /**
   * Obtener estadísticas de tipos de vehículos
   * GET /api/tipos-vehiculos/stats
   */
  async getStats(): Promise<ApiResponse<any>> {
    return await apiService.get(`${this.BASE_URL}/stats`);
  }

  /**
   * Crear un nuevo tipo de vehículo
   * POST /api/tipos-vehiculos
   */
  async createTipoVehiculo(data: CreateTipoVehiculoRequest): Promise<ApiResponse<TipoVehiculo>> {
    return await apiService.post(this.BASE_URL, data);
  }

  /**
   * Obtener un tipo de vehículo específico
   * GET /api/tipos-vehiculos/{id}
   */
  async getTipoVehiculoById(id: number): Promise<ApiResponse<TipoVehiculo>> {
    return await apiService.get(`${this.BASE_URL}/${id}`);
  }

  /**
   * Actualizar un tipo de vehículo
   * PUT /api/tipos-vehiculos/{id}
   */
  async updateTipoVehiculo(id: number, data: UpdateTipoVehiculoRequest): Promise<ApiResponse<TipoVehiculo>> {
    return await apiService.put(`${this.BASE_URL}/${id}`, data);
  }

  /**
   * Alternar el estado activo de un tipo de vehículo
   * PATCH /api/tipos-vehiculos/{id}/toggle-activo
   */
  async toggleActivo(id: number): Promise<ApiResponse<TipoVehiculo>> {
    return await apiService.patch(`${this.BASE_URL}/${id}/toggle-activo`);
  }

  /**
   * Eliminar un tipo de vehículo (soft delete)
   * DELETE /api/tipos-vehiculos/{id}
   */
  async deleteTipoVehiculo(id: number): Promise<ApiResponse<void>> {
    return await apiService.delete(`${this.BASE_URL}/${id}`);
  }

  /**
   * Restaurar un tipo de vehículo eliminado
   * PUT /api/tipos-vehiculos/{id}/restore
   */
  async restoreTipoVehiculo(id: number): Promise<ApiResponse<TipoVehiculo>> {
    return await apiService.put(`${this.BASE_URL}/${id}/restore`);
  }

  /**
   * Obtener tipos de vehículos eliminados
   * GET /api/tipos-vehiculos/trashed/list
   */
  async getTrashedTiposVehiculos(): Promise<ApiResponse<TipoVehiculo[]>> {
    return await apiService.get(`${this.BASE_URL}/trashed/list`);
  }
}

export default new TipoVehiculoService();
