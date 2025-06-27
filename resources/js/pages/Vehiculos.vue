<template>
  <AppLayout>
    <template #header>
      <PageHeader
        title="Gestión de Vehículos"
        description="Administra el registro de vehículos de tus clientes"
        :icon="TruckIcon"
        :breadcrumbs="breadcrumbs"
      >
        <template #actions>
          <MaterialButton
            text="Nuevo Vehículo"
            :prefix-icon="PlusIcon"
            @click="abrirModalNuevo"
          />
          <MaterialButton
            text="Exportar Lista"
            variant="outlined"
            :prefix-icon="DocumentArrowDownIcon"
            @click="exportarVehiculos"
          />
        </template>
      </PageHeader>
    </template>

    <!-- Filtros y Búsqueda -->
    <MaterialCard class="mb-6">
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <MaterialInput
            v-model="filtros.busqueda"
            label="Buscar vehículo"
            placeholder="Placa, marca, modelo..."
            :prefix-icon="MagnifyingGlassIcon"
            @input="aplicarFiltros"
          />
          <MaterialInput
            v-model="filtros.placa"
            label="Buscar por placa"
            placeholder="ABC123"
            @input="aplicarFiltros"
          />
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cliente</label>
            <select
              v-model="filtros.cliente_id"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              @change="aplicarFiltros"
            >
              <option value="">Todos los clientes</option>
              <option
                v-for="cliente in clientes"
                :key="cliente.id"
                :value="cliente.id"
              >
                {{ cliente.nombre }}
              </option>
            </select>
          </div>
          <div class="flex items-end space-x-2">
            <MaterialButton
              text="Limpiar Filtros"
              variant="outlined"
              size="sm"
              @click="limpiarFiltros"
            />
            <MaterialButton
              text="Buscar"
              size="sm"
              @click="cargarVehiculos"
            />
          </div>
        </div>
      </div>
    </MaterialCard>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Cargando vehículos...</p>
      </div>
    </div>

    <!-- Lista de Vehículos -->
    <MaterialCard v-else>
      <div class="p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-900">
            Vehículos Registrados ({{ vehiculos.length }})
          </h3>
        </div>

        <!-- Vista de Tarjetas -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="vehiculo in vehiculos"
            :key="vehiculo.id"
            class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow"
          >
            <!-- Header de la tarjeta -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4 text-white">
              <div class="flex items-center justify-between">
                <div>
                  <h4 class="font-semibold text-lg">{{ vehiculo.placa }}</h4>
                  <p class="text-blue-100 text-sm">{{ vehiculo.marca }} {{ vehiculo.modelo }}</p>
                </div>
                <div class="h-12 w-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                  <TruckIcon class="h-6 w-6" />
                </div>
              </div>
            </div>

            <!-- Contenido de la tarjeta -->
            <div class="p-4">
              <div class="space-y-3">
                <!-- Cliente -->
                <div class="flex items-center space-x-2">
                  <UserIcon class="h-4 w-4 text-gray-400" />
                  <span class="text-sm font-medium text-gray-900">
                    {{ vehiculo.cliente?.nombre || 'Sin cliente' }}
                  </span>
                </div>

                <!-- Año -->
                <div class="flex items-center space-x-2">
                  <CalendarIcon class="h-4 w-4 text-gray-400" />
                  <span class="text-sm text-gray-600">
                    Año {{ vehiculo.anio || 'N/A' }}
                  </span>
                </div>

                <!-- Color -->
                <div class="flex items-center space-x-2">
                  <div class="h-4 w-4 rounded-full border border-gray-300" :style="{ backgroundColor: getColorHex(vehiculo.color) }"></div>
                  <span class="text-sm text-gray-600">
                    {{ vehiculo.color || 'Sin color' }}
                  </span>
                </div>

                <!-- Último lavado -->
                <div class="flex items-center space-x-2">
                  <ClockIcon class="h-4 w-4 text-gray-400" />
                  <span class="text-sm text-gray-600">
                    Último lavado: {{ vehiculo.ultimo_lavado ? formatearFecha(vehiculo.ultimo_lavado) : 'Nunca' }}
                  </span>
                </div>

                <!-- Total de lavados -->
                <div class="flex items-center justify-between pt-2 border-t">
                  <span class="text-sm text-gray-600">Total lavados:</span>
                  <span class="text-sm font-semibold text-primary-600">
                    {{ vehiculo.total_lavados || 0 }}
                  </span>
                </div>
              </div>

              <!-- Acciones -->
              <div class="flex items-center space-x-2 mt-4 pt-4 border-t">
                <button
                  @click="verDetalles(vehiculo)"
                  class="flex-1 text-center py-2 px-3 text-xs border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
                >
                  <EyeIcon class="h-4 w-4 inline mr-1" />
                  Ver
                </button>
                <button
                  @click="editarVehiculo(vehiculo)"
                  class="flex-1 text-center py-2 px-3 text-xs bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors"
                >
                  <PencilIcon class="h-4 w-4 inline mr-1" />
                  Editar
                </button>
                <button
                  @click="nuevoLavado(vehiculo)"
                  class="flex-1 text-center py-2 px-3 text-xs bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
                >
                  <DocumentTextIcon class="h-4 w-4 inline mr-1" />
                  Lavado
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Estado vacío -->
        <div v-if="!loading && vehiculos.length === 0" class="text-center py-12">
          <TruckIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">No hay vehículos registrados</h3>
          <p class="mt-1 text-sm text-gray-500">Comienza agregando el primer vehículo.</p>
          <div class="mt-6">
            <MaterialButton
              text="Nuevo Vehículo"
              :prefix-icon="PlusIcon"
              @click="abrirModalNuevo"
            />
          </div>
        </div>
      </div>
    </MaterialCard>

    <!-- Modal de Vehículo -->
    <ModalVehiculo
      v-if="mostrarModal"
      :vehiculo="vehiculoSeleccionado"
      :modo="modoModal"
      @cerrar="cerrarModal"
      @guardar="guardarVehiculo"
    />
  </AppLayout>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import AppLayout from '../components/AppLayout.vue'
import PageHeader from '../components/PageHeader.vue'
import MaterialCard from '../components/MaterialCard.vue'
import MaterialButton from '../components/MaterialButton.vue'
import MaterialInput from '../components/MaterialInput.vue'
import ModalVehiculo from '../components/ModalVehiculo.vue'
import {
  TruckIcon,
  PlusIcon,
  DocumentArrowDownIcon,
  MagnifyingGlassIcon,
  UserIcon,
  CalendarIcon,
  ClockIcon,
  EyeIcon,
  PencilIcon,
  DocumentTextIcon
} from '@heroicons/vue/24/outline'

export default {
  name: 'Vehiculos',
  components: {
    AppLayout,
    PageHeader,
    MaterialCard,
    MaterialButton,
    MaterialInput,
    ModalVehiculo,
    TruckIcon,
    PlusIcon,
    DocumentArrowDownIcon,
    MagnifyingGlassIcon,
    UserIcon,
    CalendarIcon,
    ClockIcon,
    EyeIcon,
    PencilIcon,
    DocumentTextIcon
  },
  setup() {
    const router = useRouter()
    const loading = ref(true)
    const vehiculos = ref([])
    const vehiculosFiltrados = ref([])
    const clientes = ref([])
    const mostrarModal = ref(false)
    const vehiculoSeleccionado = ref(null)
    const modoModal = ref('crear')

    // Breadcrumbs
    const breadcrumbs = [
      { name: 'Inicio', href: '/dashboard' },
      { name: 'Vehículos', href: '/vehiculos' }
    ]

    // Filtros
    const filtros = ref({
      busqueda: '',
      placa: '',
      cliente_id: ''
    })

    // Cargar datos
    const cargarDatos = async () => {
      try {
        loading.value = true
        const [vehiculosRes, clientesRes] = await Promise.all([
          axios.get('/api/vehiculos'),
          axios.get('/api/clientes')
        ])
        
        vehiculos.value = vehiculosRes.data
        vehiculosFiltrados.value = vehiculosRes.data
        clientes.value = clientesRes.data
      } catch (error) {
        console.error('Error cargando datos:', error)
        alert('Error al cargar los datos')
      } finally {
        loading.value = false
      }
    }

    const cargarVehiculos = async () => {
      try {
        loading.value = true
        const response = await axios.get('/api/vehiculos')
        vehiculos.value = response.data
        aplicarFiltros()
      } catch (error) {
        console.error('Error cargando vehículos:', error)
        alert('Error al cargar los vehículos')
      } finally {
        loading.value = false
      }
    }

    // Aplicar filtros
    const aplicarFiltros = () => {
      let resultado = [...vehiculos.value]

      if (filtros.value.busqueda) {
        const busqueda = filtros.value.busqueda.toLowerCase()
        resultado = resultado.filter(vehiculo => 
          vehiculo.placa?.toLowerCase().includes(busqueda) ||
          vehiculo.marca?.toLowerCase().includes(busqueda) ||
          vehiculo.modelo?.toLowerCase().includes(busqueda) ||
          vehiculo.cliente?.nombre?.toLowerCase().includes(busqueda)
        )
      }

      if (filtros.value.placa) {
        resultado = resultado.filter(vehiculo => 
          vehiculo.placa?.toLowerCase().includes(filtros.value.placa.toLowerCase())
        )
      }

      if (filtros.value.cliente_id) {
        resultado = resultado.filter(vehiculo => 
          vehiculo.cliente_id == filtros.value.cliente_id
        )
      }

      vehiculosFiltrados.value = resultado
    }

    // Limpiar filtros
    const limpiarFiltros = () => {
      filtros.value.busqueda = ''
      filtros.value.placa = ''
      filtros.value.cliente_id = ''
      vehiculosFiltrados.value = [...vehiculos.value]
    }

    // Modal functions
    const abrirModalNuevo = () => {
      vehiculoSeleccionado.value = null
      modoModal.value = 'crear'
      mostrarModal.value = true
    }

    const editarVehiculo = (vehiculo) => {
      vehiculoSeleccionado.value = vehiculo
      modoModal.value = 'editar'
      mostrarModal.value = true
    }

    const verDetalles = (vehiculo) => {
      vehiculoSeleccionado.value = vehiculo
      modoModal.value = 'ver'
      mostrarModal.value = true
    }

    const cerrarModal = () => {
      mostrarModal.value = false
      vehiculoSeleccionado.value = null
    }

    const guardarVehiculo = async (datosVehiculo) => {
      try {
        if (modoModal.value === 'crear') {
          await axios.post('/api/vehiculos', datosVehiculo)
        } else {
          await axios.put(`/api/vehiculos/${vehiculoSeleccionado.value.id}`, datosVehiculo)
        }
        
        cerrarModal()
        cargarVehiculos()
        alert(modoModal.value === 'crear' ? 'Vehículo registrado exitosamente' : 'Vehículo actualizado exitosamente')
      } catch (error) {
        console.error('Error guardando vehículo:', error)
        alert('Error al guardar el vehículo')
      }
    }

    const nuevoLavado = (vehiculo) => {
      // Redirigir a la página de lavados con el vehículo preseleccionado
      router.push({
        path: '/lavados/nuevo',
        query: {
          vehiculo_id: vehiculo.id,
          cliente_id: vehiculo.cliente_id
        }
      })
    }

    const exportarVehiculos = () => {
      alert('Funcionalidad de exportación próximamente')
    }

    const formatearFecha = (fecha) => {
      return new Date(fecha).toLocaleDateString('es-ES')
    }

    const getColorHex = (color) => {
      const colores = {
        'blanco': '#ffffff',
        'negro': '#000000',
        'rojo': '#ef4444',
        'azul': '#3b82f6',
        'verde': '#10b981',
        'amarillo': '#f59e0b',
        'gris': '#6b7280',
        'plata': '#d1d5db',
        'dorado': '#f59e0b'
      }
      return colores[color?.toLowerCase()] || '#9ca3af'
    }

    onMounted(() => {
      cargarDatos()
    })

    return {
      breadcrumbs,
      loading,
      vehiculos: vehiculosFiltrados,
      clientes,
      filtros,
      mostrarModal,
      vehiculoSeleccionado,
      modoModal,
      cargarVehiculos,
      aplicarFiltros,
      limpiarFiltros,
      abrirModalNuevo,
      editarVehiculo,
      verDetalles,
      cerrarModal,
      guardarVehiculo,
      nuevoLavado,
      exportarVehiculos,
      formatearFecha,
      getColorHex
    }
  }
}
</script>
