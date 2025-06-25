<template>
  <AppLayout>
    <template #header>
      <PageHeader
        title="Gestión de Clientes"
        description="Administra la información de tus clientes"
        :icon="UserGroupIcon"
        :breadcrumbs="breadcrumbs"
      >
        <template #actions>
          <MaterialButton
            text="Nuevo Cliente"
            :prefix-icon="PlusIcon"
            @click="abrirModalNuevo"
          />
          <MaterialButton
            text="Exportar Lista"
            variant="outlined"
            :prefix-icon="DocumentArrowDownIcon"
            @click="exportarClientes"
          />
        </template>
      </PageHeader>
    </template>

    <!-- Filtros y Búsqueda -->
    <MaterialCard class="mb-6">
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <MaterialInput
            v-model="filtros.busqueda"
            label="Buscar cliente"
            placeholder="Nombre, cédula o email..."
            :prefix-icon="MagnifyingGlassIcon"
            @input="buscarClientes"
          />
          <MaterialInput
            v-model="filtros.cedula"
            label="Buscar por cédula"
            placeholder="12345678"
            @input="buscarPorCedula"
          />
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
              @click="cargarClientes"
            />
          </div>
        </div>
      </div>
    </MaterialCard>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Cargando clientes...</p>
      </div>
    </div>

    <!-- Lista de Clientes -->
    <MaterialCard v-else>
      <div class="p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-900">
            Lista de Clientes ({{ clientes.length }})
          </h3>
        </div>

        <!-- Tabla de Clientes -->
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Cliente
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Contacto
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Vehículos
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Último Lavado
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="cliente in clientes"
                :key="cliente.id"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                        <UserIcon class="h-5 w-5 text-primary-600" />
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">
                        {{ cliente.nombre }}
                      </div>
                      <div class="text-sm text-gray-500">
                        Cédula: {{ cliente.cedula }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    {{ cliente.telefono || 'N/A' }}
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ cliente.email || 'Sin email' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ cliente.vehiculos_count || 0 }} vehículos
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ cliente.ultimo_lavado ? formatearFecha(cliente.ultimo_lavado) : 'Nunca' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center space-x-2">
                    <button
                      @click="verDetalles(cliente)"
                      class="text-primary-600 hover:text-primary-900"
                    >
                      <EyeIcon class="h-4 w-4" />
                    </button>
                    <button
                      @click="editarCliente(cliente)"
                      class="text-yellow-600 hover:text-yellow-900"
                    >
                      <PencilIcon class="h-4 w-4" />
                    </button>
                    <button
                      @click="eliminarCliente(cliente)"
                      class="text-red-600 hover:text-red-900"
                    >
                      <TrashIcon class="h-4 w-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Estado vacío -->
          <div v-if="!loading && clientes.length === 0" class="text-center py-12">
            <UserGroupIcon class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay clientes</h3>
            <p class="mt-1 text-sm text-gray-500">Comienza agregando tu primer cliente.</p>
            <div class="mt-6">
              <MaterialButton
                text="Nuevo Cliente"
                :prefix-icon="PlusIcon"
                @click="abrirModalNuevo"
              />
            </div>
          </div>
        </div>
      </div>
    </MaterialCard>

    <!-- Modal de Cliente -->
    <ModalCliente
      v-if="mostrarModal"
      :cliente="clienteSeleccionado"
      :modo="modoModal"
      @cerrar="cerrarModal"
      @guardar="guardarCliente"
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
import ModalCliente from '../components/ModalCliente.vue'
import {
  UserGroupIcon,
  PlusIcon,
  DocumentArrowDownIcon,
  MagnifyingGlassIcon,
  UserIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon
} from '@heroicons/vue/24/outline'

export default {
  name: 'Clientes',
  components: {
    AppLayout,
    PageHeader,
    MaterialCard,
    MaterialButton,
    MaterialInput,
    ModalCliente,
    UserGroupIcon,
    PlusIcon,
    DocumentArrowDownIcon,
    MagnifyingGlassIcon,
    UserIcon,
    EyeIcon,
    PencilIcon,
    TrashIcon
  },
  setup() {
    const router = useRouter()
    const loading = ref(true)
    const clientes = ref([])
    const mostrarModal = ref(false)
    const clienteSeleccionado = ref(null)
    const modoModal = ref('crear') // 'crear', 'editar', 'ver'

    // Breadcrumbs
    const breadcrumbs = [
      { name: 'Inicio', href: '/dashboard' },
      { name: 'Clientes', href: '/clientes' }
    ]

    // Filtros
    const filtros = ref({
      busqueda: '',
      cedula: ''
    })

    // Cargar clientes desde la API
    const cargarClientes = async () => {
      try {
        loading.value = true
        const response = await axios.get('/api/clientes')
        clientes.value = response.data
      } catch (error) {
        console.error('Error cargando clientes:', error)
        alert('Error al cargar los clientes')
      } finally {
        loading.value = false
      }
    }

    // Buscar clientes
    const buscarClientes = async () => {
      if (filtros.value.busqueda.length < 2) {
        cargarClientes()
        return
      }
      
      try {
        loading.value = true
        const response = await axios.get('/api/clientes', {
          params: { busqueda: filtros.value.busqueda }
        })
        clientes.value = response.data
      } catch (error) {
        console.error('Error buscando clientes:', error)
      } finally {
        loading.value = false
      }
    }

    // Buscar por cédula
    const buscarPorCedula = async () => {
      if (!filtros.value.cedula) {
        cargarClientes()
        return
      }

      try {
        loading.value = true
        const response = await axios.get(`/api/clientes/buscar/cedula/${filtros.value.cedula}`)
        clientes.value = response.data ? [response.data] : []
      } catch (error) {
        console.error('Error buscando por cédula:', error)
        clientes.value = []
      } finally {
        loading.value = false
      }
    }

    // Limpiar filtros
    const limpiarFiltros = () => {
      filtros.value.busqueda = ''
      filtros.value.cedula = ''
      cargarClientes()
    }

    // Modal functions
    const abrirModalNuevo = () => {
      clienteSeleccionado.value = null
      modoModal.value = 'crear'
      mostrarModal.value = true
    }

    const editarCliente = (cliente) => {
      clienteSeleccionado.value = cliente
      modoModal.value = 'editar'
      mostrarModal.value = true
    }

    const verDetalles = (cliente) => {
      clienteSeleccionado.value = cliente
      modoModal.value = 'ver'
      mostrarModal.value = true
    }

    const cerrarModal = () => {
      mostrarModal.value = false
      clienteSeleccionado.value = null
    }

    const guardarCliente = async (datosCliente) => {
      try {
        if (modoModal.value === 'crear') {
          await axios.post('/api/clientes', datosCliente)
        } else {
          await axios.put(`/api/clientes/${clienteSeleccionado.value.id}`, datosCliente)
        }
        
        cerrarModal()
        cargarClientes()
        alert(modoModal.value === 'crear' ? 'Cliente creado exitosamente' : 'Cliente actualizado exitosamente')
      } catch (error) {
        console.error('Error guardando cliente:', error)
        alert('Error al guardar el cliente')
      }
    }

    const eliminarCliente = async (cliente) => {
      if (!confirm(`¿Estás seguro de eliminar al cliente ${cliente.nombre}?`)) {
        return
      }

      try {
        await axios.delete(`/api/clientes/${cliente.id}`)
        cargarClientes()
        alert('Cliente eliminado exitosamente')
      } catch (error) {
        console.error('Error eliminando cliente:', error)
        alert('Error al eliminar el cliente')
      }
    }

    const exportarClientes = () => {
      // TODO: Implementar exportación
      alert('Funcionalidad de exportación próximamente')
    }

    const formatearFecha = (fecha) => {
      return new Date(fecha).toLocaleDateString('es-ES')
    }

    onMounted(() => {
      cargarClientes()
    })

    return {
      breadcrumbs,
      loading,
      clientes,
      filtros,
      mostrarModal,
      clienteSeleccionado,
      modoModal,
      cargarClientes,
      buscarClientes,
      buscarPorCedula,
      limpiarFiltros,
      abrirModalNuevo,
      editarCliente,
      verDetalles,
      cerrarModal,
      guardarCliente,
      eliminarCliente,
      exportarClientes,
      formatearFecha
    }
  }
}
</script>
