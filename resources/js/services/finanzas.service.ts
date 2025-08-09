import apiService from './api';
import type { 
  ApiResponse,
  Ingreso, 
  Egreso, 
  GastoGeneral,
  CreateIngresoRequest,
  UpdateIngresoRequest,
  CreateEgresoRequest,
  UpdateEgresoRequest,
  CreateGastoGeneralRequest,
  UpdateGastoGeneralRequest,
  PaginatedResponse
} from '@/types';

// Interfaces específicas para el servicio de finanzas
export interface FinanzasIngresoFilters {
  page?: number;
  per_page?: number;
  search?: string;
  tipo?: 'venta' | 'servicio';
  fecha_inicio?: string;
  fecha_fin?: string;
}

export interface FinanzasEgresoFilters {
  page?: number;
  per_page?: number;
  search?: string;
  tipo?: 'salario' | 'proveedor' | 'gasto_general';
  fecha_inicio?: string;
  fecha_fin?: string;
}

export interface FinanzasGastoGeneralFilters {
  page?: number;
  per_page?: number;
  search?: string;
  fecha_inicio?: string;
  fecha_fin?: string;
}

export interface BalanceFilters {
  fecha_inicio?: string;
  fecha_fin?: string;
  categoria?: string;
  trimestre?: number;
  anio?: number;
  mes?: number;
}

/**
 * Servicio para Finanzas - Gestión de ingresos, egresos y balance
 * Consume las rutas /api/ingresos, /api/egresos, /api/gastos-generales, /api/balance
 */
class FinanzasService {
  // === INGRESOS ===
  async getIngresos(filters?: FinanzasIngresoFilters): Promise<PaginatedResponse<Ingreso>> {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `/ingresos${params.toString() ? `?${params.toString()}` : ''}`;
    const response = await apiService.get<PaginatedResponse<Ingreso>>(url);
    return response.data || { data: [], current_page: 1, last_page: 1, per_page: 15, total: 0, from: 0, to: 0 };
  }

  async getIngreso(id: number): Promise<ApiResponse<Ingreso>> {
    return await apiService.get(`/ingresos/${id}`);
  }

  async createIngreso(data: CreateIngresoRequest): Promise<ApiResponse<Ingreso>> {
    return await apiService.post('/ingresos', data);
  }

  async updateIngreso(id: number, data: UpdateIngresoRequest): Promise<ApiResponse<Ingreso>> {
    return await apiService.put(`/ingresos/${id}`, data);
  }

  async deleteIngreso(id: number): Promise<ApiResponse<void>> {
    return await apiService.delete(`/ingresos/${id}`);
  }

  async restoreIngreso(id: number): Promise<ApiResponse<Ingreso>> {
    return await apiService.put(`/ingresos/${id}/restore`);
  }

  async getTrashedIngresos(): Promise<ApiResponse<Ingreso[]>> {
    return await apiService.get('/ingresos/trashed');
  }

  async getMetricasIngresos(): Promise<ApiResponse<any>> {
    return await apiService.get('/ingresos/metricas');
  }

  // === EGRESOS ===
  async getEgresos(filters?: FinanzasEgresoFilters): Promise<PaginatedResponse<Egreso>> {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `/egresos${params.toString() ? `?${params.toString()}` : ''}`;
    const response = await apiService.get<PaginatedResponse<Egreso>>(url);
    return response.data || { data: [], current_page: 1, last_page: 1, per_page: 15, total: 0, from: 0, to: 0 };
  }

  async getEgreso(id: number): Promise<ApiResponse<Egreso>> {
    return await apiService.get(`/egresos/${id}`);
  }

  async createEgreso(data: CreateEgresoRequest): Promise<ApiResponse<Egreso>> {
    return await apiService.post('/egresos', data);
  }

  async updateEgreso(id: number, data: UpdateEgresoRequest): Promise<ApiResponse<Egreso>> {
    return await apiService.put(`/egresos/${id}`, data);
  }

  async deleteEgreso(id: number): Promise<ApiResponse<void>> {
    return await apiService.delete(`/egresos/${id}`);
  }

  async restoreEgreso(id: number): Promise<ApiResponse<Egreso>> {
    return await apiService.put(`/egresos/${id}/restore`);
  }

  async getTrashedEgresos(): Promise<ApiResponse<Egreso[]>> {
    return await apiService.get('/egresos/trashed');
  }

  async getMetricasEgresos(): Promise<ApiResponse<any>> {
    return await apiService.get('/egresos/metricas');
  }

  // === GASTOS GENERALES ===
  async getGastosGenerales(filters?: FinanzasGastoGeneralFilters): Promise<PaginatedResponse<GastoGeneral>> {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `/gastos-generales${params.toString() ? `?${params.toString()}` : ''}`;
    const response = await apiService.get<PaginatedResponse<GastoGeneral>>(url);
    return response.data || { data: [], current_page: 1, last_page: 1, per_page: 15, total: 0, from: 0, to: 0 };
  }

  async getGastoGeneral(id: number): Promise<ApiResponse<GastoGeneral>> {
    return await apiService.get(`/gastos-generales/${id}`);
  }

  async createGastoGeneral(data: CreateGastoGeneralRequest): Promise<ApiResponse<GastoGeneral>> {
    return await apiService.post('/gastos-generales', data);
  }

  async updateGastoGeneral(id: number, data: UpdateGastoGeneralRequest): Promise<ApiResponse<GastoGeneral>> {
    return await apiService.put(`/gastos-generales/${id}`, data);
  }

  async deleteGastoGeneral(id: number): Promise<ApiResponse<void>> {
    return await apiService.delete(`/gastos-generales/${id}`);
  }

  async restoreGastoGeneral(id: number): Promise<ApiResponse<GastoGeneral>> {
    return await apiService.put(`/gastos-generales/${id}/restore`);
  }

  async getTrashedGastosGenerales(): Promise<ApiResponse<GastoGeneral[]>> {
    return await apiService.get('/gastos-generales/trashed');
  }

  async getMetricasGastosGenerales(): Promise<ApiResponse<any>> {
    return await apiService.get('/gastos-generales/metricas');
  }

  // === BALANCE Y ANÁLISIS FINANCIERO ===
  async getBalanceGeneral(filters?: BalanceFilters): Promise<ApiResponse<any>> {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `/balance/general${params.toString() ? `?${params.toString()}` : ''}`;
    return await apiService.get(url);
  }

  async getBalancePorCategoria(filters?: BalanceFilters): Promise<ApiResponse<any>> {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `/balance/categorias${params.toString() ? `?${params.toString()}` : ''}`;
    return await apiService.get(url);
  }

  async getBalanceMensual(filters?: BalanceFilters): Promise<ApiResponse<any>> {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `/balance/mensual${params.toString() ? `?${params.toString()}` : ''}`;
    return await apiService.get(url);
  }

  async getBalanceTrimestral(filters?: BalanceFilters): Promise<ApiResponse<any>> {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `/balance/trimestral${params.toString() ? `?${params.toString()}` : ''}`;
    return await apiService.get(url);
  }

  async getBalanceAnual(filters?: BalanceFilters): Promise<ApiResponse<any>> {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `/balance/anual${params.toString() ? `?${params.toString()}` : ''}`;
    return await apiService.get(url);
  }

  async getFlujoCaja(filters?: BalanceFilters): Promise<ApiResponse<any>> {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `/balance/flujo-caja${params.toString() ? `?${params.toString()}` : ''}`;
    return await apiService.get(url);
  }

  async getIndicadoresFinancieros(filters?: BalanceFilters): Promise<ApiResponse<any>> {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `/balance/indicadores${params.toString() ? `?${params.toString()}` : ''}`;
    return await apiService.get(url);
  }

  async getComparativoMensual(filters?: BalanceFilters): Promise<ApiResponse<any>> {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `/balance/comparativo${params.toString() ? `?${params.toString()}` : ''}`;
    return await apiService.get(url);
  }

  async getProyeccion(filters?: BalanceFilters): Promise<ApiResponse<any>> {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `/balance/proyeccion${params.toString() ? `?${params.toString()}` : ''}`;
    return await apiService.get(url);
  }

  async getResumenCompleto(filters?: BalanceFilters): Promise<ApiResponse<any>> {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `/balance/resumen${params.toString() ? `?${params.toString()}` : ''}`;
    return await apiService.get(url);
  }
}

export default new FinanzasService();
