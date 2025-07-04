import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { ventaService } from '@/services/venta.service';
import type {
  VentaProductoAutomotriz,
  VentaProductoDespensa,
  VentaMetricas,
  ProductoVentaOption,
  Cliente,
  CreateVentaAutomotrizRequest,
  CreateVentaDespensaRequest,
  UpdateVentaAutomotrizRequest,
  UpdateVentaDespensaRequest,
  VentaAutomotrizFilters,
  VentaDespensaFilters,
  PaginatedResponse,
  VentaUnificada
} from '@/types';

export const useVentaStore = defineStore('venta', () => {
  // === STATE ===
  
  // Ventas automotrices
  const ventasAutomotrices = ref<VentaProductoAutomotriz[]>([]);
  const ventasAutomotricesPagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0
  });

  // Ventas de despensa
  const ventasDespensa = ref<VentaProductoDespensa[]>([]);
  const ventasDespensaPagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0
  });

  // Datos auxiliares
  const productosDisponibles = ref<ProductoVentaOption[]>([]);
  const clientes = ref<Cliente[]>([]);
  const metricas = ref<VentaMetricas | null>(null);

  // Estados de carga
  const loading = ref(false);
  const loadingMetricas = ref(false);
  const loadingProductos = ref(false);
  const loadingClientes = ref(false);

  // Filtros
  const filtrosAutomotrices = ref<VentaAutomotrizFilters>({
    page: 1,
    per_page: 10,
    search: '',
    fecha_inicio: '',
    fecha_fin: ''
  });

  const filtrosDespensa = ref<VentaDespensaFilters>({
    page: 1,
    per_page: 10,
    search: '',
    fecha_inicio: '',
    fecha_fin: ''
  });

  // === COMPUTED ===
  
  /**
   * Ventas unificadas para mostrar en tabla general
   */
  const ventasUnificadas = computed<VentaUnificada[]>(() => {
    const automotrices: VentaUnificada[] = ventasAutomotrices.value.map(venta => ({
      id: venta.id,
      tipo: 'automotriz' as const,
      producto_nombre: venta.producto_automotriz?.nombre || 'N/A',
      cliente_nombre: venta.cliente?.nombre || 'Cliente general',
      cantidad: venta.cantidad,
      precio_unitario: venta.precio_unitario,
      total: venta.cantidad * venta.precio_unitario,
      fecha: venta.fecha,
      created_at: venta.created_at,
      updated_at: venta.updated_at,
      deleted_at: venta.deleted_at,
      original_data: venta
    }));

    const despensa: VentaUnificada[] = ventasDespensa.value.map(venta => ({
      id: venta.id,
      tipo: 'despensa' as const,
      producto_nombre: venta.producto_despensa?.nombre || 'N/A',
      cliente_nombre: venta.cliente?.nombre || 'Cliente general',
      cantidad: venta.cantidad,
      precio_unitario: venta.precio_unitario,
      total: venta.cantidad * venta.precio_unitario,
      fecha: venta.fecha,
      created_at: venta.created_at,
      updated_at: venta.updated_at,
      deleted_at: venta.deleted_at,
      original_data: venta
    }));

    return [...automotrices, ...despensa].sort((a, b) => 
      new Date(b.fecha).getTime() - new Date(a.fecha).getTime()
    );
  });

  /**
   * Productos automotrices disponibles
   */
  const productosAutomotrices = computed(() => 
    productosDisponibles.value.filter(p => p.tipo === 'automotriz')
  );

  /**
   * Productos de despensa disponibles
   */
  const productosDespensa = computed(() => 
    productosDisponibles.value.filter(p => p.tipo === 'despensa')
  );

  // === ACTIONS ===

  // Productos y datos auxiliares
  async function fetchProductosDisponibles() {
    if (loadingProductos.value) return;
    
    try {
      loadingProductos.value = true;
      productosDisponibles.value = await ventaService.getProductosDisponibles();
    } catch (error) {
      console.error('Error al cargar productos:', error);
    } finally {
      loadingProductos.value = false;
    }
  }

  async function fetchClientes() {
    if (loadingClientes.value) return;
    
    try {
      loadingClientes.value = true;
      clientes.value = await ventaService.getClientes();
    } catch (error) {
      console.error('Error al cargar clientes:', error);
    } finally {
      loadingClientes.value = false;
    }
  }

  async function fetchMetricas() {
    if (loadingMetricas.value) return;
    
    try {
      loadingMetricas.value = true;
      metricas.value = await ventaService.getMetricas();
    } catch (error) {
      console.error('Error al cargar métricas:', error);
    } finally {
      loadingMetricas.value = false;
    }
  }

  // === VENTAS AUTOMOTRICES ===

  async function fetchVentasAutomotrices(filters?: VentaAutomotrizFilters) {
    if (loading.value) return;
    
    try {
      loading.value = true;
      
      if (filters) {
        filtrosAutomotrices.value = { ...filtrosAutomotrices.value, ...filters };
      }
      
      const response: PaginatedResponse<VentaProductoAutomotriz> = await ventaService.getVentasAutomotrices(filtrosAutomotrices.value);
      
      ventasAutomotrices.value = response.data;
      ventasAutomotricesPagination.value = {
        current_page: response.current_page,
        last_page: response.last_page,
        per_page: response.per_page,
        total: response.total
      };
    } catch (error) {
      console.error('Error al cargar ventas automotrices:', error);
    } finally {
      loading.value = false;
    }
  }

  async function createVentaAutomotriz(ventaData: CreateVentaAutomotrizRequest): Promise<VentaProductoAutomotriz | null> {
    try {
      loading.value = true;
      const nuevaVenta = await ventaService.createVentaAutomotriz(ventaData);
      
      // Recargar la lista
      await fetchVentasAutomotrices();
      
      return nuevaVenta;
    } catch (error) {
      console.error('Error al crear venta automotriz:', error);
      return null;
    } finally {
      loading.value = false;
    }
  }

  async function updateVentaAutomotriz(id: number, ventaData: UpdateVentaAutomotrizRequest): Promise<VentaProductoAutomotriz | null> {
    try {
      loading.value = true;
      const ventaActualizada = await ventaService.updateVentaAutomotriz(id, ventaData);
      
      // Actualizar en la lista local
      const index = ventasAutomotrices.value.findIndex(v => v.id === id);
      if (index !== -1) {
        ventasAutomotrices.value[index] = ventaActualizada;
      }
      
      return ventaActualizada;
    } catch (error) {
      console.error('Error al actualizar venta automotriz:', error);
      return null;
    } finally {
      loading.value = false;
    }
  }

  async function deleteVentaAutomotriz(id: number): Promise<boolean> {
    try {
      loading.value = true;
      await ventaService.deleteVentaAutomotriz(id);
      
      // Remover de la lista local
      ventasAutomotrices.value = ventasAutomotrices.value.filter(v => v.id !== id);
      
      return true;
    } catch (error) {
      console.error('Error al eliminar venta automotriz:', error);
      return false;
    } finally {
      loading.value = false;
    }
  }

  // === VENTAS DE DESPENSA ===

  async function fetchVentasDespensa(filters?: VentaDespensaFilters) {
    if (loading.value) return;
    
    try {
      loading.value = true;
      
      if (filters) {
        filtrosDespensa.value = { ...filtrosDespensa.value, ...filters };
      }
      
      const response: PaginatedResponse<VentaProductoDespensa> = await ventaService.getVentasDespensa(filtrosDespensa.value);
      
      ventasDespensa.value = response.data;
      ventasDespensaPagination.value = {
        current_page: response.current_page,
        last_page: response.last_page,
        per_page: response.per_page,
        total: response.total
      };
    } catch (error) {
      console.error('Error al cargar ventas de despensa:', error);
    } finally {
      loading.value = false;
    }
  }

  async function createVentaDespensa(ventaData: CreateVentaDespensaRequest): Promise<VentaProductoDespensa | null> {
    try {
      loading.value = true;
      const nuevaVenta = await ventaService.createVentaDespensa(ventaData);
      
      // Recargar la lista
      await fetchVentasDespensa();
      
      return nuevaVenta;
    } catch (error) {
      console.error('Error al crear venta de despensa:', error);
      return null;
    } finally {
      loading.value = false;
    }
  }

  async function updateVentaDespensa(id: number, ventaData: UpdateVentaDespensaRequest): Promise<VentaProductoDespensa | null> {
    try {
      loading.value = true;
      const ventaActualizada = await ventaService.updateVentaDespensa(id, ventaData);
      
      // Actualizar en la lista local
      const index = ventasDespensa.value.findIndex(v => v.id === id);
      if (index !== -1) {
        ventasDespensa.value[index] = ventaActualizada;
      }
      
      return ventaActualizada;
    } catch (error) {
      console.error('Error al actualizar venta de despensa:', error);
      return null;
    } finally {
      loading.value = false;
    }
  }

  async function deleteVentaDespensa(id: number): Promise<boolean> {
    try {
      loading.value = true;
      await ventaService.deleteVentaDespensa(id);
      
      // Remover de la lista local
      ventasDespensa.value = ventasDespensa.value.filter(v => v.id !== id);
      
      return true;
    } catch (error) {
      console.error('Error al eliminar venta de despensa:', error);
      return false;
    } finally {
      loading.value = false;
    }
  }

  // === UTILIDADES ===

  function clearFilters() {
    filtrosAutomotrices.value = {
      page: 1,
      per_page: 10,
      search: '',
      fecha_inicio: '',
      fecha_fin: ''
    };
    filtrosDespensa.value = {
      page: 1,
      per_page: 10,
      search: '',
      fecha_inicio: '',
      fecha_fin: ''
    };
  }

  async function refreshAll() {
    await Promise.all([
      fetchVentasAutomotrices(),
      fetchVentasDespensa(),
      fetchProductosDisponibles(),
      fetchClientes(),
      fetchMetricas()
    ]);
  }

  return {
    // State
    ventasAutomotrices,
    ventasAutomotricesPagination,
    ventasDespensa,
    ventasDespensaPagination,
    productosDisponibles,
    clientes,
    metricas,
    loading,
    loadingMetricas,
    loadingProductos,
    loadingClientes,
    filtrosAutomotrices,
    filtrosDespensa,
    
    // Computed
    ventasUnificadas,
    productosAutomotrices,
    productosDespensa,
    
    // Actions
    fetchProductosDisponibles,
    fetchClientes,
    fetchMetricas,
    fetchVentasAutomotrices,
    createVentaAutomotriz,
    updateVentaAutomotriz,
    deleteVentaAutomotriz,
    fetchVentasDespensa,
    createVentaDespensa,
    updateVentaDespensa,
    deleteVentaDespensa,
    clearFilters,
    refreshAll
  };
});
