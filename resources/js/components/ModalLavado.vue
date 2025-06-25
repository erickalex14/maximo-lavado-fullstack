<template>
  <div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 transition-opacity" @click="$emit('cerrar')">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
      </div>

      <!-- Modal content -->
      <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">
            <span v-if="modo === 'crear'">Nuevo Lavado</span>
            <span v-else-if="modo === 'editar'">Editar Lavado</span>
            <span v-else>Detalles del Lavado</span>
          </h3>
          <button
            @click="$emit('cerrar')"
            class="rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>

        <form @submit.prevent="guardar" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Cliente -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
              <select
                v-model="form.cliente_id"
                :disabled="modo === 'ver'"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                required
                @change="cargarVehiculos"
              >
                <option value="">Seleccionar cliente</option>
                <option
                  v-for="cliente in clientes"
                  :key="cliente.id"
                  :value="cliente.id"
                >
                  {{ cliente.nombre }} - {{ cliente.cedula }}
                </option>
              </select>
            </div>

            <!-- Vehículo -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Vehículo</label>
              <select
                v-model="form.vehiculo_id"
                :disabled="modo === 'ver' || !form.cliente_id"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                required
              >
                <option value="">Seleccionar vehículo</option>
                <option
                  v-for="vehiculo in vehiculosCliente"
                  :key="vehiculo.id"
                  :value="vehiculo.id"
                >
                  {{ vehiculo.marca }} {{ vehiculo.modelo }} - {{ vehiculo.placa }}
                </option>
              </select>
            </div>

            <!-- Empleado -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Empleado</label>
              <select
                v-model="form.empleado_id"
                :disabled="modo === 'ver'"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                required
              >
                <option value="">Seleccionar empleado</option>
                <option
                  v-for="empleado in empleados"
                  :key="empleado.id"
                  :value="empleado.id"
                >
                  {{ empleado.nombre }}
                </option>
              </select>
            </div>

            <!-- Tipo de Lavado -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Lavado</label>
              <select
                v-model="form.tipo_lavado"
                :disabled="modo === 'ver'"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                required
                @change="actualizarPrecio"
              >
                <option value="">Seleccionar tipo</option>
                <option value="Básico">Lavado Básico</option>
                <option value="Premium">Lavado Premium</option>
                <option value="Completo">Lavado Completo</option>
                <option value="Encerado">Encerado</option>
                <option value="Detailing">Detailing</option>
              </select>
            </div>

            <!-- Precio -->
            <MaterialInput
              v-model="form.precio"
              label="Precio"
              type="number"
              placeholder="0"
              required
              :disabled="modo === 'ver'"
            />

            <!-- Estado -->
            <div v-if="modo !== 'crear'">
              <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
              <select
                v-model="form.estado"
                :disabled="modo === 'ver'"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              >
                <option value="pendiente">Pendiente</option>
                <option value="en_proceso">En Proceso</option>
                <option value="completado">Completado</option>
                <option value="cancelado">Cancelado</option>
              </select>
            </div>

            <!-- Fecha -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Fecha del Servicio</label>
              <input
                v-model="form.fecha"
                type="datetime-local"
                :disabled="modo === 'ver'"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                required
              />
            </div>
          </div>

          <!-- Observaciones -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Observaciones
            </label>
            <textarea
              v-model="form.observaciones"
              :disabled="modo === 'ver'"
              rows="3"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              placeholder="Observaciones adicionales sobre el lavado..."
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
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'
import MaterialInput from './MaterialInput.vue'
import MaterialButton from './MaterialButton.vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

export default {
  name: 'ModalLavado',
  components: {
    MaterialInput,
    MaterialButton,
    XMarkIcon
  },
  props: {
    lavado: {
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
    const clientes = ref([])
    const empleados = ref([])
    const vehiculosCliente = ref([])
    
    const form = ref({
      cliente_id: '',
      vehiculo_id: '',
      empleado_id: '',
      tipo_lavado: '',
      precio: '',
      estado: 'pendiente',
      fecha: '',
      observaciones: ''
    })

    // Precios por tipo de lavado
    const precios = {
      'Básico': 30000,
      'Premium': 50000,
      'Completo': 75000,
      'Encerado': 40000,
      'Detailing': 100000
    }

    // Cargar datos necesarios
    const cargarDatos = async () => {
      try {
        const [clientesRes, empleadosRes] = await Promise.all([
          axios.get('/api/clientes'),
          axios.get('/api/empleados')
        ])
        
        clientes.value = clientesRes.data
        empleados.value = empleadosRes.data
      } catch (error) {
        console.error('Error cargando datos:', error)
      }
    }

    // Cargar vehículos del cliente seleccionado
    const cargarVehiculos = async () => {
      if (!form.value.cliente_id) {
        vehiculosCliente.value = []
        return
      }

      try {
        const response = await axios.get(`/api/vehiculos?cliente_id=${form.value.cliente_id}`)
        vehiculosCliente.value = response.data
      } catch (error) {
        console.error('Error cargando vehículos:', error)
        vehiculosCliente.value = []
      }
    }

    // Actualizar precio automáticamente
    const actualizarPrecio = () => {
      if (form.value.tipo_lavado && precios[form.value.tipo_lavado]) {
        form.value.precio = precios[form.value.tipo_lavado]
      }
    }

    // Cargar datos del lavado si está editando
    watch(() => props.lavado, (lavado) => {
      if (lavado) {
        form.value = {
          cliente_id: lavado.vehiculo?.cliente_id || '',
          vehiculo_id: lavado.vehiculo_id || '',
          empleado_id: lavado.empleado_id || '',
          tipo_lavado: lavado.tipo_lavado || '',
          precio: lavado.precio || '',
          estado: lavado.estado || 'pendiente',
          fecha: lavado.fecha ? lavado.fecha.substring(0, 16) : '',
          observaciones: lavado.observaciones || ''
        }
        
        // Cargar vehículos si hay cliente seleccionado
        if (form.value.cliente_id) {
          cargarVehiculos()
        }
      } else {
        // Formulario nuevo
        const ahora = new Date()
        ahora.setMinutes(ahora.getMinutes() - ahora.getTimezoneOffset())
        
        form.value = {
          cliente_id: '',
          vehiculo_id: '',
          empleado_id: '',
          tipo_lavado: '',
          precio: '',
          estado: 'pendiente',
          fecha: ahora.toISOString().substring(0, 16),
          observaciones: ''
        }
      }
    }, { immediate: true })

    const guardar = async () => {
      guardando.value = true
      try {
        const datos = {
          ...form.value,
          precio: parseFloat(form.value.precio)
        }
        emit('guardar', datos)
      } finally {
        guardando.value = false
      }
    }

    onMounted(() => {
      cargarDatos()
    })

    return {
      form,
      guardando,
      clientes,
      empleados,
      vehiculosCliente,
      cargarVehiculos,
      actualizarPrecio,
      guardar
    }
  }
}
</script>
