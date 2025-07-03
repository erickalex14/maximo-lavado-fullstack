<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
      <div class="fixed inset-0 bg-black bg-opacity-25 transition-opacity" @click="$emit('close')"></div>

      <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
        <form @submit.prevent="handleSubmit">
          <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
            <!-- Header -->
            <div class="sm:flex sm:items-start">
              <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                <h3 class="text-lg font-semibold leading-6 text-surface-900">
                  {{ isEditing ? 'Editar Cliente' : 'Nuevo Cliente' }}
                </h3>
                <p class="mt-1 text-sm text-surface-500">
                  {{ isEditing ? 'Modifica los datos del cliente' : 'Ingresa los datos del nuevo cliente' }}
                </p>

                <!-- Form -->
                <div class="mt-6 space-y-4">
                  <!-- Error message -->
                  <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-3">
                    <p class="text-sm text-red-700">{{ error }}</p>
                  </div>

                  <!-- Nombre -->
                  <div>
                    <label for="nombre" class="block text-sm font-medium text-surface-700 mb-1">
                      Nombre *
                    </label>
                    <input
                      id="nombre"
                      v-model="form.nombre"
                      type="text"
                      required
                      class="input-base"
                      :class="{ 'border-red-300': errors.nombre }"
                      placeholder="Nombre del cliente"
                    />
                    <p v-if="errors.nombre" class="mt-1 text-sm text-red-600">{{ errors.nombre }}</p>
                  </div>

                  <!-- Apellido -->
                  <div>
                    <label for="telefono" class="block text-sm font-medium text-surface-700 mb-1">
                      Teléfono *
                    </label>
                    <input
                      id="telefono"
                      v-model="form.telefono"
                      type="tel"
                      required
                      class="input-base"
                      :class="{ 'border-red-300': errors.telefono }"
                      placeholder="+569 1234 5678"
                    />
                    <p v-if="errors.telefono" class="mt-1 text-sm text-red-600">{{ errors.telefono }}</p>
                  </div>

                  <!-- Email -->
                  <div>
                    <label for="email" class="block text-sm font-medium text-surface-700 mb-1">
                      Email *
                    </label>
                    <input
                      id="email"
                      v-model="form.email"
                      type="email"
                      required
                      class="input-base"
                      :class="{ 'border-red-300': errors.email }"
                      placeholder="email@ejemplo.com"
                    />
                    <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                  </div>

                  <!-- Teléfono -->
                  <div>
                    <label for="cedula" class="block text-sm font-medium text-surface-700 mb-1">
                      Cédula *
                    </label>
                    <input
                      id="cedula"
                      v-model="form.cedula"
                      type="text"
                      required
                      class="input-base"
                      :class="{ 'border-red-300': errors.cedula }"
                      placeholder="12.345.678-9"
                    />
                    <p v-if="errors.cedula" class="mt-1 text-sm text-red-600">{{ errors.cedula }}</p>
                  </div>

                  <!-- Dirección -->
                  <div>
                    <label for="direccion" class="block text-sm font-medium text-surface-700 mb-1">
                      Dirección
                    </label>
                    <input
                      id="direccion"
                      v-model="form.direccion"
                      type="text"
                      class="input-base"
                      :class="{ 'border-red-300': errors.direccion }"
                      placeholder="Dirección del cliente"
                    />
                    <p v-if="errors.direccion" class="mt-1 text-sm text-red-600">{{ errors.direccion }}</p>
                  </div>

                  <!-- Dirección -->
                  <div>
                    <label for="direccion" class="block text-sm font-medium text-surface-700 mb-1">
                      Dirección
                    </label>
                    <input
                      id="direccion"
                      v-model="form.direccion"
                      type="text"
                      class="input-base"
                      :class="{ 'border-red-300': errors.direccion }"
                      placeholder="Dirección del cliente"
                    />
                    <p v-if="errors.direccion" class="mt-1 text-sm text-red-600">{{ errors.direccion }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="bg-surface-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
            <button
              type="submit"
              :disabled="loading"
              class="inline-flex w-full justify-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 sm:ml-3 sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <LoadingSpinner v-if="loading" class="mr-2" size="sm" />
              {{ loading ? 'Guardando...' : (isEditing ? 'Actualizar' : 'Crear') }}
            </button>
            <button
              type="button"
              @click="$emit('close')"
              class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-surface-900 shadow-sm ring-1 ring-inset ring-surface-300 hover:bg-surface-50 sm:mt-0 sm:w-auto"
            >
              Cancelar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, watch } from 'vue';
import { useClienteStore } from '@/stores/cliente';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';
import type { Cliente } from '@/types';

interface Props {
  isOpen: boolean;
  cliente?: Cliente | null;
  isEditing?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  cliente: null,
  isEditing: false,
});

const emit = defineEmits<{
  close: [];
  saved: [cliente: Cliente];
}>();

const clienteStore = useClienteStore();

// State
const loading = ref(false);
const error = ref('');
const errors = ref<Record<string, string>>({});

// Form
const form = reactive({
  nombre: '',
  telefono: '',
  email: '',
  direccion: '',
  cedula: '',
});

// Watch for cliente changes
watch(() => props.cliente, (newCliente) => {
  if (newCliente && props.isEditing) {
    Object.assign(form, {
      nombre: newCliente.nombre,
      telefono: newCliente.telefono,
      email: newCliente.email,
      direccion: newCliente.direccion || '',
      cedula: newCliente.cedula,
    });
  } else if (!props.isEditing) {
    // Reset form for new cliente
    Object.assign(form, {
      nombre: '',
      telefono: '',
      email: '',
      direccion: '',
      cedula: '',
    });
  }
}, { immediate: true });

// Methods
const validateForm = (): boolean => {
  errors.value = {};

  if (!form.nombre.trim()) {
    errors.value.nombre = 'El nombre es requerido';
  }

  if (!form.telefono.trim()) {
    errors.value.telefono = 'El teléfono es requerido';
  }

  if (!form.email.trim()) {
    errors.value.email = 'El email es requerido';
  } else if (!/\S+@\S+\.\S+/.test(form.email)) {
    errors.value.email = 'El email no es válido';
  }

  if (!form.cedula.trim()) {
    errors.value.cedula = 'La cédula es requerida';
  }

  return Object.keys(errors.value).length === 0;
};

const handleSubmit = async (): Promise<void> => {
  if (!validateForm()) {
    return;
  }

  loading.value = true;
  error.value = '';

  try {
    let success: boolean;

    if (props.isEditing && props.cliente) {
      success = await clienteStore.updateCliente(props.cliente.cliente_id, form);
    } else {
      success = await clienteStore.createCliente(form);
    }

    if (success) {
      emit('saved', {} as Cliente); // Just signal success
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Error al guardar el cliente';
    console.error('Error saving cliente:', err);
  } finally {
    loading.value = false;
  }
};
</script>
