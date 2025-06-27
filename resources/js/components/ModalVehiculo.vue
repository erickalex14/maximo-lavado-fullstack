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
            <span v-if="modo === 'crear'">Nuevo Vehículo</span>
            <span v-else-if="modo === 'editar'">Editar Vehículo</span>
            <span v-else>Detalles del Vehículo</span>
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
              <select
                v-model="form.cliente_id"
                :disabled="modo === 'ver'"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                required
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

            <!-- Placa -->
            <MaterialInput
              v-model="form.placa"
              label="Placa"
              placeholder="ABC123"
              required
              :disabled="modo === 'ver'"
            />

            <!-- Marca -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Marca *</label>
              <select
                v-model="form.marca"
                :disabled="modo === 'ver'"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                required
                @change="actualizarModelos"
              >
                <option value="">Seleccionar marca</option>
                <option v-for="marca in marcas" :key="marca" :value="marca">
                  {{ marca }}
                </option>
              </select>
            </div>

            <!-- Modelo -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Modelo *</label>
              <select
                v-if="modelosDisponibles.length > 0"
                v-model="form.modelo"
                :disabled="modo === 'ver'"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                required
              >
                <option value="">Seleccionar modelo</option>
                <option v-for="modelo in modelosDisponibles" :key="modelo" :value="modelo">
                  {{ modelo }}
                </option>
              </select>
              <MaterialInput
                v-else
                v-model="form.modelo"
                placeholder="Escribir modelo"
                required
                :disabled="modo === 'ver'"
              />
            </div>

            <!-- Año -->
            <MaterialInput
              v-model="form.anio"
              label="Año"
              type="number"
              :min="1980"
              :max="new Date().getFullYear() + 1"
              placeholder="2020"
              required
              :disabled="modo === 'ver'"
            />

            <!-- Color -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Color</label>
              <select
                v-model="form.color"
                :disabled="modo === 'ver'"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              >
                <option value="">Seleccionar color</option>
                <option v-for="color in colores" :key="color" :value="color">
                  {{ color }}
                </option>
              </select>
            </div>

            <!-- Tipo de Vehículo -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de vehículo</label>
              <select
                v-model="form.tipo_vehiculo"
                :disabled="modo === 'ver'"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              >
                <option value="">Seleccionar tipo</option>
                <option value="sedan">Sedán</option>
                <option value="suv">SUV</option>
                <option value="hatchback">Hatchback</option>
                <option value="pickup">Pickup</option>
                <option value="coupe">Coupé</option>
                <option value="convertible">Convertible</option>
                <option value="wagon">Station Wagon</option>
                <option value="van">Van/Minivan</option>
                <option value="camion">Camión</option>
                <option value="moto">Motocicleta</option>
              </select>
            </div>

            <!-- Motor -->
            <MaterialInput
              v-model="form.motor"
              label="Motor"
              placeholder="Ej: 2.0L, 1.6L Turbo"
              :disabled="modo === 'ver'"
            />
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
              placeholder="Observaciones adicionales sobre el vehículo..."
            ></textarea>
          </div>

          <!-- Información adicional solo en modo ver -->
          <div v-if="modo === 'ver' && vehiculo" class="border-t pt-4">
            <h4 class="text-md font-medium text-gray-900 mb-4">Historial</h4>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <span class="font-medium text-gray-700">Fecha de registro:</span>
                <div class="text-gray-600">{{ formatearFecha(vehiculo.created_at) }}</div>
              </div>
              <div>
                <span class="font-medium text-gray-700">Total de lavados:</span>
                <div class="text-gray-600">{{ vehiculo.total_lavados || 0 }}</div>
              </div>
              <div>
                <span class="font-medium text-gray-700">Último lavado:</span>
                <div class="text-gray-600">{{ vehiculo.ultimo_lavado ? formatearFecha(vehiculo.ultimo_lavado) : 'Nunca' }}</div>
              </div>
              <div>
                <span class="font-medium text-gray-700">Estado:</span>
                <div class="text-gray-600">{{ vehiculo.estado || 'Activo' }}</div>
              </div>
            </div>
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
            <MaterialButton
              v-if="modo === 'ver'"
              text="Nuevo Lavado"
              :prefix-icon="PlusIcon"
              @click="nuevoLavado"
            />
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import MaterialInput from './MaterialInput.vue'
import MaterialButton from './MaterialButton.vue'
import { XMarkIcon, PlusIcon } from '@heroicons/vue/24/outline'

export default {
  name: 'ModalVehiculo',
  components: {
    MaterialInput,
    MaterialButton,
    XMarkIcon,
    PlusIcon
  },
  props: {
    vehiculo: {
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
    const router = useRouter()
    const guardando = ref(false)
    const clientes = ref([])
    
    const form = ref({
      cliente_id: '',
      placa: '',
      marca: '',
      modelo: '',
      anio: '',
      color: '',
      tipo_vehiculo: '',
      motor: '',
      observaciones: ''
    })

    // Marcas de vehículos
    const marcas = [
      'Toyota', 'Honda', 'Nissan', 'Hyundai', 'Kia', 'Mazda', 'Mitsubishi',
      'Chevrolet', 'Ford', 'Volkswagen', 'Renault', 'Peugeot', 'Citroën',
      'BMW', 'Mercedes-Benz', 'Audi', 'Volvo', 'Jeep', 'Subaru', 'Suzuki'
    ]

    // Modelos por marca (algunos ejemplos)
    const modelosPorMarca = {
      'Toyota': ['Corolla', 'Camry', 'RAV4', 'Prius', 'Highlander', 'Yaris', 'Hilux'],
      'Honda': ['Civic', 'Accord', 'CR-V', 'HR-V', 'Pilot', 'Fit', 'City'],
      'Nissan': ['Sentra', 'Altima', 'X-Trail', 'Qashqai', 'Versa', 'Frontier'],
      'Hyundai': ['Elantra', 'Accent', 'Tucson', 'Santa Fe', 'i10', 'i20'],
      'Chevrolet': ['Spark', 'Aveo', 'Cruze', 'Tracker', 'Tahoe', 'Silverado']
    }

    // Colores disponibles
    const colores = [
      'Blanco', 'Negro', 'Gris', 'Plata', 'Rojo', 'Azul', 'Verde', 
      'Amarillo', 'Dorado', 'Marrón', 'Naranja', 'Violeta'
    ]

    const modelosDisponibles = computed(() => {
      return modelosPorMarca[form.value.marca] || []
    })

    // Cargar clientes
    const cargarClientes = async () => {
      try {
        const response = await axios.get('/api/clientes')
        clientes.value = response.data
      } catch (error) {
        console.error('Error cargando clientes:', error)
      }
    }

    const actualizarModelos = () => {
      // Limpiar el modelo cuando cambia la marca
      form.value.modelo = ''
    }

    // Cargar datos del vehículo si está editando
    watch(() => props.vehiculo, (vehiculo) => {
      if (vehiculo) {
        form.value = {
          cliente_id: vehiculo.cliente_id || '',
          placa: vehiculo.placa || '',
          marca: vehiculo.marca || '',
          modelo: vehiculo.modelo || '',
          anio: vehiculo.anio || '',
          color: vehiculo.color || '',
          tipo_vehiculo: vehiculo.tipo_vehiculo || '',
          motor: vehiculo.motor || '',
          observaciones: vehiculo.observaciones || ''
        }
      } else {
        form.value = {
          cliente_id: '',
          placa: '',
          marca: '',
          modelo: '',
          anio: '',
          color: '',
          tipo_vehiculo: '',
          motor: '',
          observaciones: ''
        }
      }
    }, { immediate: true })

    const guardar = async () => {
      guardando.value = true
      try {
        const datos = {
          ...form.value,
          anio: parseInt(form.value.anio)
        }
        emit('guardar', datos)
      } finally {
        guardando.value = false
      }
    }

    const nuevoLavado = () => {
      router.push({
        path: '/lavados/nuevo',
        query: {
          vehiculo_id: props.vehiculo.id,
          cliente_id: props.vehiculo.cliente_id
        }
      })
      emit('cerrar')
    }

    const formatearFecha = (fecha) => {
      return new Date(fecha).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
      })
    }

    onMounted(() => {
      cargarClientes()
    })

    return {
      form,
      guardando,
      clientes,
      marcas,
      colores,
      modelosDisponibles,
      actualizarModelos,
      guardar,
      nuevoLavado,
      formatearFecha
    }
  }
}
</script>
