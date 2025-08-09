import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import productoService from '@/services/producto.service';
import type { ProductoAutomotriz, ProductoDespensa, PaginatedResponse, CreateProductoAutomotrizRequest, UpdateProductoAutomotrizRequest, CreateProductoDespensaRequest, UpdateProductoDespensaRequest, ProductoFilters } from '@/types';

export const useProductoStore = defineStore('producto', () => {
  // State
  const productosAutomotrices = ref<ProductoAutomotriz[]>([]);
  const productosDespensa = ref<ProductoDespensa[]>([]);
  const currentProductoAutomotriz = ref<ProductoAutomotriz | null>(null);
  const currentProductoDespensa = ref<ProductoDespensa | null>(null);
  
  const paginationAutomotriz = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  });
  
  const paginationDespensa = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  });
  
  const loading = ref(false);
  const error = ref<string | null>(null);
  const metricas = ref<any>({});
  
  const filtersAutomotriz = ref<ProductoFilters>({
    page: 1,
    per_page: 15,
    search: '',
  });
  
  const filtersDespensa = ref<ProductoFilters>({
    page: 1,
    per_page: 15,
    search: '',
  });

  // Getters
  const hasProductosAutomotrices = computed(() => productosAutomotrices.value.length > 0);
  const hasProductosDespensa = computed(() => productosDespensa.value.length > 0);
  const isLoading = computed(() => loading.value);
  const hasError = computed(() => error.value !== null);
  
  const totalProductosAutomotrices = computed(() => paginationAutomotriz.value.total);
  const totalProductosDespensa = computed(() => paginationDespensa.value.total);
  
  const currentPageAutomotriz = computed(() => paginationAutomotriz.value.current_page);
  const lastPageAutomotriz = computed(() => paginationAutomotriz.value.last_page);
  const currentPageDespensa = computed(() => paginationDespensa.value.current_page);
  const lastPageDespensa = computed(() => paginationDespensa.value.last_page);

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

  // ===== PRODUCTOS AUTOMOTRICES =====
  
  const setFiltersAutomotriz = (newFilters: Partial<ProductoFilters>) => {
    filtersAutomotriz.value = { ...filtersAutomotriz.value, ...newFilters };
  };

  const clearFiltersAutomotriz = () => {
    filtersAutomotriz.value = {
      page: 1,
      per_page: 15,
      search: '',
    };
  };

  const fetchProductosAutomotrices = async (customFilters?: ProductoFilters) => {
    try {
      setLoading(true);
      clearError();
      
      const filtersToUse = customFilters || filtersAutomotriz.value;
      const response: PaginatedResponse<ProductoAutomotriz> = await productoService.getProductosAutomotrices(filtersToUse);
      
      productosAutomotrices.value = response.data;
      paginationAutomotriz.value = {
        current_page: response.current_page,
        last_page: response.last_page,
        per_page: response.per_page,
        total: response.total,
      };
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al cargar productos automotrices');
      console.error('Error fetching productos automotrices:', err);
    } finally {
      setLoading(false);
    }
  };

  const fetchProductoAutomotriz = async (id: number) => {
    try {
      setLoading(true);
      clearError();
      
  const resp = await productoService.getProductoAutomotrizById(id);
  currentProductoAutomotriz.value = resp.data ?? null;
  return resp.data ?? null;
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al cargar el producto automotriz');
      console.error('Error fetching producto automotriz:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const createProductoAutomotriz = async (data: CreateProductoAutomotrizRequest) => {
    try {
      setLoading(true);
      clearError();
      
  const resp = await productoService.createProductoAutomotriz(data);
      
      // Actualizar la lista
      await fetchProductosAutomotrices();
      
  return resp.data ?? null;
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al crear el producto automotriz');
      console.error('Error creating producto automotriz:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const updateProductoAutomotriz = async (id: number, data: UpdateProductoAutomotrizRequest) => {
    try {
      setLoading(true);
      clearError();
      
  const resp = await productoService.updateProductoAutomotriz(id, data);
  const updatedProducto = resp.data;
      
      // Actualizar en la lista local
      const index = productosAutomotrices.value.findIndex(p => p.producto_automotriz_id === id);
      if (index !== -1 && updatedProducto) {
        productosAutomotrices.value[index] = updatedProducto;
      }
      
      // Actualizar el producto actual si coincide
      if (currentProductoAutomotriz.value?.producto_automotriz_id === id) {
        currentProductoAutomotriz.value = updatedProducto ?? null;
      }
      
  return updatedProducto ?? null;
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al actualizar el producto automotriz');
      console.error('Error updating producto automotriz:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const deleteProductoAutomotriz = async (id: number) => {
    try {
      setLoading(true);
      clearError();
      
  await productoService.deleteProductoAutomotriz(id);
      
      // Remover de la lista local
      productosAutomotrices.value = productosAutomotrices.value.filter(p => p.producto_automotriz_id !== id);
      
      // Limpiar producto actual si coincide
      if (currentProductoAutomotriz.value?.producto_automotriz_id === id) {
        currentProductoAutomotriz.value = null;
      }
      
      // Actualizar paginación
      paginationAutomotriz.value.total--;
      
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al eliminar el producto automotriz');
      console.error('Error deleting producto automotriz:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const updateStockAutomotriz = async (id: number, data: { stock: number }) => {
    try {
      setLoading(true);
      clearError();
      
  const resp = await productoService.updateStockAutomotriz(id, data.stock);
  const updatedProducto = resp.data;
      
      // Actualizar en la lista local
      const index = productosAutomotrices.value.findIndex(p => p.producto_automotriz_id === id);
      if (index !== -1 && updatedProducto) {
        productosAutomotrices.value[index] = updatedProducto;
      }
      
  return updatedProducto ?? null;
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al actualizar el stock');
      console.error('Error updating stock:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  // ===== PRODUCTOS DE DESPENSA =====
  
  const setFiltersDespensa = (newFilters: Partial<ProductoFilters>) => {
    filtersDespensa.value = { ...filtersDespensa.value, ...newFilters };
  };

  const clearFiltersDespensa = () => {
    filtersDespensa.value = {
      page: 1,
      per_page: 15,
      search: '',
    };
  };

  const fetchProductosDespensa = async (customFilters?: ProductoFilters) => {
    try {
      setLoading(true);
      clearError();
      
      const filtersToUse = customFilters || filtersDespensa.value;
      const response: PaginatedResponse<ProductoDespensa> = await productoService.getProductosDespensa(filtersToUse);
      
      productosDespensa.value = response.data;
      paginationDespensa.value = {
        current_page: response.current_page,
        last_page: response.last_page,
        per_page: response.per_page,
        total: response.total,
      };
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al cargar productos de despensa');
      console.error('Error fetching productos despensa:', err);
    } finally {
      setLoading(false);
    }
  };

  const createProductoDespensa = async (data: CreateProductoDespensaRequest) => {
    try {
      setLoading(true);
      clearError();
      
  const resp = await productoService.createProductoDespensa(data);
      
      // Actualizar la lista
      await fetchProductosDespensa();
      
  return resp.data ?? null;
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al crear el producto de despensa');
      console.error('Error creating producto despensa:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const updateProductoDespensa = async (id: number, data: UpdateProductoDespensaRequest) => {
    try {
      setLoading(true);
      clearError();
      
  const resp = await productoService.updateProductoDespensa(id, data);
  const updatedProducto = resp.data;
      
      // Actualizar en la lista local
      const index = productosDespensa.value.findIndex(p => p.producto_despensa_id === id);
      if (index !== -1 && updatedProducto) {
        productosDespensa.value[index] = updatedProducto;
      }
      
      // Actualizar el producto actual si coincide
      if (currentProductoDespensa.value?.producto_despensa_id === id) {
        currentProductoDespensa.value = updatedProducto ?? null;
      }
      
  return updatedProducto ?? null;
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al actualizar el producto de despensa');
      console.error('Error updating producto despensa:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const deleteProductoDespensa = async (id: number) => {
    try {
      setLoading(true);
      clearError();
      
  await productoService.deleteProductoDespensa(id);
      
      // Remover de la lista local
      productosDespensa.value = productosDespensa.value.filter(p => p.producto_despensa_id !== id);
      
      // Limpiar producto actual si coincide
      if (currentProductoDespensa.value?.producto_despensa_id === id) {
        currentProductoDespensa.value = null;
      }
      
      // Actualizar paginación
      paginationDespensa.value.total--;
      
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al eliminar el producto de despensa');
      console.error('Error deleting producto despensa:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const updateStockDespensa = async (id: number, data: { stock: number }) => {
    try {
      setLoading(true);
      clearError();
      
  const resp = await productoService.updateStockDespensa(id, data.stock);
  const updatedProducto = resp.data;
      
      // Actualizar en la lista local
      const index = productosDespensa.value.findIndex(p => p.producto_despensa_id === id);
      if (index !== -1 && updatedProducto) {
        productosDespensa.value[index] = updatedProducto;
      }
      
  return updatedProducto ?? null;
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al actualizar el stock');
      console.error('Error updating stock despensa:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  // ===== MÉTODOS GENERALES =====
  
  const fetchMetricas = async () => {
    try {
      setLoading(true);
      clearError();
      
  const resp = await productoService.getMetricas();
  metricas.value = resp.data || {};
  return metricas.value;
    } catch (err: any) {
      setError(err.response?.data?.message || 'Error al cargar las métricas');
      console.error('Error fetching metricas:', err);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const changePageAutomotriz = async (page: number) => {
    setFiltersAutomotriz({ page });
    await fetchProductosAutomotrices();
  };

  const changePageDespensa = async (page: number) => {
    setFiltersDespensa({ page });
    await fetchProductosDespensa();
  };

  const searchAutomotriz = async (searchTerm: string) => {
    setFiltersAutomotriz({ page: 1, search: searchTerm });
    await fetchProductosAutomotrices();
  };

  const searchDespensa = async (searchTerm: string) => {
    setFiltersDespensa({ page: 1, search: searchTerm });
    await fetchProductosDespensa();
  };

  // Reset store
  const $reset = () => {
    productosAutomotrices.value = [];
    productosDespensa.value = [];
    currentProductoAutomotriz.value = null;
    currentProductoDespensa.value = null;
    paginationAutomotriz.value = {
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0,
    };
    paginationDespensa.value = {
      current_page: 1,
      last_page: 1,
      per_page: 15,
      total: 0,
    };
    loading.value = false;
    error.value = null;
    metricas.value = {};
    filtersAutomotriz.value = {
      page: 1,
      per_page: 15,
      search: '',
    };
    filtersDespensa.value = {
      page: 1,
      per_page: 15,
      search: '',
    };
  };

  return {
    // State
    productosAutomotrices,
    productosDespensa,
    currentProductoAutomotriz,
    currentProductoDespensa,
    paginationAutomotriz,
    paginationDespensa,
    loading,
    error,
    metricas,
    filtersAutomotriz,
    filtersDespensa,
    
    // Getters
    hasProductosAutomotrices,
    hasProductosDespensa,
    isLoading,
    hasError,
    totalProductosAutomotrices,
    totalProductosDespensa,
    currentPageAutomotriz,
    lastPageAutomotriz,
    currentPageDespensa,
    lastPageDespensa,
    
    // Actions - Productos Automotrices
    setFiltersAutomotriz,
    clearFiltersAutomotriz,
    fetchProductosAutomotrices,
    fetchProductoAutomotriz,
    createProductoAutomotriz,
    updateProductoAutomotriz,
    deleteProductoAutomotriz,
    updateStockAutomotriz,
    changePageAutomotriz,
    searchAutomotriz,
    
    // Actions - Productos Despensa
    setFiltersDespensa,
    clearFiltersDespensa,
    fetchProductosDespensa,
    createProductoDespensa,
    updateProductoDespensa,
    deleteProductoDespensa,
    updateStockDespensa,
    changePageDespensa,
    searchDespensa,
    
    // Actions - General
    fetchMetricas,
    setError,
    clearError,
    setLoading,
    $reset,
  };
});
