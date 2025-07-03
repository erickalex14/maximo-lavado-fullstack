import apiService from './api';
import type { Lavado, PaginatedResponse, CreateLavadoRequest, UpdateLavadoRequest } from '@/types';

export interface LavadoFilters {
  page?: number;
  per_page?: number;
  search?: string;
  empleado_id?: number;
  vehiculo_id?: number;
  fecha_inicio?: string;
  fecha_fin?: string;
  tipo_vehiculo?: string;
  estado?: string;
}

export interface LavadoDateFilters {
  fecha?: string;
  anio?: number;
  mes?: number;
}

class LavadoService {
  async getLavados(filters: LavadoFilters = {}): Promise<PaginatedResponse<Lavado>> {
    const params = new URLSearchParams();
    
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.append(key, value.toString());
      }
    });

    const response = await apiService.get(`/api/lavados?${params.toString()}`);
    return response.data;
  }

  async getLavado(id: number): Promise<Lavado> {
    const response = await apiService.get(`/api/lavados/${id}`);
    return response.data.data;
  }

  async createLavado(data: CreateLavadoRequest): Promise<Lavado> {
    const response = await apiService.post('/api/lavados', data);
    return response.data.data;
  }

  async updateLavado(id: number, data: UpdateLavadoRequest): Promise<Lavado> {
    const response = await apiService.put(`/api/lavados/${id}`, data);
    return response.data.data;
  }

  async deleteLavado(id: number): Promise<void> {
    await apiService.delete(`/api/lavados/${id}`);
  }

  async restoreLavado(id: number): Promise<Lavado> {
    const response = await apiService.put(`/api/lavados/${id}/restore`);
    return response.data.data;
  }

  async getTrashedLavados(): Promise<Lavado[]> {
    const response = await apiService.get('/api/lavados/trashed');
    return response.data.data;
  }

  async getStats(): Promise<any> {
    const response = await apiService.get('/api/lavados/stats');
    return response.data.data;
  }

  // Filtros por relaciones
  async getByEmpleado(empleadoId: number, filters: LavadoFilters = {}): Promise<PaginatedResponse<Lavado>> {
    const params = new URLSearchParams();
    
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.append(key, value.toString());
      }
    });

    const response = await apiService.get(`/api/lavados/empleado/${empleadoId}?${params.toString()}`);
    return response.data;
  }

  async getByVehiculo(vehiculoId: number, filters: LavadoFilters = {}): Promise<PaginatedResponse<Lavado>> {
    const params = new URLSearchParams();
    
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.append(key, value.toString());
      }
    });

    const response = await apiService.get(`/api/lavados/vehiculo/${vehiculoId}?${params.toString()}`);
    return response.data;
  }

  // Filtros por fecha
  async getByDay(filters: LavadoDateFilters = {}): Promise<Lavado[]> {
    const params = new URLSearchParams();
    
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.append(key, value.toString());
      }
    });

    const response = await apiService.get(`/api/lavados/dia?${params.toString()}`);
    return response.data.data;
  }

  async getByWeek(filters: LavadoDateFilters = {}): Promise<Lavado[]> {
    const params = new URLSearchParams();
    
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.append(key, value.toString());
      }
    });

    const response = await apiService.get(`/api/lavados/semana?${params.toString()}`);
    return response.data.data;
  }

  async getByMonth(filters: LavadoDateFilters = {}): Promise<Lavado[]> {
    const params = new URLSearchParams();
    
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.append(key, value.toString());
      }
    });

    const response = await apiService.get(`/api/lavados/mes?${params.toString()}`);
    return response.data.data;
  }

  async getByYear(filters: LavadoDateFilters = {}): Promise<Lavado[]> {
    const params = new URLSearchParams();
    
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.append(key, value.toString());
      }
    });

    const response = await apiService.get(`/api/lavados/anio?${params.toString()}`);
    return response.data.data;
  }
}

export default new LavadoService();
