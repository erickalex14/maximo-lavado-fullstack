<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
      <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ empleado ? 'Editar Empleado' : 'Nuevo Empleado' }}
        </h3>
      </div>

      <form @submit.prevent="submitForm" class="px-6 py-4 space-y-4">
        <!-- Nombres -->
        <div>
          <label for="nombres" class="block text-sm font-medium text-gray-700 mb-1">
            Nombres *
          </label>
          <input
            id="nombres"
            v-model="form.nombres"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            placeholder="Nombres del empleado"
          />
          <span v-if="errors.nombres" class="text-red-500 text-sm">{{ errors.nombres[0] }}</span>
        </div>

        <!-- Apellidos -->
        <div>
          <label for="apellidos" class="block text-sm font-medium text-gray-700 mb-1">
            Apellidos *
          </label>
          <input
            id="apellidos"
            v-model="form.apellidos"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            placeholder="Apellidos del empleado"
          />
          <span v-if="errors.apellidos" class="text-red-500 text-sm">{{ errors.apellidos[0] }}</span>
        </div>

        <!-- Teléfono -->
        <div>
          <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">
            Teléfono *
          </label>
          <input
            id="telefono"
            v-model="form.telefono"
            type="tel"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            placeholder="Número de teléfono"
          />
          <span v-if="errors.telefono" class="text-red-500 text-sm">{{ errors.telefono[0] }}</span>
        </div>

        <!-- Cédula -->
        <div>
          <label for="cedula" class="block text-sm font-medium text-gray-700 mb-1">
            Cédula *
          </label>
          <input
            id="cedula"
            v-model="form.cedula"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            placeholder="Número de cédula"
          />
          <span v-if="errors.cedula" class="text-red-500 text-sm">{{ errors.cedula[0] }}</span>
        </div>

        <!-- Tipo de Salario -->
        <div>
          <label for="tipo_salario" class="block text-sm font-medium text-gray-700 mb-1">
            Tipo de Salario *
          </label>
          <select
            id="tipo_salario"
            v-model="form.tipo_salario"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Seleccionar tipo de salario</option>
            <option value="mensual">Mensual</option>
            <option value="diario">Diario</option>
            <option value="quincenal">Quincenal</option>
            <option value="semanal">Semanal</option>
          </select>
          <span v-if="errors.tipo_salario" class="text-red-500 text-sm">{{ errors.tipo_salario[0] }}</span>
        </div>

        <!-- Salario -->
        <div>
          <label for="salario" class="block text-sm font-medium text-gray-700 mb-1">
            Salario *
          </label>
          <input
            id="salario"
            v-model.number="form.salario"
            type="number"
            step="0.01"
            min="0"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            placeholder="0.00"
          />
          <span v-if="errors.salario" class="text-red-500 text-sm">{{ errors.salario[0] }}</span>
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
            :disabled="loading"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-50"
          >
            <span v-if="loading">Guardando...</span>
            <span v-else>{{ empleado ? 'Actualizar' : 'Crear' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { useEmpleadoStore } from '@/stores/empleado';
import type { Empleado, CreateEmpleadoRequest } from '@/types';

interface Props {
  isOpen: boolean;
  empleado?: Empleado | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  close: [];
  success: [];
}>();

const empleadoStore = useEmpleadoStore();

// Estado del formulario
const form = ref<CreateEmpleadoRequest>({
  nombres: '',
  apellidos: '',
  telefono: '',
  cedula: '',
  tipo_salario: 'mensual',
  salario: 0
});

const loading = ref(false);
const errors = ref<Record<string, string[]>>({});

// Llenar formulario cuando se edita
watch(() => props.empleado, (empleado) => {
  if (empleado) {
    form.value = {
      nombres: empleado.nombres || '',
      apellidos: empleado.apellidos || '',
      telefono: empleado.telefono || '',
      cedula: empleado.cedula || '',
      tipo_salario: empleado.tipo_salario || 'mensual',
      salario: empleado.salario || 0
    };
  } else {
    form.value = {
      nombres: '',
      apellidos: '',
      telefono: '',
      cedula: '',
      tipo_salario: 'mensual',
      salario: 0
    };
  }
  errors.value = {};
}, { immediate: true });

// Enviar formulario
const submitForm = async () => {
  try {
    loading.value = true;
    errors.value = {};

    if (props.empleado) {
      await empleadoStore.updateEmpleado(props.empleado.empleado_id, form.value);
    } else {
      await empleadoStore.createEmpleado(form.value);
    }

    emit('success');
  } catch (error: any) {
    console.error('Error saving empleado:', error);
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    }
  } finally {
    loading.value = false;
  }
};
</script>
