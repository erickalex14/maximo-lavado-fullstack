import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import proveedorService, { type ProveedorFilters, type PagoFilters } from '@/services/proveedor.service';
import type { 
  Proveedor, 
  PagoProveedor, 
  CreateProveedorRequest, 
  UpdateProveedorRequest,
  CreatePagoProveedorRequest,
  UpdatePagoProveedorRequest 
} from '@/types';

export const useProveedorStore = defineStore('proveedor', () => {
  // State
  const proveedores = ref<Proveedor[]>([]);
  const pagos = ref<PagoProveedor[]>([]);
  const currentProveedor = ref<Proveedor | null>(null);
  const currentPago = ref<PagoProveedor | null>(null);
  
  const paginationProveedores = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
    from: 0,
    to: 0
  });
  
  const paginationPagos = ref({
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
  
  const filtersProveedores = ref<ProveedorFilters>({
    page: 1,
    per_page: 15,
    search: '',
  });
  
  const filtersPagos = ref<PagoFilters>({
    page: 1,
    per_page: 15,
    search: '',
  });

  // Getters
  const hasProveedores = computed(() => proveedores.value.length > 0);
  const hasPagos = computed(() => pagos.value.length > 0);
  const isLoading = computed(() => loading.value);
  const hasError = computed(() => error.value !== null);
  
  const totalProveedores = computed(() => paginationProveedores.value.total);
  const totalPagos = computed(() => paginationPagos.value.total);
  
  const proveedoresConDeuda = computed(() => 
    proveedores.value.filter(p => p.deuda_pendiente > 0)
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

  // CRUD Proveedores
  const fetchProveedores = async (filters?: ProveedorFilters) => {
    try {
      setLoading(true);
      clearError();
      
      const response = await proveedorService.getProveedores(filters);
      
      if (response.data) {
        proveedores.value = Array.isArray(response.data) ? response.data : [];
      }
    } catch (err: any) {
      setError(err.message || 'Error al cargar proveedores');
      console.error('Error fetching proveedores:', err);
    } finally {
      setLoading(false);
    }
  };

  const fetchProveedor = async (id: number) => {
    try {
      setLoading(true);
      clearError();
      
      const response = await proveedorService.getProveedor(id);
      if (response.data) {
        currentProveedor.value = response.data;
      }
      
      return response.data;
    } catch (err: any) {
      setError(err.message || 'Error al cargar proveedor');
      console.error('Error fetching proveedor:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const createProveedor = async (data: CreateProveedorRequest) => {
    try {
      setLoading(true);
      clearError();
      
      const response = await proveedorService.createProveedor(data);
      
      if (response.data) {
        proveedores.value.unshift(response.data);
        paginationProveedores.value.total += 1;
      }
      
      return response.data;
    } catch (err: any) {
      setError(err.message || 'Error al crear proveedor');
      console.error('Error creating proveedor:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const updateProveedor = async (id: number, data: UpdateProveedorRequest) => {
    try {
      setLoading(true);
      clearError();
      
      const response = await proveedorService.updateProveedor(id, data);
      
      if (response.data) {
        const index = proveedores.value.findIndex(p => p.proveedor_id === id);
        if (index !== -1) {
          proveedores.value[index] = response.data;
        }
        
        if (currentProveedor.value?.proveedor_id === id) {
          currentProveedor.value = response.data;
        }
      }
      
      return response.data;
    } catch (err: any) {
      setError(err.message || 'Error al actualizar proveedor');
      console.error('Error updating proveedor:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const deleteProveedor = async (id: number) => {
    try {
      setLoading(true);
      clearError();
      
      await proveedorService.deleteProveedor(id);
      
      const index = proveedores.value.findIndex(p => p.proveedor_id === id);
      if (index !== -1) {
        proveedores.value.splice(index, 1);
        paginationProveedores.value.total -= 1;
      }
      
      if (currentProveedor.value?.proveedor_id === id) {
        currentProveedor.value = null;
      }
    } catch (err: any) {
      setError(err.message || 'Error al eliminar proveedor');
      console.error('Error deleting proveedor:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  // Gestión de pagos
  const fetchPagos = async (filters?: PagoFilters) => {
    try {
      setLoading(true);
      clearError();
      
      const response = await proveedorService.getAllPagos(filters);
      
      if (response.data) {
        pagos.value = Array.isArray(response.data) ? response.data : [];
      }
    } catch (err: any) {
      setError(err.message || 'Error al cargar pagos');
      console.error('Error fetching pagos:', err);
    } finally {
      setLoading(false);
    }
  };

  const createPago = async (data: CreatePagoProveedorRequest) => {
    try {
      setLoading(true);
      clearError();
      
      const response = await proveedorService.createPago(data);
      
      if (response.data) {
        pagos.value.unshift(response.data);
        paginationPagos.value.total += 1;
      }
      
      return response.data;
    } catch (err: any) {
      setError(err.message || 'Error al crear pago');
      console.error('Error creating pago:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const deletePago = async (id: number) => {
    try {
      setLoading(true);
      clearError();
      
      await proveedorService.deletePago(id);
      
      const index = pagos.value.findIndex(p => p.id_pago_proveedor === id);
      if (index !== -1) {
        pagos.value.splice(index, 1);
        paginationPagos.value.total -= 1;
      }
      
      if (currentPago.value?.id_pago_proveedor === id) {
        currentPago.value = null;
      }
    } catch (err: any) {
      setError(err.message || 'Error al eliminar pago');
      console.error('Error deleting pago:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const fetchMetricas = async () => {
    try {
      const response = await proveedorService.getMetricasPagos();
      metricas.value = response.data || {};
    } catch (err: any) {
      console.error('Error fetching metricas:', err);
    }
  };

  const clearCurrentProveedor = () => {
    currentProveedor.value = null;
  };

  const clearCurrentPago = () => {
    currentPago.value = null;
  };

  return {
    // State
    proveedores,
    pagos,
    currentProveedor,
    currentPago,
    paginationProveedores,
    paginationPagos,
    loading,
    error,
    metricas,
    filtersProveedores,
    filtersPagos,
    
    // Getters
    hasProveedores,
    hasPagos,
    isLoading,
    hasError,
    totalProveedores,
    totalPagos,
    proveedoresConDeuda,
    
    // Actions
    setLoading,
    setError,
    clearError,
    fetchProveedores,
    fetchProveedor,
    createProveedor,
    updateProveedor,
    deleteProveedor,
    fetchPagos,
    createPago,
    deletePago,
    fetchMetricas,
    clearCurrentProveedor,
    clearCurrentPago
  };
});
