import api from '@/services/api';
import type { 
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

// Interfaces para filtros
export interface IngresoFilters {
  page?: number;
  per_page?: number;
  search?: string;
  tipo?: 'lavado' | 'producto_automotriz' | 'producto_despensa';
  fecha_inicio?: string;
  fecha_fin?: string;
}

export interface EgresoFilters {
  page?: number;
  per_page?: number;
  search?: string;
  tipo?: 'salario' | 'proveedor' | 'gasto_general';
  fecha_inicio?: string;
  fecha_fin?: string;
}

export interface GastoGeneralFilters {
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

class FinanzasService {
  // === INGRESOS ===
  async getIngresos(filters?: IngresoFilters) {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `/ingresos${params.toString() ? `?${params.toString()}` : ''}`;
    return api.get<PaginatedResponse<Ingreso>>(url);
  }

  async getIngreso(id: number) {
    return api.get<Ingreso>(`/ingresos/${id}`);
  }

  async createIngreso(data: CreateIngresoRequest) {
    return api.post<Ingreso>('/ingresos', data);
  }

  async updateIngreso(id: number, data: UpdateIngresoRequest) {
    return api.put<Ingreso>(`/ingresos/${id}`, data);
  }

  async deleteIngreso(id: number) {
    return api.delete(`/ingresos/${id}`);
  }

  async getMetricasIngresos() {
    return api.get<any>('/ingresos/metricas');
  }

  // === EGRESOS ===
  async getEgresos(filters?: EgresoFilters) {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `/egresos${params.toString() ? `?${params.toString()}` : ''}`;
    return api.get<PaginatedResponse<Egreso>>(url);
  }

  async getEgreso(id: number) {
    return api.get<Egreso>(`/egresos/${id}`);
  }

  async createEgreso(data: CreateEgresoRequest) {
    return api.post<Egreso>('/egresos', data);
  }

  async updateEgreso(id: number, data: UpdateEgresoRequest) {
    return api.put<Egreso>(`/egresos/${id}`, data);
  }

  async deleteEgreso(id: number) {
    return api.delete(`/egresos/${id}`);
  }

  async getMetricasEgresos() {
    return api.get<any>('/egresos/metricas');
  }

  // === GASTOS GENERALES ===
  async getGastosGenerales(filters?: GastoGeneralFilters) {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `/gastos-generales${params.toString() ? `?${params.toString()}` : ''}`;
    return api.get<PaginatedResponse<GastoGeneral>>(url);
  }

  async getGastoGeneral(id: number) {
    return api.get<GastoGeneral>(`/gastos-generales/${id}`);
  }

  async createGastoGeneral(data: CreateGastoGeneralRequest) {
    return api.post<GastoGeneral>('/gastos-generales', data);
  }

  async updateGastoGeneral(id: number, data: UpdateGastoGeneralRequest) {
    return api.put<GastoGeneral>(`/gastos-generales/${id}`, data);
  }

  async deleteGastoGeneral(id: number) {
    return api.delete(`/gastos-generales/${id}`);
  }

  async getMetricasGastosGenerales() {
    return api.get<any>('/gastos-generales/metricas');
  }

  // === BALANCE ===
  async getBalanceGeneral(filters?: BalanceFilters) {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `/balance/general${params.toString() ? `?${params.toString()}` : ''}`;
    return api.get<any>(url);
  }

  async getBalancePorCategoria(filters?: BalanceFilters) {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `/balance/categorias${params.toString() ? `?${params.toString()}` : ''}`;
    return api.get<any>(url);
  }

  async getBalanceMensual(filters?: BalanceFilters) {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `/balance/mensual${params.toString() ? `?${params.toString()}` : ''}`;
    return api.get<any>(url);
  }

  async getFlujoCaja(filters?: BalanceFilters) {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `/balance/flujo-caja${params.toString() ? `?${params.toString()}` : ''}`;
    return api.get<any>(url);
  }

  async getIndicadoresFinancieros(filters?: BalanceFilters) {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `/balance/indicadores${params.toString() ? `?${params.toString()}` : ''}`;
    return api.get<any>(url);
  }

  async getResumenCompleto(filters?: BalanceFilters) {
    const params = new URLSearchParams();
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `/balance/resumen${params.toString() ? `?${params.toString()}` : ''}`;
    return api.get<any>(url);
  }
}

export default new FinanzasService();
