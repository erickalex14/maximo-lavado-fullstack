<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="flex justify-between items-center p-6 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">
          {{ isEditing ? 'Editar Lavado' : 'Nuevo Lavado' }}
        </h2>
        <button
          @click="closeModal"
          class="text-gray-500 hover:text-gray-700"
        >
          <XMarkIcon class="h-6 w-6" />
        </button>
      </div>

      <!-- Form -->
      <form @submit.prevent="submitForm" class="p-6 space-y-6">
        <!-- Vehículo -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Vehículo <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.vehiculo_id"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            :class="{ 'border-red-500': errors.vehiculo_id }"
          >
            <option value="">Seleccionar vehículo</option>
            <option v-for="vehiculo in vehiculos" :key="vehiculo.vehiculo_id" :value="vehiculo.vehiculo_id">
              {{ formatTipoVehiculo(vehiculo.tipo) }} - {{ vehiculo.cliente?.nombre }}
              <span v-if="vehiculo.matricula">({{ vehiculo.matricula }})</span>
              <span v-if="vehiculo.descripcion"> - {{ vehiculo.descripcion }}</span>
            </option>
          </select>
          <p v-if="errors.vehiculo_id" class="text-red-500 text-sm mt-1">{{ errors.vehiculo_id }}</p>
        </div>

        <!-- Empleado -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Empleado <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.empleado_id"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            :class="{ 'border-red-500': errors.empleado_id }"
          >
            <option value="">Seleccionar empleado</option>
            <option v-for="empleado in empleados" :key="empleado.empleado_id" :value="empleado.empleado_id">
              {{ empleado.nombres }} {{ empleado.apellidos }}
            </option>
          </select>
          <p v-if="errors.empleado_id" class="text-red-500 text-sm mt-1">{{ errors.empleado_id }}</p>
        </div>

        <!-- Fecha -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Fecha <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.fecha"
            type="date"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            :class="{ 'border-red-500': errors.fecha }"
          />
          <p v-if="errors.fecha" class="text-red-500 text-sm mt-1">{{ errors.fecha }}</p>
        </div>

        <!-- Tipo de Lavado -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Tipo de Lavado <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.tipo_lavado"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            :class="{ 'border-red-500': errors.tipo_lavado }"
          >
            <option value="">Seleccionar tipo</option>
            <option value="completo">Completo</option>
            <option value="solo_fuera">Solo Por Fuera</option>
            <option value="solo_por_dentro">Solo Por Dentro</option>
          </select>
          <p v-if="errors.tipo_lavado" class="text-red-500 text-sm mt-1">{{ errors.tipo_lavado }}</p>
        </div>

        <!-- Precio -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Precio <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <span class="absolute left-3 top-2 text-gray-500">$</span>
            <input
              v-model.number="form.precio"
              type="number"
              step="0.01"
              min="0"
              required
              placeholder="0.00"
              class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="{ 'border-red-500': errors.precio }"
            />
          </div>
          <p v-if="errors.precio" class="text-red-500 text-sm mt-1">{{ errors.precio }}</p>
        </div>

        <!-- Pulverizado -->
        <div class="flex items-center">
          <input
            v-model="form.pulverizado"
            type="checkbox"
            id="pulverizado"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          />
          <label for="pulverizado" class="ml-2 block text-sm text-gray-700">
            Pulverizado
          </label>
        </div>

        <!-- Botones -->
        <div class="flex justify-end gap-3 pt-6 border-t border-gray-200">
          <button
            type="button"
            @click="closeModal"
            class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
          >
            <span v-if="loading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
            {{ isEditing ? 'Actualizar' : 'Crear' }} Lavado
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useLavadoStore } from '@/stores/lavado';
import { useVehiculoStore } from '@/stores/vehiculo';
import { useEmpleadoStore } from '@/stores/empleado';
import type { Lavado, CreateLavadoRequest, UpdateLavadoRequest } from '@/types';
import { XMarkIcon } from '@heroicons/vue/24/outline';

// Props
interface Props {
  lavado?: Lavado | null;
  isEditing?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  lavado: null,
  isEditing: false,
});

// Emits
const emit = defineEmits<{
  close: [];
  saved: [];
}>();

// Stores
const lavadoStore = useLavadoStore();
const vehiculoStore = useVehiculoStore();
const empleadoStore = useEmpleadoStore();

// Estado local
const loading = ref(false);
const errors = ref<Record<string, string>>({});

// Form data
const form = ref<CreateLavadoRequest>({
  vehiculo_id: 0,
  empleado_id: 0,
  fecha: new Date().toISOString().split('T')[0], // Fecha actual por defecto
  tipo_lavado: 'completo',
  precio: 0,
  pulverizado: false,
});

// Computed
const vehiculos = computed(() => vehiculoStore.vehiculos);
const empleados = computed(() => empleadoStore.empleados);

// Watchers
watch(() => props.lavado, (newLavado) => {
  if (newLavado && props.isEditing) {
    form.value = {
      vehiculo_id: newLavado.vehiculo_id,
      empleado_id: newLavado.empleado_id,
      fecha: newLavado.fecha,
      tipo_lavado: newLavado.tipo_lavado,
      precio: newLavado.precio,
      pulverizado: newLavado.pulverizado,
    };
  }
}, { immediate: true });

// Métodos
const loadData = async () => {
  await Promise.all([
    vehiculoStore.fetchVehiculos({ per_page: 100 }),
    empleadoStore.fetchEmpleados({ per_page: 100 }),
  ]);
};

const validateForm = (): boolean => {
  errors.value = {};
  let isValid = true;

  if (!form.value.vehiculo_id) {
    errors.value.vehiculo_id = 'El vehículo es requerido';
    isValid = false;
  }

  if (!form.value.empleado_id) {
    errors.value.empleado_id = 'El empleado es requerido';
    isValid = false;
  }

  if (!form.value.fecha) {
    errors.value.fecha = 'La fecha es requerida';
    isValid = false;
  }

  if (!form.value.tipo_lavado) {
    errors.value.tipo_lavado = 'El tipo de lavado es requerido';
    isValid = false;
  }

  if (!form.value.precio || form.value.precio <= 0) {
    errors.value.precio = 'El precio debe ser mayor a 0';
    isValid = false;
  }

  return isValid;
};

const submitForm = async () => {
  if (!validateForm()) {
    return;
  }

  try {
    loading.value = true;
    errors.value = {};

    if (props.isEditing && props.lavado) {
      await lavadoStore.updateLavado(props.lavado.lavado_id, form.value as UpdateLavadoRequest);
    } else {
      await lavadoStore.createLavado(form.value);
    }

    emit('saved');
  } catch (error: any) {
    console.error('Error saving lavado:', error);
    
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      // Error general
      const message = error.response?.data?.message || 'Error al guardar el lavado';
      errors.value.general = message;
    }
  } finally {
    loading.value = false;
  }
};

const closeModal = () => {
  emit('close');
};

const formatTipoVehiculo = (tipo: string) => {
  const tipos = {
    'moto': 'Moto',
    'camioneta': 'Camioneta',
    'auto_pequeno': 'Auto Pequeño',
    'auto_mediano': 'Auto Mediano'
  };
  return tipos[tipo as keyof typeof tipos] || tipo;
};

// Lifecycle
onMounted(() => {
  loadData();
});
</script>
