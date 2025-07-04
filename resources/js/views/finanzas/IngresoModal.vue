<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
      <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ isEditing ? 'Editar Ingreso' : 'Nuevo Ingreso' }}
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
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
            :class="{ 'border-red-500': errors.fecha }"
          />
          <p v-if="errors.fecha" class="text-red-500 text-xs mt-1">{{ errors.fecha }}</p>
        </div>

        <!-- Tipo -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Tipo <span class="text-red-500">*</span>
          </label>
          <select
            v-model="form.tipo"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
            :class="{ 'border-red-500': errors.tipo }"
          >
            <option value="">Seleccionar tipo</option>
            <option value="lavado">Lavado</option>
            <option value="producto_automotriz">Producto Automotriz</option>
            <option value="producto_despensa">Producto de Despensa</option>
          </select>
          <p v-if="errors.tipo" class="text-red-500 text-xs mt-1">{{ errors.tipo }}</p>
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
              class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
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
            placeholder="Descripción opcional del ingreso..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
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
          class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
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
import type { Ingreso } from '@/types';

interface Props {
  isOpen: boolean;
  ingreso?: Ingreso | null;
}

const props = withDefaults(defineProps<Props>(), {
  ingreso: null
});

const emit = defineEmits<{
  close: [];
  save: [];
}>();

// Store
const finanzasStore = useFinanzasStore();

// Estado local
const form = ref({
  fecha: new Date().toISOString().split('T')[0],
  tipo: 'lavado' as 'lavado' | 'producto_automotriz' | 'producto_despensa',
  referencia_id: null as number | null,
  monto: 0,
  descripcion: ''
});

const errors = ref<Record<string, string>>({});
const isSubmitting = ref(false);

// Computed
const isEditing = computed(() => !!props.ingreso);

// Métodos
const resetForm = () => {
  form.value = {
    fecha: new Date().toISOString().split('T')[0],
    tipo: 'lavado',
    referencia_id: null,
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

  if (!form.value.tipo?.trim()) {
    errors.value.tipo = 'El tipo es requerido';
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
    if (isEditing.value && props.ingreso) {
      // TODO: Implementar updateIngreso cuando esté disponible
      console.log('Edición de ingreso no implementada aún');
      emit('save');
      return;
    } else {
      // Para crear, necesitamos construir el request según el tipo
      const createRequest: any = {
        fecha: form.value.fecha,
        tipo: form.value.tipo,
        monto: form.value.monto,
        descripcion: form.value.descripcion
      };
      
      // Agregar referencia_id si existe
      if (form.value.referencia_id) {
        createRequest.referencia_id = form.value.referencia_id;
      }
      
      await finanzasStore.createIngreso(createRequest as any);
    }

    emit('save');
  } catch (error: any) {
    console.error('Error saving ingreso:', error);
    
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
    if (props.ingreso) {
      // Cargar datos para edición
      form.value = {
        fecha: props.ingreso.fecha,
        tipo: props.ingreso.tipo,
        referencia_id: props.ingreso.referencia_id || null,
        monto: props.ingreso.monto,
        descripcion: props.ingreso.descripcion || ''
      };
    } else {
      resetForm();
    }
  }
});

watch(() => props.ingreso, (newIngreso) => {
  if (newIngreso && props.isOpen) {
    form.value = {
      fecha: newIngreso.fecha,
      tipo: newIngreso.tipo,
      referencia_id: newIngreso.referencia_id || null,
      monto: newIngreso.monto,
      descripcion: newIngreso.descripcion || ''
    };
  }
});
</script>
