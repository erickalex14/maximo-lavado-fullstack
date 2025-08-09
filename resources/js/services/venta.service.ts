import apiService from './api';
import type { 
  ApiResponse, 
  PaginatedResponse,
  Venta,
  CreateVentaRequest,
  UpdateVentaRequest,
  VentaFilters
} from '@/types';

/**
 * Servicio para el Sistema Unificado V2 de Ventas
 * Consume las rutas /api/ventas del ControladorVenta unificado
 */
class VentaService {
  private readonly BASE_URL = '/ventas';

  /**
   * Obtener todas las ventas con paginación y filtros
   * GET /api/ventas
   */
  async getVentas(filters?: VentaFilters): Promise<PaginatedResponse<Venta>> {
    const params = new URLSearchParams();

    if (filters) {
      Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
          params.append(key, value.toString());
        }
      });
    }

    const url = `${this.BASE_URL}${params.toString() ? `?${params.toString()}` : ''}`;
    const raw: any = await apiService.get<any>(url);
    // El apiService ya retorna response.data. Dependiendo del backend puede ser:
    // 1) Paginación estándar Laravel (current_page, data, last_page, etc.) directamente.
    // 2) Wrapper { success, message, data: { current_page, data: [], ... } }
    // 3) Wrapper { success, data: [] } sin paginación (fallback)
    const candidate =
      (raw && 'current_page' in raw && Array.isArray(raw.data)) ? raw :
      (raw && raw.data && 'current_page' in raw.data && Array.isArray(raw.data.data)) ? raw.data :
      // Caso: backend retorna { success:true, ventas:[...] }
      (raw && Array.isArray(raw.ventas)) ? {
        data: raw.ventas,
        current_page: 1,
        last_page: 1,
        per_page: raw.ventas.length || 15,
        total: raw.ventas.length,
        from: raw.ventas.length ? 1 : 0,
        to: raw.ventas.length,
      } :
      null;

    if (candidate) {
      // Detectar estructura Venta con detalles y aplanar a items estilo frontend
      const first = (candidate as any).data?.[0];
      if (first && first.detalles && Array.isArray(first.detalles)) {
        // En lugar de aplanar cada detalle como venta separada, representamos una sola venta agregada.
        const agregadas: Venta[] = (candidate as any).data.map((ventaRaw: any) => {
          const fechaStr = typeof ventaRaw.fecha === 'string' ? ventaRaw.fecha : (ventaRaw.fecha?.date || new Date().toISOString().slice(0,10));
          const detalles: any[] = ventaRaw.detalles || [];
          let tiposSet = new Set<string>();
          let total = 0;
          let referenciaCompuestaPartes: string[] = [];
          detalles.forEach(det => {
            const t = (det.tipo_item || '').toString();
            let tipoItem: string;
            if (t === 'servicio') tipoItem = 'servicio';
            else if (t.includes('despensa')) tipoItem = 'producto_despensa';
            else if (t.includes('automotriz')) tipoItem = 'producto_automotriz';
            else if (t === 'producto_despensa') tipoItem = 'producto_despensa';
            else if (t === 'producto_automotriz') tipoItem = 'producto_automotriz';
            else tipoItem = 'producto_automotriz';
            tiposSet.add(tipoItem);
            const nombre = det.item_nombre || det.nombre || det.servicio?.nombre || det.producto_automotriz?.nombre || det.productoAutomotriz?.nombre || det.producto_despensa?.nombre || `#${det.item_id}`;
            referenciaCompuestaPartes.push(nombre);
            const subtotal = Number(det.total ?? (det.cantidad * det.precio_unitario)) || 0;
            total += subtotal;
          });
          const tipos = Array.from(tiposSet) as any[];
          const tipoVenta: any = tipos.length > 1 ? 'mixta' : (tipos[0] || 'producto_automotriz');
          // Si solo hay un detalle usar su nombre directo como referencia_compuesta limpia
          let referenciaCompuesta = referenciaCompuestaPartes.slice(0,3).join(', ') + (referenciaCompuestaPartes.length>3 ? ` +${referenciaCompuestaPartes.length-3}`:'');
          if (detalles.length === 1 && referenciaCompuestaPartes.length === 1) referenciaCompuesta = referenciaCompuestaPartes[0];
          return {
            id: ventaRaw.venta_id || ventaRaw.id,
            cliente_id: ventaRaw.cliente_id ?? null,
            empleado_id: ventaRaw.empleado_id ?? null,
            vehiculo_id: ventaRaw.vehiculo_id ?? null,
            tipo: tipoVenta,
            referencia_id: 0,
            cantidad: detalles.reduce((s,d)=> s + (d.cantidad||0), 0),
            precio_unitario: 0,
            total,
            fecha: fechaStr,
            descripcion: ventaRaw.observaciones || null,
            cliente: ventaRaw.cliente || undefined,
            empleado: ventaRaw.empleado || undefined,
            vehiculo: ventaRaw.vehiculo || undefined,
            referencia_compuesta: referenciaCompuesta,
            detalles_tipos: tipos as any,
            factura_electronica: ventaRaw.facturaElectronica || undefined,
          } as any;
        });
        return {
          data: agregadas,
          current_page: 1,
          last_page: 1,
          per_page: agregadas.length || 15,
          total: agregadas.length,
          from: agregadas.length ? 1 : 0,
          to: agregadas.length,
        } as PaginatedResponse<Venta>;
      }
      return candidate as PaginatedResponse<Venta>;
    }

    // Fallback si sólo vino un array de ventas
    const arrayData: Venta[] = Array.isArray(raw?.data) ? raw.data : (Array.isArray(raw) ? raw : []);
    return {
      data: arrayData,
      current_page: 1,
      last_page: 1,
      per_page: arrayData.length || 15,
      total: arrayData.length,
      from: arrayData.length ? 1 : 0,
      to: arrayData.length,
    };
  }

  /**
   * Obtener todas las ventas sin paginación
   * GET /api/ventas/all
   */
  async getAllVentas(): Promise<ApiResponse<Venta[]>> {
    return await apiService.get(`${this.BASE_URL}/all`);
  }

  /**
   * Obtener estadísticas de ventas
   * GET /api/ventas/stats
   */
  async getStats(): Promise<ApiResponse<any>> {
    const raw: any = await apiService.get(`${this.BASE_URL}/stats`);
    // Normalizar para que siempre exponga raw.data
    if (raw && raw.stats && !raw.data) {
      return { ...raw, data: raw.stats };
    }
    return raw;
  }

  /**
   * Obtener ventas del día actual
   * GET /api/ventas/del-dia
   */
  async getVentasDelDia(): Promise<ApiResponse<Venta[]>> {
    return await apiService.get(`${this.BASE_URL}/del-dia`);
  }

  /**
   * Crear una nueva venta
   * POST /api/ventas
   */
  async createVenta(data: CreateVentaRequest): Promise<ApiResponse<Venta>> {
    return await apiService.post(this.BASE_URL, data);
  }

  /**
   * Obtener una venta específica por ID
   * GET /api/ventas/{id}
   */
  async getVentaById(id: number): Promise<ApiResponse<Venta>> {
    return await apiService.get(`${this.BASE_URL}/${id}`);
  }

  /**
   * Actualizar una venta
   * PUT /api/ventas/{id}
   */
  async updateVenta(id: number, data: UpdateVentaRequest): Promise<ApiResponse<Venta>> {
    return await apiService.put(`${this.BASE_URL}/${id}`, data);
  }

  /**
   * Eliminar una venta (soft delete)
   * DELETE /api/ventas/{id}
   */
  async deleteVenta(id: number): Promise<ApiResponse<void>> {
    return await apiService.delete(`${this.BASE_URL}/${id}`);
  }

  /**
   * Restaurar una venta eliminada
   * PUT /api/ventas/{id}/restore
   */
  async restoreVenta(id: number): Promise<ApiResponse<Venta>> {
    return await apiService.put(`${this.BASE_URL}/${id}/restore`);
  }

  /**
   * Obtener ventas eliminadas
   * GET /api/ventas/trashed/list
   */
  async getTrashedVentas(): Promise<ApiResponse<Venta[]>> {
    return await apiService.get(`${this.BASE_URL}/trashed/list`);
  }

  // === CONSULTAS ESPECÍFICAS ===

  /**
   * Obtener ventas por cliente
   * GET /api/ventas/cliente/{clienteId}
   */
  async getVentasByCliente(clienteId: number): Promise<ApiResponse<Venta[]>> {
    return await apiService.get(`${this.BASE_URL}/cliente/${clienteId}`);
  }

  /**
   * Obtener ventas por empleado
   * GET /api/ventas/empleado/{empleadoId}
   */
  async getVentasByEmpleado(empleadoId: number): Promise<ApiResponse<Venta[]>> {
    return await apiService.get(`${this.BASE_URL}/empleado/${empleadoId}`);
  }

  /**
   * Obtener ventas por fecha específica
   * GET /api/ventas/fecha/{fecha}
   */
  async getVentasByFecha(fecha: string): Promise<ApiResponse<Venta[]>> {
    return await apiService.get(`${this.BASE_URL}/fecha/${fecha}`);
  }

  /**
   * Obtener ventas por rango de fechas
   * GET /api/ventas/rango-fechas
   */
  async getVentasByRangoFechas(fechaInicio: string, fechaFin: string): Promise<ApiResponse<Venta[]>> {
    const params = new URLSearchParams({
      fecha_inicio: fechaInicio,
      fecha_fin: fechaFin
    });
    return await apiService.get(`${this.BASE_URL}/rango-fechas?${params.toString()}`);
  }

  // === ANÁLISIS Y REPORTES ===

  /**
   * Obtener productos más vendidos
   * GET /api/ventas/productos-mas-vendidos
   */
  async getProductosMasVendidos(limite?: number): Promise<ApiResponse<any[]>> {
    const params = limite ? `?limite=${limite}` : '';
    return await apiService.get(`${this.BASE_URL}/productos-mas-vendidos${params}`);
  }

  /**
   * Obtener servicios más vendidos
   * GET /api/ventas/servicios-mas-vendidos
   */
  async getServiciosMasVendidos(limite?: number): Promise<ApiResponse<any[]>> {
    const params = limite ? `?limite=${limite}` : '';
    return await apiService.get(`${this.BASE_URL}/servicios-mas-vendidos${params}`);
  }

  /**
   * Debug endpoint para validar lavados
   * POST /api/ventas/debug-lavados
   */
  async debugLavados(data: any): Promise<ApiResponse<any>> {
    return await apiService.post(`${this.BASE_URL}/debug-lavados`, data);
  }
}

export default new VentaService();
