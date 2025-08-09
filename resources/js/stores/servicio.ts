import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import servicioService from '@/services/servicio.service';
import type { Servicio, ServicioPrecio, PaginatedResponse, CreateServicioRequest, UpdateServicioRequest, FilterOptions } from '@/types';

// Filtros específicos para servicios
interface ServicioFilters extends FilterOptions {
  activo?: boolean;
}

export const useServicioStore = defineStore('servicio', () => {
  // State
  const servicios = ref<Servicio[]>([]);
  const serviciosActivos = ref<Servicio[]>([]);
  const serviciosTrashed = ref<Servicio[]>([]);
  const currentServicio = ref<Servicio | null>(null);
  const preciosServicio = ref<ServicioPrecio[]>([]);
  const stats = ref<any | null>(null);

  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0,
  });

  const filters = ref<ServicioFilters>({ page: 1, per_page: 15, search: '' });

  const loading = ref(false);
  const loadingPrecios = ref(false);
  const error = ref<string | null>(null);

  // Getters
  const hasServicios = computed(() => servicios.value.length > 0);
  const hasActivos = computed(() => serviciosActivos.value.length > 0);
  const hasTrashed = computed(() => serviciosTrashed.value.length > 0);
  const isLoading = computed(() => loading.value);

  // Internal helpers
  function setLoading(val: boolean) { loading.value = val; }
  function setError(msg: string | null) { error.value = msg; }
  function clearError() { error.value = null; }

  // Actions
  async function fetchServicios(custom?: Partial<ServicioFilters>) {
    try {
      setLoading(true); clearError();
      if (custom) filters.value = { ...filters.value, ...custom };
      const resp: PaginatedResponse<Servicio> = await servicioService.getServicios(filters.value);
      servicios.value = resp.data;
      pagination.value = {
        current_page: resp.current_page,
        last_page: resp.last_page,
        per_page: resp.per_page,
        total: resp.total,
        from: resp.from,
        to: resp.to,
      };
    } catch (e: any) {
      setError(e.response?.data?.message || 'Error al cargar servicios');
      console.error('fetchServicios error', e);
    } finally { setLoading(false); }
  }

  async function fetchServiciosActivos() {
    try {
      const resp = await servicioService.getServiciosActivos();
      serviciosActivos.value = resp.data || [];
    } catch (e) { console.error('fetchServiciosActivos error', e); }
  }

  async function fetchStats() {
    try { const resp = await servicioService.getStats(); stats.value = resp.data || null; }
    catch (e) { console.error('fetchStats error', e); }
  }

  async function fetchTrashed() {
    try { const resp = await servicioService.getTrashedServicios(); serviciosTrashed.value = resp.data || []; }
    catch (e) { console.error('fetchTrashedServicios error', e); }
  }

  async function getById(id: number) {
    try {
      setLoading(true); clearError();
      const resp = await servicioService.getServicioById(id);
      currentServicio.value = resp.data || null;
      return currentServicio.value;
    } catch (e: any) {
      setError(e.response?.data?.message || 'Error al obtener servicio');
      throw e;
    } finally { setLoading(false); }
  }

  async function create(data: CreateServicioRequest) {
    try {
      setLoading(true); clearError();
      const resp = await servicioService.createServicio(data);
      if (resp.data) servicios.value.unshift(resp.data);
      return resp.data;
    } catch (e: any) { setError(e.response?.data?.message || 'Error al crear servicio'); throw e; }
    finally { setLoading(false); }
  }

  async function update(id: number, data: UpdateServicioRequest) {
    try {
      setLoading(true); clearError();
      const resp = await servicioService.updateServicio(id, data);
      if (resp.data) {
        const idx = servicios.value.findIndex(s => s.id === id);
        if (idx !== -1) servicios.value[idx] = resp.data;
        if (currentServicio.value?.id === id) currentServicio.value = resp.data;
      }
      return resp.data;
    } catch (e: any) { setError(e.response?.data?.message || 'Error al actualizar servicio'); throw e; }
    finally { setLoading(false); }
  }

  async function toggleActivo(id: number) {
    try {
      const resp = await servicioService.toggleActivo(id);
      if (resp.data) {
        const idx = servicios.value.findIndex(s => s.id === id);
        if (idx !== -1) servicios.value[idx] = resp.data;
        if (currentServicio.value?.id === id) currentServicio.value = resp.data;
      }
      return resp.data;
    } catch (e) { console.error('toggleActivo error', e); }
  }

  async function remove(id: number) {
    try {
      setLoading(true); clearError();
      await servicioService.deleteServicio(id);
      servicios.value = servicios.value.filter(s => s.id !== id);
      pagination.value.total = Math.max(0, pagination.value.total - 1);
      if (currentServicio.value?.id === id) currentServicio.value = null;
    } catch (e: any) { setError(e.response?.data?.message || 'Error al eliminar servicio'); throw e; }
    finally { setLoading(false); }
  }

  async function restore(id: number) {
    try {
      const resp = await servicioService.restoreServicio(id);
      if (resp.data) {
        servicios.value.unshift(resp.data);
        serviciosTrashed.value = serviciosTrashed.value.filter(s => s.id !== id);
      }
      return resp.data;
    } catch (e) { console.error('restoreServicio error', e); }
  }

  // Precios
  async function fetchPrecios(servicioId: number) {
    try {
      loadingPrecios.value = true;
      const resp = await servicioService.getPrecios(servicioId);
      preciosServicio.value = resp.data || [];
    } catch (e) { console.error('fetchPrecios error', e); }
    finally { loadingPrecios.value = false; }
  }

  async function updatePrecio(servicioId: number, tipoVehiculoId: number, precio: number) {
    const resp = await servicioService.updatePrecio(servicioId, tipoVehiculoId, precio);
    if (resp.data) {
      const idx = preciosServicio.value.findIndex(p => p.tipo_vehiculo_id === tipoVehiculoId);
      if (idx !== -1) preciosServicio.value[idx] = resp.data; else preciosServicio.value.push(resp.data);
    }
    return resp.data;
  }

  async function deletePrecio(servicioId: number, tipoVehiculoId: number) {
    await servicioService.deletePrecio(servicioId, tipoVehiculoId);
    preciosServicio.value = preciosServicio.value.filter(p => p.tipo_vehiculo_id !== tipoVehiculoId);
  }

  function setFilters(partial: Partial<ServicioFilters>) { filters.value = { ...filters.value, ...partial }; }
  async function changePage(page: number) { setFilters({ page }); await fetchServicios(); }
  async function changePerPage(perPage: number) { setFilters({ page: 1, per_page: perPage }); await fetchServicios(); }
  async function search(term: string) { setFilters({ page: 1, search: term }); await fetchServicios(); }

  function $reset() {
    servicios.value = [];
    serviciosActivos.value = [];
    serviciosTrashed.value = [];
    currentServicio.value = null;
    preciosServicio.value = [];
    stats.value = null;
    pagination.value = { current_page: 1, last_page: 1, per_page: 15, total: 0, from: 0, to: 0 };
    filters.value = { page: 1, per_page: 15, search: '' };
    loading.value = false; loadingPrecios.value = false; error.value = null;
  }

  return {
    // State
    servicios,
    serviciosActivos,
    serviciosTrashed,
    currentServicio,
    preciosServicio,
    stats,
    pagination,
    filters,
    loading,
    loadingPrecios,
    error,

    // Getters
    hasServicios,
    hasActivos,
    hasTrashed,
    isLoading,

    // Actions
    fetchServicios,
    fetchServiciosActivos,
    fetchStats,
    fetchTrashed,
    getById,
    create,
    update,
    toggleActivo,
    remove,
    restore,
    fetchPrecios,
    updatePrecio,
    deletePrecio,
    changePage,
    changePerPage,
    search,
    setFilters,
    clearError,
    $reset,
  };
});
