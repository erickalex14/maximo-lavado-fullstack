import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import tipoVehiculoService from '@/services/tipo-vehiculo.service';
import type { TipoVehiculo, PaginatedResponse, CreateTipoVehiculoRequest, UpdateTipoVehiculoRequest, FilterOptions } from '@/types';

interface TipoVehiculoFilters extends FilterOptions { activo?: boolean }

export const useTipoVehiculoStore = defineStore('tipoVehiculo', () => {
  // State
  const tipos = ref<TipoVehiculo[]>([]);
  const tiposAll = ref<TipoVehiculo[]>([]);
  const tiposTrashed = ref<TipoVehiculo[]>([]);
  const current = ref<TipoVehiculo | null>(null);
  const stats = ref<any | null>(null);

  const pagination = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0, from: 0, to: 0 });
  const filters = ref<TipoVehiculoFilters>({ page: 1, per_page: 15, search: '' });

  const loading = ref(false);
  const error = ref<string | null>(null);

  // Getters
  const hasTipos = computed(() => tipos.value.length > 0);
  const hasAll = computed(() => tiposAll.value.length > 0);
  const hasTrashed = computed(() => tiposTrashed.value.length > 0);

  function setLoading(v: boolean) { loading.value = v; }
  function setError(msg: string | null) { error.value = msg; }
  function clearError() { error.value = null; }

  async function fetchTipos(custom?: Partial<TipoVehiculoFilters>) {
    try {
      setLoading(true); clearError();
      if (custom) filters.value = { ...filters.value, ...custom };
      const resp: PaginatedResponse<TipoVehiculo> = await tipoVehiculoService.getTiposVehiculos(filters.value);
      tipos.value = resp.data;
      pagination.value = { current_page: resp.current_page, last_page: resp.last_page, per_page: resp.per_page, total: resp.total, from: resp.from, to: resp.to };
    } catch (e: any) {
      setError(e.response?.data?.message || 'Error al cargar tipos de vehículo');
      console.error('fetchTipos error', e);
    } finally { setLoading(false); }
  }

  async function fetchAll() {
    try { const resp = await tipoVehiculoService.getAllTiposVehiculos(); tiposAll.value = resp.data || []; } catch (e) { console.error('fetchAll tipos error', e); }
  }

  async function fetchStats() { try { const resp = await tipoVehiculoService.getStats(); stats.value = resp.data || null; } catch (e) { console.error('fetchStats tipos error', e); } }
  async function fetchTrashed() { try { const resp = await tipoVehiculoService.getTrashedTiposVehiculos(); tiposTrashed.value = resp.data || []; } catch (e) { console.error('fetchTrashed error', e); } }

  async function getById(id: number) {
    try { setLoading(true); clearError(); const resp = await tipoVehiculoService.getTipoVehiculoById(id); current.value = resp.data || null; return current.value; }
    catch (e: any) { setError(e.response?.data?.message || 'Error al obtener tipo de vehículo'); throw e; }
    finally { setLoading(false); }
  }

  async function create(data: CreateTipoVehiculoRequest) {
    try { setLoading(true); clearError(); const resp = await tipoVehiculoService.createTipoVehiculo(data); if (resp.data) tipos.value.unshift(resp.data); return resp.data; }
    catch (e: any) { setError(e.response?.data?.message || 'Error al crear tipo de vehículo'); throw e; }
    finally { setLoading(false); }
  }

  async function update(id: number, data: UpdateTipoVehiculoRequest) {
    try { setLoading(true); clearError(); const resp = await tipoVehiculoService.updateTipoVehiculo(id, data); if (resp.data) { const idx = tipos.value.findIndex(t => t.id === id); if (idx !== -1) tipos.value[idx] = resp.data; if (current.value?.id === id) current.value = resp.data; } return resp.data; }
    catch (e: any) { setError(e.response?.data?.message || 'Error al actualizar tipo de vehículo'); throw e; }
    finally { setLoading(false); }
  }

  async function toggleActivo(id: number) {
    try { const resp = await tipoVehiculoService.toggleActivo(id); if (resp.data) { const idx = tipos.value.findIndex(t => t.id === id); if (idx !== -1) tipos.value[idx] = resp.data; if (current.value?.id === id) current.value = resp.data; } return resp.data; }
    catch (e) { console.error('toggleActivo tipoVehiculo error', e); }
  }

  async function remove(id: number) {
    try { setLoading(true); clearError(); await tipoVehiculoService.deleteTipoVehiculo(id); tipos.value = tipos.value.filter(t => t.id !== id); pagination.value.total = Math.max(0, pagination.value.total - 1); if (current.value?.id === id) current.value = null; }
    catch (e: any) { setError(e.response?.data?.message || 'Error al eliminar tipo de vehículo'); throw e; }
    finally { setLoading(false); }
  }

  async function restore(id: number) {
    try { const resp = await tipoVehiculoService.restoreTipoVehiculo(id); if (resp.data) { tipos.value.unshift(resp.data); tiposTrashed.value = tiposTrashed.value.filter(t => t.id !== id); } return resp.data; }
    catch (e) { console.error('restore tipoVehiculo error', e); }
  }

  function setFilters(partial: Partial<TipoVehiculoFilters>) { filters.value = { ...filters.value, ...partial }; }
  async function changePage(page: number) { setFilters({ page }); await fetchTipos(); }
  async function changePerPage(perPage: number) { setFilters({ page: 1, per_page: perPage }); await fetchTipos(); }
  async function search(term: string) { setFilters({ page: 1, search: term }); await fetchTipos(); }

  function $reset() {
    tipos.value = []; tiposAll.value = []; tiposTrashed.value = []; current.value = null; stats.value = null;
    pagination.value = { current_page: 1, last_page: 1, per_page: 15, total: 0, from: 0, to: 0 }; filters.value = { page: 1, per_page: 15, search: '' };
    loading.value = false; error.value = null;
  }

  return {
    tipos, tiposAll, tiposTrashed, current, stats, pagination, filters, loading, error,
    hasTipos, hasAll, hasTrashed,
    fetchTipos, fetchAll, fetchStats, fetchTrashed,
    getById, create, update, toggleActivo, remove, restore,
    changePage, changePerPage, search, setFilters, clearError, $reset,
  };
});
