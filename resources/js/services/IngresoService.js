import { BaseService } from './BaseService';

//SERVICIO PARA GESTIONAR INGRESOS

class IngresoService extends BaseService {
  constructor() {
    super('/ingresos');
  }

  // LOS METODOS CRUD BÁSICOS YA ESTÁN HEREDADOS DEL BaseService:
  // index(), store(), show(), update(), destroy(), trashed(), restore(), metricas()

  // METODOS DE CONVENIENCIA PARA INGRESOS

  // OBTENER INGRESOS POR RANGO DE FECHAS

  async getIngresosPorRangoFechas(fechaInicio, fechaFin, params = {}) {
    return this.index({
      fecha_inicio: fechaInicio,
      fecha_fin: fechaFin,
      ...params
    });
  }

  // OBTENER INGRESOS POR TIPO

  async getIngresosPorTipo(tipo, params = {}) {
    return this.index({ tipo, ...params });
  }

  // OBTENER INGRESOS DEL DIA

  async getIngresosDelDia(fecha = null, params = {}) {
    const fechaIngreso = fecha || new Date().toISOString().split('T')[0];
    return this.getIngresosPorRangoFechas(fechaIngreso, fechaIngreso, params);
  }

  // OBTENER INGRESOS DEL MES

  async getIngresosDelMes(anio = null, mes = null, params = {}) {
    const now = new Date();
    const year = anio || now.getFullYear();
    const month = mes || (now.getMonth() + 1);
    
    const fechaInicio = `${year}-${month.toString().padStart(2, '0')}-01`;
    const fechaFin = new Date(year, month, 0).toISOString().split('T')[0];
    
    return this.getIngresosPorRangoFechas(fechaInicio, fechaFin, params);
  }

  // OBTENER INGRESOS DEL AÑO

  async getIngresosDelAnio(anio = null, params = {}) {
    const year = anio || new Date().getFullYear();
    const fechaInicio = `${year}-01-01`;
    const fechaFin = `${year}-12-31`;
    
    return this.getIngresosPorRangoFechas(fechaInicio, fechaFin, params);
  }

  // BUSCAR INGRESOS POR CONCEPTO O DESCRIPCIÓN

  async buscarIngresos(termino, params = {}) {
    return this.index({ buscar: termino, ...params });
  }

  // OBTENER INGRESOS POR MONTO MÍNIMO

  async getIngresosPorMontoMinimo(montoMinimo, params = {}) {
    return this.index({ monto_minimo: montoMinimo, ...params });
  }

  // OBTENER INGRESOS POR MONTO MÁXIMO

  async getIngresosPorMontoMaximo(montoMaximo, params = {}) {
    return this.index({ monto_maximo: montoMaximo, ...params });
  }

  // OBTENER INGRESOS EN RANGO DE MONTOS

  async getIngresosPorRangoMontos(montoMinimo, montoMaximo, params = {}) {
    return this.index({
      monto_minimo: montoMinimo,
      monto_maximo: montoMaximo,
      ...params
    });
  }

  // OBTENER RESUMEN DE INGRESOS

  async getResumenIngresos(fechaInicio = null, fechaFin = null) {
    try {
      let ingresos;
      if (fechaInicio && fechaFin) {
        ingresos = await this.getIngresosPorRangoFechas(fechaInicio, fechaFin);
      } else {
        ingresos = await this.index();
      }

      const metricas = await this.metricas();

      // Calcular estadísticas
      const listaIngresos = ingresos.data || ingresos;
      const totalIngresos = listaIngresos.length;
      const montoTotal = listaIngresos.reduce((sum, ingreso) => sum + parseFloat(ingreso.monto || 0), 0);
      const promedioMonto = totalIngresos > 0 ? montoTotal / totalIngresos : 0;

      // Agrupar por tipo
      const ingresosPorTipo = listaIngresos.reduce((acc, ingreso) => {
        const tipo = ingreso.tipo || 'Sin tipo';
        if (!acc[tipo]) {
          acc[tipo] = { cantidad: 0, monto: 0 };
        }
        acc[tipo].cantidad++;
        acc[tipo].monto += parseFloat(ingreso.monto || 0);
        return acc;
      }, {});

      return {
        periodo: fechaInicio && fechaFin ? { fecha_inicio: fechaInicio, fecha_fin: fechaFin } : 'Todos los tiempos',
        total_ingresos: totalIngresos,
        monto_total: montoTotal,
        promedio_monto: promedioMonto,
        ingresos_por_tipo: ingresosPorTipo,
        metricas: metricas.data || metricas
      };
    } catch (error) {
      throw error;
    }
  }

  // OBTENER INGRESOS MAYORES

  async getIngresosMayores(limite = 10, params = {}) {
    try {
      const ingresos = await this.index(params);
      const listaIngresos = ingresos.data || ingresos;
      
      return listaIngresos
        .sort((a, b) => parseFloat(b.monto || 0) - parseFloat(a.monto || 0))
        .slice(0, limite);
    } catch (error) {
      throw error;
    }
  }

  // OBTENER ESTADÍSTICAS POR PERÍODO

  async getEstadisticasPorPeriodo(fechaInicio, fechaFin) {
    try {
      const ingresos = await this.getIngresosPorRangoFechas(fechaInicio, fechaFin);
      const listaIngresos = ingresos.data || ingresos;

      // Agrupar por fecha
      const ingresosPorFecha = listaIngresos.reduce((acc, ingreso) => {
        const fecha = ingreso.fecha || ingreso.created_at?.split('T')[0];
        if (!acc[fecha]) {
          acc[fecha] = { cantidad: 0, monto: 0 };
        }
        acc[fecha].cantidad++;
        acc[fecha].monto += parseFloat(ingreso.monto || 0);
        return acc;
      }, {});

      // Calcular tendencias
      const fechas = Object.keys(ingresosPorFecha).sort();
      const montos = fechas.map(fecha => ingresosPorFecha[fecha].monto);
      const cantidades = fechas.map(fecha => ingresosPorFecha[fecha].cantidad);

      return {
        periodo: { fecha_inicio: fechaInicio, fecha_fin: fechaFin },
        total_dias: fechas.length,
        ingresos_por_fecha: ingresosPorFecha,
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

  // OBTENER INGRESOS POR CATEGORÍA

  async getIngresosPorCategoria(categoria, params = {}) {
    return this.index({ categoria, ...params });
  }

  // OBTENER INGRESOS PRIORITARIOS

  async getIngresosPrioritarios(prioridad, params = {}) {
    return this.index({ prioridad, ...params });
  }

  // ANÁLISIS DE CONTROL DE INGRESOS

  async getAnalisisControlIngresos(fechaInicio, fechaFin, metaIngresos = null) {
    try {
      const estadisticas = await this.getEstadisticasPorPeriodo(fechaInicio, fechaFin);
      const resumen = await this.getResumenIngresos(fechaInicio, fechaFin);

      let analisisMeta = null;
      if (metaIngresos) {
        const porcentajeAlcanzado = (estadisticas.monto_total / metaIngresos) * 100;
        const faltante = metaIngresos - estadisticas.monto_total;
        
        analisisMeta = {
          meta_establecida: metaIngresos,
          monto_alcanzado: estadisticas.monto_total,
          porcentaje_alcanzado: porcentajeAlcanzado,
          faltante: faltante > 0 ? faltante : 0,
          estado: porcentajeAlcanzado >= 100 ? 'meta_superada' : porcentajeAlcanzado >= 80 ? 'cerca_de_meta' : 'necesita_mejorar'
        };
      }

      return {
        periodo: { fecha_inicio: fechaInicio, fecha_fin: fechaFin },
        estadisticas_generales: estadisticas,
        resumen_por_tipo: resumen.ingresos_por_tipo,
        analisis_meta: analisisMeta,
        recomendaciones: this._generarRecomendacionesIngresos(estadisticas, analisisMeta)
      };
    } catch (error) {
      throw error;
    }
  }

  // MÉTODO PRIVADO PARA GENERAR RECOMENDACIONES DE INGRESOS

  _generarRecomendacionesIngresos(estadisticas, analisisMeta) {
    const recomendaciones = [];

    if (analisisMeta) {
      if (analisisMeta.porcentaje_alcanzado >= 100) {
        recomendaciones.push('¡Felicitaciones! Has superado la meta de ingresos establecida.');
      } else if (analisisMeta.porcentaje_alcanzado >= 80) {
        recomendaciones.push('Estás cerca de alcanzar la meta. Mantén el buen trabajo.');
      } else if (analisisMeta.porcentaje_alcanzado < 50) {
        recomendaciones.push('Los ingresos están por debajo del 50% de la meta. Revisar estrategias.');
      }
    }

    if (estadisticas.promedio_diario_monto > 0) {
      const proyeccionMensual = estadisticas.promedio_diario_monto * 30;
      recomendaciones.push(`Proyección mensual basada en tendencia actual: $${proyeccionMensual.toFixed(2)}`);
    }

    if (recomendaciones.length === 0) {
      recomendaciones.push('Los ingresos están dentro de parámetros normales.');
    }

    return recomendaciones;
  }
}

// Instancia única del servicio
const ingresoService = new IngresoService();

export default ingresoService;
