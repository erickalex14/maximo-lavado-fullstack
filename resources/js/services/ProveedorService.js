import { BaseService } from './BaseService';

//SERVICIO PARA GESTIONAR PROVEEDORES

class ProveedorService extends BaseService {
  constructor() {
    super('/proveedores');
  }

  // LOS METODOS CRUD BÁSICOS YA ESTÁN HEREDADOS DEL BaseService:

  // METODOS ESPECÍFICOS DE PROVEEDORES

  // OBTENER DEUDA PENDIENTE DE UN PROVEEDOR

  async getDeuda(id) {
    return this.customAction(`${id}/deuda`, { method: 'GET' });
  }

  // OBTENER HISTORIAL DE PAGOS DE UN PROVEEDOR ESPECÍFICO

  async getPagosProveedor(id) {
    return this.customAction(`${id}/pagos`, { method: 'GET' });
  }

  // GESTIÓN DE PAGOS DE PROVEEDORES

  // OBTENER TODOS LOS PAGOS DE PROVEEDORES

  async getAllPagos(params = {}) {
    return this.customAction('pagos', {
      method: 'GET',
      data: params,
      useParams: true
    });
  }

  // OBTENER PAGOS EN UN RANGO DE FECHAS

  async getPagosByRangoFechas(fechaInicio, fechaFin, params = {}) {
    return this.getAllPagos({
      fecha_inicio: fechaInicio,
      fecha_fin: fechaFin,
      ...params
    });
  }

  // CREAR UN NUEVO PAGO DE PROVEEDOR

  async createPago(pagoData) {
    return this.customAction('pagos', {
      method: 'POST',
      data: pagoData
    });
  }

  // OBTENER MÉTRICAS DE PAGOS

  async getMetricasPagos(params = {}) {
    return this.customAction('pagos/metricas', {
      method: 'GET',
      data: params,
      useParams: true
    });
  }

  // OBTENER UN PAGO ESPECÍFICO POR ID

  async getPago(pagoId) {
    return this.customAction(`pagos/${pagoId}`, { method: 'GET' });
  }

  // ACTUALIZAR UN PAGO ESPECÍFICO

  async updatePago(pagoId, pagoData) {
    return this.customAction(`pagos/${pagoId}`, {
      method: 'PUT',
      data: pagoData
    });
  }

  // ELIMINAR UN PAGO ESPECÍFICO

  async deletePago(pagoId) {
    return this.customAction(`pagos/${pagoId}`, { method: 'DELETE' });
  }

  // METODOS DE CONVENIENCIA PARA PROVEEDORES

  // OBTENER PROVEEDORES CON DEUDA PENDIENTE

  async getProveedoresConDeuda() {
    const proveedores = await this.index();
    const proveedoresConDeuda = [];
    
    for (const proveedor of proveedores) {
      try {
        const deudaResponse = await this.getDeuda(proveedor.id);
        if (deudaResponse.data && deudaResponse.data.deuda_pendiente > 0) {
          proveedoresConDeuda.push({
            ...proveedor,
            deuda_pendiente: deudaResponse.data.deuda_pendiente
          });
        }
      } catch (error) {
        console.warn(`Error obteniendo deuda del proveedor ${proveedor.id}:`, error);
      }
    }
    
    return proveedoresConDeuda;
  }

  // OBTENER RESUMEN COMPLETO DE UN PROVEEDOR

  async getResumenProveedor(id, limitePagos = 10) {
    try {
      const [proveedor, deuda, pagos] = await Promise.all([
        this.show(id),
        this.getDeuda(id),
        this.getPagosProveedor(id)
      ]);

      return {
        proveedor: proveedor.data,
        deuda_pendiente: deuda.data?.deuda_pendiente || 0,
        pagos_recientes: pagos.data?.slice(0, limitePagos) || [],
        total_pagos: pagos.data?.length || 0
      };
    } catch (error) {
      throw error;
    }
  }

  // OBTENER ESTADÍSTICAS DE PAGOS DE UN PROVEEDOR POR PERÍODO

  async getEstadisticasPagosProveedor(id, fechaInicio, fechaFin) {
    try {
      const pagos = await this.getPagosProveedor(id);
      
      const pagosFiltrados = pagos.data.filter(pago => {
        const fechaPago = new Date(pago.fecha);
        const inicio = new Date(fechaInicio);
        const fin = new Date(fechaFin);
        return fechaPago >= inicio && fechaPago <= fin;
      });

      const totalPagado = pagosFiltrados.reduce((sum, pago) => sum + parseFloat(pago.monto), 0);
      
      return {
        total_pagos: pagosFiltrados.length,
        monto_total: totalPagado,
        promedio_pago: pagosFiltrados.length > 0 ? totalPagado / pagosFiltrados.length : 0,
        pagos: pagosFiltrados
      };
    } catch (error) {
      throw error;
    }
  }

  // BUSCAR PROVEEDORES POR NOMBRE O EMPRESA

  async buscarProveedores(termino, params = {}) {
    return this.index({ buscar: termino, ...params });
  }
}

// Instancia única del servicio
const proveedorService = new ProveedorService();

export default proveedorService;
