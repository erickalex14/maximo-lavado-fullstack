import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import ventaService from '@/services/venta.service';
import clienteService from '@/services/cliente.service';
import productoService from '@/services/producto.service';
import type {
  Venta,
  Cliente,
  CreateVentaRequest,
  UpdateVentaRequest,
  VentaFilters,
  PaginatedResponse,
} from '@/types';

// Opción simple para selects de productos
type ProductoVentaOption = { id: number; nombre: string; tipo: 'automotriz' | 'despensa' };

export const useVentaStore = defineStore('venta', () => {
  // === STATE ===
  
  // Ventas automotrices
  const ventasAutomotrices = ref<Venta[]>([]);
  const ventasAutomotricesPagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0
  });

  // Ventas de despensa
  const ventasDespensa = ref<Venta[]>([]);
  const ventasDespensaPagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0
  });

  // Datos auxiliares
  const productosDisponibles = ref<ProductoVentaOption[]>([]);
  const clientes = ref<Cliente[]>([]);
  const metricas = ref<any | null>(null);

  // Estados de carga
  const loading = ref(false);
  const loadingMetricas = ref(false);
  const loadingProductos = ref(false);
  const loadingClientes = ref(false);

  // Filtros
  const filtrosAutomotrices = ref<VentaFilters>({
    page: 1,
    per_page: 10,
    search: '',
    fecha_inicio: '',
    fecha_fin: ''
  });

  const filtrosDespensa = ref<VentaFilters>({
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
  const ventasUnificadas = computed<Venta[]>(() => {
    return [...ventasAutomotrices.value, ...ventasDespensa.value]
      .sort((a, b) => new Date(b.fecha).getTime() - new Date(a.fecha).getTime());
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
      // Cargar productos automotrices activos
      const autoResp = await productoService.getProductosAutomotrices({ page: 1, per_page: 1000, activo: true });
      const automotrices = (autoResp.data || []).map(p => ({ id: p.producto_automotriz_id, nombre: p.nombre, tipo: 'automotriz' as const }));
      // Cargar productos de despensa activos
      const despResp = await productoService.getProductosDespensa({ page: 1, per_page: 1000, activo: true });
      const despensa = (despResp.data || []).map(p => ({ id: p.producto_despensa_id, nombre: p.nombre, tipo: 'despensa' as const }));
      productosDisponibles.value = [...automotrices, ...despensa];
    } catch (error) {
      console.error('Error al cargar productos:', error);
      productosDisponibles.value = [];
    } finally {
      loadingProductos.value = false;
    }
  }

  async function fetchClientes() {
    if (loadingClientes.value) return;
    
    try {
      loadingClientes.value = true;
      const resp = await clienteService.getAllForSelect();
      clientes.value = resp.data || [];
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
      const resp = await ventaService.getStats();
      metricas.value = resp.data || null;
    } catch (error) {
      console.error('Error al cargar métricas:', error);
    } finally {
      loadingMetricas.value = false;
    }
  }

  // === VENTAS AUTOMOTRICES ===

  async function fetchVentasAutomotrices(filters?: VentaFilters) {
    if (loading.value) return;
    
    try {
      loading.value = true;
      
      if (filters) {
        filtrosAutomotrices.value = { ...filtrosAutomotrices.value, ...filters };
      }
      
      const response: PaginatedResponse<Venta> = await ventaService.getVentas({ ...filtrosAutomotrices.value, tipo: 'producto_automotriz' });
      
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

  async function createVentaAutomotriz(ventaData: { producto_automotriz_id: number; cantidad: number; precio_unitario?: number; cliente_id?: number; empleado_id?: number; vehiculo_id?: number; descripcion?: string; generar_factura?: boolean; }): Promise<Venta | null> {
    try {
      loading.value = true;
      const payload: CreateVentaRequest = {
        tipo: 'producto_automotriz',
        referencia_id: ventaData.producto_automotriz_id,
        cantidad: ventaData.cantidad,
        precio_unitario: ventaData.precio_unitario,
        cliente_id: ventaData.cliente_id,
        empleado_id: ventaData.empleado_id,
        vehiculo_id: ventaData.vehiculo_id,
        descripcion: ventaData.descripcion,
        generar_factura: ventaData.generar_factura,
      };
      const resp = await ventaService.createVenta(payload);
      const nuevaVenta = resp.data || null;
      
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

  async function updateVentaAutomotriz(id: number, ventaData: { producto_automotriz_id?: number; cantidad?: number; precio_unitario?: number; cliente_id?: number; empleado_id?: number; vehiculo_id?: number; descripcion?: string; generar_factura?: boolean; }): Promise<Venta | null> {
    try {
      loading.value = true;
      const patch: UpdateVentaRequest = {
        tipo: 'producto_automotriz',
        referencia_id: ventaData.producto_automotriz_id ?? undefined,
        cantidad: ventaData.cantidad,
        precio_unitario: ventaData.precio_unitario,
        cliente_id: ventaData.cliente_id,
        empleado_id: ventaData.empleado_id,
        vehiculo_id: ventaData.vehiculo_id,
        descripcion: ventaData.descripcion,
        generar_factura: ventaData.generar_factura,
      };
      const resp = await ventaService.updateVenta(id, patch);
      const ventaActualizada = resp.data || null;
      
      // Actualizar en la lista local
      const index = ventasAutomotrices.value.findIndex(v => v.id === id);
      if (index !== -1) {
        if (ventaActualizada) ventasAutomotrices.value[index] = ventaActualizada;
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
      await ventaService.deleteVenta(id);
      
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

  async function fetchVentasDespensa(filters?: VentaFilters) {
    if (loading.value) return;
    
    try {
      loading.value = true;
      
      if (filters) {
        filtrosDespensa.value = { ...filtrosDespensa.value, ...filters };
      }
      
      const response: PaginatedResponse<Venta> = await ventaService.getVentas({ ...filtrosDespensa.value, tipo: 'producto_despensa' });
      
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

  async function createVentaDespensa(ventaData: { producto_despensa_id: number; cantidad: number; precio_unitario?: number; cliente_id?: number; empleado_id?: number; vehiculo_id?: number; descripcion?: string; generar_factura?: boolean; }): Promise<Venta | null> {
    try {
      loading.value = true;
      const payload: CreateVentaRequest = {
        tipo: 'producto_despensa',
        referencia_id: ventaData.producto_despensa_id,
        cantidad: ventaData.cantidad,
        precio_unitario: ventaData.precio_unitario,
        cliente_id: ventaData.cliente_id,
        empleado_id: ventaData.empleado_id,
        vehiculo_id: ventaData.vehiculo_id,
        descripcion: ventaData.descripcion,
        generar_factura: ventaData.generar_factura,
      };
      const resp = await ventaService.createVenta(payload);
      const nuevaVenta = resp.data || null;
      
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

  async function updateVentaDespensa(id: number, ventaData: { producto_despensa_id?: number; cantidad?: number; precio_unitario?: number; cliente_id?: number; empleado_id?: number; vehiculo_id?: number; descripcion?: string; generar_factura?: boolean; }): Promise<Venta | null> {
    try {
      loading.value = true;
      const patch: UpdateVentaRequest = {
        tipo: 'producto_despensa',
        referencia_id: ventaData.producto_despensa_id ?? undefined,
        cantidad: ventaData.cantidad,
        precio_unitario: ventaData.precio_unitario,
        cliente_id: ventaData.cliente_id,
        empleado_id: ventaData.empleado_id,
        vehiculo_id: ventaData.vehiculo_id,
        descripcion: ventaData.descripcion,
        generar_factura: ventaData.generar_factura,
      };
      const resp = await ventaService.updateVenta(id, patch);
      const ventaActualizada = resp.data || null;
      
      // Actualizar en la lista local
      const index = ventasDespensa.value.findIndex(v => v.id === id);
      if (index !== -1) {
        if (ventaActualizada) ventasDespensa.value[index] = ventaActualizada;
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
      await ventaService.deleteVenta(id);
      
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
