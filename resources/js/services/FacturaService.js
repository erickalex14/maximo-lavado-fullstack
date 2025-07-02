import { BaseService } from './BaseService';

//SERVICIO PARA GESTIONAR FACTURAS

class FacturaService extends BaseService {
  constructor() {
    super('/facturas');
  }

  // LOS METODOS CRUD BÁSICOS YA ESTÁN HEREDADOS DEL BaseService:
  // index(), store(), show(), update(), destroy(), trashed(), restore(), metricas()

  // MÉTODOS DE CONVENIENCIA PARA FACTURAS

  // OBTENER FACTURAS POR RANGO DE FECHAS

  async getFacturasPorRangoFechas(fechaInicio, fechaFin, params = {}) {
    return this.index({
      fecha_inicio: fechaInicio,
      fecha_fin: fechaFin,
      ...params
    });
  }

  // OBTENER FACTURAS POR CLIENTE

  async getFacturasPorCliente(clienteId, params = {}) {
    return this.index({ cliente_id: clienteId, ...params });
  }

  // OBTENER FACTURAS DEL DIA

  async getFacturasDelDia(fecha = null, params = {}) {
    const fechaFactura = fecha || new Date().toISOString().split('T')[0];
    return this.getFacturasPorRangoFechas(fechaFactura, fechaFactura, params);
  }

  // OBTENER FACTURAS DEL MES

  async getFacturasDelMes(anio = null, mes = null, params = {}) {
    const now = new Date();
    const year = anio || now.getFullYear();
    const month = mes || (now.getMonth() + 1);
    
    const fechaInicio = `${year}-${month.toString().padStart(2, '0')}-01`;
    const fechaFin = new Date(year, month, 0).toISOString().split('T')[0];
    
    return this.getFacturasPorRangoFechas(fechaInicio, fechaFin, params);
  }

  // OBTENER FACTURAS DEL AÑO

  async getFacturasDelAnio(anio = null, params = {}) {
    const year = anio || new Date().getFullYear();
    const fechaInicio = `${year}-01-01`;
    const fechaFin = `${year}-12-31`;
    
    return this.getFacturasPorRangoFechas(fechaInicio, fechaFin, params);
  }

  // BUSCAR FACTURA POR NÚMERO

  async buscarPorNumeroFactura(numeroFactura) {
    return this.apiCall('GET', `/numero/${numeroFactura}`);
  }

  // BUSCAR FACTURAS POR DESCRIPCIÓN

  async buscarFacturas(termino, params = {}) {
    return this.index({ buscar: termino, ...params });
  }

  // OBTENER FACTURAS POR MONTO MÍNIMO

  async getFacturasPorMontoMinimo(montoMinimo, params = {}) {
    return this.index({ monto_minimo: montoMinimo, ...params });
  }

  // OBTENER FACTURAS POR MONTO MÁXIMO

  async getFacturasPorMontoMaximo(montoMaximo, params = {}) {
    return this.index({ monto_maximo: montoMaximo, ...params });
  }

  // OBTENER FACTURAS EN RANGO DE MONTOS

  async getFacturasPorRangoMontos(montoMinimo, montoMaximo, params = {}) {
    return this.index({
      monto_minimo: montoMinimo,
      monto_maximo: montoMaximo,
      ...params
    });
  }

  // OBTENER RESUMEN DE FACTURAS

  async getResumenFacturas(fechaInicio = null, fechaFin = null) {
    try {
      let facturas;
      if (fechaInicio && fechaFin) {
        facturas = await this.getFacturasPorRangoFechas(fechaInicio, fechaFin);
      } else {
        facturas = await this.index();
      }

      const metricas = await this.metricas();

      // Calcular estadísticas
      const listaFacturas = facturas.data || facturas;
      const totalFacturas = listaFacturas.length;
      const montoTotal = listaFacturas.reduce((sum, factura) => sum + parseFloat(factura.total || 0), 0);
      const promedioMonto = totalFacturas > 0 ? montoTotal / totalFacturas : 0;

      // Agrupar por cliente
      const facturasPorCliente = listaFacturas.reduce((acc, factura) => {
        const clienteNombre = factura.cliente?.nombre || 'Cliente desconocido';
        if (!acc[clienteNombre]) {
          acc[clienteNombre] = { cantidad: 0, monto: 0 };
        }
        acc[clienteNombre].cantidad++;
        acc[clienteNombre].monto += parseFloat(factura.total || 0);
        return acc;
      }, {});

      // Agrupar por mes
      const facturasPorMes = listaFacturas.reduce((acc, factura) => {
        const fecha = new Date(factura.fecha);
        const mesAnio = `${fecha.getFullYear()}-${(fecha.getMonth() + 1).toString().padStart(2, '0')}`;
        if (!acc[mesAnio]) {
          acc[mesAnio] = { cantidad: 0, monto: 0 };
        }
        acc[mesAnio].cantidad++;
        acc[mesAnio].monto += parseFloat(factura.total || 0);
        return acc;
      }, {});

      return {
        periodo: fechaInicio && fechaFin ? { fecha_inicio: fechaInicio, fecha_fin: fechaFin } : 'Todos los tiempos',
        total_facturas: totalFacturas,
        monto_total: montoTotal,
        promedio_monto: promedioMonto,
        facturas_por_cliente: facturasPorCliente,
        facturas_por_mes: facturasPorMes,
        metricas: metricas.data || metricas
      };
    } catch (error) {
      throw error;
    }
  }

  // OBTENER FACTURAS MAYORES

  async getFacturasMayores(limite = 10, params = {}) {
    try {
      const facturas = await this.index(params);
      const listaFacturas = facturas.data || facturas;
      
      return listaFacturas
        .sort((a, b) => parseFloat(b.total || 0) - parseFloat(a.total || 0))
        .slice(0, limite);
    } catch (error) {
      throw error;
    }
  }

  // OBTENER ESTADÍSTICAS POR PERÍODO

  async getEstadisticasPorPeriodo(fechaInicio, fechaFin) {
    try {
      const facturas = await this.getFacturasPorRangoFechas(fechaInicio, fechaFin);
      const listaFacturas = facturas.data || facturas;

      // Agrupar por fecha
      const facturasPorFecha = listaFacturas.reduce((acc, factura) => {
        const fecha = factura.fecha || factura.created_at?.split('T')[0];
        if (!acc[fecha]) {
          acc[fecha] = { cantidad: 0, monto: 0 };
        }
        acc[fecha].cantidad++;
        acc[fecha].monto += parseFloat(factura.total || 0);
        return acc;
      }, {});

      // Calcular tendencias
      const fechas = Object.keys(facturasPorFecha).sort();
      const montos = fechas.map(fecha => facturasPorFecha[fecha].monto);
      const cantidades = fechas.map(fecha => facturasPorFecha[fecha].cantidad);

      return {
        periodo: { fecha_inicio: fechaInicio, fecha_fin: fechaFin },
        total_dias: fechas.length,
        facturas_por_fecha: facturasPorFecha,
        monto_total: montos.reduce((sum, monto) => sum + monto, 0),
        cantidad_total: cantidades.reduce((sum, cantidad) => sum + cantidad, 0),
        promedio_diario_monto: montos.length > 0 ? montos.reduce((sum, monto) => sum + monto, 0) / montos.length : 0,
        promedio_diario_cantidad: cantidades.length > 0 ? cantidades.reduce((sum, cantidad) => sum + cantidad, 0) / cantidades.length : 0
      };
    } catch (error) {
      throw error;
    }
  }

  // COMPARAR PERÍODOS

  async compararPeriodos(periodo1, periodo2) {
    try {
      const [estadisticas1, estadisticas2] = await Promise.all([
        this.getEstadisticasPorPeriodo(periodo1.fecha_inicio, periodo1.fecha_fin),
        this.getEstadisticasPorPeriodo(periodo2.fecha_inicio, periodo2.fecha_fin)
      ]);

      const diferenciaMonto = estadisticas2.monto_total - estadisticas1.monto_total;
      const diferenciaCantidad = estadisticas2.cantidad_total - estadisticas1.cantidad_total;
      const porcentajeMonto = estadisticas1.monto_total > 0 ? (diferenciaMonto / estadisticas1.monto_total) * 100 : 0;
      const porcentajeCantidad = estadisticas1.cantidad_total > 0 ? (diferenciaCantidad / estadisticas1.cantidad_total) * 100 : 0;

      return {
        periodo_1: estadisticas1,
        periodo_2: estadisticas2,
        comparacion: {
          diferencia_monto: diferenciaMonto,
          diferencia_cantidad: diferenciaCantidad,
          porcentaje_variacion_monto: porcentajeMonto,
          porcentaje_variacion_cantidad: porcentajeCantidad,
          tendencia_monto: diferenciaMonto > 0 ? 'incremento' : diferenciaMonto < 0 ? 'decremento' : 'estable',
          tendencia_cantidad: diferenciaCantidad > 0 ? 'incremento' : diferenciaCantidad < 0 ? 'decremento' : 'estable'
        }
      };
    } catch (error) {
      throw error;
    }
  }

  // OBTENER PROYECCIÓN

  async getProyeccion(diasProyeccion = 30) {
    try {
      // Obtener datos de los últimos 30 días para calcular tendencia
      const fechaFin = new Date().toISOString().split('T')[0];
      const fechaInicio = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
      
      const estadisticas = await this.getEstadisticasPorPeriodo(fechaInicio, fechaFin);
      
      const promedioMontoDiario = estadisticas.promedio_diario_monto;
      const promedioCantidadDiaria = estadisticas.promedio_diario_cantidad;
      
      const proyeccionMonto = promedioMontoDiario * diasProyeccion;
      const proyeccionCantidad = Math.round(promedioCantidadDiaria * diasProyeccion);

      return {
        base_calculo: {
          periodo_analisis: { fecha_inicio: fechaInicio, fecha_fin: fechaFin },
          promedio_monto_diario: promedioMontoDiario,
          promedio_cantidad_diaria: promedioCantidadDiaria
        },
        proyeccion: {
          dias_proyectados: diasProyeccion,
          monto_proyectado: proyeccionMonto,
          cantidad_proyectada: proyeccionCantidad,
          fecha_proyeccion: new Date(Date.now() + diasProyeccion * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
        }
      };
    } catch (error) {
      throw error;
    }
  }

  // ANÁLISIS DE RENDIMIENTO DE FACTURACIÓN

  async getAnalisisRendimiento(fechaInicio, fechaFin, metaFacturacion = null) {
    try {
      const estadisticas = await this.getEstadisticasPorPeriodo(fechaInicio, fechaFin);
      const resumen = await this.getResumenFacturas(fechaInicio, fechaFin);

      let analisisMeta = null;
      if (metaFacturacion) {
        const porcentajeAlcanzado = (estadisticas.monto_total / metaFacturacion) * 100;
        const faltante = metaFacturacion - estadisticas.monto_total;
        
        analisisMeta = {
          meta_establecida: metaFacturacion,
          monto_facturado: estadisticas.monto_total,
          porcentaje_alcanzado: porcentajeAlcanzado,
          faltante: faltante > 0 ? faltante : 0,
          estado: porcentajeAlcanzado >= 100 ? 'meta_superada' : porcentajeAlcanzado >= 80 ? 'cerca_de_meta' : 'necesita_mejorar'
        };
      }

      // Calcular métricas adicionales
      const ticketPromedio = estadisticas.cantidad_total > 0 ? estadisticas.monto_total / estadisticas.cantidad_total : 0;
      const clientesAtendidos = Object.keys(resumen.facturas_por_cliente).length;

      return {
        periodo: { fecha_inicio: fechaInicio, fecha_fin: fechaFin },
        estadisticas_generales: estadisticas,
        resumen_por_cliente: resumen.facturas_por_cliente,
        analisis_meta: analisisMeta,
        metricas_adicionales: {
          ticket_promedio: ticketPromedio,
          clientes_atendidos: clientesAtendidos,
          facturacion_promedio_por_cliente: clientesAtendidos > 0 ? estadisticas.monto_total / clientesAtendidos : 0
        },
        recomendaciones: this._generarRecomendaciones(estadisticas, analisisMeta, ticketPromedio)
      };
    } catch (error) {
      throw error;
    }
  }

  // OBTENER TOP CLIENTES POR FACTURACIÓN

  async getTopClientes(fechaInicio = null, fechaFin = null, limite = 10) {
    try {
      const resumen = await this.getResumenFacturas(fechaInicio, fechaFin);
      
      return Object.entries(resumen.facturas_por_cliente)
        .map(([cliente, datos]) => ({
          cliente,
          cantidad_facturas: datos.cantidad,
          monto_total: datos.monto,
          promedio_por_factura: datos.cantidad > 0 ? datos.monto / datos.cantidad : 0
        }))
        .sort((a, b) => b.monto_total - a.monto_total)
        .slice(0, limite);
    } catch (error) {
      throw error;
    }
  }

  // OBTENER TENDENCIAS MENSUALES

  async getTendenciasMensuales(anio = null) {
    try {
      const year = anio || new Date().getFullYear();
      const fechaInicio = `${year}-01-01`;
      const fechaFin = `${year}-12-31`;
      
      const resumen = await this.getResumenFacturas(fechaInicio, fechaFin);
      
      const tendencias = [];
      for (let mes = 1; mes <= 12; mes++) {
        const mesKey = `${year}-${mes.toString().padStart(2, '0')}`;
        const datosDelMes = resumen.facturas_por_mes[mesKey] || { cantidad: 0, monto: 0 };
        
        tendencias.push({
          mes: mes,
          mes_nombre: new Date(year, mes - 1).toLocaleString('es', { month: 'long' }),
          cantidad: datosDelMes.cantidad,
          monto: datosDelMes.monto,
          promedio: datosDelMes.cantidad > 0 ? datosDelMes.monto / datosDelMes.cantidad : 0
        });
      }
      
      return {
        anio: year,
        tendencias_mensuales: tendencias,
        totales: {
          cantidad_anual: tendencias.reduce((sum, mes) => sum + mes.cantidad, 0),
          monto_anual: tendencias.reduce((sum, mes) => sum + mes.monto, 0),
          promedio_mensual_cantidad: tendencias.reduce((sum, mes) => sum + mes.cantidad, 0) / 12,
          promedio_mensual_monto: tendencias.reduce((sum, mes) => sum + mes.monto, 0) / 12
        }
      };
    } catch (error) {
      throw error;
    }
  }

  // MÉTODO PRIVADO PARA GENERAR RECOMENDACIONES

  _generarRecomendaciones(estadisticas, analisisMeta, ticketPromedio) {
    const recomendaciones = [];

    if (analisisMeta) {
      if (analisisMeta.porcentaje_alcanzado >= 100) {
        recomendaciones.push('¡Excelente! Has superado la meta de facturación establecida.');
      } else if (analisisMeta.porcentaje_alcanzado >= 80) {
        recomendaciones.push('Muy cerca de la meta. Mantén el buen trabajo en facturación.');
      } else if (analisisMeta.porcentaje_alcanzado < 50) {
        recomendaciones.push('Facturación por debajo del 50% de la meta. Revisar estrategias de ventas.');
      }
    }

    if (ticketPromedio > 0) {
      recomendaciones.push(`Ticket promedio actual: $${ticketPromedio.toFixed(2)}. ${ticketPromedio > 1000 ? 'Excelente nivel de facturación por cliente.' : 'Considerar estrategias para aumentar el valor promedio por factura.'}`);
    }

    if (estadisticas.promedio_diario_monto > 0) {
      const proyeccionMensual = estadisticas.promedio_diario_monto * 30;
      recomendaciones.push(`Proyección mensual de facturación: $${proyeccionMensual.toFixed(2)}`);
    }

    if (recomendaciones.length === 0) {
      recomendaciones.push('La facturación está dentro de parámetros normales.');
    }

    return recomendaciones;
  }
}

// Instancia única del servicio
const facturaService = new FacturaService();

export default facturaService;
