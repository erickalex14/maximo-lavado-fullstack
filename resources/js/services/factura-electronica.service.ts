import apiService from './api';
import type { 
  ApiResponse, 
  PaginatedResponse,
  FacturaElectronica,
  CreateFacturaElectronicaRequest,
  UpdateFacturaElectronicaRequest,
  FacturaElectronicaFilters
} from '@/types';

/**
 * Servicio para Facturas Electrónicas - Sistema SRI Ecuador
 * Consume las rutas /api/facturas-electronicas
 */
class FacturaElectronicaService {
  private readonly BASE_URL = '/facturas-electronicas';

  /**
   * Obtener todas las facturas electrónicas con paginación y filtros
   * GET /api/facturas-electronicas
   */
  async getFacturasElectronicas(filters?: FacturaElectronicaFilters): Promise<PaginatedResponse<any>> {
    const params = new URLSearchParams();
    
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `${this.BASE_URL}${params.toString() ? `?${params.toString()}` : ''}`;
    console.debug('[FacturaElectronicaService] GET', url, 'filters=', filters);
    try {
      // Usar instancia directa para ver respuesta cruda
      const axiosResp = await apiService.instance.get(url);
      const payload = axiosResp.data || {};
      let lista:any[] = [];
      if (Array.isArray(payload.facturas)) {
        lista = payload.facturas;
      } else if (Array.isArray(payload.data)) {
        lista = payload.data;
      } else if (payload.data && Array.isArray(payload.data.data)) {
        lista = payload.data.data;
      } else {
        console.warn('[FacturaElectronicaService] Estructura inesperada (sin array)', payload);
      }
      return {
        data: lista,
        current_page: 1,
        last_page: 1,
        per_page: lista.length,
        total: lista.length,
        from: lista.length ? 1 : 0,
        to: lista.length
      };
    } catch (err:any) {
      console.error('[FacturaElectronicaService] Error request', err?.response?.status, err?.response?.data);
      throw err;
    }
  }

  /**
   * Crear una nueva factura electrónica
   * POST /api/facturas-electronicas
   */
  async createFacturaElectronica(data: CreateFacturaElectronicaRequest): Promise<ApiResponse<FacturaElectronica>> {
    const resp = await apiService.post(this.BASE_URL, data);
    const factura = (resp as any).factura ?? (resp as any).data ?? null;
    return { success: true, message: resp.message, data: factura };
  }

  /**
   * Obtener una factura electrónica específica
   * GET /api/facturas-electronicas/{id}
   */
  async getFacturaElectronicaById(id: number): Promise<ApiResponse<FacturaElectronica>> {
    const resp = await apiService.get(`${this.BASE_URL}/${id}`);
    const factura = (resp as any).factura ?? (resp as any).data ?? null;
    return { success: true, message: resp.message, data: factura };
  }

  /**
   * Actualizar una factura electrónica
   * PUT /api/facturas-electronicas/{id}
   */
  async updateFacturaElectronica(id: number, data: UpdateFacturaElectronicaRequest): Promise<ApiResponse<FacturaElectronica>> {
    const resp = await apiService.put(`${this.BASE_URL}/${id}`, data);
    const factura = (resp as any).factura ?? (resp as any).data ?? null;
    return { success: true, message: resp.message, data: factura };
  }

  // === PROCESAMIENTO SRI ===

  /**
   * Procesar factura con el SRI
   * POST /api/facturas-electronicas/{id}/procesar-sri
   */
  async procesarConSRI(id: number): Promise<ApiResponse<FacturaElectronica>> {
    const resp = await apiService.post(`${this.BASE_URL}/${id}/procesar-sri`);
    const resultado = (resp as any).resultado ?? (resp as any).factura ?? null;
    return { success: true, message: resp.message, data: resultado };
  }

  /**
   * Reenviar factura al SRI
   * POST /api/facturas-electronicas/{id}/reenviar
   */
  async reenviarAlSRI(id: number): Promise<ApiResponse<FacturaElectronica>> {
    const resp = await apiService.post(`${this.BASE_URL}/${id}/reenviar`);
    const resultado = (resp as any).resultado ?? null;
    return { success: true, message: resp.message, data: resultado };
  }

  /**
   * Anular una factura electrónica
   * POST /api/facturas-electronicas/{id}/anular
   */
  async anular(id: number): Promise<ApiResponse<FacturaElectronica>> {
    const resp = await apiService.post(`${this.BASE_URL}/${id}/anular`);
    const factura = (resp as any).factura ?? null;
    return { success: true, message: resp.message, data: factura };
  }

  // === DOCUMENTOS ===

  /**
   * Obtener XML de la factura
   * GET /api/facturas-electronicas/{id}/xml
   */
  async getXML(id: number): Promise<ApiResponse<string>> {
    const resp = await apiService.get(`${this.BASE_URL}/${id}/xml`);
    const xml = (resp as any).xml ?? (resp as any).data ?? '';
    return { success: true, message: resp.message, data: xml };
  }

  /**
   * Descargar PDF de la factura
   * GET /api/facturas-electronicas/{id}/pdf
   */
  async downloadPDF(id: number, filename?: string): Promise<void> {
    return await apiService.download(`${this.BASE_URL}/${id}/pdf`, filename || `factura-${id}.pdf`);
  }

  // === CONSULTAS ESPECÍFICAS ===

  /**
   * Obtener factura por venta
   * GET /api/facturas-electronicas/venta/{ventaId}
   */
  async getByVenta(ventaId: number): Promise<ApiResponse<FacturaElectronica>> {
    const resp = await apiService.get(`${this.BASE_URL}/venta/${ventaId}`);
    const factura = (resp as any).factura ?? null;
    return { success: true, message: resp.message, data: factura };
  }

  /**
   * Obtener facturas pendientes de procesar con el SRI
   * GET /api/facturas-electronicas/pendientes-sri
   */
  async getPendientesSRI(): Promise<ApiResponse<FacturaElectronica[]>> {
    const resp = await apiService.get(`${this.BASE_URL}/pendientes-sri`);
    const facturas = (resp as any).facturas ?? [];
    return { success: true, message: resp.message, data: facturas };
  }

  /**
   * Obtener estadísticas de facturas electrónicas
   * GET /api/facturas-electronicas/estadisticas
   */
  async getEstadisticas(): Promise<ApiResponse<any>> {
    const resp = await apiService.get(`${this.BASE_URL}/estadisticas`);
    const estadisticas = (resp as any).estadisticas ?? (resp as any).data ?? null;
    return { success: true, message: resp.message, data: estadisticas };
  }

  // === OPERACIONES EN LOTE ===

  /**
   * Procesar múltiples facturas en lote
   * POST /api/facturas-electronicas/procesar-lote
   */
  async procesarLote(facturaIds: number[]): Promise<ApiResponse<any>> {
    const resp = await apiService.post(`${this.BASE_URL}/procesar-lote`, { factura_ids: facturaIds });
    const resultado = (resp as any).resultado ?? null;
    return { success: true, message: resp.message, data: resultado };
  }

  /**
   * Validar conexión con el SRI
   * GET /api/facturas-electronicas/validar-sri
   */
  async validarConexionSRI(): Promise<ApiResponse<any>> {
    const resp = await apiService.get(`${this.BASE_URL}/validar-sri`);
    const conexion = (resp as any).conexion ?? null;
    return { success: true, message: resp.message, data: conexion };
  }
}

export default new FacturaElectronicaService();
