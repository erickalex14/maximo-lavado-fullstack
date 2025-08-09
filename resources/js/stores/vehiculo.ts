import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import vehiculoService from '@/services/vehiculo.service';
import type { Vehiculo, PaginatedResponse, VehiculoFilters } from '@/types';

export const useVehiculoStore = defineStore('vehiculo', () => {
  // State
  const vehiculos = ref<Vehiculo[]>([]);
  const vehiculosPaginados = ref<PaginatedResponse<Vehiculo> | null>(null);
  const vehiculoActual = ref<Vehiculo | null>(null);
  const estadisticas = ref<any | null>(null);
  const trashedVehiculos = ref<Vehiculo[]>([]);
  const loading = ref(false);
  const error = ref<string | null>(null);

  // Getters
  const getVehiculoById = computed(() => {
    return (id: number) => vehiculos.value.find(v => v.vehiculo_id === id);
  });

  const vehiculosActivos = computed(() => {
    return vehiculos.value.filter(v => !v.deleted_at);
  });

  const vehiculosPorTipo = computed(() => {
    return (tipoVehiculoId: number) => vehiculos.value.filter(v => v.tipo_vehiculo_id === tipoVehiculoId);
  });

  // Actions
  const fetchVehiculos = async (filters?: VehiculoFilters) => {
    try {
      loading.value = true;
      error.value = null;
      
      const response = await vehiculoService.getVehiculos(filters);
      vehiculosPaginados.value = response;
      vehiculos.value = response.data;
      
      return response;
    } catch (err: any) {
      error.value = err.message || 'Error al cargar vehículos';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const fetchAllVehiculos = async () => {
    try {
      loading.value = true;
      error.value = null;
      
  const response = await vehiculoService.getAllVehiculos();
  vehiculos.value = response.data ?? [];
      
      return response;
    } catch (err: any) {
      error.value = err.message || 'Error al cargar vehículos';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const fetchEstadisticas = async () => {
    try {
      error.value = null;
      
  const response = await vehiculoService.getStats();
  estadisticas.value = response.data ?? null;
      
      return response;
    } catch (err: any) {
      error.value = err.message || 'Error al cargar estadísticas';
      throw err;
    }
  };

  const fetchVehiculosByCliente = async (clienteId: number) => {
    try {
      loading.value = true;
      error.value = null;
      
  const response = await vehiculoService.getVehiculosByCliente(clienteId);
  return response.data ?? [];
    } catch (err: any) {
      error.value = err.message || 'Error al cargar vehículos del cliente';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const fetchVehiculoById = async (id: number) => {
    try {
      loading.value = true;
      error.value = null;
      
  const response = await vehiculoService.getVehiculoById(id);
  vehiculoActual.value = response.data ?? null;
  return response.data ?? null;
    } catch (err: any) {
      error.value = err.message || 'Error al cargar vehículo';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const createVehiculo = async (vehiculo: any) => {
    try {
      loading.value = true;
      error.value = null;
      
      const response = await vehiculoService.createVehiculo(vehiculo);
      
      // Agregar a la lista local si existe
      if (vehiculos.value && response.data) {
        vehiculos.value.unshift(response.data);
      }
      
      return response.data ?? null;
    } catch (err: any) {
      error.value = err.message || 'Error al crear vehículo';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const updateVehiculo = async (id: number, vehiculo: any) => {
    try {
      loading.value = true;
      error.value = null;
      
      const response = await vehiculoService.updateVehiculo(id, vehiculo);
      
      // Actualizar en la lista local
      const index = vehiculos.value.findIndex(v => v.vehiculo_id === id);
      if (index !== -1 && response.data) {
        vehiculos.value[index] = response.data;
      }
      
      // Actualizar vehículo actual si coincide
      if (vehiculoActual.value?.vehiculo_id === id) {
        vehiculoActual.value = response.data ?? null;
      }
      
      return response.data ?? null;
    } catch (err: any) {
      error.value = err.message || 'Error al actualizar vehículo';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const deleteVehiculo = async (id: number) => {
    try {
      loading.value = true;
      error.value = null;
      
      await vehiculoService.deleteVehiculo(id);
      
      // Remover de la lista local (soft delete)
      const vehiculo = vehiculos.value.find(v => v.vehiculo_id === id);
      if (vehiculo) {
        vehiculo.deleted_at = new Date().toISOString();
      }
      
      return true;
    } catch (err: any) {
      error.value = err.message || 'Error al eliminar vehículo';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const restoreVehiculo = async (id: number) => {
    try {
      loading.value = true;
      error.value = null;
      
      await vehiculoService.restoreVehiculo(id);
      
      // Restaurar en la lista local
      const vehiculo = vehiculos.value.find(v => v.vehiculo_id === id);
      if (vehiculo) {
        vehiculo.deleted_at = null;
      }
      
      return true;
    } catch (err: any) {
      error.value = err.message || 'Error al restaurar vehículo';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const fetchTrashedVehiculos = async () => {
    try {
      loading.value = true;
      error.value = null;
      
  const response = await vehiculoService.getTrashedVehiculos();
  trashedVehiculos.value = response.data ?? [];
      
  return trashedVehiculos.value;
    } catch (err: any) {
      error.value = err.message || 'Error al cargar vehículos eliminados';
      throw err;
    } finally {
      loading.value = false;
    }
  };

  const clearError = () => {
    error.value = null;
  };

  const clearVehiculos = () => {
    vehiculos.value = [];
    vehiculosPaginados.value = null;
    vehiculoActual.value = null;
    estadisticas.value = null;
    trashedVehiculos.value = [];
  };

  return {
    // State
    vehiculos,
    vehiculosPaginados,
    vehiculoActual,
    estadisticas,
    trashedVehiculos,
    loading,
    error,
    
    // Getters
    getVehiculoById,
    vehiculosActivos,
    vehiculosPorTipo,
    
    // Actions
    fetchVehiculos,
    fetchAllVehiculos,
    fetchEstadisticas,
    fetchVehiculosByCliente,
    fetchVehiculoById,
    createVehiculo,
    updateVehiculo,
    deleteVehiculo,
    restoreVehiculo,
    fetchTrashedVehiculos,
    clearError,
    clearVehiculos
  };
});
