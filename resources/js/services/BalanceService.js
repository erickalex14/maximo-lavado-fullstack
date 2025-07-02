import { BaseService } from './BaseService';

//SERVICIO PARA GESTIONAR BALANCE Y ANÁLISIS FINANCIERO

class BalanceService extends BaseService {
  constructor() {
    super('/balance');
  }

  // NOTA: BalanceService NO hereda métodos CRUD tradicionales ya que el balance
  // es un módulo de análisis financiero sin entidades persistentes propias

  // MÉTODOS DE BALANCE FINANCIERO

  // OBTENER BALANCE GENERAL

  async getBalanceGeneral(params = {}) {
    return this.apiCall('GET', '/general', {}, params);
  }

  // OBTENER BALANCE POR CATEGORÍA

  async getBalancePorCategoria(params = {}) {
    return this.apiCall('GET', '/categorias', {}, params);
  }

  // OBTENER BALANCE POR MES

  async getBalancePorMes(anio = null, params = {}) {
    const year = anio || new Date().getFullYear();
    return this.apiCall('GET', '/mensual', {}, { año: year, ...params });
  }

  // OBTENER BALANCE POR TRIMESTRE

  async getBalancePorTrimestre(anio = null, params = {}) {
    const year = anio || new Date().getFullYear();
    return this.apiCall('GET', '/trimestral', {}, { año: year, ...params });
  }

  // OBTENER BALANCE ANUAL

  async getBalanceAnual(anio = null, params = {}) {
    const year = anio || new Date().getFullYear();
    return this.apiCall('GET', '/anual', {}, { año: year, ...params });
  }

  // OBTENER FLUJO DE CAJA

  async getFlujoCaja(fechaInicio = null, fechaFin = null, params = {}) {
    const queryParams = { ...params };
    if (fechaInicio) queryParams.fecha_inicio = fechaInicio;
    if (fechaFin) queryParams.fecha_fin = fechaFin;
    
    return this.apiCall('GET', '/flujo-caja', {}, queryParams);
  }

  // MÉTODOS DE CONVENIENCIA PARA ANÁLISIS TEMPORAL

  // OBTENER BALANCE DEL MES ACTUAL

  async getBalanceMesActual(params = {}) {
    const now = new Date();
    return this.getBalancePorMes(now.getFullYear(), { 
      mes: now.getMonth() + 1, 
      ...params 
    });
  }

  // OBTENER BALANCE DEL TRIMESTRE ACTUAL

  async getBalanceTrimestreActual(params = {}) {
    const now = new Date();
    const trimestre = Math.ceil((now.getMonth() + 1) / 3);
    return this.getBalancePorTrimestre(now.getFullYear(), { 
      trimestre, 
      ...params 
    });
  }

  // OBTENER BALANCE DEL AÑO ACTUAL

  async getBalanceAnioActual(params = {}) {
    return this.getBalanceAnual(new Date().getFullYear(), params);
  }

  // OBTENER FLUJO DE CAJA DEL MES

  async getFlujoCajaMes(anio = null, mes = null, params = {}) {
    const now = new Date();
    const year = anio || now.getFullYear();
    const month = mes || (now.getMonth() + 1);
    
    const fechaInicio = `${year}-${month.toString().padStart(2, '0')}-01`;
    const fechaFin = new Date(year, month, 0).toISOString().split('T')[0];
    
    return this.getFlujoCaja(fechaInicio, fechaFin, params);
  }

  // OBTENER FLUJO DE CAJA DEL AÑO

  async getFlujoCajaAnio(anio = null, params = {}) {
    const year = anio || new Date().getFullYear();
    const fechaInicio = `${year}-01-01`;
    const fechaFin = `${year}-12-31`;
    
    return this.getFlujoCaja(fechaInicio, fechaFin, params);
  }

  // MÉTODOS DE ANÁLISIS COMPARATIVO

  // COMPARAR BALANCES ENTRE PERÍODOS

  async compararBalances(periodo1, periodo2) {
    try {
      const [balance1, balance2] = await Promise.all([
        this.getBalanceGeneral({
          fecha_inicio: periodo1.fecha_inicio,
          fecha_fin: periodo1.fecha_fin
        }),
        this.getBalanceGeneral({
          fecha_inicio: periodo2.fecha_inicio,
          fecha_fin: periodo2.fecha_fin
        })
      ]);

      const data1 = balance1.data || balance1;
      const data2 = balance2.data || balance2;

      const diferenciaTotalIngresos = (data2.total_ingresos || 0) - (data1.total_ingresos || 0);
      const diferenciaTotalEgresos = (data2.total_egresos || 0) - (data1.total_egresos || 0);
      const diferenciaSaldo = (data2.saldo || 0) - (data1.saldo || 0);

      const porcentajeIngresos = data1.total_ingresos > 0 ? 
        (diferenciaTotalIngresos / data1.total_ingresos) * 100 : 0;
      const porcentajeEgresos = data1.total_egresos > 0 ? 
        (diferenciaTotalEgresos / data1.total_egresos) * 100 : 0;

      return {
        periodo_1: {
          periodo: periodo1,
          datos: data1
        },
        periodo_2: {
          periodo: periodo2,
          datos: data2
        },
        comparacion: {
          diferencia_ingresos: diferenciaTotalIngresos,
          diferencia_egresos: diferenciaTotalEgresos,
          diferencia_saldo: diferenciaSaldo,
          porcentaje_variacion_ingresos: porcentajeIngresos,
          porcentaje_variacion_egresos: porcentajeEgresos,
          tendencia_ingresos: diferenciaTotalIngresos > 0 ? 'incremento' : 
                            diferenciaTotalIngresos < 0 ? 'decremento' : 'estable',
          tendencia_egresos: diferenciaTotalEgresos > 0 ? 'incremento' : 
                           diferenciaTotalEgresos < 0 ? 'decremento' : 'estable',
          tendencia_saldo: diferenciaSaldo > 0 ? 'mejora' : 
                         diferenciaSaldo < 0 ? 'deterioro' : 'estable'
        }
      };
    } catch (error) {
      throw error;
    }
  }

  // OBTENER RESUMEN FINANCIERO COMPLETO

  async getResumenFinanciero(fechaInicio = null, fechaFin = null) {
    try {
      const params = {};
      if (fechaInicio) params.fecha_inicio = fechaInicio;
      if (fechaFin) params.fecha_fin = fechaFin;

      const [balanceGeneral, balancePorCategoria, flujoCaja] = await Promise.all([
        this.getBalanceGeneral(params),
        this.getBalancePorCategoria(params),
        this.getFlujoCaja(fechaInicio, fechaFin)
      ]);

      const balance = balanceGeneral.data || balanceGeneral;
      const categorias = balancePorCategoria.data || balancePorCategoria;
      const flujo = flujoCaja.data || flujoCaja;

      // Calcular indicadores financieros
      const margenOperativo = balance.total_ingresos > 0 ? 
        ((balance.total_ingresos - balance.total_egresos) / balance.total_ingresos) * 100 : 0;
      
      const ratioIngresoEgreso = balance.total_egresos > 0 ? 
        balance.total_ingresos / balance.total_egresos : 0;

      return {
        periodo: fechaInicio && fechaFin ? 
          { fecha_inicio: fechaInicio, fecha_fin: fechaFin } : 'Período completo',
        balance_general: balance,
        balance_por_categoria: categorias,
        flujo_caja: flujo,
        indicadores_financieros: {
          margen_operativo: margenOperativo,
          ratio_ingreso_egreso: ratioIngresoEgreso,
          saldo_neto: balance.saldo || 0,
          estado_financiero: balance.saldo > 0 ? 'positivo' : 
                           balance.saldo < 0 ? 'negativo' : 'equilibrado'
        },
        recomendaciones: this._generarRecomendacionesFinancieras(balance, margenOperativo)
      };
    } catch (error) {
      throw error;
    }
  }

  // OBTENER PROYECCIÓN FINANCIERA

  async getProyeccionFinanciera(mesesProyeccion = 3) {
    try {
      // Obtener datos de los últimos 6 meses para calcular tendencia
      const fechaFin = new Date().toISOString().split('T')[0];
      const fechaInicio = new Date(Date.now() - 6 * 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
      
      const balanceHistorico = await this.getBalanceGeneral({
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin
      });

      const datos = balanceHistorico.data || balanceHistorico;
      
      // Calcular promedios mensuales
      const promedioIngresosMensual = (datos.total_ingresos || 0) / 6;
      const promedioEgresosMensual = (datos.total_egresos || 0) / 6;
      const promedioSaldoMensual = promedioIngresosMensual - promedioEgresosMensual;

      // Proyectar para los próximos meses
      const proyeccionIngresos = promedioIngresosMensual * mesesProyeccion;
      const proyeccionEgresos = promedioEgresosMensual * mesesProyeccion;
      const proyeccionSaldo = promedioSaldoMensual * mesesProyeccion;

      const fechaProyeccion = new Date(Date.now() + mesesProyeccion * 30 * 24 * 60 * 60 * 1000)
        .toISOString().split('T')[0];

      return {
        base_calculo: {
          periodo_analisis: { fecha_inicio: fechaInicio, fecha_fin: fechaFin },
          promedio_ingresos_mensual: promedioIngresosMensual,
          promedio_egresos_mensual: promedioEgresosMensual,
          promedio_saldo_mensual: promedioSaldoMensual
        },
        proyeccion: {
          meses_proyectados: mesesProyeccion,
          ingresos_proyectados: proyeccionIngresos,
          egresos_proyectados: proyeccionEgresos,
          saldo_proyectado: proyeccionSaldo,
          fecha_proyeccion: fechaProyeccion,
          escenarios: {
            optimista: {
              ingresos: proyeccionIngresos * 1.15,
              egresos: proyeccionEgresos * 0.95,
              saldo: (proyeccionIngresos * 1.15) - (proyeccionEgresos * 0.95)
            },
            pesimista: {
              ingresos: proyeccionIngresos * 0.85,
              egresos: proyeccionEgresos * 1.10,
              saldo: (proyeccionIngresos * 0.85) - (proyeccionEgresos * 1.10)
            }
          }
        }
      };
    } catch (error) {
      throw error;
    }
  }

  // OBTENER ANÁLISIS DE RENTABILIDAD

  async getAnalisisRentabilidad(fechaInicio, fechaFin) {
    try {
      const resumen = await this.getResumenFinanciero(fechaInicio, fechaFin);
      const balance = resumen.balance_general;

      const totalIngresos = balance.total_ingresos || 0;
      const totalEgresos = balance.total_egresos || 0;
      const utilidadNeta = totalIngresos - totalEgresos;
      
      const margenUtilidadNeta = totalIngresos > 0 ? (utilidadNeta / totalIngresos) * 100 : 0;
      const puntoEquilibrio = totalEgresos; // Ingresos necesarios para cubrir gastos

      return {
        periodo: { fecha_inicio: fechaInicio, fecha_fin: fechaFin },
        indicadores_rentabilidad: {
          total_ingresos: totalIngresos,
          total_egresos: totalEgresos,
          utilidad_neta: utilidadNeta,
          margen_utilidad_neta: margenUtilidadNeta,
          punto_equilibrio: puntoEquilibrio,
          roi: totalEgresos > 0 ? (utilidadNeta / totalEgresos) * 100 : 0
        },
        clasificacion_rentabilidad: this._clasificarRentabilidad(margenUtilidadNeta),
        recomendaciones_rentabilidad: this._generarRecomendacionesRentabilidad(margenUtilidadNeta, utilidadNeta)
      };
    } catch (error) {
      throw error;
    }
  }

  // MÉTODOS PRIVADOS PARA ANÁLISIS

  _generarRecomendacionesFinancieras(balance, margenOperativo) {
    const recomendaciones = [];

    if (margenOperativo < 0) {
      recomendaciones.push('Los egresos superan los ingresos. Revisar gastos y estrategias de ingresos urgentemente.');
    } else if (margenOperativo < 10) {
      recomendaciones.push('Margen operativo bajo. Considerar optimizar gastos o aumentar ingresos.');
    } else if (margenOperativo > 30) {
      recomendaciones.push('Excelente margen operativo. Considerar reinversión para crecimiento.');
    }

    if (balance.saldo > 0) {
      recomendaciones.push('Balance positivo. Considerar estrategias de inversión o expansión.');
    } else if (balance.saldo < 0) {
      recomendaciones.push('Balance negativo. Implementar plan de recuperación financiera.');
    }

    if (recomendaciones.length === 0) {
      recomendaciones.push('La situación financiera se encuentra en parámetros normales.');
    }

    return recomendaciones;
  }

  _clasificarRentabilidad(margenUtilidadNeta) {
    if (margenUtilidadNeta < 0) return 'deficitaria';
    if (margenUtilidadNeta < 5) return 'baja';
    if (margenUtilidadNeta < 15) return 'moderada';
    if (margenUtilidadNeta < 25) return 'buena';
    return 'excelente';
  }

  _generarRecomendacionesRentabilidad(margen, utilidad) {
    const recomendaciones = [];

    if (margen < 0) {
      recomendaciones.push('Empresa en pérdidas. Reducir costos y aumentar ingresos inmediatamente.');
    } else if (margen < 5) {
      recomendaciones.push('Rentabilidad muy baja. Revisar estructura de costos y precios.');
    } else if (margen > 20) {
      recomendaciones.push('Excelente rentabilidad. Considerar expansión o mejoras en el servicio.');
    }

    if (utilidad > 0) {
      recomendaciones.push('Generar reservas para contingencias y futuras inversiones.');
    }

    return recomendaciones;
  }
}

// Instancia única del servicio
const balanceService = new BalanceService();

export default balanceService;
