import apiService from './api';
import type { 
  Vehiculo, 
  CreateVehiculoRequest, 
  UpdateVehiculoRequest,
  ApiResponse, 
  PaginatedResponse,
  FilterOptions 
} from '@/types';

export interface VehiculoStats {
  total_vehiculos: number;
  vehiculos_activos: number;
  por_tipo: {
    moto: number;
    camioneta: number;
    auto_pequeno: number;
    auto_mediano: number;
  };
  con_lavados: number;
}

export interface VehiculoFilters extends FilterOptions {
  cliente_id?: number;
  tipo?: string;
}

class VehiculoService {
  private readonly baseUrl = '/vehiculos';

  /**
   * Obtener lista paginada de vehículos
   * GET /vehiculos
   */
  async getVehiculos(params?: VehiculoFilters): Promise<PaginatedResponse<Vehiculo>> {
    const response = await apiService.get<PaginatedResponse<Vehiculo>>(
      this.baseUrl,
      params
    );
    
    if (!response.success) {
      throw new Error(response.message);
    }
    
    return response.data!;
  }

  /**
   * Obtener todos los vehículos (para selects)
   * GET /vehiculos/all
   */
  async getAllVehiculos(): Promise<Vehiculo[]> {
    const response = await apiService.get<Vehiculo[]>(
      `${this.baseUrl}/all`
    );
    
    if (!response.success) {
      throw new Error(response.message);
    }
    
    return response.data!;
  }

  /**
   * Obtener estadísticas de vehículos
   * GET /vehiculos/stats
   */
  async getEstadisticas(): Promise<VehiculoStats> {
    const response = await apiService.get<VehiculoStats>(
      `${this.baseUrl}/stats`
    );
    
    if (!response.success) {
      throw new Error(response.message);
    }
    
    return response.data!;
  }

  /**
   * Obtener vehículos por cliente
   * GET /vehiculos/cliente/{clienteId}
   */
  async getVehiculosByCliente(clienteId: number): Promise<Vehiculo[]> {
    const response = await apiService.get<Vehiculo[]>(
      `${this.baseUrl}/cliente/${clienteId}`
    );
    
    if (!response.success) {
      throw new Error(response.message);
    }
    
    return response.data!;
  }

  /**
   * Crear nuevo vehículo
   * POST /vehiculos
   */
  async createVehiculo(vehiculo: CreateVehiculoRequest): Promise<Vehiculo> {
    const response = await apiService.post<Vehiculo>(
      this.baseUrl,
      vehiculo
    );
    
    if (!response.success) {
      throw new Error(response.message);
    }
    
    return response.data!;
  }

  /**
   * Obtener vehículo específico
   * GET /vehiculos/{id}
   */
  async getVehiculoById(id: number): Promise<Vehiculo> {
    const response = await apiService.get<Vehiculo>(
      `${this.baseUrl}/${id}`
    );
    
    if (!response.success) {
      throw new Error(response.message);
    }
    
    return response.data!;
  }

  /**
   * Actualizar vehículo
   * PUT /vehiculos/{id}
   */
  async updateVehiculo(id: number, vehiculo: UpdateVehiculoRequest): Promise<Vehiculo> {
    const response = await apiService.put<Vehiculo>(
      `${this.baseUrl}/${id}`,
      vehiculo
    );
    
    if (!response.success) {
      throw new Error(response.message);
    }
    
    return response.data!;
  }

  /**
   * Eliminar vehículo (soft delete)
   * DELETE /vehiculos/{id}
   */
  async deleteVehiculo(id: number): Promise<void> {
    const response = await apiService.delete<void>(
      `${this.baseUrl}/${id}`
    );
    
    if (!response.success) {
      throw new Error(response.message);
    }
  }

  /**
   * Restaurar vehículo eliminado
   * PUT /vehiculos/{id}/restore
   */
  async restoreVehiculo(id: number): Promise<void> {
    const response = await apiService.put<void>(
      `${this.baseUrl}/${id}/restore`
    );
    
    if (!response.success) {
      throw new Error(response.message);
    }
  }

  /**
   * Obtener vehículos eliminados
   * GET /vehiculos/trashed
   */
  async getTrashedVehiculos(): Promise<Vehiculo[]> {
    const response = await apiService.get<Vehiculo[]>(
      `${this.baseUrl}/trashed`
    );
    
    if (!response.success) {
      throw new Error(response.message);
    }
    
    return response.data!;
  }
}

export const vehiculoService = new VehiculoService();
export default vehiculoService;
