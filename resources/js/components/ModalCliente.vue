<template>
  <div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 transition-opacity" @click="$emit('cerrar')">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
      </div>

      <!-- Modal content -->
      <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">
            <span v-if="modo === 'crear'">Nuevo Cliente</span>
            <span v-else-if="modo === 'editar'">Editar Cliente</span>
            <span v-else>Detalles del Cliente</span>
          </h3>
          <button
            @click="$emit('cerrar')"
            class="rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>

        <form @submit.prevent="guardar" class="space-y-4">
          <!-- Nombre -->
          <MaterialInput
            v-model="form.nombre"
            label="Nombre completo"
            placeholder="Ej: Juan Pérez"
            required
            :disabled="modo === 'ver'"
          />

          <!-- Cédula -->
          <MaterialInput
            v-model="form.cedula"
            label="Cédula"
            placeholder="12345678"
            required
            :disabled="modo === 'ver'"
          />

          <!-- Teléfono -->
          <MaterialInput
            v-model="form.telefono"
            label="Teléfono"
            placeholder="0987654321"
            :disabled="modo === 'ver'"
          />

          <!-- Email -->
          <MaterialInput
            v-model="form.email"
            label="Email"
            type="email"
            placeholder="cliente@email.com"
            :disabled="modo === 'ver'"
          />

          <!-- Dirección -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Dirección
            </label>
            <textarea
              v-model="form.direccion"
              :disabled="modo === 'ver'"
              rows="3"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              placeholder="Dirección completa del cliente"
            ></textarea>
          </div>

          <!-- Acciones -->
          <div class="flex justify-end space-x-3 pt-4">
            <MaterialButton
              text="Cancelar"
              variant="outlined"
              @click="$emit('cerrar')"
            />
            <MaterialButton
              v-if="modo !== 'ver'"
              text="Guardar"
              type="submit"
              :loading="guardando"
            />
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue'
import MaterialInput from './MaterialInput.vue'
import MaterialButton from './MaterialButton.vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

export default {
  name: 'ModalCliente',
  components: {
    MaterialInput,
    MaterialButton,
    XMarkIcon
  },
  props: {
    cliente: {
      type: Object,
      default: null
    },
    modo: {
      type: String,
      default: 'crear' // 'crear', 'editar', 'ver'
    }
  },
  emits: ['cerrar', 'guardar'],
  setup(props, { emit }) {
    const guardando = ref(false)
    
    const form = ref({
      nombre: '',
      cedula: '',
      telefono: '',
      email: '',
      direccion: ''
    })

    // Cargar datos del cliente si está editando
    watch(() => props.cliente, (cliente) => {
      if (cliente) {
        form.value = {
          nombre: cliente.nombre || '',
          cedula: cliente.cedula || '',
          telefono: cliente.telefono || '',
          email: cliente.email || '',
          direccion: cliente.direccion || ''
        }
      } else {
        form.value = {
          nombre: '',
          cedula: '',
          telefono: '',
          email: '',
          direccion: ''
        }
      }
    }, { immediate: true })

    const guardar = async () => {
      guardando.value = true
      try {
        emit('guardar', form.value)
      } finally {
        guardando.value = false
      }
    }

    return {
      form,
      guardando,
      guardar
    }
  }
}
</script>
