import apiService from './api';
import type { 
  Vehiculo, 
  CreateVehiculoRequest, 
  UpdateVehiculoRequest,
  ApiResponse, 
  PaginatedResponse,
  VehiculoFilters
} from '@/types';

/**
 * Servicio para Vehículos - Sistema Legacy
 * Consume las rutas /api/vehiculos
 */
class VehiculoService {
  private readonly BASE_URL = '/vehiculos';

  /**
   * Obtener todos los vehículos con paginación y filtros
   * GET /api/vehiculos
   */
  async getVehiculos(filters?: VehiculoFilters): Promise<PaginatedResponse<Vehiculo>> {
    const params = new URLSearchParams();
    
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }

    const url = `${this.BASE_URL}${params.toString() ? `?${params.toString()}` : ''}`;
    const response = await apiService.get<PaginatedResponse<Vehiculo>>(url);
    const base = response.data || { data: [], current_page: 1, last_page: 1, per_page: 15, total: 0, from: 0, to: 0 };
    try {
      // Intentar enriquecer con tipos si no vienen
      const { useTipoVehiculoStore } = await import('@/stores/tipoVehiculo');
      const tipoStore = useTipoVehiculoStore();
  if (tipoStore.tipos.length) {
        base.data = base.data.map(v => {
          if (v && !v.tipo_vehiculo && v.tipo_vehiculo_id) {
    const t = tipoStore.tipos.find(x => x.tipo_vehiculo_id === v.tipo_vehiculo_id);
            if (t) (v as any).tipo_vehiculo = t;
          }
          return v;
        });
      }
    } catch (e) {
      // ignore if store not ready
    }
    return base;
  }

  /**
   * Obtener todos los vehículos sin paginación
   * GET /api/vehiculos/all
   */
  async getAllVehiculos(): Promise<ApiResponse<Vehiculo[]>> {
    return await apiService.get(`${this.BASE_URL}/all`);
  }

  /**
   * Obtener estadísticas de vehículos
   * GET /api/vehiculos/stats
   */
  async getStats(): Promise<ApiResponse<any>> {
    return await apiService.get(`${this.BASE_URL}/stats`);
  }

  /**
   * Obtener vehículos eliminados
   * GET /api/vehiculos/trashed
   */
  async getTrashedVehiculos(): Promise<ApiResponse<Vehiculo[]>> {
    return await apiService.get(`${this.BASE_URL}/trashed`);
  }

  /**
   * Obtener vehículos por cliente
   * GET /api/vehiculos/cliente/{clienteId}
   */
  async getVehiculosByCliente(clienteId: number): Promise<ApiResponse<Vehiculo[]>> {
    return await apiService.get(`${this.BASE_URL}/cliente/${clienteId}`);
  }

  /**
   * Crear un nuevo vehículo
   * POST /api/vehiculos
   */
  async createVehiculo(data: CreateVehiculoRequest): Promise<ApiResponse<Vehiculo>> {
    return await apiService.post(this.BASE_URL, data);
  }

  /**
   * Obtener un vehículo específico
   * GET /api/vehiculos/{id}
   */
  async getVehiculoById(id: number): Promise<ApiResponse<Vehiculo>> {
    const resp = await apiService.get< ApiResponse<Vehiculo> >(`${this.BASE_URL}/${id}`);
    const vehiculo: any = (resp as any).data; // ApiResponse payload
    try {
      if (vehiculo && !vehiculo.tipo_vehiculo && vehiculo.tipo_vehiculo_id) {
        const { useTipoVehiculoStore } = await import('@/stores/tipoVehiculo');
        const tipoStore = useTipoVehiculoStore();
  const t = tipoStore.tipos.find(x => x.tipo_vehiculo_id === vehiculo.tipo_vehiculo_id);
        if (t) vehiculo.tipo_vehiculo = t;
      }
    } catch {}
    return resp as any;
  }

  /**
   * Actualizar un vehículo
   * PUT /api/vehiculos/{id}
   */
  async updateVehiculo(id: number, data: UpdateVehiculoRequest): Promise<ApiResponse<Vehiculo>> {
    return await apiService.put(`${this.BASE_URL}/${id}`, data);
  }

  /**
   * Eliminar un vehículo (soft delete)
   * DELETE /api/vehiculos/{id}
   */
  async deleteVehiculo(id: number): Promise<ApiResponse<void>> {
    return await apiService.delete(`${this.BASE_URL}/${id}`);
  }

  /**
   * Restaurar un vehículo eliminado
   * PUT /api/vehiculos/{id}/restore
   */
  async restoreVehiculo(id: number): Promise<ApiResponse<Vehiculo>> {
    return await apiService.put(`${this.BASE_URL}/${id}/restore`);
  }
}

export default new VehiculoService();
