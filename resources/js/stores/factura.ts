import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { facturaService } from '@/services/factura.service';
import type { 
  Factura, 
  FacturaDetalle, 
  Cliente, 
  PaginatedResponse 
} from '@/types';
import type { 
  FacturaFilters, 
  CreateFacturaRequest, 
  UpdateFacturaRequest, 
  FacturaMetricas 
} from '@/services/factura.service';

export const useFacturaStore = defineStore('factura', () => {
  // === STATE ===
  
  // Facturas
  const facturas = ref<Factura[]>([]);
  const facturasEliminadas = ref<Factura[]>([]);
  const facturaActual = ref<Factura | null>(null);
  
  // Paginación
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0
  });
  
  // Datos auxiliares
  const clientes = ref<Cliente[]>([]);
  const metricas = ref<FacturaMetricas | null>(null);
  
  // Estados de carga
  const loading = ref(false);
  const loadingMetricas = ref(false);
  const loadingClientes = ref(false);
  
  // Filtros
  const filtros = ref<FacturaFilters>({
    page: 1,
    per_page: 10,
    search: '',
    cliente_id: undefined,
    fecha_inicio: '',
    fecha_fin: ''
  });
  
  // === COMPUTED ===
  
  const facturasCount = computed(() => facturas.value.length);
  const hasFacturas = computed(() => facturasCount.value > 0);
  
  // === ACTIONS ===
  
  // Obtener facturas con filtros
  async function fetchFacturas(filters: FacturaFilters = {}) {
    if (loading.value) return;
    
    try {
      loading.value = true;
      const mergedFilters = { ...filtros.value, ...filters };
      const response = await facturaService.getFacturas(mergedFilters);
      
      facturas.value = response.data;
      pagination.value = {
        current_page: response.current_page,
        last_page: response.last_page,
        per_page: response.per_page,
        total: response.total
      };
      
      // Actualizar filtros
      Object.assign(filtros.value, mergedFilters);
    } catch (error) {
      console.error('Error al obtener facturas:', error);
      facturas.value = [];
    } finally {
      loading.value = false;
    }
  }
  
  // Obtener factura por ID
  async function fetchFacturaById(id: number) {
    try {
      loading.value = true;
      facturaActual.value = await facturaService.getFacturaById(id);
      return facturaActual.value;
    } catch (error) {
      console.error('Error al obtener factura:', error);
      facturaActual.value = null;
      return null;
    } finally {
      loading.value = false;
    }
  }
  
  // Crear nueva factura
  async function createFactura(data: CreateFacturaRequest) {
    try {
      loading.value = true;
      const nuevaFactura = await facturaService.createFactura(data);
      facturas.value.unshift(nuevaFactura);
      return nuevaFactura;
    } catch (error) {
      console.error('Error al crear factura:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  }
  
  // Actualizar factura
  async function updateFactura(id: number, data: UpdateFacturaRequest) {
    try {
      loading.value = true;
      const facturaActualizada = await facturaService.updateFactura(id, data);
      
      const index = facturas.value.findIndex(f => f.factura_id === id);
      if (index !== -1) {
        facturas.value[index] = facturaActualizada;
      }
      
      if (facturaActual.value?.factura_id === id) {
        facturaActual.value = facturaActualizada;
      }
      
      return facturaActualizada;
    } catch (error) {
      console.error('Error al actualizar factura:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  }
  
  // Eliminar factura
  async function deleteFactura(id: number) {
    try {
      loading.value = true;
      await facturaService.deleteFactura(id);
      
      // Remover de la lista
      facturas.value = facturas.value.filter(f => f.factura_id !== id);
      
      // Limpiar si era la factura actual
      if (facturaActual.value?.factura_id === id) {
        facturaActual.value = null;
      }
    } catch (error) {
      console.error('Error al eliminar factura:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  }
  
  // Obtener facturas eliminadas
  async function fetchFacturasEliminadas() {
    try {
      loading.value = true;
      facturasEliminadas.value = await facturaService.getTrashedFacturas();
    } catch (error) {
      console.error('Error al obtener facturas eliminadas:', error);
      facturasEliminadas.value = [];
    } finally {
      loading.value = false;
    }
  }
  
  // Restaurar factura eliminada
  async function restoreFactura(id: number) {
    try {
      loading.value = true;
      const facturaRestaurada = await facturaService.restoreFactura(id);
      
      // Remover de eliminadas y agregar a activas
      facturasEliminadas.value = facturasEliminadas.value.filter(f => f.factura_id !== id);
      facturas.value.unshift(facturaRestaurada);
      
      return facturaRestaurada;
    } catch (error) {
      console.error('Error al restaurar factura:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  }
  
  // Buscar factura por número
  async function findByNumero(numeroFactura: string) {
    try {
      loading.value = true;
      return await facturaService.findByNumero(numeroFactura);
    } catch (error) {
      console.error('Error al buscar factura por número:', error);
      return null;
    } finally {
      loading.value = false;
    }
  }
  
  // Obtener métricas
  async function fetchMetricas() {
    if (loadingMetricas.value) return;
    
    try {
      loadingMetricas.value = true;
      metricas.value = await facturaService.getMetricas();
    } catch (error) {
      console.error('Error al obtener métricas:', error);
      metricas.value = null;
    } finally {
      loadingMetricas.value = false;
    }
  }
  
  // Obtener clientes
  async function fetchClientes() {
    if (loadingClientes.value) return;
    
    try {
      loadingClientes.value = true;
      clientes.value = await facturaService.getClientes();
    } catch (error) {
      console.error('Error al obtener clientes:', error);
      clientes.value = [];
    } finally {
      loadingClientes.value = false;
    }
  }
  
  // Cambiar página
  async function changePage(page: number) {
    if (page === filtros.value.page) return;
    
    filtros.value.page = page;
    await fetchFacturas();
  }
  
  // Cambiar elementos por página
  async function changePerPage(perPage: number) {
    filtros.value.per_page = perPage;
    filtros.value.page = 1;
    await fetchFacturas();
  }
  
  // Aplicar filtros
  async function applyFilters(newFilters: Partial<FacturaFilters>) {
    Object.assign(filtros.value, newFilters);
    filtros.value.page = 1;
    await fetchFacturas();
  }
  
  // Limpiar filtros
  function clearFilters() {
    filtros.value = {
      page: 1,
      per_page: 10,
      search: '',
      cliente_id: undefined,
      fecha_inicio: '',
      fecha_fin: ''
    };
  }
  
  // Limpiar estado
  function clearState() {
    facturas.value = [];
    facturasEliminadas.value = [];
    facturaActual.value = null;
    metricas.value = null;
    clientes.value = [];
    clearFilters();
  }
  
  // Refrescar datos
  async function refreshAll() {
    await Promise.all([
      fetchFacturas(),
      fetchMetricas(),
      fetchClientes()
    ]);
  }
  
  return {
    // State
    facturas,
    facturasEliminadas,
    facturaActual,
    pagination,
    clientes,
    metricas,
    loading,
    loadingMetricas,
    loadingClientes,
    filtros,
    
    // Computed
    facturasCount,
    hasFacturas,
    
    // Actions
    fetchFacturas,
    fetchFacturaById,
    createFactura,
    updateFactura,
    deleteFactura,
    fetchFacturasEliminadas,
    restoreFactura,
    findByNumero,
    fetchMetricas,
    fetchClientes,
    changePage,
    changePerPage,
    applyFilters,
    clearFilters,
    clearState,
    refreshAll
  };
});
