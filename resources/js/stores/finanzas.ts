import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import finanzasService, { 
  type IngresoFilters, 
  type EgresoFilters, 
  type GastoGeneralFilters,
  type BalanceFilters 
} from '@/services/finanzas.service';
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
  
  const paginationIngresos = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0
  });
  
  const paginationEgresos = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0
  });
  
  const paginationGastosGenerales = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0
  });

  const loading = ref(false);
  const error = ref<string | null>(null);
  const metricas = ref<any>({});
  const balance = ref<any>({});
  
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
      
      if (response.data) {
        if (Array.isArray(response.data)) {
          ingresos.value = response.data;
        } else {
          ingresos.value = response.data.data || [];
          paginationIngresos.value = {
            current_page: response.data.current_page || 1,
            last_page: response.data.last_page || 1,
            per_page: response.data.per_page || 15,
            total: response.data.total || 0,
            from: response.data.from || 0,
            to: response.data.to || 0
          };
        }
      }
    } catch (err: any) {
      setError(err.message || 'Error al cargar ingresos');
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
      
      if (response.data) {
        if (Array.isArray(response.data)) {
          egresos.value = response.data;
        } else {
          egresos.value = response.data.data || [];
          paginationEgresos.value = {
            current_page: response.data.current_page || 1,
            last_page: response.data.last_page || 1,
            per_page: response.data.per_page || 15,
            total: response.data.total || 0,
            from: response.data.from || 0,
            to: response.data.to || 0
          };
        }
      }
    } catch (err: any) {
      setError(err.message || 'Error al cargar egresos');
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
      
      if (response.data) {
        if (Array.isArray(response.data)) {
          gastosGenerales.value = response.data;
        } else {
          gastosGenerales.value = response.data.data || [];
          paginationGastosGenerales.value = {
            current_page: response.data.current_page || 1,
            last_page: response.data.last_page || 1,
            per_page: response.data.per_page || 15,
            total: response.data.total || 0,
            from: response.data.from || 0,
            to: response.data.to || 0
          };
        }
      }
    } catch (err: any) {
      setError(err.message || 'Error al cargar gastos generales');
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
    } catch (err: any) {
      console.error('Error fetching balance:', err);
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
    clearCurrents
  };
});
