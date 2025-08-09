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
  async getFacturasElectronicas(filters?: FacturaElectronicaFilters): Promise<PaginatedResponse<FacturaElectronica>> {
    const params = new URLSearchParams();
    
    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }
    
    const url = `${this.BASE_URL}${params.toString() ? `?${params.toString()}` : ''}`;
    const response = await apiService.get<PaginatedResponse<FacturaElectronica>>(url);
    return response.data || { data: [], current_page: 1, last_page: 1, per_page: 15, total: 0, from: 0, to: 0 };
  }

  /**
   * Crear una nueva factura electrónica
   * POST /api/facturas-electronicas
   */
  async createFacturaElectronica(data: CreateFacturaElectronicaRequest): Promise<ApiResponse<FacturaElectronica>> {
    return await apiService.post(this.BASE_URL, data);
  }

  /**
   * Obtener una factura electrónica específica
   * GET /api/facturas-electronicas/{id}
   */
  async getFacturaElectronicaById(id: number): Promise<ApiResponse<FacturaElectronica>> {
    return await apiService.get(`${this.BASE_URL}/${id}`);
  }

  /**
   * Actualizar una factura electrónica
   * PUT /api/facturas-electronicas/{id}
   */
  async updateFacturaElectronica(id: number, data: UpdateFacturaElectronicaRequest): Promise<ApiResponse<FacturaElectronica>> {
    return await apiService.put(`${this.BASE_URL}/${id}`, data);
  }

  // === PROCESAMIENTO SRI ===

  /**
   * Procesar factura con el SRI
   * POST /api/facturas-electronicas/{id}/procesar-sri
   */
  async procesarConSRI(id: number): Promise<ApiResponse<FacturaElectronica>> {
    return await apiService.post(`${this.BASE_URL}/${id}/procesar-sri`);
  }

  /**
   * Reenviar factura al SRI
   * POST /api/facturas-electronicas/{id}/reenviar
   */
  async reenviarAlSRI(id: number): Promise<ApiResponse<FacturaElectronica>> {
    return await apiService.post(`${this.BASE_URL}/${id}/reenviar`);
  }

  /**
   * Anular una factura electrónica
   * POST /api/facturas-electronicas/{id}/anular
   */
  async anular(id: number): Promise<ApiResponse<FacturaElectronica>> {
    return await apiService.post(`${this.BASE_URL}/${id}/anular`);
  }

  // === DOCUMENTOS ===

  /**
   * Obtener XML de la factura
   * GET /api/facturas-electronicas/{id}/xml
   */
  async getXML(id: number): Promise<ApiResponse<string>> {
    return await apiService.get(`${this.BASE_URL}/${id}/xml`);
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
    return await apiService.get(`${this.BASE_URL}/venta/${ventaId}`);
  }

  /**
   * Obtener facturas pendientes de procesar con el SRI
   * GET /api/facturas-electronicas/pendientes-sri
   */
  async getPendientesSRI(): Promise<ApiResponse<FacturaElectronica[]>> {
    return await apiService.get(`${this.BASE_URL}/pendientes-sri`);
  }

  /**
   * Obtener estadísticas de facturas electrónicas
   * GET /api/facturas-electronicas/estadisticas
   */
  async getEstadisticas(): Promise<ApiResponse<any>> {
    return await apiService.get(`${this.BASE_URL}/estadisticas`);
  }

  // === OPERACIONES EN LOTE ===

  /**
   * Procesar múltiples facturas en lote
   * POST /api/facturas-electronicas/procesar-lote
   */
  async procesarLote(facturaIds: number[]): Promise<ApiResponse<any>> {
    return await apiService.post(`${this.BASE_URL}/procesar-lote`, { factura_ids: facturaIds });
  }

  /**
   * Validar conexión con el SRI
   * GET /api/facturas-electronicas/validar-sri
   */
  async validarConexionSRI(): Promise<ApiResponse<any>> {
    return await apiService.get(`${this.BASE_URL}/validar-sri`);
  }
}

export default new FacturaElectronicaService();
