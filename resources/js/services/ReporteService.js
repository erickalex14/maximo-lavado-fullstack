import { BaseService } from './BaseService';

//SERVICIO PARA GESTIONAR REPORTES

class ReporteService extends BaseService {
  constructor() {
    super('/reportes');
  }

  // NOTA: ReporteService NO hereda métodos CRUD tradicionales ya que los reportes
  // son generados dinámicamente, no son entidades persistentes

  // MÉTODOS DE REPORTES BÁSICOS

  // OBTENER LISTA DE REPORTES DISPONIBLES

  async getReportesDisponibles() {
    return this.apiCall('GET', '');
  }

  // REPORTES POR MÓDULO ESPECÍFICO

  // REPORTE DE VENTAS

  async getReporteVentas(fechaInicio, fechaFin, formato = 'json', params = {}) {
    return this.apiCall('GET', '/ventas', {}, {
      fecha_inicio: fechaInicio,
      fecha_fin: fechaFin,
      formato,
      ...params
    });
  }

  // REPORTE DE LAVADOS

  async getReporteLavados(fechaInicio, fechaFin, formato = 'json', params = {}) {
    return this.apiCall('GET', '/lavados', {}, {
      fecha_inicio: fechaInicio,
      fecha_fin: fechaFin,
      formato,
      ...params
    });
  }

  // REPORTE DE INGRESOS

  async getReporteIngresos(fechaInicio, fechaFin, formato = 'json', params = {}) {
    return this.apiCall('GET', '/ingresos', {}, {
      fecha_inicio: fechaInicio,
      fecha_fin: fechaFin,
      formato,
      ...params
    });
  }

  // REPORTE DE EGRESOS

  async getReporteEgresos(fechaInicio, fechaFin, formato = 'json', params = {}) {
    return this.apiCall('GET', '/egresos', {}, {
      fecha_inicio: fechaInicio,
      fecha_fin: fechaFin,
      formato,
      ...params
    });
  }

  // REPORTE DE FACTURAS

  async getReporteFacturas(fechaInicio, fechaFin, formato = 'json', params = {}) {
    return this.apiCall('GET', '/facturas', {}, {
      fecha_inicio: fechaInicio,
      fecha_fin: fechaFin,
      formato,
      ...params
    });
  }

  // REPORTE DE EMPLEADOS

  async getReporteEmpleados(fechaInicio, fechaFin, formato = 'json', params = {}) {
    return this.apiCall('GET', '/empleados', {}, {
      fecha_inicio: fechaInicio,
      fecha_fin: fechaFin,
      formato,
      ...params
    });
  }

  // REPORTE DE PRODUCTOS

  async getReporteProductos(formato = 'json', params = {}) {
    return this.apiCall('GET', '/productos', {}, {
      formato,
      ...params
    });
  }

  // REPORTE DE CLIENTES

  async getReporteClientes(formato = 'json', params = {}) {
    return this.apiCall('GET', '/clientes', {}, {
      formato,
      ...params
    });
  }

  // REPORTE FINANCIERO

  async getReporteFinanciero(fechaInicio, fechaFin, formato = 'json', params = {}) {
    return this.apiCall('GET', '/financiero', {}, {
      fecha_inicio: fechaInicio,
      fecha_fin: fechaFin,
      formato,
      ...params
    });
  }

  // REPORTE DE BALANCE

  async getReporteBalance(fechaInicio, fechaFin, formato = 'json', params = {}) {
    return this.apiCall('GET', '/balance', {}, {
      fecha_inicio: fechaInicio,
      fecha_fin: fechaFin,
      formato,
      ...params
    });
  }

  // REPORTE COMPLETO

  async getReporteCompleto(fechaInicio, fechaFin, params = {}) {
    return this.apiCall('GET', '/completo', {}, {
      fecha_inicio: fechaInicio,
      fecha_fin: fechaFin,
      ...params
    });
  }

  // MÉTODOS DE CONVENIENCIA PARA REPORTES POR PERÍODO

  // REPORTES DEL DÍA ACTUAL

  async getReportesDelDia(fecha = null, tipoReporte = 'completo') {
    const fechaReporte = fecha || new Date().toISOString().split('T')[0];
    
    switch (tipoReporte) {
      case 'ventas':
        return this.getReporteVentas(fechaReporte, fechaReporte);
      case 'lavados':
        return this.getReporteLavados(fechaReporte, fechaReporte);
      case 'ingresos':
        return this.getReporteIngresos(fechaReporte, fechaReporte);
      case 'egresos':
        return this.getReporteEgresos(fechaReporte, fechaReporte);
      case 'facturas':
        return this.getReporteFacturas(fechaReporte, fechaReporte);
      case 'empleados':
        return this.getReporteEmpleados(fechaReporte, fechaReporte);
      case 'financiero':
        return this.getReporteFinanciero(fechaReporte, fechaReporte);
      case 'balance':
        return this.getReporteBalance(fechaReporte, fechaReporte);
      case 'completo':
      default:
        return this.getReporteCompleto(fechaReporte, fechaReporte);
    }
  }

  // REPORTES DEL MES

  async getReportesDelMes(anio = null, mes = null, tipoReporte = 'completo') {
    const now = new Date();
    const year = anio || now.getFullYear();
    const month = mes || (now.getMonth() + 1);
    
    const fechaInicio = `${year}-${month.toString().padStart(2, '0')}-01`;
    const fechaFin = new Date(year, month, 0).toISOString().split('T')[0];
    
    switch (tipoReporte) {
      case 'ventas':
        return this.getReporteVentas(fechaInicio, fechaFin);
      case 'lavados':
        return this.getReporteLavados(fechaInicio, fechaFin);
      case 'ingresos':
        return this.getReporteIngresos(fechaInicio, fechaFin);
      case 'egresos':
        return this.getReporteEgresos(fechaInicio, fechaFin);
      case 'facturas':
        return this.getReporteFacturas(fechaInicio, fechaFin);
      case 'empleados':
        return this.getReporteEmpleados(fechaInicio, fechaFin);
      case 'financiero':
        return this.getReporteFinanciero(fechaInicio, fechaFin);
      case 'balance':
        return this.getReporteBalance(fechaInicio, fechaFin);
      case 'completo':
      default:
        return this.getReporteCompleto(fechaInicio, fechaFin);
    }
  }

  // REPORTES DEL AÑO

  async getReportesDelAnio(anio = null, tipoReporte = 'completo') {
    const year = anio || new Date().getFullYear();
    const fechaInicio = `${year}-01-01`;
    const fechaFin = `${year}-12-31`;
    
    switch (tipoReporte) {
      case 'ventas':
        return this.getReporteVentas(fechaInicio, fechaFin);
      case 'lavados':
        return this.getReporteLavados(fechaInicio, fechaFin);
      case 'ingresos':
        return this.getReporteIngresos(fechaInicio, fechaFin);
      case 'egresos':
        return this.getReporteEgresos(fechaInicio, fechaFin);
      case 'facturas':
        return this.getReporteFacturas(fechaInicio, fechaFin);
      case 'empleados':
        return this.getReporteEmpleados(fechaInicio, fechaFin);
      case 'financiero':
        return this.getReporteFinanciero(fechaInicio, fechaFin);
      case 'balance':
        return this.getReporteBalance(fechaInicio, fechaFin);
      case 'completo':
      default:
        return this.getReporteCompleto(fechaInicio, fechaFin);
    }
  }

  // MÉTODOS DE GENERACIÓN MÚLTIPLE

  // GENERAR MÚLTIPLES REPORTES EN PARALELO

  async generarReportesMultiples(fechaInicio, fechaFin, tiposReporte = []) {
    try {
      const reportesPromises = tiposReporte.map(tipo => {
        switch (tipo) {
          case 'ventas':
            return this.getReporteVentas(fechaInicio, fechaFin).then(data => ({ tipo: 'ventas', data }));
          case 'lavados':
            return this.getReporteLavados(fechaInicio, fechaFin).then(data => ({ tipo: 'lavados', data }));
          case 'ingresos':
            return this.getReporteIngresos(fechaInicio, fechaFin).then(data => ({ tipo: 'ingresos', data }));
          case 'egresos':
            return this.getReporteEgresos(fechaInicio, fechaFin).then(data => ({ tipo: 'egresos', data }));
          case 'facturas':
            return this.getReporteFacturas(fechaInicio, fechaFin).then(data => ({ tipo: 'facturas', data }));
          case 'empleados':
            return this.getReporteEmpleados(fechaInicio, fechaFin).then(data => ({ tipo: 'empleados', data }));
          case 'productos':
            return this.getReporteProductos().then(data => ({ tipo: 'productos', data }));
          case 'clientes':
            return this.getReporteClientes().then(data => ({ tipo: 'clientes', data }));
          case 'financiero':
            return this.getReporteFinanciero(fechaInicio, fechaFin).then(data => ({ tipo: 'financiero', data }));
          case 'balance':
            return this.getReporteBalance(fechaInicio, fechaFin).then(data => ({ tipo: 'balance', data }));
          default:
            return Promise.resolve({ tipo, data: null, error: 'Tipo de reporte no válido' });
        }
      });

      const resultados = await Promise.allSettled(reportesPromises);
      
      return {
        periodo: { fecha_inicio: fechaInicio, fecha_fin: fechaFin },
        reportes_solicitados: tiposReporte,
        reportes_generados: resultados.map(resultado => {
          if (resultado.status === 'fulfilled') {
            return resultado.value;
          } else {
            return {
              tipo: 'error',
              data: null,
              error: resultado.reason?.message || 'Error desconocido'
            };
          }
        }),
        total_exitosos: resultados.filter(r => r.status === 'fulfilled').length,
        total_fallidos: resultados.filter(r => r.status === 'rejected').length
      };
    } catch (error) {
      throw error;
    }
  }

  // OBTENER DASHBOARD DE REPORTES

  async getDashboardReportes(fechaInicio = null, fechaFin = null) {
    try {
      // Si no se proporcionan fechas, usar el mes actual
      if (!fechaInicio || !fechaFin) {
        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth() + 1;
        fechaInicio = `${year}-${month.toString().padStart(2, '0')}-01`;
        fechaFin = new Date(year, month, 0).toISOString().split('T')[0];
      }

      // Generar reportes principales
      const reportesPrincipales = ['ventas', 'lavados', 'ingresos', 'egresos', 'facturas', 'financiero'];
      const reportes = await this.generarReportesMultiples(fechaInicio, fechaFin, reportesPrincipales);

      // Generar reportes estáticos (sin fechas)
      const [reporteProductos, reporteClientes] = await Promise.allSettled([
        this.getReporteProductos(),
        this.getReporteClientes()
      ]);

      return {
        periodo: { fecha_inicio: fechaInicio, fecha_fin: fechaFin },
        reportes_periodicos: reportes.reportes_generados,
        reportes_estaticos: {
          productos: reporteProductos.status === 'fulfilled' ? reporteProductos.value : null,
          clientes: reporteClientes.status === 'fulfilled' ? reporteClientes.value : null
        },
        resumen: {
          total_reportes: reportesPrincipales.length + 2,
          reportes_exitosos: reportes.total_exitosos + 
            (reporteProductos.status === 'fulfilled' ? 1 : 0) +
            (reporteClientes.status === 'fulfilled' ? 1 : 0),
          reportes_fallidos: reportes.total_fallidos +
            (reporteProductos.status === 'rejected' ? 1 : 0) +
            (reporteClientes.status === 'rejected' ? 1 : 0)
        }
      };
    } catch (error) {
      throw error;
    }
  }

  // COMPARAR REPORTES ENTRE PERÍODOS

  async compararReportesPeriodos(periodo1, periodo2, tiposReporte = ['ventas', 'ingresos', 'egresos']) {
    try {
      const [reportes1, reportes2] = await Promise.all([
        this.generarReportesMultiples(periodo1.fecha_inicio, periodo1.fecha_fin, tiposReporte),
        this.generarReportesMultiples(periodo2.fecha_inicio, periodo2.fecha_fin, tiposReporte)
      ]);

      const comparaciones = tiposReporte.map(tipo => {
        const reporte1 = reportes1.reportes_generados.find(r => r.tipo === tipo);
        const reporte2 = reportes2.reportes_generados.find(r => r.tipo === tipo);

        if (!reporte1?.data || !reporte2?.data) {
          return {
            tipo,
            disponible: false,
            error: 'Datos no disponibles para comparación'
          };
        }

        // Extraer métricas principales según el tipo de reporte
        const metricas1 = this._extraerMetricasReporte(reporte1.data, tipo);
        const metricas2 = this._extraerMetricasReporte(reporte2.data, tipo);

        return {
          tipo,
          disponible: true,
          periodo_1: { periodo: periodo1, metricas: metricas1 },
          periodo_2: { periodo: periodo2, metricas: metricas2 },
          comparacion: this._calcularComparacion(metricas1, metricas2, tipo)
        };
      });

      return {
        periodos: { periodo_1: periodo1, periodo_2: periodo2 },
        tipos_comparados: tiposReporte,
        comparaciones,
        resumen_comparacion: this._generarResumenComparacion(comparaciones)
      };
    } catch (error) {
      throw error;
    }
  }

  // MÉTODOS PRIVADOS DE APOYO

  _extraerMetricasReporte(datos, tipo) {
    const reporteData = datos.data || datos;
    
    switch (tipo) {
      case 'ventas':
        return {
          total: reporteData.total_ventas || 0,
          monto: reporteData.monto_total || 0,
          cantidad: reporteData.cantidad_total || 0
        };
      case 'ingresos':
        return {
          total: reporteData.total_ingresos || 0,
          monto: reporteData.monto_total || 0,
          cantidad: reporteData.cantidad_total || 0
        };
      case 'egresos':
        return {
          total: reporteData.total_egresos || 0,
          monto: reporteData.monto_total || 0,
          cantidad: reporteData.cantidad_total || 0
        };
      case 'facturas':
        return {
          total: reporteData.total_facturas || 0,
          monto: reporteData.monto_total || 0,
          cantidad: reporteData.cantidad_total || 0
        };
      case 'lavados':
        return {
          total: reporteData.total_lavados || 0,
          monto: reporteData.monto_total || 0,
          cantidad: reporteData.cantidad_total || 0
        };
      default:
        return {
          total: 0,
          monto: 0,
          cantidad: 0
        };
    }
  }

  _calcularComparacion(metricas1, metricas2, tipo) {
    const diferenciaMonto = metricas2.monto - metricas1.monto;
    const diferenciaCantidad = metricas2.cantidad - metricas1.cantidad;
    
    const porcentajeMonto = metricas1.monto > 0 ? (diferenciaMonto / metricas1.monto) * 100 : 0;
    const porcentajeCantidad = metricas1.cantidad > 0 ? (diferenciaCantidad / metricas1.cantidad) * 100 : 0;

    return {
      diferencia_monto: diferenciaMonto,
      diferencia_cantidad: diferenciaCantidad,
      porcentaje_variacion_monto: porcentajeMonto,
      porcentaje_variacion_cantidad: porcentajeCantidad,
      tendencia_monto: diferenciaMonto > 0 ? 'incremento' : diferenciaMonto < 0 ? 'decremento' : 'estable',
      tendencia_cantidad: diferenciaCantidad > 0 ? 'incremento' : diferenciaCantidad < 0 ? 'decremento' : 'estable'
    };
  }

  _generarResumenComparacion(comparaciones) {
    const disponibles = comparaciones.filter(c => c.disponible);
    const incrementos = disponibles.filter(c => c.comparacion?.tendencia_monto === 'incremento').length;
    const decrementos = disponibles.filter(c => c.comparacion?.tendencia_monto === 'decremento').length;
    const estables = disponibles.filter(c => c.comparacion?.tendencia_monto === 'estable').length;

    return {
      total_comparaciones: comparaciones.length,
      comparaciones_exitosas: disponibles.length,
      tendencias: {
        incrementos,
        decrementos,
        estables
      },
      tendencia_general: incrementos > decrementos ? 'crecimiento' : 
                       decrementos > incrementos ? 'decrecimiento' : 'estable'
    };
  }
}

// Instancia única del servicio
const reporteService = new ReporteService();

export default reporteService;
