import apiService from './api';
import type { 
  Cliente, 
  CreateClienteRequest, 
  UpdateClienteRequest,
  ApiResponse, 
  PaginatedResponse, 
  FilterOptions 
} from '@/types';

class ClienteService {
  /**
   * Obtener todos los clientes con paginación
   * GET /api/clientes
   */
  async getAll(filters?: FilterOptions): Promise<ApiResponse<PaginatedResponse<Cliente>>> {
    return await apiService.get('/clientes', filters);
  }

  /**
   * Obtener todos los clientes sin paginación (para selects)
   * GET /api/clientes/all
   */
  async getAllForSelect(): Promise<ApiResponse<Cliente[]>> {
    return await apiService.get('/clientes/all');
  }

  /**
   * Buscar clientes
   * GET /api/clientes/search
   */
  async search(query: string): Promise<ApiResponse<Cliente[]>> {
    return await apiService.get('/clientes/search', { query });
  }

  /**
   * Obtener estadísticas de clientes
   * GET /api/clientes/stats
   */
  async getStats(): Promise<ApiResponse<any>> {
    return await apiService.get('/clientes/stats');
  }

  /**
   * Crear nuevo cliente
   * POST /api/clientes
   */
  async create(data: CreateClienteRequest): Promise<ApiResponse<Cliente>> {
    return await apiService.post('/clientes', data);
  }

  /**
   * Obtener cliente por ID
   * GET /api/clientes/{id}
   */
  async getById(id: number): Promise<ApiResponse<Cliente>> {
    return await apiService.get(`/clientes/${id}`);
  }

  /**
   * Actualizar cliente
   * PUT /api/clientes/{id}
   */
  async update(id: number, data: UpdateClienteRequest): Promise<ApiResponse<Cliente>> {
    return await apiService.put(`/clientes/${id}`, data);
  }

  /**
   * Alternar estado activo del cliente
   * PATCH /api/clientes/{id}/toggle-activo
   */
  async toggleActivo(id: number): Promise<ApiResponse<Cliente>> {
    return await apiService.patch(`/clientes/${id}/toggle-activo`);
  }

  /**
   * Eliminar cliente
   * DELETE /api/clientes/{id}
   */
  async delete(id: number): Promise<ApiResponse> {
    return await apiService.delete(`/clientes/${id}`);
  }

  /**
   * Restaurar cliente eliminado
   * PUT /api/clientes/{id}/restore
   */
  async restore(id: number): Promise<ApiResponse<Cliente>> {
    return await apiService.put(`/clientes/${id}/restore`);
  }

  /**
   * Obtener clientes eliminados
   * GET /api/clientes/trashed
   */
  async getTrashed(): Promise<ApiResponse<Cliente[]>> {
    return await apiService.get('/clientes/trashed');
  }
}

export default new ClienteService();
