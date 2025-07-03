import { defineStore } from 'pinia';
import { ref, computed, readonly } from 'vue';
import clienteService from '@/services/cliente.service';
import type { Cliente, CreateClienteForm, FilterOptions, PaginatedResponse } from '@/types';

export const useClienteStore = defineStore('cliente', () => {
  // Estado
  const clientes = ref<Cliente[]>([]);
  const clientesPaginados = ref<PaginatedResponse<Cliente> | null>(null);
  const clienteActual = ref<Cliente | null>(null);
  const clientesSelect = ref<Cliente[]>([]);
  const clientesEliminados = ref<Cliente[]>([]);
  const estadisticas = ref<any | null>(null);
  
  const loading = ref(false);
  const loadingCreate = ref(false);
  const loadingUpdate = ref(false);
  const loadingDelete = ref(false);
  const error = ref<string | null>(null);

  // Getters
  const totalClientes = computed(() => clientesPaginados.value?.total || 0);
  const hasClientes = computed(() => clientes.value.length > 0);
  const clientesOptions = computed(() => 
    clientesSelect.value.map(cliente => ({
      value: cliente.cliente_id,
      label: `${cliente.nombre} (${cliente.cedula})`,
    }))
  );

  // Actions
  async function fetchClientes(filters?: FilterOptions): Promise<void> {
    loading.value = true;
    error.value = null;

    try {
      const response = await clienteService.getAll(filters);
      
      if (response.success && response.data) {
        clientesPaginados.value = response.data;
        clientes.value = response.data.data;
      } else {
        error.value = response.message || 'Error al cargar clientes';
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Error al cargar clientes';
    } finally {
      loading.value = false;
    }
  }

  async function fetchClientesForSelect(): Promise<void> {
    try {
      const response = await clienteService.getAllForSelect();
      
      if (response.success && response.data) {
        clientesSelect.value = response.data;
      }
    } catch (err: any) {
      console.error('Error al cargar clientes para select:', err);
    }
  }

  async function fetchClienteById(id: number): Promise<Cliente | null> {
    loading.value = true;
    error.value = null;

    try {
      const response = await clienteService.getById(id);
      
      if (response.success && response.data) {
        clienteActual.value = response.data;
        return response.data;
      } else {
        error.value = response.message || 'Cliente no encontrado';
        return null;
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Error al cargar cliente';
      return null;
    } finally {
      loading.value = false;
    }
  }

  async function searchClientes(query: string): Promise<Cliente[]> {
    try {
      const response = await clienteService.search(query);
      
      if (response.success && response.data) {
        return response.data;
      }
      return [];
    } catch (err: any) {
      console.error('Error al buscar clientes:', err);
      return [];
    }
  }

  async function createCliente(data: CreateClienteForm): Promise<boolean> {
    loadingCreate.value = true;
    error.value = null;

    try {
      const response = await clienteService.create(data);
      
      if (response.success && response.data) {
        // Agregar el nuevo cliente a la lista
        clientes.value.unshift(response.data);
        return true;
      } else {
        error.value = response.message || 'Error al crear cliente';
        return false;
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Error al crear cliente';
      return false;
    } finally {
      loadingCreate.value = false;
    }
  }

  async function updateCliente(id: number, data: Partial<CreateClienteForm>): Promise<boolean> {
    loadingUpdate.value = true;
    error.value = null;

    try {
      const response = await clienteService.update(id, data);
      
      if (response.success && response.data) {
        // Actualizar en la lista
        const index = clientes.value.findIndex(c => c.cliente_id === id);
        if (index !== -1) {
          clientes.value[index] = response.data;
        }
        // Actualizar cliente actual si es el mismo
        if (clienteActual.value?.cliente_id === id) {
          clienteActual.value = response.data;
        }
        return true;
      } else {
        error.value = response.message || 'Error al actualizar cliente';
        return false;
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Error al actualizar cliente';
      return false;
    } finally {
      loadingUpdate.value = false;
    }
  }

  async function toggleActivoCliente(id: number): Promise<boolean> {
    try {
      const response = await clienteService.toggleActivo(id);
      
      if (response.success && response.data) {
        // Actualizar en la lista
        const index = clientes.value.findIndex(c => c.cliente_id === id);
        if (index !== -1) {
          clientes.value[index] = response.data;
        }
        return true;
      }
      return false;
    } catch (err: any) {
      console.error('Error al cambiar estado del cliente:', err);
      return false;
    }
  }

  async function deleteCliente(id: number): Promise<boolean> {
    loadingDelete.value = true;
    error.value = null;

    try {
      const response = await clienteService.delete(id);
      
      if (response.success) {
        // Remover de la lista
        clientes.value = clientes.value.filter(c => c.cliente_id !== id);
        return true;
      } else {
        error.value = response.message || 'Error al eliminar cliente';
        return false;
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Error al eliminar cliente';
      return false;
    } finally {
      loadingDelete.value = false;
    }
  }

  async function restoreCliente(id: number): Promise<boolean> {
    try {
      const response = await clienteService.restore(id);
      
      if (response.success && response.data) {
        // Remover de eliminados y agregar a activos
        clientesEliminados.value = clientesEliminados.value.filter(c => c.cliente_id !== id);
        clientes.value.unshift(response.data);
        return true;
      }
      return false;
    } catch (err: any) {
      console.error('Error al restaurar cliente:', err);
      return false;
    }
  }

  async function fetchClientesEliminados(): Promise<void> {
    try {
      const response = await clienteService.getTrashed();
      
      if (response.success && response.data) {
        clientesEliminados.value = response.data;
      }
    } catch (err: any) {
      console.error('Error al cargar clientes eliminados:', err);
    }
  }

  async function fetchEstadisticas(): Promise<void> {
    try {
      const response = await clienteService.getStats();
      
      if (response.success && response.data) {
        estadisticas.value = response.data;
      }
    } catch (err: any) {
      console.error('Error al cargar estadísticas de clientes:', err);
    }
  }

  function setClienteActual(cliente: Cliente | null): void {
    clienteActual.value = cliente;
  }

  function clearError(): void {
    error.value = null;
  }

  function $reset(): void {
    clientes.value = [];
    clientesPaginados.value = null;
    clienteActual.value = null;
    clientesSelect.value = [];
    clientesEliminados.value = [];
    estadisticas.value = null;
    loading.value = false;
    loadingCreate.value = false;
    loadingUpdate.value = false;
    loadingDelete.value = false;
    error.value = null;
  }

  return {
    // Estado
    clientes: readonly(clientes),
    clientesPaginados: readonly(clientesPaginados),
    clienteActual: readonly(clienteActual),
    clientesSelect: readonly(clientesSelect),
    clientesEliminados: readonly(clientesEliminados),
    estadisticas: readonly(estadisticas),
    loading: readonly(loading),
    loadingCreate: readonly(loadingCreate),
    loadingUpdate: readonly(loadingUpdate),
    loadingDelete: readonly(loadingDelete),
    error: readonly(error),
    
    // Getters
    totalClientes,
    hasClientes,
    clientesOptions,
    
    // Actions
    fetchClientes,
    fetchClientesForSelect,
    fetchClienteById,
    searchClientes,
    createCliente,
    updateCliente,
    toggleActivoCliente,
    deleteCliente,
    restoreCliente,
    fetchClientesEliminados,
    fetchEstadisticas,
    setClienteActual,
    clearError,
    $reset,
  };
});
