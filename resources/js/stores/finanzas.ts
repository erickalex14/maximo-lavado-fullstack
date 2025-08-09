import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import finanzasService from '@/services/finanzas.service';
import { buildPagination, emptyPagination, extractErrorMessage } from './helpers/storeHelpers';
import type { 
  IngresoFilters, 
  EgresoFilters, 
  GastoGeneralFilters,
  
} from '@/types';
import type { BalanceFilters } from '@/services/finanzas.service';
import type { 
  Ingreso, 
  Egreso, 
  GastoGeneral,
  CreateIngresoRequest,
  UpdateIngresoRequest,
  CreateEgresoRequest,
  UpdateEgresoRequest,
  CreateGastoGeneralRequest,
  UpdateGastoGeneralRequest
} from '@/types';

export const useFinanzasStore = defineStore('finanzas', () => {
  // State
  const ingresos = ref<Ingreso[]>([]);
  const egresos = ref<Egreso[]>([]);
  const gastosGenerales = ref<GastoGeneral[]>([]);
  const currentIngreso = ref<Ingreso | null>(null);
  const currentEgreso = ref<Egreso | null>(null);
  const currentGastoGeneral = ref<GastoGeneral | null>(null);
  
  const paginationIngresos = ref(emptyPagination());
  
  const paginationEgresos = ref(emptyPagination());
  
  const paginationGastosGenerales = ref(emptyPagination());

  const loading = ref(false);
  const error = ref<string | null>(null);
  const metricas = ref<any>({});
  const balance = ref<any>({});
  // Estados para endpoints avanzados de balance
  const balanceCategorias = ref<any>({});
  const balanceMensual = ref<any>({});
  const balanceTrimestral = ref<any>({});
  const balanceAnual = ref<any>({});
  const flujoCaja = ref<any>({});
  const indicadoresFinancieros = ref<any>({});
  const comparativoMensual = ref<any>({});
  const proyeccion = ref<any>({});
  const resumenCompleto = ref<any>({});
  
  const filtersIngresos = ref<IngresoFilters>({
    page: 1,
    per_page: 15,
    search: '',
  });
  
  const filtersEgresos = ref<EgresoFilters>({
    page: 1,
    per_page: 15,
    search: '',
  });
  
  const filtersGastosGenerales = ref<GastoGeneralFilters>({
    page: 1,
    per_page: 15,
    search: '',
  });

  // Getters
  const hasIngresos = computed(() => ingresos.value.length > 0);
  const hasEgresos = computed(() => egresos.value.length > 0);
  const hasGastosGenerales = computed(() => gastosGenerales.value.length > 0);
  const isLoading = computed(() => loading.value);
  const hasError = computed(() => error.value !== null);
  
  const totalIngresos = computed(() => paginationIngresos.value.total);
  const totalEgresos = computed(() => paginationEgresos.value.total);
  const totalGastosGenerales = computed(() => paginationGastosGenerales.value.total);
  
  const totalMontosIngresos = computed(() => 
    ingresos.value.reduce((sum, i) => sum + i.monto, 0)
  );
  
  const totalMontosEgresos = computed(() => 
    egresos.value.reduce((sum, e) => sum + e.monto, 0)
  );
  
  const totalMontosGastosGenerales = computed(() => 
    gastosGenerales.value.reduce((sum, g) => sum + g.monto, 0)
  );
  
  const balanceNeto = computed(() => 
    totalMontosIngresos.value - totalMontosEgresos.value - totalMontosGastosGenerales.value
  );

  // Actions
  const setLoading = (state: boolean) => {
    loading.value = state;
  };

  const setError = (err: string | null) => {
    error.value = err;
  };

  const clearError = () => {
    error.value = null;
  };

  // === INGRESOS ===
  const fetchIngresos = async (filters?: IngresoFilters) => {
    try {
      setLoading(true);
      clearError();
      const response = await finanzasService.getIngresos(filters);
      ingresos.value = response.data || [];
      paginationIngresos.value = buildPagination(response);
    } catch (err) {
      setError(extractErrorMessage(err, 'Error al cargar ingresos'));
      console.error('Error fetching ingresos:', err);
    } finally {
      setLoading(false);
    }
  };

  const fetchIngreso = async (id: number) => {
    try {
      setLoading(true);
      clearError();
      
      const response = await finanzasService.getIngreso(id);
      if (response.data) {
        currentIngreso.value = response.data;
      }
      
      return response.data;
    } catch (err: any) {
      setError(err.message || 'Error al cargar ingreso');
      console.error('Error fetching ingreso:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const createIngreso = async (data: CreateIngresoRequest) => {
    try {
      setLoading(true);
      clearError();
      
      const response = await finanzasService.createIngreso(data);
      
      if (response.data) {
        ingresos.value.unshift(response.data);
        paginationIngresos.value.total += 1;
      }
      
      return response.data;
    } catch (err: any) {
      setError(err.message || 'Error al crear ingreso');
      console.error('Error creating ingreso:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const updateIngreso = async (id: number, data: UpdateIngresoRequest) => {
    try {
      setLoading(true);
      clearError();
      
      const response = await finanzasService.updateIngreso(id, data);
      
      if (response.data) {
        const index = ingresos.value.findIndex(i => i.ingreso_id === id);
        if (index !== -1) {
          ingresos.value[index] = response.data;
        }
        
        if (currentIngreso.value?.ingreso_id === id) {
          currentIngreso.value = response.data;
        }
      }
      
      return response.data;
    } catch (err: any) {
      setError(err.message || 'Error al actualizar ingreso');
      console.error('Error updating ingreso:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const deleteIngreso = async (id: number) => {
    try {
      setLoading(true);
      clearError();
      
      await finanzasService.deleteIngreso(id);
      
      const index = ingresos.value.findIndex(i => i.ingreso_id === id);
      if (index !== -1) {
        ingresos.value.splice(index, 1);
        paginationIngresos.value.total -= 1;
      }
      
      if (currentIngreso.value?.ingreso_id === id) {
        currentIngreso.value = null;
      }
    } catch (err: any) {
      setError(err.message || 'Error al eliminar ingreso');
      console.error('Error deleting ingreso:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  // === EGRESOS ===
  const fetchEgresos = async (filters?: EgresoFilters) => {
    try {
      setLoading(true);
      clearError();
      const response = await finanzasService.getEgresos(filters);
      egresos.value = response.data || [];
      paginationEgresos.value = buildPagination(response);
    } catch (err) {
      setError(extractErrorMessage(err, 'Error al cargar egresos'));
      console.error('Error fetching egresos:', err);
    } finally {
      setLoading(false);
    }
  };

  const createEgreso = async (data: CreateEgresoRequest) => {
    try {
      setLoading(true);
      clearError();
      
      const response = await finanzasService.createEgreso(data);
      
      if (response.data) {
        egresos.value.unshift(response.data);
        paginationEgresos.value.total += 1;
      }
      
      return response.data;
    } catch (err: any) {
      setError(err.message || 'Error al crear egreso');
      console.error('Error creating egreso:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const deleteEgreso = async (id: number) => {
    try {
      setLoading(true);
      clearError();
      
      await finanzasService.deleteEgreso(id);
      
      const index = egresos.value.findIndex(e => e.egreso_id === id);
      if (index !== -1) {
        egresos.value.splice(index, 1);
        paginationEgresos.value.total -= 1;
      }
      
      if (currentEgreso.value?.egreso_id === id) {
        currentEgreso.value = null;
      }
    } catch (err: any) {
      setError(err.message || 'Error al eliminar egreso');
      console.error('Error deleting egreso:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  // === GASTOS GENERALES ===
  const fetchGastosGenerales = async (filters?: GastoGeneralFilters) => {
    try {
      setLoading(true);
      clearError();
      const response = await finanzasService.getGastosGenerales(filters);
      gastosGenerales.value = response.data || [];
      paginationGastosGenerales.value = buildPagination(response);
    } catch (err) {
      setError(extractErrorMessage(err, 'Error al cargar gastos generales'));
      console.error('Error fetching gastos generales:', err);
    } finally {
      setLoading(false);
    }
  };

  const createGastoGeneral = async (data: CreateGastoGeneralRequest) => {
    try {
      setLoading(true);
      clearError();
      
      const response = await finanzasService.createGastoGeneral(data);
      
      if (response.data) {
        gastosGenerales.value.unshift(response.data);
        paginationGastosGenerales.value.total += 1;
      }
      
      return response.data;
    } catch (err: any) {
      setError(err.message || 'Error al crear gasto general');
      console.error('Error creating gasto general:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const deleteGastoGeneral = async (id: number) => {
    try {
      setLoading(true);
      clearError();
      
      await finanzasService.deleteGastoGeneral(id);
      
      const index = gastosGenerales.value.findIndex(g => g.gasto_general_id === id);
      if (index !== -1) {
        gastosGenerales.value.splice(index, 1);
        paginationGastosGenerales.value.total -= 1;
      }
      
      if (currentGastoGeneral.value?.gasto_general_id === id) {
        currentGastoGeneral.value = null;
      }
    } catch (err: any) {
      setError(err.message || 'Error al eliminar gasto general');
      console.error('Error deleting gasto general:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  // === BALANCE Y MÉTRICAS ===
  const fetchMetricas = async () => {
    try {
      const [metricasIngresos, metricasEgresos, metricasGastos] = await Promise.all([
        finanzasService.getMetricasIngresos(),
        finanzasService.getMetricasEgresos(),
        finanzasService.getMetricasGastosGenerales()
      ]);
      
      metricas.value = {
        ingresos: metricasIngresos.data || {},
        egresos: metricasEgresos.data || {},
        gastos: metricasGastos.data || {}
      };
    } catch (err: any) {
      console.error('Error fetching metricas:', err);
    }
  };

  const fetchBalance = async (filters?: BalanceFilters) => {
    try {
      const response = await finanzasService.getBalanceGeneral(filters);
      balance.value = response.data || {};
    } catch (err) {
      console.error('Error fetching balance:', err);
    }
  };

  // Endpoints avanzados de balance
  const fetchBalanceCategorias = async (filters?: BalanceFilters) => {
    try {
      const response = await finanzasService.getBalancePorCategoria(filters);
      balanceCategorias.value = response.data || {};
    } catch (err) {
      console.error('Error fetching balance categorias:', err);
    }
  };

  const fetchBalanceMensual = async (filters?: BalanceFilters) => {
    try {
      const response = await finanzasService.getBalanceMensual(filters);
      balanceMensual.value = response.data || {};
    } catch (err) {
      console.error('Error fetching balance mensual:', err);
    }
  };

  const fetchBalanceTrimestral = async (filters?: BalanceFilters) => {
    try {
      const response = await finanzasService.getBalanceTrimestral(filters);
      balanceTrimestral.value = response.data || {};
    } catch (err) {
      console.error('Error fetching balance trimestral:', err);
    }
  };

  const fetchBalanceAnual = async (filters?: BalanceFilters) => {
    try {
      const response = await finanzasService.getBalanceAnual(filters);
      balanceAnual.value = response.data || {};
    } catch (err) {
      console.error('Error fetching balance anual:', err);
    }
  };

  const fetchFlujoCaja = async (filters?: BalanceFilters) => {
    try {
      const response = await finanzasService.getFlujoCaja(filters);
      flujoCaja.value = response.data || {};
    } catch (err) {
      console.error('Error fetching flujo de caja:', err);
    }
  };

  const fetchIndicadoresFinancieros = async (filters?: BalanceFilters) => {
    try {
      const response = await finanzasService.getIndicadoresFinancieros(filters);
      indicadoresFinancieros.value = response.data || {};
    } catch (err) {
      console.error('Error fetching indicadores financieros:', err);
    }
  };

  const fetchComparativoMensual = async (filters?: BalanceFilters) => {
    try {
      const response = await finanzasService.getComparativoMensual(filters);
      comparativoMensual.value = response.data || {};
    } catch (err) {
      console.error('Error fetching comparativo mensual:', err);
    }
  };

  const fetchProyeccion = async (filters?: BalanceFilters) => {
    try {
      const response = await finanzasService.getProyeccion(filters);
      proyeccion.value = response.data || {};
    } catch (err) {
      console.error('Error fetching proyeccion:', err);
    }
  };

  const fetchResumenCompleto = async (filters?: BalanceFilters) => {
    try {
      const response = await finanzasService.getResumenCompleto(filters);
      resumenCompleto.value = response.data || {};
    } catch (err) {
      console.error('Error fetching resumen completo:', err);
    }
  };

  const clearCurrents = () => {
    currentIngreso.value = null;
    currentEgreso.value = null;
    currentGastoGeneral.value = null;
  };

  return {
    // State
    ingresos,
    egresos,
    gastosGenerales,
    currentIngreso,
    currentEgreso,
    currentGastoGeneral,
    paginationIngresos,
    paginationEgresos,
    paginationGastosGenerales,
    loading,
    error,
    metricas,
    balance,
  filtersIngresos,
    filtersEgresos,
    filtersGastosGenerales,
  balanceCategorias,
  balanceMensual,
  balanceTrimestral,
  balanceAnual,
  flujoCaja,
  indicadoresFinancieros,
  comparativoMensual,
  proyeccion,
  resumenCompleto,
    
    // Getters
    hasIngresos,
    hasEgresos,
    hasGastosGenerales,
    isLoading,
    hasError,
    totalIngresos,
    totalEgresos,
    totalGastosGenerales,
    totalMontosIngresos,
    totalMontosEgresos,
    totalMontosGastosGenerales,
    balanceNeto,
    
    // Actions
    setLoading,
    setError,
    clearError,
    fetchIngresos,
    fetchIngreso,
    createIngreso,
    updateIngreso,
    deleteIngreso,
    fetchEgresos,
    createEgreso,
    deleteEgreso,
    fetchGastosGenerales,
    createGastoGeneral,
    deleteGastoGeneral,
    fetchMetricas,
  fetchBalance,
  fetchBalanceCategorias,
  fetchBalanceMensual,
  fetchBalanceTrimestral,
  fetchBalanceAnual,
  fetchFlujoCaja,
  fetchIndicadoresFinancieros,
  fetchComparativoMensual,
  fetchProyeccion,
  fetchResumenCompleto,
    clearCurrents
  };
});
