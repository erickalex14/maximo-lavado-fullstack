<template>
  <AppLayout>
    <template #header>
      <PageHeader
        title="Gestión de Productos"
        description="Administra el inventario de productos automotrices y de despensa"
        :icon="ShoppingCartIcon"
        :breadcrumbs="breadcrumbs"
      >
        <template #actions>
          <MaterialButton
            text="Nuevo Producto"
            :prefix-icon="PlusIcon"
            @click="abrirModalNuevo"
          />
          <MaterialButton
            text="Exportar Inventario"
            variant="outlined"
            :prefix-icon="DocumentArrowDownIcon"
            @click="exportarInventario"
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
                label="Buscar producto"
                placeholder="Nombre, código..."
                :prefix-icon="MagnifyingGlassIcon"
                @input="aplicarFiltros"
              />
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                <select
                  v-model="filtros.categoria"
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                  @change="aplicarFiltros"
                >
                  <option value="">Todas las categorías</option>
                  <option value="automotriz">Productos Automotrices</option>
                  <option value="despensa">Productos de Despensa</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                <select
                  v-model="filtros.stock"
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                  @change="aplicarFiltros"
                >
                  <option value="">Todo el stock</option>
                  <option value="bajo">Stock bajo (< 10)</option>
                  <option value="agotado">Agotado (0)</option>
                  <option value="disponible">Disponible (> 0)</option>
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

      <!-- Estadísticas del inventario -->
      <MaterialCard>
        <div class="p-6">
          <h3 class="text-sm font-medium text-gray-500 mb-4">Resumen de Inventario</h3>
          <div class="space-y-3">
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Total Productos</span>
              <span class="text-sm font-semibold">{{ estadisticas.total }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Automotrices</span>
              <span class="text-sm font-semibold text-blue-600">{{ estadisticas.automotrices }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Despensa</span>
              <span class="text-sm font-semibold text-green-600">{{ estadisticas.despensa }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Stock Bajo</span>
              <span class="text-sm font-semibold text-red-600">{{ estadisticas.stockBajo }}</span>
            </div>
          </div>
        </div>
      </MaterialCard>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Cargando productos...</p>
      </div>
    </div>

    <!-- Lista de Productos -->
    <MaterialCard v-else>
      <div class="p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-900">
            Productos ({{ productos.length }})
          </h3>
          <div class="flex items-center space-x-2">
            <button
              @click="vistaActual = 'tarjetas'"
              :class="[
                'p-2 rounded-md',
                vistaActual === 'tarjetas' ? 'bg-primary-100 text-primary-600' : 'text-gray-400 hover:text-gray-600'
              ]"
            >
              <div class="grid grid-cols-2 gap-1 w-4 h-4">
                <div class="bg-current rounded-sm"></div>
                <div class="bg-current rounded-sm"></div>
                <div class="bg-current rounded-sm"></div>
                <div class="bg-current rounded-sm"></div>
              </div>
            </button>
            <button
              @click="vistaActual = 'tabla'"
              :class="[
                'p-2 rounded-md',
                vistaActual === 'tabla' ? 'bg-primary-100 text-primary-600' : 'text-gray-400 hover:text-gray-600'
              ]"
            >
              <div class="space-y-1 w-4 h-4">
                <div class="h-1 bg-current rounded"></div>
                <div class="h-1 bg-current rounded"></div>
                <div class="h-1 bg-current rounded"></div>
              </div>
            </button>
          </div>
        </div>

        <!-- Vista de Tarjetas -->
        <div v-if="vistaActual === 'tarjetas'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div
            v-for="producto in productos"
            :key="producto.id"
            class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
          >
            <div class="flex items-start justify-between mb-3">
              <div class="flex-1">
                <h4 class="text-sm font-semibold text-gray-900 mb-1">{{ producto.nombre }}</h4>
                <p class="text-xs text-gray-500">{{ producto.categoria === 'automotriz' ? 'Automotriz' : 'Despensa' }}</p>
              </div>
              <span
                :class="[
                  'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                  getStockClass(producto.stock)
                ]"
              >
                {{ producto.stock }} unidades
              </span>
            </div>
            
            <div class="space-y-2 mb-4">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Precio:</span>
                <span class="font-medium">${{ producto.precio?.toLocaleString() || '0' }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Código:</span>
                <span class="font-mono text-xs">{{ producto.codigo || 'N/A' }}</span>
              </div>
            </div>

            <div class="flex items-center space-x-2">
              <button
                @click="verDetalles(producto)"
                class="flex-1 text-center py-2 px-3 text-xs border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
              >
                Ver
              </button>
              <button
                @click="editarProducto(producto)"
                class="flex-1 text-center py-2 px-3 text-xs bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors"
              >
                Editar
              </button>
            </div>
          </div>
        </div>

        <!-- Vista de Tabla -->
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Producto
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Categoría
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Stock
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Precio
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Proveedor
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="producto in productos"
                :key="producto.id"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center">
                        <ShoppingCartIcon class="h-5 w-5 text-primary-600" />
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">
                        {{ producto.nombre }}
                      </div>
                      <div class="text-sm text-gray-500">
                        Código: {{ producto.codigo || 'N/A' }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="producto.categoria === 'automotriz' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                  >
                    {{ producto.categoria === 'automotriz' ? 'Automotriz' : 'Despensa' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getStockClass(producto.stock)"
                  >
                    {{ producto.stock }} unidades
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  ${{ producto.precio?.toLocaleString() || '0' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ producto.proveedor?.nombre || 'Sin proveedor' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center space-x-2">
                    <button
                      @click="verDetalles(producto)"
                      class="text-primary-600 hover:text-primary-900"
                    >
                      <EyeIcon class="h-4 w-4" />
                    </button>
                    <button
                      @click="editarProducto(producto)"
                      class="text-yellow-600 hover:text-yellow-900"
                    >
                      <PencilIcon class="h-4 w-4" />
                    </button>
                    <button
                      @click="ajustarStock(producto)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      <ArrowsUpDownIcon class="h-4 w-4" />
                    </button>
                    <button
                      @click="eliminarProducto(producto)"
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
          <div v-if="!loading && productos.length === 0" class="text-center py-12">
            <ShoppingCartIcon class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay productos</h3>
            <p class="mt-1 text-sm text-gray-500">Comienza agregando tu primer producto al inventario.</p>
            <div class="mt-6">
              <MaterialButton
                text="Nuevo Producto"
                :prefix-icon="PlusIcon"
                @click="abrirModalNuevo"
              />
            </div>
          </div>
        </div>
      </div>
    </MaterialCard>

    <!-- Modal de Producto -->
    <ModalProducto
      v-if="mostrarModal"
      :producto="productoSeleccionado"
      :modo="modoModal"
      @cerrar="cerrarModal"
      @guardar="guardarProducto"
    />

    <!-- Modal de Ajuste de Stock -->
    <ModalAjusteStock
      v-if="mostrarModalStock"
      :producto="productoSeleccionado"
      @cerrar="cerrarModalStock"
      @guardar="guardarAjusteStock"
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
import ModalProducto from '../components/ModalProducto.vue'
import ModalAjusteStock from '../components/ModalAjusteStock.vue'
import {
  ShoppingCartIcon,
  PlusIcon,
  DocumentArrowDownIcon,
  MagnifyingGlassIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  ArrowsUpDownIcon
} from '@heroicons/vue/24/outline'

export default {
  name: 'Productos',
  components: {
    AppLayout,
    PageHeader,
    MaterialCard,
    MaterialButton,
    MaterialInput,
    ModalProducto,
    ModalAjusteStock,
    ShoppingCartIcon,
    PlusIcon,
    DocumentArrowDownIcon,
    MagnifyingGlassIcon,
    EyeIcon,
    PencilIcon,
    TrashIcon,
    ArrowsUpDownIcon
  },
  setup() {
    const router = useRouter()
    const loading = ref(true)
    const productos = ref([])
    const productosFiltrados = ref([])
    const mostrarModal = ref(false)
    const mostrarModalStock = ref(false)
    const productoSeleccionado = ref(null)
    const modoModal = ref('crear')
    const vistaActual = ref('tarjetas') // 'tarjetas' o 'tabla'

    // Breadcrumbs
    const breadcrumbs = [
      { name: 'Inicio', href: '/dashboard' },
      { name: 'Productos', href: '/productos' }
    ]

    // Filtros
    const filtros = ref({
      busqueda: '',
      categoria: '',
      stock: ''
    })

    // Estadísticas computadas
    const estadisticas = computed(() => {
      const todos = productos.value
      return {
        total: todos.length,
        automotrices: todos.filter(p => p.categoria === 'automotriz').length,
        despensa: todos.filter(p => p.categoria === 'despensa').length,
        stockBajo: todos.filter(p => p.stock < 10).length
      }
    })

    // Cargar productos
    const cargarProductos = async () => {
      try {
        loading.value = true
        
        // Cargar ambos tipos de productos
        const [automotricesRes, despensaRes] = await Promise.all([
          axios.get('/api/productos-automotrices'),
          axios.get('/api/productos-despensa')
        ])
        
        // Combinar y normalizar los datos
        const automotrices = automotricesRes.data.map(p => ({
          ...p,
          categoria: 'automotriz'
        }))
        
        const despensa = despensaRes.data.map(p => ({
          ...p,
          categoria: 'despensa'
        }))
        
        productos.value = [...automotrices, ...despensa]
        productosFiltrados.value = productos.value
        
      } catch (error) {
        console.error('Error cargando productos:', error)
        alert('Error al cargar los productos')
      } finally {
        loading.value = false
      }
    }

    // Aplicar filtros
    const aplicarFiltros = () => {
      let resultado = [...productos.value]

      if (filtros.value.busqueda) {
        const busqueda = filtros.value.busqueda.toLowerCase()
        resultado = resultado.filter(producto => 
          producto.nombre?.toLowerCase().includes(busqueda) ||
          producto.codigo?.toLowerCase().includes(busqueda)
        )
      }

      if (filtros.value.categoria) {
        resultado = resultado.filter(producto => producto.categoria === filtros.value.categoria)
      }

      if (filtros.value.stock) {
        switch (filtros.value.stock) {
          case 'bajo':
            resultado = resultado.filter(producto => producto.stock < 10)
            break
          case 'agotado':
            resultado = resultado.filter(producto => producto.stock === 0)
            break
          case 'disponible':
            resultado = resultado.filter(producto => producto.stock > 0)
            break
        }
      }

      productosFiltrados.value = resultado
    }

    // Limpiar filtros
    const limpiarFiltros = () => {
      filtros.value.busqueda = ''
      filtros.value.categoria = ''
      filtros.value.stock = ''
      productosFiltrados.value = [...productos.value]
    }

    // Funciones del modal
    const abrirModalNuevo = () => {
      productoSeleccionado.value = null
      modoModal.value = 'crear'
      mostrarModal.value = true
    }

    const editarProducto = (producto) => {
      productoSeleccionado.value = producto
      modoModal.value = 'editar'
      mostrarModal.value = true
    }

    const verDetalles = (producto) => {
      productoSeleccionado.value = producto
      modoModal.value = 'ver'
      mostrarModal.value = true
    }

    const cerrarModal = () => {
      mostrarModal.value = false
      productoSeleccionado.value = null
    }

    const ajustarStock = (producto) => {
      productoSeleccionado.value = producto
      mostrarModalStock.value = true
    }

    const cerrarModalStock = () => {
      mostrarModalStock.value = false
      productoSeleccionado.value = null
    }

    const guardarProducto = async (datosProducto) => {
      try {
        const endpoint = datosProducto.categoria === 'automotriz' 
          ? '/api/productos-automotrices' 
          : '/api/productos-despensa'
        
        if (modoModal.value === 'crear') {
          await axios.post(endpoint, datosProducto)
        } else {
          await axios.put(`${endpoint}/${productoSeleccionado.value.id}`, datosProducto)
        }
        
        cerrarModal()
        cargarProductos()
        alert(modoModal.value === 'crear' ? 'Producto creado exitosamente' : 'Producto actualizado exitosamente')
      } catch (error) {
        console.error('Error guardando producto:', error)
        alert('Error al guardar el producto')
      }
    }

    const guardarAjusteStock = async (datosAjuste) => {
      try {
        const endpoint = productoSeleccionado.value.categoria === 'automotriz' 
          ? `/api/productos-automotrices/${productoSeleccionado.value.id}/stock`
          : `/api/productos-despensa/${productoSeleccionado.value.id}/stock`
        
        await axios.put(endpoint, datosAjuste)
        
        cerrarModalStock()
        cargarProductos()
        alert('Stock actualizado exitosamente')
      } catch (error) {
        console.error('Error actualizando stock:', error)
        alert('Error al actualizar el stock')
      }
    }

    const eliminarProducto = async (producto) => {
      if (!confirm(`¿Estás seguro de eliminar el producto ${producto.nombre}?`)) {
        return
      }

      try {
        const endpoint = producto.categoria === 'automotriz' 
          ? `/api/productos-automotrices/${producto.id}`
          : `/api/productos-despensa/${producto.id}`
        
        await axios.delete(endpoint)
        cargarProductos()
        alert('Producto eliminado exitosamente')
      } catch (error) {
        console.error('Error eliminando producto:', error)
        alert('Error al eliminar el producto')
      }
    }

    const exportarInventario = () => {
      alert('Funcionalidad de exportación próximamente')
    }

    const getStockClass = (stock) => {
      if (stock === 0) return 'bg-red-100 text-red-800'
      if (stock < 10) return 'bg-yellow-100 text-yellow-800'
      return 'bg-green-100 text-green-800'
    }

    onMounted(() => {
      cargarProductos()
    })

    return {
      breadcrumbs,
      loading,
      productos: productosFiltrados,
      filtros,
      estadisticas,
      mostrarModal,
      mostrarModalStock,
      productoSeleccionado,
      modoModal,
      vistaActual,
      cargarProductos,
      aplicarFiltros,
      limpiarFiltros,
      abrirModalNuevo,
      editarProducto,
      verDetalles,
      cerrarModal,
      ajustarStock,
      cerrarModalStock,
      guardarProducto,
      guardarAjusteStock,
      eliminarProducto,
      exportarInventario,
      getStockClass
    }
  }
}
</script>
