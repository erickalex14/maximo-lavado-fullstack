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
          <label for="cliente_id" class="block text-sm font-medium text-gray-700 mb-1">
            Cliente *
          </label>
          <select
            id="cliente_id"
            v-model="form.cliente_id"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Seleccionar cliente</option>
            <option v-for="cliente in clientes" :key="cliente.cliente_id" :value="cliente.cliente_id">
              {{ cliente.nombre }} - {{ cliente.cedula }}
            </option>
          </select>
          <span v-if="errors.cliente_id" class="text-red-500 text-sm">{{ errors.cliente_id[0] }}</span>
        </div>

        <!-- Tipo de Vehículo -->
        <div>
          <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">
            Tipo de Vehículo *
          </label>
          <select
            id="tipo"
            v-model="form.tipo"
            @change="onTipoChange"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Seleccionar tipo</option>
            <option value="moto">Moto</option>
            <option value="camioneta">Camioneta</option>
            <option value="auto_pequeno">Auto Pequeño</option>
            <option value="auto_mediano">Auto Mediano</option>
          </select>
          <span v-if="errors.tipo" class="text-red-500 text-sm">{{ errors.tipo[0] }}</span>
        </div>

        <!-- Matrícula -->
        <div>
          <label for="matricula" class="block text-sm font-medium text-gray-700 mb-1">
            Matrícula {{ matriculaRequired ? '*' : '(Opcional para motos)' }}
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
          <p v-if="!matriculaRequired" class="text-xs text-gray-500 mt-1">
            Las motos no requieren matrícula obligatoriamente
          </p>
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
import type { Vehiculo, CreateVehiculoRequest, Cliente } from '@/types';

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
const form = ref<CreateVehiculoRequest>({
  cliente_id: 0,
  tipo: 'moto',
  matricula: '',
  descripcion: ''
});

const loading = ref(false);
const errors = ref<Record<string, string[]>>({});
const clientes = ref<Cliente[]>([]);

// Computed para determinar si la matrícula es requerida
const matriculaRequired = computed(() => {
  // Importar la función helper desde types
  return form.value.tipo !== 'moto';
});

// Cargar clientes
const loadClientes = async () => {
  try {
    // Temporalmente usar array vacío hasta implementar el store
    clientes.value = [];
  } catch (error) {
    console.error('Error loading clientes:', error);
  }
};

// Manejar cambio de tipo de vehículo
const onTipoChange = () => {
  // Si es moto, limpiar matrícula para que sea opcional
  if (form.value.tipo === 'moto') {
    form.value.matricula = '';
  }
  // Limpiar errores de validación
  if (errors.value.matricula) {
    delete errors.value.matricula;
  }
};

// Llenar formulario cuando se edita
watch(() => props.vehiculo, (vehiculo) => {
  if (vehiculo) {
    form.value = {
      cliente_id: vehiculo.cliente_id,
      tipo: vehiculo.tipo,
      matricula: vehiculo.matricula || '',
      descripcion: vehiculo.descripcion || ''
    };
  } else {
    form.value = {
      cliente_id: 0,
      tipo: 'moto',
      matricula: '',
      descripcion: ''
    };
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
    if (formData.tipo === 'moto' && !formData.matricula?.trim()) {
      formData.matricula = undefined;
    }

    if (props.vehiculo) {
      // await vehiculoStore.updateVehiculo(props.vehiculo.vehiculo_id, formData);
    } else {
      // await vehiculoStore.createVehiculo(formData);
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
onMounted(() => {
  loadClientes();
});
</script>
