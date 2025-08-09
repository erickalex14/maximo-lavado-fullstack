<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
      <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ vehiculo ? 'Editar Vehículo' : 'Nuevo Vehículo' }}
        </h3>
      </div>

      <form @submit.prevent="submitForm" class="px-6 py-4 space-y-4">
        <!-- Cliente -->
        <div>
          <label for="cliente_id" class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
          <select id="cliente_id" v-model.number="form.cliente_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            <option :value="0">Seleccionar cliente</option>
            <option v-for="c in clienteStore.clientesSelect" :key="c.cliente_id" :value="c.cliente_id">
              {{ c.nombre }} - {{ c.cedula }}
            </option>
          </select>
          <span v-if="errors.cliente_id" class="text-red-500 text-sm">{{ errors.cliente_id[0] }}</span>
        </div>

        <!-- Tipo de Vehículo -->
        <div>
          <label for="tipo_vehiculo_id" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Vehículo *</label>
          <select id="tipo_vehiculo_id" v-model.number="form.tipo_vehiculo_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            <option :value="0">Seleccionar tipo</option>
            <option v-for="t in tipoVehiculoStore.tipos" :key="t.tipo_vehiculo_id" :value="t.tipo_vehiculo_id">{{ t.nombre }}</option>
          </select>
          <span v-if="errors.tipo_vehiculo_id" class="text-red-500 text-sm">{{ errors.tipo_vehiculo_id[0] }}</span>
        </div>

        <!-- Matrícula -->
        <div>
          <label for="matricula" class="block text-sm font-medium text-gray-700 mb-1">
            Matrícula {{ matriculaRequired ? '*' : '(Opcional si el tipo lo permite)' }}
          </label>
          <input
            id="matricula"
            v-model="form.matricula"
            type="text"
            :required="matriculaRequired"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            :placeholder="matriculaRequired ? 'Matrícula del vehículo' : 'Matrícula (opcional)'"
          />
          <span v-if="errors.matricula" class="text-red-500 text-sm">{{ errors.matricula[0] }}</span>
          <p v-if="!matriculaRequired" class="text-xs text-gray-500 mt-1">Este tipo de vehículo puede omitir matrícula.</p>
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
            placeholder="Descripción del vehículo (color, modelo, etc.)"
          ></textarea>
          <span v-if="errors.descripcion" class="text-red-500 text-sm">{{ errors.descripcion[0] }}</span>
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
            <span v-else>{{ vehiculo ? 'Actualizar' : 'Crear' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, computed, onMounted } from 'vue';
import type { Vehiculo } from '@/types';
import { useVehiculoStore } from '@/stores/vehiculo';
import { useClienteStore } from '@/stores/cliente';
import { useTipoVehiculoStore } from '@/stores/tipoVehiculo';

interface Props {
  isOpen: boolean;
  vehiculo?: Vehiculo | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  close: [];
  success: [];
}>();

// Estado del formulario
const form = ref<any>({
  cliente_id: 0,
  tipo_vehiculo_id: 0,
  matricula: '',
  descripcion: ''
});

const loading = ref(false);
const errors = ref<Record<string, string[]>>({});
const vehiculoStore = useVehiculoStore();
const clienteStore = useClienteStore();
const tipoVehiculoStore = useTipoVehiculoStore();

// Computed para determinar si la matrícula es requerida
const matriculaRequired = computed(() => {
  const t = tipoVehiculoStore.tipos.find(x => x.tipo_vehiculo_id === form.value.tipo_vehiculo_id);
  if (!t) return true;
  if (typeof t.requiere_matricula === 'boolean') return t.requiere_matricula;
  return !/moto/i.test(t.nombre);
});

// Al cambiar tipo limpiar matrícula si deja de ser requerida
watch(() => form.value.tipo_vehiculo_id, () => {
  if (!matriculaRequired.value) {
    form.value.matricula = '';
  }
});

// Llenar formulario cuando se edita
watch(() => props.vehiculo, (vehiculo) => {
  if (vehiculo) {
    form.value = {
      cliente_id: vehiculo.cliente_id,
      tipo_vehiculo_id: vehiculo.tipo_vehiculo_id,
      matricula: vehiculo.matricula || '',
      descripcion: vehiculo.descripcion || ''
    };
  } else {
    form.value = { cliente_id: 0, tipo_vehiculo_id: 0, matricula: '', descripcion: '' };
  }
  errors.value = {};
}, { immediate: true });

// Enviar formulario
const submitForm = async () => {
  try {
    loading.value = true;
    errors.value = {};

    // Validación adicional de matrícula
    if (matriculaRequired.value && !form.value.matricula?.trim()) {
      errors.value.matricula = ['La matrícula es requerida para este tipo de vehículo'];
      return;
    }

    // Limpiar matrícula si es moto y está vacía
    const formData = { ...form.value };
    if (!matriculaRequired.value && !formData.matricula?.trim()) formData.matricula = undefined;

    if (props.vehiculo) {
      await vehiculoStore.updateVehiculo(props.vehiculo.vehiculo_id, formData);
    } else {
      await vehiculoStore.createVehiculo(formData);
    }

    emit('success');
  } catch (error: any) {
    console.error('Error saving vehiculo:', error);
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    }
  } finally {
    loading.value = false;
  }
};

// Cargar datos al montar
onMounted(async () => {
  if (!clienteStore.clientesSelect.length) await clienteStore.fetchClientesForSelect();
  if (!tipoVehiculoStore.tipos.length) await tipoVehiculoStore.fetchTipos();
});
</script>
