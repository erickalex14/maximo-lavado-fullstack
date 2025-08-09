import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import lavadoService from '@/services/lavado.service';
import type { Lavado, PaginatedResponse, CreateLavadoRequest, UpdateLavadoRequest, LavadoFilters } from '@/types';

// Filtros de fecha locales para compatibilidad con la UI
type LavadoDateFilters = { fecha?: string; anio?: number; mes?: number };

export const useLavadoStore = defineStore('lavado', () => {
  // State
  const lavados = ref<Lavado[]>([]);
  const currentLavado = ref<Lavado | null>(null);
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  });
  const loading = ref(false);
  const error = ref<string | null>(null);
  const filters = ref<LavadoFilters>({
    page: 1,
    per_page: 15,
    search: '',
  });

  // Getters
  const hasLavados = computed(() => lavados.value.length > 0);
  const isLoading = computed(() => loading.value);
  const hasError = computed(() => error.value !== null);
  const totalLavados = computed(() => pagination.value.total);
  const currentPage = computed(() => pagination.value.current_page);
  const lastPage = computed(() => pagination.value.last_page);

  // Actions
  const setError = (message: string) => {
    error.value = message;
  };

  const clearError = () => {
    error.value = null;
  };

  const setLoading = (isLoading: boolean) => {
    loading.value = isLoading;
  };

  const setFilters = (newFilters: Partial<LavadoFilters>) => {
    filters.value = { ...filters.value, ...newFilters };
  };

  const clearFilters = () => {
    filters.value = {
      page: 1,
      per_page: 15,
      search: '',
    };
  };

  const fetchLavados = async (customFilters?: LavadoFilters) => {
    try {
      setLoading(true);
      clearError();
      
      const filtersToUse = customFilters || filters.value;
      const response: PaginatedResponse<Lavado> = await lavadoService.getLavados(filtersToUse);
      
      lavados.value = response.data;
      pagination.value = {
        current_page: response.current_page,
        last_page: response.last_page,
        per_page: response.per_page,
        total: response.total,
      };
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al cargar los lavados');
      console.error('Error fetching lavados:', err);
    } finally {
      setLoading(false);
    }
  };

  const fetchLavado = async (id: number) => {
    try {
      setLoading(true);
      clearError();
      
      const resp = await lavadoService.getLavado(id);
      currentLavado.value = resp.data ?? null;
      
      return resp.data;
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al cargar el lavado');
      console.error('Error fetching lavado:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const createLavado = async (data: CreateLavadoRequest) => {
    try {
      setLoading(true);
      clearError();
      
      const resp = await lavadoService.createLavado(data);
      
      // Actualizar la lista de lavados
      await fetchLavados();
      
      return resp.data;
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al crear el lavado');
      console.error('Error creating lavado:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const updateLavado = async (id: number, data: UpdateLavadoRequest) => {
    try {
      setLoading(true);
      clearError();
      
      const resp = await lavadoService.updateLavado(id, data);
      
      // Actualizar en la lista local
      const index = lavados.value.findIndex(l => l.lavado_id === id);
      if (index !== -1) {
        if (resp.data) lavados.value[index] = resp.data;
      }
      
      // Actualizar el lavado actual si coincide
      if (currentLavado.value?.lavado_id === id) {
        currentLavado.value = resp.data ?? null;
      }
      
      return resp.data;
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al actualizar el lavado');
      console.error('Error updating lavado:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const deleteLavado = async (id: number) => {
    try {
      setLoading(true);
      clearError();
      
      await lavadoService.deleteLavado(id);
      
      // Remover de la lista local
      lavados.value = lavados.value.filter(l => l.lavado_id !== id);
      
      // Limpiar lavado actual si coincide
      if (currentLavado.value?.lavado_id === id) {
        currentLavado.value = null;
      }
      
      // Actualizar paginación
      pagination.value.total--;
      
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al eliminar el lavado');
      console.error('Error deleting lavado:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const restoreLavado = async (id: number) => {
    try {
      setLoading(true);
      clearError();
      
      const resp = await lavadoService.restoreLavado(id);
      
      // Actualizar la lista de lavados
      await fetchLavados();
      
      return resp.data;
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al restaurar el lavado');
      console.error('Error restoring lavado:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const fetchLavadosByEmpleado = async (empleadoId: number) => {
    try {
      setLoading(true);
      clearError();
      
      const resp = await lavadoService.getByEmpleado(empleadoId);
      lavados.value = resp.data || [];
      pagination.value = {
        current_page: 1,
        last_page: 1,
        per_page: resp.data?.length ?? 0,
        total: resp.data?.length ?? 0,
      };
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al cargar los lavados del empleado');
      console.error('Error fetching lavados by empleado:', err);
    } finally {
      setLoading(false);
    }
  };

  const fetchLavadosByVehiculo = async (vehiculoId: number) => {
    try {
      setLoading(true);
      clearError();
      
      const resp = await lavadoService.getByVehiculo(vehiculoId);
      lavados.value = resp.data || [];
      pagination.value = {
        current_page: 1,
        last_page: 1,
        per_page: resp.data?.length ?? 0,
        total: resp.data?.length ?? 0,
      };
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al cargar los lavados del vehículo');
      console.error('Error fetching lavados by vehiculo:', err);
    } finally {
      setLoading(false);
    }
  };

  const fetchLavadosByDay = async (dateFilters?: LavadoDateFilters) => {
    try {
      setLoading(true);
      clearError();
      
      const fecha = dateFilters?.fecha ?? new Date().toISOString().slice(0, 10);
      const resp = await lavadoService.getByDay(fecha);
      const lavadosDelDia = resp.data || [];
      lavados.value = lavadosDelDia;
      
      return lavadosDelDia;
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al cargar los lavados del día');
      console.error('Error fetching lavados by day:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const fetchLavadosByWeek = async (dateFilters?: LavadoDateFilters) => {
    try {
      setLoading(true);
      clearError();
      
      const fecha = dateFilters?.fecha ?? new Date().toISOString().slice(0, 10);
      const resp = await lavadoService.getByWeek(fecha);
      const lavadosDeLaSemana = resp.data || [];
      lavados.value = lavadosDeLaSemana;
      
      return lavadosDeLaSemana;
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al cargar los lavados de la semana');
      console.error('Error fetching lavados by week:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const fetchLavadosByMonth = async (dateFilters?: LavadoDateFilters) => {
    try {
      setLoading(true);
      clearError();
      
      const anio = dateFilters?.anio ?? new Date().getFullYear();
      const mes = dateFilters?.mes ?? (new Date().getMonth() + 1);
      const resp = await lavadoService.getByMonth(anio, mes);
      const lavadosDelMes = resp.data || [];
      lavados.value = lavadosDelMes;
      
      return lavadosDelMes;
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al cargar los lavados del mes');
      console.error('Error fetching lavados by month:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const fetchStats = async () => {
    try {
      setLoading(true);
      clearError();
      const resp = await lavadoService.getStats();
      const raw = resp.data || {};
      // Backend keys: total_hoy, total_mes, ingresos_mes, promedio_diario
      return {
        lavados_hoy: raw.total_hoy ?? raw.lavados_hoy ?? 0,
        lavados_mes: raw.total_mes ?? raw.lavados_mes ?? 0,
        ingresos_hoy: raw.ingresos_hoy ?? raw.total_ingresos_hoy ?? 0,
        ingresos_mes: raw.ingresos_mes ?? raw.total_ingresos_mes ?? 0,
        promedio_diario: raw.promedio_diario ?? 0,
        migracion: raw.migracion
      };
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al cargar las estadísticas');
      console.error('Error fetching lavado stats:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const setCurrentLavado = (lavado: Lavado | null) => {
    currentLavado.value = lavado;
  };

  const clearCurrentLavado = () => {
    currentLavado.value = null;
  };

  const changePage = async (page: number) => {
    setFilters({ page });
    await fetchLavados();
  };

  const changePerPage = async (perPage: number) => {
    setFilters({ page: 1, per_page: perPage });
    await fetchLavados();
  };

  const search = async (searchTerm: string) => {
    setFilters({ page: 1, search: searchTerm });
    await fetchLavados();
  };

  // Reset store
  const $reset = () => {
    lavados.value = [];
    currentLavado.value = null;
    pagination.value = {
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0,
    };
    loading.value = false;
    error.value = null;
    filters.value = {
      page: 1,
      per_page: 15,
      search: '',
    };
  };

  return {
    // State
    lavados,
    currentLavado,
    pagination,
    loading,
    error,
    filters,
    
    // Getters
    hasLavados,
    isLoading,
    hasError,
    totalLavados,
    currentPage,
    lastPage,
    
    // Actions
    setError,
    clearError,
    setLoading,
    setFilters,
    clearFilters,
    fetchLavados,
    fetchLavado,
    createLavado,
    updateLavado,
    deleteLavado,
    restoreLavado,
    fetchLavadosByEmpleado,
    fetchLavadosByVehiculo,
    fetchLavadosByDay,
    fetchLavadosByWeek,
    fetchLavadosByMonth,
    fetchStats,
    setCurrentLavado,
    clearCurrentLavado,
    changePage,
    changePerPage,
    search,
    $reset,
  };
});
