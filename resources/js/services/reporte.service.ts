import apiService from './api';
import type { 
  ReporteDisponible,
  ReporteRequest,
  ReporteVentas,
  ReporteLavados,
  ReporteIngresos,
  ReporteEgresos,
  ReporteFacturas,
  ReporteEmpleados,
  ReporteProductos,
  ReporteClientes,
  ReporteFinanciero,
  ReporteBalance,
  ReporteCompleto
} from '@/types';

export interface ApiResponse<T> {
  status: string;
  data: T;
  message?: string;
}

export class ReporteService {
  private baseUrl = '/api/reportes';

  // Obtener reportes disponibles
  async getReportesDisponibles(): Promise<ReporteDisponible[]> {
    const response = await apiService.get<ApiResponse<ReporteDisponible[]>>(this.baseUrl);
    return response.data?.data || [];
  }

  // Reporte de Ventas
  async getReporteVentas(params: ReporteRequest): Promise<ReporteVentas> {
    const response = await apiService.get<ApiResponse<ReporteVentas>>(`${this.baseUrl}/ventas`, { params });
    return response.data?.data!;
  }

  // Reporte de Lavados
  async getReporteLavados(params: ReporteRequest): Promise<ReporteLavados> {
    const response = await apiService.get<ApiResponse<ReporteLavados>>(`${this.baseUrl}/lavados`, { params });
    return response.data?.data!;
  }

  // Reporte de Ingresos
  async getReporteIngresos(params: ReporteRequest): Promise<ReporteIngresos> {
    const response = await apiService.get<ApiResponse<ReporteIngresos>>(`${this.baseUrl}/ingresos`, { params });
    return response.data?.data!;
  }

  // Reporte de Egresos
  async getReporteEgresos(params: ReporteRequest): Promise<ReporteEgresos> {
    const response = await apiService.get<ApiResponse<ReporteEgresos>>(`${this.baseUrl}/egresos`, { params });
    return response.data?.data!;
  }

  // Reporte de Facturas
  async getReporteFacturas(params: ReporteRequest): Promise<ReporteFacturas> {
    const response = await apiService.get<ApiResponse<ReporteFacturas>>(`${this.baseUrl}/facturas`, { params });
    return response.data?.data!;
  }

  // Reporte de Empleados
  async getReporteEmpleados(params: ReporteRequest): Promise<ReporteEmpleados> {
    const response = await apiService.get<ApiResponse<ReporteEmpleados>>(`${this.baseUrl}/empleados`, { params });
    return response.data?.data!;
  }

  // Reporte de Productos
  async getReporteProductos(params?: Partial<ReporteRequest>): Promise<ReporteProductos> {
    const response = await apiService.get<ApiResponse<ReporteProductos>>(`${this.baseUrl}/productos`, { params });
    return response.data?.data!;
  }

  // Reporte de Clientes
  async getReporteClientes(params?: Partial<ReporteRequest>): Promise<ReporteClientes> {
    const response = await apiService.get<ApiResponse<ReporteClientes>>(`${this.baseUrl}/clientes`, { params });
    return response.data?.data!;
  }

  // Reporte Financiero
  async getReporteFinanciero(params: ReporteRequest): Promise<ReporteFinanciero> {
    const response = await apiService.get<ApiResponse<ReporteFinanciero>>(`${this.baseUrl}/financiero`, { params });
    return response.data?.data!;
  }

  // Reporte de Balance
  async getReporteBalance(params: ReporteRequest): Promise<ReporteBalance> {
    const response = await apiService.get<ApiResponse<ReporteBalance>>(`${this.baseUrl}/balance`, { params });
    return response.data?.data!;
  }

  // Reporte Completo
  async getReporteCompleto(params: ReporteRequest): Promise<ReporteCompleto> {
    const response = await apiService.get<ApiResponse<ReporteCompleto>>(`${this.baseUrl}/completo`, { params });
    return response.data?.data!;
  }

  // Descargar reporte en formato específico
  async descargarReporte(tipo: string, params: ReporteRequest & { formato: 'pdf' | 'excel' }): Promise<Blob> {
    const response = await apiService.get(`${this.baseUrl}/${tipo}`, {
      params,
      responseType: 'blob'
    });
    return response.data;
  }

  // Generar parámetros con fechas por defecto
  getParametrosDefecto(): ReporteRequest {
    const fechaFin = new Date();
    const fechaInicio = new Date();
    fechaInicio.setMonth(fechaFin.getMonth() - 1);

    return {
      fecha_inicio: fechaInicio.toISOString().split('T')[0],
      fecha_fin: fechaFin.toISOString().split('T')[0],
      formato: 'json'
    };
  }

  // Validar parámetros de fecha
  validarParametros(params: ReporteRequest): { valid: boolean; error?: string } {
    if (!params.fecha_inicio || !params.fecha_fin) {
      return { valid: false, error: 'Las fechas de inicio y fin son requeridas' };
    }

    const fechaInicio = new Date(params.fecha_inicio);
    const fechaFin = new Date(params.fecha_fin);

    if (fechaInicio > fechaFin) {
      return { valid: false, error: 'La fecha de inicio no puede ser mayor a la fecha de fin' };
    }

    const diffTime = Math.abs(fechaFin.getTime() - fechaInicio.getTime());
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays > 365) {
      return { valid: false, error: 'El rango de fechas no puede ser mayor a 365 días' };
    }

    return { valid: true };
  }
}

export const reporteService = new ReporteService();
export default reporteService;
