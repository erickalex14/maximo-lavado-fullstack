<template>
  <AppLayout>
    <template #header>
      <PageHeader
        title="Gestión de Lavados"
        description="Administra los servicios de lavado de vehículos"
        :icon="DocumentTextIcon"
        :breadcrumbs="breadcrumbs"
      >
        <template #actions>
          <MaterialButton
            text="Nuevo Lavado"
            :prefix-icon="PlusIcon"
            @click="abrirModalNuevo"
          />
          <MaterialButton
            text="Reporte del Día"
            variant="outlined"
            :prefix-icon="DocumentArrowDownIcon"
            @click="exportarReporte"
          />
        </template>
      </PageHeader>
    </template>

    <!-- Filtros y Estadísticas -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
      <!-- Filtros -->
      <div class="lg:col-span-3">
        <MaterialCard>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <MaterialInput
                v-model="filtros.busqueda"
                label="Buscar"
                placeholder="Cliente, placa..."
                :prefix-icon="MagnifyingGlassIcon"
                @input="aplicarFiltros"
              />
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                <input
                  v-model="filtros.fecha"
                  type="date"
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                  @change="aplicarFiltros"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select
                  v-model="filtros.estado"
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                  @change="aplicarFiltros"
                >
                  <option value="">Todos</option>
                  <option value="pendiente">Pendiente</option>
                  <option value="en_proceso">En Proceso</option>
                  <option value="completado">Completado</option>
                  <option value="cancelado">Cancelado</option>
                </select>
              </div>
              <div class="flex items-end space-x-2">
                <MaterialButton
                  text="Limpiar"
                  variant="outlined"
                  size="sm"
                  @click="limpiarFiltros"
                />
              </div>
            </div>
          </div>
        </MaterialCard>
      </div>

      <!-- Estadísticas del día -->
      <MaterialCard>
        <div class="p-6">
          <h3 class="text-sm font-medium text-gray-500 mb-4">Resumen de Hoy</h3>
          <div class="space-y-3">
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Total</span>
              <span class="text-sm font-semibold">{{ estadisticas.total }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Completados</span>
              <span class="text-sm font-semibold text-green-600">{{ estadisticas.completados }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">En Proceso</span>
              <span class="text-sm font-semibold text-yellow-600">{{ estadisticas.enProceso }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Ingresos</span>
              <span class="text-sm font-semibold text-blue-600">${{ estadisticas.ingresos.toLocaleString() }}</span>
            </div>
          </div>
        </div>
      </MaterialCard>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Cargando lavados...</p>
      </div>
    </div>

    <!-- Lista de Lavados -->
    <MaterialCard v-else>
      <div class="p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-900">
            Lavados ({{ lavados.length }})
          </h3>
        </div>

        <!-- Tabla de Lavados -->
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  ID
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Cliente/Vehículo
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tipo de Lavado
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Empleado
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Precio
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Estado
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Fecha
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="lavado in lavados"
                :key="lavado.id"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  #{{ lavado.id }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">
                    {{ lavado.vehiculo?.cliente?.nombre || 'Sin cliente' }}
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ lavado.vehiculo?.marca }} {{ lavado.vehiculo?.modelo }} - {{ lavado.vehiculo?.placa }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ lavado.tipo_lavado }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ lavado.empleado?.nombre || 'Sin asignar' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  ${{ lavado.precio?.toLocaleString() || '0' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getEstadoClass(lavado.estado)"
                  >
                    {{ formatearEstado(lavado.estado) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatearFecha(lavado.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center space-x-2">
                    <button
                      @click="verDetalles(lavado)"
                      class="text-primary-600 hover:text-primary-900"
                    >
                      <EyeIcon class="h-4 w-4" />
                    </button>
                    <button
                      @click="editarLavado(lavado)"
                      class="text-yellow-600 hover:text-yellow-900"
                    >
                      <PencilIcon class="h-4 w-4" />
                    </button>
                    <button
                      @click="cambiarEstado(lavado)"
                      class="text-green-600 hover:text-green-900"
                    >
                      <CheckIcon class="h-4 w-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Estado vacío -->
          <div v-if="!loading && lavados.length === 0" class="text-center py-12">
            <DocumentTextIcon class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay lavados registrados</h3>
            <p class="mt-1 text-sm text-gray-500">Comienza registrando tu primer lavado.</p>
            <div class="mt-6">
              <MaterialButton
                text="Nuevo Lavado"
                :prefix-icon="PlusIcon"
                @click="abrirModalNuevo"
              />
            </div>
          </div>
        </div>
      </div>
    </MaterialCard>

    <!-- Modal de Lavado -->
    <ModalLavado
      v-if="mostrarModal"
      :lavado="lavadoSeleccionado"
      :modo="modoModal"
      @cerrar="cerrarModal"
      @guardar="guardarLavado"
    />
  </AppLayout>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import AppLayout from '../components/AppLayout.vue'
import PageHeader from '../components/PageHeader.vue'
import MaterialCard from '../components/MaterialCard.vue'
import MaterialButton from '../components/MaterialButton.vue'
import MaterialInput from '../components/MaterialInput.vue'
import ModalLavado from '../components/ModalLavado.vue'
import {
  DocumentTextIcon,
  PlusIcon,
  DocumentArrowDownIcon,
  MagnifyingGlassIcon,
  EyeIcon,
  PencilIcon,
  CheckIcon
} from '@heroicons/vue/24/outline'

export default {
  name: 'Lavados',
  components: {
    AppLayout,
    PageHeader,
    MaterialCard,
    MaterialButton,
    MaterialInput,
    ModalLavado,
    DocumentTextIcon,
    PlusIcon,
    DocumentArrowDownIcon,
    MagnifyingGlassIcon,
    EyeIcon,
    PencilIcon,
    CheckIcon
  },
  setup() {
    const router = useRouter()
    const loading = ref(true)
    const lavados = ref([])
    const lavadosFiltrados = ref([])
    const mostrarModal = ref(false)
    const lavadoSeleccionado = ref(null)
    const modoModal = ref('crear')

    // Breadcrumbs
    const breadcrumbs = [
      { name: 'Inicio', href: '/dashboard' },
      { name: 'Lavados', href: '/lavados' }
    ]

    // Filtros
    const filtros = ref({
      busqueda: '',
      fecha: '',
      estado: ''
    })

    // Estadísticas computadas
    const estadisticas = computed(() => {
      const hoy = new Date().toISOString().split('T')[0]
      const lavadosHoy = lavados.value.filter(lavado => 
        lavado.created_at && lavado.created_at.startsWith(hoy)
      )
      
      return {
        total: lavadosHoy.length,
        completados: lavadosHoy.filter(l => l.estado === 'completado').length,
        enProceso: lavadosHoy.filter(l => l.estado === 'en_proceso').length,
        ingresos: lavadosHoy
          .filter(l => l.estado === 'completado')
          .reduce((sum, l) => sum + (l.precio || 0), 0)
      }
    })

    // Cargar lavados desde la API
    const cargarLavados = async () => {
      try {
        loading.value = true
        const response = await axios.get('/api/lavados')
        lavados.value = response.data
        lavadosFiltrados.value = response.data
      } catch (error) {
        console.error('Error cargando lavados:', error)
        alert('Error al cargar los lavados')
      } finally {
        loading.value = false
      }
    }

    // Aplicar filtros
    const aplicarFiltros = () => {
      let resultado = [...lavados.value]

      if (filtros.value.busqueda) {
        const busqueda = filtros.value.busqueda.toLowerCase()
        resultado = resultado.filter(lavado => 
          lavado.vehiculo?.cliente?.nombre?.toLowerCase().includes(busqueda) ||
          lavado.vehiculo?.placa?.toLowerCase().includes(busqueda) ||
          lavado.tipo_lavado?.toLowerCase().includes(busqueda)
        )
      }

      if (filtros.value.fecha) {
        resultado = resultado.filter(lavado => 
          lavado.created_at && lavado.created_at.startsWith(filtros.value.fecha)
        )
      }

      if (filtros.value.estado) {
        resultado = resultado.filter(lavado => lavado.estado === filtros.value.estado)
      }

      lavadosFiltrados.value = resultado
    }

    // Limpiar filtros
    const limpiarFiltros = () => {
      filtros.value.busqueda = ''
      filtros.value.fecha = ''
      filtros.value.estado = ''
      lavadosFiltrados.value = [...lavados.value]
    }

    // Modal functions
    const abrirModalNuevo = () => {
      lavadoSeleccionado.value = null
      modoModal.value = 'crear'
      mostrarModal.value = true
    }

    const editarLavado = (lavado) => {
      lavadoSeleccionado.value = lavado
      modoModal.value = 'editar'
      mostrarModal.value = true
    }

    const verDetalles = (lavado) => {
      lavadoSeleccionado.value = lavado
      modoModal.value = 'ver'
      mostrarModal.value = true
    }

    const cerrarModal = () => {
      mostrarModal.value = false
      lavadoSeleccionado.value = null
    }

    const guardarLavado = async (datosLavado) => {
      try {
        if (modoModal.value === 'crear') {
          await axios.post('/api/lavados', datosLavado)
        } else {
          await axios.put(`/api/lavados/${lavadoSeleccionado.value.id}`, datosLavado)
        }
        
        cerrarModal()
        cargarLavados()
        alert(modoModal.value === 'crear' ? 'Lavado registrado exitosamente' : 'Lavado actualizado exitosamente')
      } catch (error) {
        console.error('Error guardando lavado:', error)
        alert('Error al guardar el lavado')
      }
    }

    const cambiarEstado = async (lavado) => {
      const estadosSiguientes = {
        'pendiente': 'en_proceso',
        'en_proceso': 'completado',
        'completado': 'completado'
      }

      const nuevoEstado = estadosSiguientes[lavado.estado] || 'pendiente'
      
      try {
        await axios.put(`/api/lavados/${lavado.id}`, {
          ...lavado,
          estado: nuevoEstado
        })
        cargarLavados()
      } catch (error) {
        console.error('Error cambiando estado:', error)
        alert('Error al cambiar el estado')
      }
    }

    const exportarReporte = () => {
      alert('Funcionalidad de reporte próximamente')
    }

    const getEstadoClass = (estado) => {
      const clases = {
        'pendiente': 'bg-yellow-100 text-yellow-800',
        'en_proceso': 'bg-blue-100 text-blue-800',
        'completado': 'bg-green-100 text-green-800',
        'cancelado': 'bg-red-100 text-red-800'
      }
      return clases[estado] || 'bg-gray-100 text-gray-800'
    }

    const formatearEstado = (estado) => {
      const estados = {
        'pendiente': 'Pendiente',
        'en_proceso': 'En Proceso',
        'completado': 'Completado',
        'cancelado': 'Cancelado'
      }
      return estados[estado] || estado
    }

    const formatearFecha = (fecha) => {
      return new Date(fecha).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    onMounted(() => {
      cargarLavados()
    })

    return {
      breadcrumbs,
      loading,
      lavados: lavadosFiltrados,
      filtros,
      estadisticas,
      mostrarModal,
      lavadoSeleccionado,
      modoModal,
      cargarLavados,
      aplicarFiltros,
      limpiarFiltros,
      abrirModalNuevo,
      editarLavado,
      verDetalles,
      cerrarModal,
      guardarLavado,
      cambiarEstado,
      exportarReporte,
      getEstadoClass,
      formatearEstado,
      formatearFecha
    }
  }
}
</script>
