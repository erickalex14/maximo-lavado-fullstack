import { BaseService } from './BaseService';

//SERVICIO PARA GESTIONAR VENTAS

class VentaService extends BaseService {
  constructor() {
    super('/ventas');
  }

  // LOS METODOS CRUD BÁSICOS YA ESTÁN HEREDADOS DEL BaseService:

  // METODOS ESPECÍFICOS DE VENTAS

  // OBTENER TODAS LAS VENTAS ELIMINADAS

  async getTrashedVentas() {
    return this.trashed();
  }

  // OBTENER MÉTRICAS GENERALES DE VENTAS

  async getMetricasVentas(params = {}) {
    return this.metricas(params);
  }

  // OBTENER PRODUCTOS DISPONIBLES PARA VENTA

  async getProductosDisponibles() {
    return this.customAction('productos-disponibles', { method: 'GET' });
  }

  // OBTENER CLIENTES PARA VENTAS

  async getClientesVentas() {
    return this.customAction('clientes', { method: 'GET' });
  }

  // VENTAS DE PRODUCTOS AUTOMOTRICES

  // OBTENER TODAS LAS VENTAS AUTOMOTRICES

  async getVentasAutomotrices(params = {}) {
    return this.customAction('automotrices', {
      method: 'GET',
      data: params,
      useParams: true
    });
  }

  // CREAR UNA VENTA AUTOMOTRIZ

  async createVentaAutomotriz(data) {
    return this.customAction('automotrices', {
      method: 'POST',
      data: data
    });
  }

  // OBTENER UNA VENTA AUTOMOTRIZ POR ID

  async getVentaAutomotriz(id) {
    return this.customAction(`automotrices/${id}`, { method: 'GET' });
  }

  // ACTUALIZAR UNA VENTA AUTOMOTRIZ

  async updateVentaAutomotriz(id, data) {
    return this.customAction(`automotrices/${id}`, {
      method: 'PUT',
      data: data
    });
  }

  // ELIMINAR UNA VENTA AUTOMOTRIZ

  async deleteVentaAutomotriz(id) {
    return this.customAction(`automotrices/${id}`, { method: 'DELETE' });
  }

  // RESTAURAR UNA VENTA AUTOMOTRIZ

  async restoreVentaAutomotriz(id) {
    return this.customAction(`automotrices/${id}/restore`, { method: 'PUT' });
  }

  // OBTENER VENTAS AUTOMOTRICES ELIMINADAS

  async getTrashedVentasAutomotrices() {
    return this.customAction('automotrices/trashed', { method: 'GET' });
  }

  // OBTENER MÉTRICAS DE VENTAS AUTOMOTRICES

  async getMetricasAutomotrices(params = {}) {
    return this.customAction('automotrices/metricas', {
      method: 'GET',
      data: params,
      useParams: true
    });
  }

  // VENTAS DE PRODUCTOS DE DESPENSA

  // OBTENER TODAS LAS VENTAS DE DESPENSA

  async getVentasDespensa(params = {}) {
    return this.customAction('despensa', {
      method: 'GET',
      data: params,
      useParams: true
    });
  }

  // CREAR UNA VENTA DE DESPENSA

  async createVentaDespensa(data) {
    return this.customAction('despensa', {
      method: 'POST',
      data: data
    });
  }

  // OBTENER UNA VENTA DE DESPENSA POR ID

  async getVentaDespensa(id) {
    return this.customAction(`despensa/${id}`, { method: 'GET' });
  }

  // ACTUALIZAR UNA VENTA DE DESPENSA

  async updateVentaDespensa(id, data) {
    return this.customAction(`despensa/${id}`, {
      method: 'PUT',
      data: data
    });
  }

  // ELIMINAR UNA VENTA DE DESPENSA

  async deleteVentaDespensa(id) {
    return this.customAction(`despensa/${id}`, { method: 'DELETE' });
  }

  // RESTAURAR UNA VENTA DE DESPENSA

  async restoreVentaDespensa(id) {
    return this.customAction(`despensa/${id}/restore`, { method: 'PUT' });
  }

  // OBTENER VENTAS DE DESPENSA ELIMINADAS

  async getTrashedVentasDespensa() {
    return this.customAction('despensa/trashed', { method: 'GET' });
  }

  // OBTENER MÉTRICAS DE VENTAS DE DESPENSA

  async getMetricasDespensa(params = {}) {
    return this.customAction('despensa/metricas', {
      method: 'GET',
      data: params,
      useParams: true
    });
  }

  // METODOS DE CONVENIENCIA PARA VENTAS

  // OBTENER TODAS LAS VENTAS (AUTOMOTRICES Y DESPENSA)

  async getTodasLasVentas(params = {}) {
    return this.index(params);
  }

  // OBTENER VENTAS POR RANGO DE FECHAS

  async getVentasPorRangoFechas(fechaInicio, fechaFin, params = {}) {
    return this.index({
      fecha_inicio: fechaInicio,
      fecha_fin: fechaFin,
      ...params
    });
  }

  // OBTENER VENTAS AUTOMOTRICES POR RANGO DE FECHAS

  async getVentasAutomotricesPorRangoFechas(fechaInicio, fechaFin, params = {}) {
    return this.getVentasAutomotrices({
      fecha_inicio: fechaInicio,
      fecha_fin: fechaFin,
      ...params
    });
  }

  // OBTENER VENTAS DE DESPENSA POR RANGO DE FECHAS

  async getVentasDespensaPorRangoFechas(fechaInicio, fechaFin, params = {}) {
    return this.getVentasDespensa({
      fecha_inicio: fechaInicio,
      fecha_fin: fechaFin,
      ...params
    });
  }

  // OBTENER VENTAS POR CLIENTE

  async getVentasPorCliente(clienteId, params = {}) {
    return this.index({ cliente_id: clienteId, ...params });
  }

  // OBTENER RESUMEN DE VENTAS

  async getResumenVentas() {
    try {
      const [
        ventasAutomotrices,
        ventasDespensa,
        metricasGenerales,
        metricasAutomotrices,
        metricasDespensa
      ] = await Promise.all([
        this.getVentasAutomotrices(),
        this.getVentasDespensa(),
        this.getMetricasVentas(),
        this.getMetricasAutomotrices(),
        this.getMetricasDespensa()
      ]);

      return {
        total_ventas: (ventasAutomotrices.data?.length || 0) + (ventasDespensa.data?.length || 0),
        ventas_automotrices: ventasAutomotrices.data?.length || 0,
        ventas_despensa: ventasDespensa.data?.length || 0,
        metricas_generales: metricasGenerales.data || {},
        metricas_automotrices: metricasAutomotrices.data || {},
        metricas_despensa: metricasDespensa.data || {}
      };
    } catch (error) {
      throw error;
    }
  }

  // OBTENER VENTAS DEL DIA

  async getVentasDelDia(fecha = null, params = {}) {
    const fechaVenta = fecha || new Date().toISOString().split('T')[0];
    return this.getVentasPorRangoFechas(fechaVenta, fechaVenta, params);
  }

  // OBTENER VENTAS DEL MES

  async getVentasDelMes(anio = null, mes = null, params = {}) {
    const now = new Date();
    const year = anio || now.getFullYear();
    const month = mes || (now.getMonth() + 1);
    
    const fechaInicio = `${year}-${month.toString().padStart(2, '0')}-01`;
    const fechaFin = new Date(year, month, 0).toISOString().split('T')[0];
    
    return this.getVentasPorRangoFechas(fechaInicio, fechaFin, params);
  }

  // BUSCAR VENTAS POR CRITERIO

  async buscarVentas(termino, tipo = 'todas', params = {}) {
    const filtros = { buscar: termino, ...params };
    
    if (tipo === 'automotrices') {
      return this.getVentasAutomotrices(filtros);
    } else if (tipo === 'despensa') {
      return this.getVentasDespensa(filtros);
    } else {
      return this.index(filtros);
    }
  }

  // OBTENER VENTAS CON MAYOR VALOR

  async getVentasMayorValor(limite = 10, tipo = 'todas') {
    try {
      let ventas = [];
      
      if (tipo === 'automotrices') {
        const ventasAutomotrices = await this.getVentasAutomotrices();
        ventas = ventasAutomotrices.data || [];
      } else if (tipo === 'despensa') {
        const ventasDespensa = await this.getVentasDespensa();
        ventas = ventasDespensa.data || [];
      } else {
        const todasVentas = await this.getTodasLasVentas();
        ventas = todasVentas.data || [];
      }

      return ventas
        .sort((a, b) => (b.total || b.monto_total || 0) - (a.total || a.monto_total || 0))
        .slice(0, limite);
    } catch (error) {
      throw error;
    }
  }

  // OBTENER ESTADÍSTICAS COMPARATIVAS

  async getEstadisticasComparativas(fechaInicio, fechaFin) {
    try {
      const [ventasAutomotrices, ventasDespensa] = await Promise.all([
        this.getVentasAutomotricesPorRangoFechas(fechaInicio, fechaFin),
        this.getVentasDespensaPorRangoFechas(fechaInicio, fechaFin)
      ]);

      const totalAutomotrices = ventasAutomotrices.data?.reduce((sum, venta) => 
        sum + (venta.total || venta.monto_total || 0), 0) || 0;
      
      const totalDespensa = ventasDespensa.data?.reduce((sum, venta) => 
        sum + (venta.total || venta.monto_total || 0), 0) || 0;

      return {
        periodo: { fecha_inicio: fechaInicio, fecha_fin: fechaFin },
        ventas_automotrices: {
          cantidad: ventasAutomotrices.data?.length || 0,
          total: totalAutomotrices
        },
        ventas_despensa: {
          cantidad: ventasDespensa.data?.length || 0,
          total: totalDespensa
        },
        total_general: {
          cantidad: (ventasAutomotrices.data?.length || 0) + (ventasDespensa.data?.length || 0),
          total: totalAutomotrices + totalDespensa
        }
      };
    } catch (error) {
      throw error;
    }
  }
}

// Instancia única del servicio
const ventaService = new VentaService();

export default ventaService;
