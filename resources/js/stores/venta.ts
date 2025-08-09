import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import ventaService from '@/services/venta.service';
import clienteService from '@/services/cliente.service';
import productoService from '@/services/producto.service';
import type { Venta, Cliente, CreateVentaRequest, UpdateVentaRequest, VentaFilters, PaginatedResponse } from '@/types';

// Métricas tipadas (ajustar según respuesta real del backend stats)
interface VentasMetricas {
  total_ventas: number;
  total_ingresos: number;
  ventas_automotrices: { total: number };
  ventas_despensa: { total: number };
  servicios?: { total: number };
  [k: string]: any; // fallback para campos adicionales no mapeados aún
}

// Opción simple para selects de productos
type ProductoVentaOption = { id: number; nombre: string; tipo: 'automotriz' | 'despensa'; stock?: number; precio?: number };

export const useVentaStore = defineStore('venta', () => {
  // === STATE ===
  
  // Colección unificada de ventas (sustituye listas segmentadas legacy)
  const ventas = ref<Venta[]>([]);
  const ventasPagination = ref({ current_page: 1, last_page: 1, per_page: 10, total: 0, from: 0, to: 0 });
  const filtros = ref<VentaFilters>({ page: 1, per_page: 10, search: '', fecha_inicio: '', fecha_fin: '' });
  const filtroTipo = ref<'' | 'producto_automotriz' | 'producto_despensa' | 'servicio'>('');
  // paginación legacy eliminada -> sólo mantenemos ventasPagination

  // Datos auxiliares
  const productosDisponibles = ref<ProductoVentaOption[]>([]);
  const clientes = ref<Cliente[]>([]);
  const metricas = ref<VentasMetricas | null>(null);
  // Objeto base para evitar errores de acceso en la vista
  const defaultMetricas: VentasMetricas = {
    total_ventas: 0,
    total_ingresos: 0,
    ventas_automotrices: { total: 0 },
    ventas_despensa: { total: 0 },
    servicios: { total: 0 }
  };

  // Estados de carga
  const loading = ref(false);
  const loadingMetricas = ref(false);
  const loadingProductos = ref(false);
  const loadingClientes = ref(false);

  // Filtros unificados (listas legacy eliminadas)

  // === COMPUTED ===
  
  /**
   * Ventas unificadas para mostrar en tabla general
   */
  const ventasUnificadas = computed<Venta[]>(() => [...ventas.value].sort((a,b)=> new Date(b.fecha).getTime()-new Date(a.fecha).getTime()));

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
      const automotricesRaw = autoResp.data || [];
      const automotrices = automotricesRaw.map(p => ({
        id: Number((p as any).producto_automotriz_id ?? (p as any).id ?? (p as any).producto_id),
        nombre: (p as any).nombre ?? (p as any).descripcion ?? 'Producto Automotriz',
        tipo: 'automotriz' as const,
	stock: Number((p as any).stock ?? (p as any).stock_actual ?? 0),
	precio: Number((p as any).precio_venta ?? (p as any).precio ?? (p as any).precio_unitario ?? 0)
      })).filter(p => !Number.isNaN(p.id));
      // Cargar productos de despensa activos
      const despResp = await productoService.getProductosDespensa({ page: 1, per_page: 1000, activo: true });
      const despensaRaw = despResp.data || [];
      const despensa = despensaRaw.map(p => ({
        id: Number((p as any).producto_despensa_id ?? (p as any).id ?? (p as any).producto_id),
        nombre: (p as any).nombre ?? (p as any).descripcion ?? 'Producto Despensa',
        tipo: 'despensa' as const,
	stock: Number((p as any).stock ?? (p as any).stock_actual ?? 0),
	precio: Number((p as any).precio_venta ?? (p as any).precio ?? (p as any).precio_unitario ?? 0)
      })).filter(p => !Number.isNaN(p.id));
      productosDisponibles.value = [...automotrices, ...despensa];
      console.debug('[fetchProductosDisponibles] cargados', {
        automotrices: automotrices.slice(0,5),
        despensa: despensa.slice(0,5),
        total: productosDisponibles.value.length
      });
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
      // resp.data ya normalizado; fallback a resp.stats
      const raw = resp.data || (resp as any).stats || null;
      if (!raw) {
        metricas.value = { ...defaultMetricas };
      } else {
        // Backend actual (VentaRepository::getStats) devuelve: total_ventas, ventas_hoy, ventas_mes, total_facturado_hoy, total_facturado_mes, eliminadas
        // Mapear a estructura usada en UI y extender con campos originales
        const ventasAutomotrices = raw.ventas_automotrices ?? 0; // puede no existir
        const ventasDespensa = raw.ventas_despensa ?? 0;
        const serviciosCount = raw.servicios ?? 0;
        metricas.value = {
          ...defaultMetricas,
          total_ventas: raw.total_ventas ?? raw.total ?? 0,
          total_ingresos: raw.total_facturado_mes ?? raw.total_facturado_hoy ?? 0,
          ventas_automotrices: { total: typeof ventasAutomotrices === 'number' ? ventasAutomotrices : (ventasAutomotrices.total ?? 0) },
          ventas_despensa: { total: typeof ventasDespensa === 'number' ? ventasDespensa : (ventasDespensa.total ?? 0) },
          servicios: { total: typeof serviciosCount === 'number' ? serviciosCount : (serviciosCount.total ?? 0) },
          // Extras originales
          ventas_hoy: raw.ventas_hoy ?? 0,
          ventas_mes: raw.ventas_mes ?? 0,
          total_facturado_hoy: raw.total_facturado_hoy ?? 0,
          total_facturado_mes: raw.total_facturado_mes ?? 0,
          eliminadas: raw.eliminadas ?? 0,
        } as any;
      }
    } catch (error) {
      console.error('Error al cargar métricas:', error);
    } finally {
      loadingMetricas.value = false;
    }
  }

  // === FETCH UNIFICADO NUEVO ===
  async function fetchVentas(params?: VentaFilters & { tipo?: 'producto_automotriz' | 'producto_despensa' | 'servicio' }) {
    if (loading.value) return;
    try {
      loading.value = true;
      if (params) {
        const { tipo, ...rest } = params;
        if (tipo !== undefined) filtroTipo.value = (tipo as any) || '';
        filtros.value = { ...filtros.value, ...rest };
      }
  const response: PaginatedResponse<Venta> = await ventaService.getVentas({ ...filtros.value, tipo: filtroTipo.value || undefined });
  // Debug: loggear respuesta para diagnosticar
  console.debug('[fetchVentas] respuesta', response);
  ventas.value = Array.isArray(response.data) ? response.data : [];
      ventasPagination.value = {
        current_page: response.current_page,
        last_page: response.last_page,
        per_page: response.per_page,
        total: response.total,
        from: (response as any).from ?? ((response.current_page-1)*response.per_page+1),
        to: (response as any).to ?? ((response.current_page-1)*response.per_page + response.data.length)
      };
      // Actualizar contadores de métricas por tipo (si metricas ya cargadas o crear base)
  const autosCount = ventas.value.reduce((s,v)=> s + (v.tipo==='producto_automotriz' ? 1 : (v.tipo==='mixta' && v.detalles_tipos?.includes('producto_automotriz') ? 1:0)),0);
  const despensaCount = ventas.value.reduce((s,v)=> s + (v.tipo==='producto_despensa' ? 1 : (v.tipo==='mixta' && v.detalles_tipos?.includes('producto_despensa') ? 1:0)),0);
  const serviciosCount = ventas.value.reduce((s,v)=> s + (v.tipo==='servicio' ? 1 : (v.tipo==='mixta' && v.detalles_tipos?.includes('servicio') ? 1:0)),0);
      if (!metricas.value) {
        metricas.value = { ...defaultMetricas };
      }
      metricas.value.ventas_automotrices.total = autosCount;
      metricas.value.ventas_despensa.total = despensaCount;
      if (metricas.value.servicios) metricas.value.servicios.total = serviciosCount; else (metricas.value as any).servicios = { total: serviciosCount };
    } catch (e) {
      console.error('Error al cargar ventas unificadas:', e);
    } finally {
      loading.value = false;
    }
  }

  // === CREACIÓN / ACTUALIZACIÓN / ELIMINACIÓN GENÉRICA ===
  async function createVentaGenerica(data: { tipo: 'producto_automotriz' | 'producto_despensa' | 'servicio'; referencia_id: number; cantidad: number; precio_unitario?: number; cliente_id?: number; descripcion?: string; generar_factura?: boolean; nombre?: string; }): Promise<Venta | null> {
    // Backend actual espera estructura de venta con detalles[] (tipo_item, item_id,...)
    try {
      loading.value = true;
      let nombreItem = data.nombre;
      if (!nombreItem) {
        if (data.tipo === 'servicio') {
          nombreItem = 'Servicio';
        } else {
          const baseProducto = productosDisponibles.value.find(p => p.id === data.referencia_id);
          nombreItem = baseProducto?.nombre || 'Item';
        }
      }
      const detalles = [{
        tipo_item: data.tipo,
        item_id: data.referencia_id,
        item_nombre: nombreItem,
        item_descripcion: null,
        cantidad: data.cantidad,
        precio_unitario: data.precio_unitario || 0,
        descuento: 0,
      }];
      const payload: any = {
  cliente_id: data.cliente_id ?? null,
        fecha: new Date().toISOString().slice(0,10),
        detalles,
        observaciones: data.descripcion || null,
      };
      const resp = await (ventaService as any).createVenta(payload);
      const nueva = resp.data || resp.venta || null;
      await fetchVentas({ tipo: filtroTipo.value || undefined });
      return nueva;
    } catch (error) {
      console.error('Error al crear venta genérica (adaptada):', error);
      return null;
    } finally {
      loading.value = false;
    }
  }
  
  // Crear una sola venta con múltiples detalles (mixta)
  async function createVentaMixta(data: { items: Array<{ tipo: 'producto_automotriz' | 'producto_despensa' | 'servicio'; referencia_id: number; cantidad: number; precio_unitario: number; nombre?: string }>; cliente_id?: number; descripcion?: string; generar_factura?: boolean; }): Promise<Venta | null> {
    if (!data.items.length) return null;
    try {
      loading.value = true;
      const detalles = data.items.map(it => {
        let nombreItem = it.nombre;
        if (!nombreItem) {
          if (it.tipo === 'servicio') nombreItem = 'Servicio';
          else {
            const prod = productosDisponibles.value.find(p => p.id === it.referencia_id);
            nombreItem = prod?.nombre || 'Item';
          }
        }
        return {
          tipo_item: it.tipo,
          item_id: it.referencia_id,
          item_nombre: nombreItem,
          item_descripcion: null,
          cantidad: it.cantidad,
          precio_unitario: it.precio_unitario,
          descuento: 0,
        };
      });
      const payload: any = {
        cliente_id: data.cliente_id ?? null,
        fecha: new Date().toISOString().slice(0,10),
        detalles,
        observaciones: data.descripcion || null,
      };
      const resp = await (ventaService as any).createVenta(payload);
      const ventaCreada = resp.data || resp.venta || null;
      await fetchVentas();
      return ventaCreada;
    } catch (error) {
      console.error('Error al crear venta mixta:', error);
      return null;
    } finally {
      loading.value = false;
    }
  }
  async function updateVentaGenerica(id: number, patchData: { tipo?: 'producto_automotriz' | 'producto_despensa' | 'servicio'; referencia_id?: number; cantidad?: number; precio_unitario?: number; cliente_id?: number; empleado_id?: number; vehiculo_id?: number; descripcion?: string; generar_factura?: boolean; }): Promise<Venta | null> {
    try {
      loading.value = true;
      const patch: UpdateVentaRequest = {
        tipo: patchData.tipo,
        referencia_id: patchData.referencia_id,
        cantidad: patchData.cantidad,
        precio_unitario: patchData.precio_unitario,
        cliente_id: patchData.cliente_id,
        empleado_id: patchData.empleado_id,
        vehiculo_id: patchData.vehiculo_id,
        descripcion: patchData.descripcion,
        generar_factura: patchData.generar_factura,
      };
      const resp = await ventaService.updateVenta(id, patch);
      const actualizada = resp.data || null;
      // refrescar lista en memoria
      if (actualizada) {
        const idx = ventas.value.findIndex(v => v.id === id);
        if (idx !== -1) ventas.value[idx] = actualizada; else await fetchVentas();
      }
      return actualizada;
    } catch (e) {
      console.error('Error al actualizar venta genérica:', e);
      return null;
    } finally { loading.value = false; }
  }

  async function deleteVentaGenerica(id: number): Promise<boolean> {
    try {
      loading.value = true;
      await ventaService.deleteVenta(id);
      ventas.value = ventas.value.filter(v => v.id !== id);
      return true;
    } catch (e) {
      console.error('Error al eliminar venta:', e);
      return false;
    } finally { loading.value = false; }
  }

  // Wrappers legacy eliminados tras migración completa

  // === UTILIDADES ===

  function clearFilters() {
    filtros.value = { page: 1, per_page: 10, search: '', fecha_inicio: '', fecha_fin: '' };
    filtroTipo.value = '';
  }

  async function refreshAll() {
    await Promise.all([
      fetchVentas(),
      fetchProductosDisponibles(),
      fetchClientes(),
      fetchMetricas()
    ]);
  }

  return {
  // State
  ventas,
  ventasPagination,
    productosDisponibles,
    clientes,
    metricas,
    loading,
    loadingMetricas,
    loadingProductos,
    loadingClientes,
  filtros,
  filtroTipo,
    
    // Computed
    ventasUnificadas,
    productosAutomotrices,
    productosDespensa,
    
    // Actions
  fetchProductosDisponibles,
  fetchClientes,
  fetchMetricas,
  fetchVentas,
  // Genéricas
  createVentaGenerica,
  createVentaMixta,
  updateVentaGenerica,
  deleteVentaGenerica,
  // Wrappers legacy (deprecados)
    clearFilters,
    refreshAll
  };
});
