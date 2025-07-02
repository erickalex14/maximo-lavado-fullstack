import { BaseService } from './BaseService';

//SERVICIO PARA GESTIONAR GASTOS GENERALES

class GastoGeneralService extends BaseService {
  constructor() {
    super('/gastos-generales');
  }

  // LOS METODOS CRUD BÁSICOS YA ESTÁN HEREDADOS DEL BaseService:
  // index(), store(), show(), update(), destroy(), trashed(), restore(), metricas()

  // MÉTODOS DE CONVENIENCIA PARA GASTOS GENERALES

  // OBTENER GASTOS GENERALES POR RANGO DE FECHAS

  async getGastosGeneralesPorRangoFechas(fechaInicio, fechaFin, params = {}) {
    return this.index({
      fecha_inicio: fechaInicio,
      fecha_fin: fechaFin,
      ...params
    });
  }

  // OBTENER GASTOS GENERALES DEL DIA

  async getGastosGeneralesDelDia(fecha = null, params = {}) {
    const fechaGasto = fecha || new Date().toISOString().split('T')[0];
    return this.getGastosGeneralesPorRangoFechas(fechaGasto, fechaGasto, params);
  }

  // OBTENER GASTOS GENERALES DEL MES

  async getGastosGeneralesDelMes(anio = null, mes = null, params = {}) {
    const now = new Date();
    const year = anio || now.getFullYear();
    const month = mes || (now.getMonth() + 1);
    
    const fechaInicio = `${year}-${month.toString().padStart(2, '0')}-01`;
    const fechaFin = new Date(year, month, 0).toISOString().split('T')[0];
    
    return this.getGastosGeneralesPorRangoFechas(fechaInicio, fechaFin, params);
  }

  // OBTENER GASTOS GENERALES DEL AÑO

  async getGastosGeneralesDelAnio(anio = null, params = {}) {
    const year = anio || new Date().getFullYear();
    const fechaInicio = `${year}-01-01`;
    const fechaFin = `${year}-12-31`;
    
    return this.getGastosGeneralesPorRangoFechas(fechaInicio, fechaFin, params);
  }

  // BUSCAR GASTOS GENERALES POR NOMBRE O DESCRIPCIÓN

  async buscarGastosGenerales(termino, params = {}) {
    return this.index({ buscar: termino, ...params });
  }

  // OBTENER GASTOS GENERALES POR MONTO MÍNIMO

  async getGastosGeneralesPorMontoMinimo(montoMinimo, params = {}) {
    return this.index({ monto_minimo: montoMinimo, ...params });
  }

  // OBTENER GASTOS GENERALES POR MONTO MÁXIMO

  async getGastosGeneralesPorMontoMaximo(montoMaximo, params = {}) {
    return this.index({ monto_maximo: montoMaximo, ...params });
  }

  // OBTENER GASTOS GENERALES EN RANGO DE MONTOS

  async getGastosGeneralesPorRangoMontos(montoMinimo, montoMaximo, params = {}) {
    return this.index({
      monto_minimo: montoMinimo,
      monto_maximo: montoMaximo,
      ...params
    });
  }

  // OBTENER RESUMEN DE GASTOS GENERALES

  async getResumenGastosGenerales(fechaInicio = null, fechaFin = null) {
    try {
      let gastos;
      if (fechaInicio && fechaFin) {
        gastos = await this.getGastosGeneralesPorRangoFechas(fechaInicio, fechaFin);
      } else {
        gastos = await this.index();
      }

      const metricas = await this.metricas();

      // Calcular estadísticas
      const listaGastos = gastos.data || gastos;
      const totalGastos = listaGastos.length;
      const montoTotal = listaGastos.reduce((sum, gasto) => sum + parseFloat(gasto.monto || 0), 0);
      const promedioMonto = totalGastos > 0 ? montoTotal / totalGastos : 0;

      // Agrupar por categoría/nombre
      const gastosPorCategoria = listaGastos.reduce((acc, gasto) => {
        const categoria = gasto.nombre || 'Sin categoría';
        if (!acc[categoria]) {
          acc[categoria] = { cantidad: 0, monto: 0 };
        }
        acc[categoria].cantidad++;
        acc[categoria].monto += parseFloat(gasto.monto || 0);
        return acc;
      }, {});

      return {
        periodo: fechaInicio && fechaFin ? { fecha_inicio: fechaInicio, fecha_fin: fechaFin } : 'Todos los tiempos',
        total_gastos: totalGastos,
        monto_total: montoTotal,
        promedio_monto: promedioMonto,
        gastos_por_categoria: gastosPorCategoria,
        metricas: metricas.data || metricas
      };
    } catch (error) {
      throw error;
    }
  }

  // OBTENER GASTOS MAYORES

  async getGastosMayores(limite = 10, params = {}) {
    try {
      const gastos = await this.index(params);
      const listaGastos = gastos.data || gastos;
      
      return listaGastos
        .sort((a, b) => parseFloat(b.monto || 0) - parseFloat(a.monto || 0))
        .slice(0, limite);
    } catch (error) {
      throw error;
    }
  }

  // OBTENER ESTADÍSTICAS POR PERÍODO

  async getEstadisticasPorPeriodo(fechaInicio, fechaFin) {
    try {
      const gastos = await this.getGastosGeneralesPorRangoFechas(fechaInicio, fechaFin);
      const listaGastos = gastos.data || gastos;

      // Agrupar por fecha
      const gastosPorFecha = listaGastos.reduce((acc, gasto) => {
        const fecha = gasto.fecha || gasto.created_at?.split('T')[0];
        if (!acc[fecha]) {
          acc[fecha] = { cantidad: 0, monto: 0 };
        }
        acc[fecha].cantidad++;
        acc[fecha].monto += parseFloat(gasto.monto || 0);
        return acc;
      }, {});

      // Calcular tendencias
      const fechas = Object.keys(gastosPorFecha).sort();
      const montos = fechas.map(fecha => gastosPorFecha[fecha].monto);
      const cantidades = fechas.map(fecha => gastosPorFecha[fecha].cantidad);

      return {
        periodo: { fecha_inicio: fechaInicio, fecha_fin: fechaFin },
        total_dias: fechas.length,
        gastos_por_fecha: gastosPorFecha,
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

  // ANÁLISIS DE CONTROL DE GASTOS GENERALES

  async getAnalisisControlGastos(fechaInicio, fechaFin, presupuesto = null) {
    try {
      const estadisticas = await this.getEstadisticasPorPeriodo(fechaInicio, fechaFin);
      const resumen = await this.getResumenGastosGenerales(fechaInicio, fechaFin);

      let analisisPresupuesto = null;
      if (presupuesto) {
        const porcentajeUsado = (estadisticas.monto_total / presupuesto) * 100;
        const saldoRestante = presupuesto - estadisticas.monto_total;
        
        analisisPresupuesto = {
          presupuesto_asignado: presupuesto,
          monto_gastado: estadisticas.monto_total,
          porcentaje_usado: porcentajeUsado,
          saldo_restante: saldoRestante,
          estado: porcentajeUsado > 100 ? 'sobrepasado' : porcentajeUsado > 80 ? 'alerta' : 'normal'
        };
      }

      return {
        periodo: { fecha_inicio: fechaInicio, fecha_fin: fechaFin },
        estadisticas_generales: estadisticas,
        resumen_por_categoria: resumen.gastos_por_categoria,
        analisis_presupuesto: analisisPresupuesto,
        recomendaciones: this._generarRecomendaciones(estadisticas, analisisPresupuesto)
      };
    } catch (error) {
      throw error;
    }
  }

  // MÉTODO PRIVADO PARA GENERAR RECOMENDACIONES

  _generarRecomendaciones(estadisticas, analisisPresupuesto) {
    const recomendaciones = [];

    if (analisisPresupuesto) {
      if (analisisPresupuesto.porcentaje_usado > 90) {
        recomendaciones.push('Presupuesto de gastos generales casi agotado. Revisar gastos urgentemente.');
      } else if (analisisPresupuesto.porcentaje_usado > 75) {
        recomendaciones.push('Presupuesto de gastos generales en estado de alerta. Controlar gastos futuros.');
      }
    }

    if (estadisticas.promedio_diario_monto > 0) {
      const proyeccionMensual = estadisticas.promedio_diario_monto * 30;
      recomendaciones.push(`Proyección mensual de gastos generales: $${proyeccionMensual.toFixed(2)}`);
    }

    if (recomendaciones.length === 0) {
      recomendaciones.push('Los gastos generales están dentro de parámetros normales.');
    }

    return recomendaciones;
  }
}

// Instancia única del servicio
const gastoGeneralService = new GastoGeneralService();

export default gastoGeneralService;
