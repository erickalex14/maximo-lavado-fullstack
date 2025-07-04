<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
      <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ proveedor ? 'Editar Proveedor' : 'Nuevo Proveedor' }}
        </h3>
      </div>

      <form @submit.prevent="submitForm" class="px-6 py-4 space-y-4">
        <!-- Nombre -->
        <div>
          <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
            Nombre *
          </label>
          <input
            id="nombre"
            v-model="form.nombre"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            placeholder="Nombre del proveedor"
          />
          <span v-if="errors.nombre" class="text-red-500 text-sm">{{ errors.nombre[0] }}</span>
        </div>

        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
            Email
          </label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            placeholder="correo@ejemplo.com"
          />
          <span v-if="errors.email" class="text-red-500 text-sm">{{ errors.email[0] }}</span>
        </div>

        <!-- Teléfono -->
        <div>
          <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">
            Teléfono
          </label>
          <input
            id="telefono"
            v-model="form.telefono"
            type="tel"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            placeholder="Número de teléfono"
          />
          <span v-if="errors.telefono" class="text-red-500 text-sm">{{ errors.telefono[0] }}</span>
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
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            placeholder="Descripción del proveedor"
          ></textarea>
          <span v-if="errors.descripcion" class="text-red-500 text-sm">{{ errors.descripcion[0] }}</span>
        </div>

        <!-- Deuda Pendiente -->
        <div>
          <label for="deuda_pendiente" class="block text-sm font-medium text-gray-700 mb-1">
            Deuda Pendiente
          </label>
          <input
            id="deuda_pendiente"
            v-model.number="form.deuda_pendiente"
            type="number"
            step="0.01"
            min="0"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            placeholder="0.00"
          />
          <span v-if="errors.deuda_pendiente" class="text-red-500 text-sm">{{ errors.deuda_pendiente[0] }}</span>
        </div>

        <!-- Botones -->
        <div class="flex justify-end gap-3 pt-4">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="props.loading"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-50"
          >
            <span v-if="props.loading">Guardando...</span>
            <span v-else>{{ proveedor ? 'Actualizar' : 'Crear' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import type { Proveedor, CreateProveedorRequest } from '@/types';

interface Props {
  isOpen: boolean;
  proveedor?: Proveedor | null;
  loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  proveedor: null,
  loading: false
});

const emit = defineEmits<{
  close: [];
  submit: [data: CreateProveedorRequest];
}>();

// Estado del formulario
const form = ref<CreateProveedorRequest>({
  nombre: '',
  email: '',
  telefono: '',
  descripcion: '',
  deuda_pendiente: 0
});

const errors = ref<Record<string, string[]>>({});

// Función para resetear el formulario
const resetForm = () => {
  form.value = {
    nombre: '',
    email: '',
    telefono: '',
    descripcion: '',
    deuda_pendiente: 0
  };
  errors.value = {};
};

// Llenar formulario cuando se edita
watch(() => props.proveedor, (proveedor) => {
  if (proveedor) {
    form.value = {
      nombre: proveedor.nombre || '',
      email: proveedor.email || '',
      telefono: proveedor.telefono || '',
      descripcion: proveedor.descripcion || '',
      deuda_pendiente: proveedor.deuda_pendiente || 0
    };
  } else {
    resetForm();
  }
  errors.value = {};
}, { immediate: true });

// Enviar formulario
const submitForm = async () => {
  try {
    errors.value = {};
    emit('submit', form.value);
  } catch (error: any) {
    console.error('Error submitting form:', error);
  }
};
</script>
