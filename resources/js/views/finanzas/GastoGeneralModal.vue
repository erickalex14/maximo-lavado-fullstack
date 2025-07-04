<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
      <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ isEditing ? 'Editar Gasto General' : 'Nuevo Gasto General' }}
        </h3>
      </div>

      <form @submit.prevent="handleSubmit" class="px-6 py-4 space-y-4">
        <!-- Fecha -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Fecha <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.fecha"
            type="date"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
            :class="{ 'border-red-500': errors.fecha }"
          />
          <p v-if="errors.fecha" class="text-red-500 text-xs mt-1">{{ errors.fecha }}</p>
        </div>

        <!-- Nombre -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Nombre <span class="text-red-500">*</span>
          </label>
          <input
            v-model="form.nombre"
            type="text"
            required
            placeholder="Ej: Servicios públicos, Mantenimiento, Publicidad..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
            :class="{ 'border-red-500': errors.nombre }"
          />
          <p v-if="errors.nombre" class="text-red-500 text-xs mt-1">{{ errors.nombre }}</p>
        </div>

        <!-- Monto -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Monto <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <span class="absolute left-3 top-2 text-gray-500">$</span>
            <input
              v-model="form.monto"
              type="number"
              min="0"
              step="0.01"
              required
              placeholder="0.00"
              class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
              :class="{ 'border-red-500': errors.monto }"
            />
          </div>
          <p v-if="errors.monto" class="text-red-500 text-xs mt-1">{{ errors.monto }}</p>
        </div>

        <!-- Descripción -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Descripción
          </label>
          <textarea
            v-model="form.descripcion"
            rows="3"
            placeholder="Descripción opcional del gasto..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
            :class="{ 'border-red-500': errors.descripcion }"
          ></textarea>
          <p v-if="errors.descripcion" class="text-red-500 text-xs mt-1">{{ errors.descripcion }}</p>
        </div>
      </form>

      <!-- Botones -->
      <div class="px-6 py-4 border-t flex justify-end gap-3">
        <button
          @click="$emit('close')"
          type="button"
          class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg"
        >
          Cancelar
        </button>
        <button
          @click="handleSubmit"
          :disabled="isSubmitting"
          class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ isSubmitting ? 'Guardando...' : (isEditing ? 'Actualizar' : 'Crear') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { useFinanzasStore } from '@/stores/finanzas';
import type { GastoGeneral, CreateGastoGeneralRequest, UpdateGastoGeneralRequest } from '@/types';

interface Props {
  isOpen: boolean;
  gasto?: GastoGeneral | null;
}

const props = withDefaults(defineProps<Props>(), {
  gasto: null
});

const emit = defineEmits<{
  close: [];
  save: [];
}>();

// Store
const finanzasStore = useFinanzasStore();

// Estado local
const form = ref<CreateGastoGeneralRequest>({
  fecha: new Date().toISOString().split('T')[0],
  nombre: '',
  monto: 0,
  descripcion: ''
});

const errors = ref<Record<string, string>>({});
const isSubmitting = ref(false);

// Computed
const isEditing = computed(() => !!props.gasto);

// Métodos
const resetForm = () => {
  form.value = {
    fecha: new Date().toISOString().split('T')[0],
    nombre: '',
    monto: 0,
    descripcion: ''
  };
  errors.value = {};
};

const validateForm = (): boolean => {
  errors.value = {};

  if (!form.value.fecha) {
    errors.value.fecha = 'La fecha es requerida';
  }

  if (!form.value.nombre?.trim()) {
    errors.value.nombre = 'El nombre es requerido';
  }

  if (!form.value.monto || form.value.monto <= 0) {
    errors.value.monto = 'El monto debe ser mayor a 0';
  }

  return Object.keys(errors.value).length === 0;
};

const handleSubmit = async () => {
  if (!validateForm()) return;

  isSubmitting.value = true;

  try {
    if (isEditing.value && props.gasto) {
      // TODO: Implementar updateGastoGeneral en el store cuando esté disponible en el backend
      console.log('Edición de gasto general no implementada aún');
      emit('save');
      return;
    } else {
      await finanzasStore.createGastoGeneral(form.value);
    }

    emit('save');
  } catch (error: any) {
    console.error('Error saving gasto general:', error);
    
    // Manejar errores de validación del backend
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    }
  } finally {
    isSubmitting.value = false;
  }
};

// Watchers
watch(() => props.isOpen, (newValue) => {
  if (newValue) {
    if (props.gasto) {
      // Cargar datos para edición
      form.value = {
        fecha: props.gasto.fecha,
        nombre: props.gasto.nombre,
        monto: props.gasto.monto,
        descripcion: props.gasto.descripcion || ''
      };
    } else {
      resetForm();
    }
  }
});

watch(() => props.gasto, (newGasto) => {
  if (newGasto && props.isOpen) {
    form.value = {
      fecha: newGasto.fecha,
      nombre: newGasto.nombre,
      monto: newGasto.monto,
      descripcion: newGasto.descripcion || ''
    };
  }
});
</script>
