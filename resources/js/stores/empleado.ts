import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import empleadoService from '@/services/empleado.service';
import type { Empleado, PaginatedResponse, CreateEmpleadoRequest, UpdateEmpleadoRequest, EmpleadoFilters } from '@/types';

export const useEmpleadoStore = defineStore('empleado', () => {
  // State
  const empleados = ref<Empleado[]>([]);
  const currentEmpleado = ref<Empleado | null>(null);
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  });
  const loading = ref(false);
  const error = ref<string | null>(null);
  const filters = ref<EmpleadoFilters>({
    page: 1,
    per_page: 15,
    search: '',
  });

  // Getters
  const hasEmpleados = computed(() => empleados.value.length > 0);
  const isLoading = computed(() => loading.value);
  const hasError = computed(() => error.value !== null);
  const totalEmpleados = computed(() => pagination.value.total);
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

  const setFilters = (newFilters: Partial<EmpleadoFilters>) => {
    filters.value = { ...filters.value, ...newFilters };
  };

  const clearFilters = () => {
    filters.value = {
      page: 1,
      per_page: 15,
      search: '',
    };
  };

  const fetchEmpleados = async (customFilters?: EmpleadoFilters) => {
    try {
      setLoading(true);
      clearError();
      
      const filtersToUse = customFilters || filters.value;
      const response: PaginatedResponse<Empleado> = await empleadoService.getEmpleados(filtersToUse);
      
      empleados.value = response.data;
      pagination.value = {
        current_page: response.current_page,
        last_page: response.last_page,
        per_page: response.per_page,
        total: response.total,
      };
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al cargar los empleados');
      console.error('Error fetching empleados:', err);
    } finally {
      setLoading(false);
    }
  };

  const fetchEmpleado = async (id: number) => {
    try {
      setLoading(true);
      clearError();
      
  const resp = await empleadoService.getEmpleado(id);
  currentEmpleado.value = resp.data ?? null;
  return resp.data ?? null;
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al cargar el empleado');
      console.error('Error fetching empleado:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const createEmpleado = async (data: CreateEmpleadoRequest) => {
    try {
      setLoading(true);
      clearError();
      
  const resp = await empleadoService.createEmpleado(data);
      
      // Actualizar la lista de empleados
  await fetchEmpleados();
  return resp.data ?? null;
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al crear el empleado');
      console.error('Error creating empleado:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const updateEmpleado = async (id: number, data: UpdateEmpleadoRequest) => {
    try {
      setLoading(true);
      clearError();
      
  const resp = await empleadoService.updateEmpleado(id, data);
  const updatedEmpleado = resp.data;
      
      // Actualizar en la lista local
      const index = empleados.value.findIndex(e => e.empleado_id === id);
      if (index !== -1 && updatedEmpleado) {
        empleados.value[index] = updatedEmpleado;
      }
      
      // Actualizar el empleado actual si coincide
      if (currentEmpleado.value?.empleado_id === id) {
        currentEmpleado.value = updatedEmpleado ?? null;
      }
      
  return updatedEmpleado ?? null;
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al actualizar el empleado');
      console.error('Error updating empleado:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const deleteEmpleado = async (id: number) => {
    try {
      setLoading(true);
      clearError();
      
      await empleadoService.deleteEmpleado(id);
      
      // Remover de la lista local
      empleados.value = empleados.value.filter(e => e.empleado_id !== id);
      
      // Limpiar empleado actual si coincide
      if (currentEmpleado.value?.empleado_id === id) {
        currentEmpleado.value = null;
      }
      
      // Actualizar paginación
      pagination.value.total--;
      
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al eliminar el empleado');
      console.error('Error deleting empleado:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const setCurrentEmpleado = (empleado: Empleado | null) => {
    currentEmpleado.value = empleado;
  };

  const clearCurrentEmpleado = () => {
    currentEmpleado.value = null;
  };

  const changePage = async (page: number) => {
    setFilters({ page });
    await fetchEmpleados();
  };

  const changePerPage = async (perPage: number) => {
    setFilters({ page: 1, per_page: perPage });
    await fetchEmpleados();
  };

  const search = async (searchTerm: string) => {
    setFilters({ page: 1, search: searchTerm });
    await fetchEmpleados();
  };

  const filterByTipoSalario = async (tipoSalario: EmpleadoFilters['tipo_salario']) => {
    setFilters({ page: 1, tipo_salario: tipoSalario });
    await fetchEmpleados();
  };

  // Reset store
  const $reset = () => {
    empleados.value = [];
    currentEmpleado.value = null;
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
    empleados,
    currentEmpleado,
    pagination,
    loading,
    error,
    filters,
    
    // Getters
    hasEmpleados,
    isLoading,
    hasError,
    totalEmpleados,
    currentPage,
    lastPage,
    
    // Actions
    setError,
    clearError,
    setLoading,
    setFilters,
    clearFilters,
    fetchEmpleados,
    fetchEmpleado,
    createEmpleado,
    updateEmpleado,
    deleteEmpleado,
    setCurrentEmpleado,
    clearCurrentEmpleado,
    changePage,
    changePerPage,
    search,
    filterByTipoSalario,
    $reset,
  };
});
