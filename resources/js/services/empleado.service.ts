import apiService from './api';
import type { Empleado, PaginatedResponse, CreateEmpleadoRequest, UpdateEmpleadoRequest } from '@/types';

export interface EmpleadoFilters {
  page?: number;
  per_page?: number;
  search?: string;
  tipo_salario?: string;
}

class EmpleadoService {
  async getEmpleados(filters: EmpleadoFilters = {}): Promise<PaginatedResponse<Empleado>> {
    const params = new URLSearchParams();
    
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.append(key, value.toString());
      }
    });

    const response = await apiService.get(`/api/empleados?${params.toString()}`);
    return response.data;
  }

  async getEmpleado(id: number): Promise<Empleado> {
    const response = await apiService.get(`/api/empleados/${id}`);
    return response.data.data;
  }

  async createEmpleado(data: CreateEmpleadoRequest): Promise<Empleado> {
    const response = await apiService.post('/api/empleados', data);
    return response.data.data;
  }

  async updateEmpleado(id: number, data: UpdateEmpleadoRequest): Promise<Empleado> {
    const response = await apiService.put(`/api/empleados/${id}`, data);
    return response.data.data;
  }

  async deleteEmpleado(id: number): Promise<void> {
    await apiService.delete(`/api/empleados/${id}`);
  }
}

export default new EmpleadoService();
