<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ isEdit ? 'Editar Pago' : 'Nuevo Pago' }}
        </h3>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
        <!-- Proveedor -->
        <div>
          <label for="proveedor_id" class="block text-sm font-medium text-gray-700 mb-1">
            Proveedor *
          </label>
          <select
            id="proveedor_id"
            v-model="form.proveedor_id"
            :disabled="isEdit"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100"
            required
          >
            <option value="">Seleccionar proveedor</option>
            <option 
              v-for="proveedor in proveedores" 
              :key="proveedor.proveedor_id" 
              :value="proveedor.proveedor_id"
            >
              {{ proveedor.nombre }}
            </option>
          </select>
          <span v-if="errors.proveedor_id" class="text-red-500 text-sm">{{ errors.proveedor_id }}</span>
        </div>

        <!-- Monto -->
        <div>
          <label for="monto" class="block text-sm font-medium text-gray-700 mb-1">
            Monto *
          </label>
          <input
            id="monto"
            v-model.number="form.monto"
            type="number"
            step="0.01"
            min="0"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            required
          />
          <span v-if="errors.monto" class="text-red-500 text-sm">{{ errors.monto }}</span>
        </div>

        <!-- Fecha de Pago -->
        <div>
          <label for="fecha" class="block text-sm font-medium text-gray-700 mb-1">
            Fecha de Pago *
          </label>
          <input
            id="fecha"
            v-model="form.fecha"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            required
          />
          <span v-if="errors.fecha" class="text-red-500 text-sm">{{ errors.fecha }}</span>
        </div>

        <!-- Descripción -->
        <div>
          <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">
            Descripción
          </label>
          <textarea
            id="descripcion"
            v-model="form.descripcion"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Descripción opcional del pago"
          ></textarea>
          <span v-if="errors.descripcion" class="text-red-500 text-sm">{{ errors.descripcion }}</span>
        </div>

        <!-- Error General -->
        <div v-if="submitError" class="text-red-500 text-sm">
          {{ submitError }}
        </div>

        <!-- Buttons -->
        <div class="flex justify-end space-x-3 pt-4">
          <button
            type="button"
            @click="closeModal"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="loading" class="flex items-center">
              <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
              Guardando...
            </span>
            <span v-else>
              {{ isEdit ? 'Actualizar' : 'Crear' }}
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, watch, computed } from 'vue';
import type { PagoProveedor, Proveedor, CreatePagoProveedorRequest, UpdatePagoProveedorRequest } from '@/types';

interface Props {
  isOpen: boolean;
  pago?: PagoProveedor | null;
  proveedores: Proveedor[];
  loading?: boolean;
}

interface Emits {
  (e: 'close'): void;
  (e: 'submit', data: CreatePagoProveedorRequest | UpdatePagoProveedorRequest): void;
}

const props = withDefaults(defineProps<Props>(), {
  pago: null,
  loading: false
});

const emit = defineEmits<Emits>();

const isEdit = computed(() => !!props.pago);

const form = reactive<CreatePagoProveedorRequest & { id_pago_proveedor?: number }>({
  proveedor_id: 0,
  monto: 0,
  fecha: '',
  descripcion: ''
});

const errors = ref<Record<string, string>>({});
const submitError = ref<string>('');

// Initialize form when pago prop changes
watch(() => props.pago, (newPago) => {
  if (newPago) {
    form.id_pago_proveedor = newPago.id_pago_proveedor;
    form.proveedor_id = newPago.proveedor_id;
    form.monto = newPago.monto;
    form.fecha = newPago.fecha.split('T')[0]; // Convert to date format
    form.descripcion = newPago.descripcion || '';
  } else {
    resetForm();
  }
}, { immediate: true });

// Reset form when modal opens/closes
watch(() => props.isOpen, (isOpen) => {
  if (isOpen && !props.pago) {
    resetForm();
  }
  errors.value = {};
  submitError.value = '';
});

const resetForm = () => {
  form.proveedor_id = 0;
  form.monto = 0;
  form.fecha = new Date().toISOString().split('T')[0];
  form.descripcion = '';
  delete form.id_pago_proveedor;
};

const validateForm = (): boolean => {
  errors.value = {};
  
  if (!form.proveedor_id) {
    errors.value.proveedor_id = 'El proveedor es requerido';
  }
  
  if (!form.monto || form.monto <= 0) {
    errors.value.monto = 'El monto debe ser mayor a 0';
  }
  
  if (!form.fecha) {
    errors.value.fecha = 'La fecha de pago es requerida';
  }
  
  return Object.keys(errors.value).length === 0;
};

const handleSubmit = () => {
  if (!validateForm()) {
    return;
  }
  
  try {
    const submitData = { ...form };
    delete submitData.id_pago_proveedor;
    
    emit('submit', submitData);
  } catch (error: any) {
    submitError.value = error.message || 'Error al procesar el formulario';
  }
};

const closeModal = () => {
  emit('close');
};
</script>
