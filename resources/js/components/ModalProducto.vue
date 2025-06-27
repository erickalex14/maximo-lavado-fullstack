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
            <span v-if="modo === 'crear'">Nuevo Producto</span>
            <span v-else-if="modo === 'editar'">Editar Producto</span>
            <span v-else>Detalles del Producto</span>
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
            <!-- Categoría -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Categoría *</label>
              <select
                v-model="form.categoria"
                :disabled="modo === 'ver'"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                required
              >
                <option value="">Seleccionar categoría</option>
                <option value="automotriz">Producto Automotriz</option>
                <option value="despensa">Producto de Despensa</option>
              </select>
            </div>

            <!-- Nombre -->
            <MaterialInput
              v-model="form.nombre"
              label="Nombre del producto"
              placeholder="Ej: Shampoo para autos"
              required
              :disabled="modo === 'ver'"
            />

            <!-- Código -->
            <MaterialInput
              v-model="form.codigo"
              label="Código"
              placeholder="Ej: SHA001"
              :disabled="modo === 'ver'"
            />

            <!-- Precio -->
            <MaterialInput
              v-model="form.precio"
              label="Precio"
              type="number"
              step="0.01"
              placeholder="0.00"
              required
              :disabled="modo === 'ver'"
            />

            <!-- Stock Inicial -->
            <MaterialInput
              v-model="form.stock"
              label="Stock inicial"
              type="number"
              placeholder="0"
              required
              :disabled="modo === 'ver'"
            />

            <!-- Unidad de Medida -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Unidad de medida</label>
              <select
                v-model="form.unidad_medida"
                :disabled="modo === 'ver'"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              >
                <option value="unidad">Unidad</option>
                <option value="litro">Litro</option>
                <option value="galón">Galón</option>
                <option value="kilogramo">Kilogramo</option>
                <option value="gramo">Gramo</option>
                <option value="botella">Botella</option>
                <option value="caja">Caja</option>
              </select>
            </div>

            <!-- Proveedor -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Proveedor</label>
              <select
                v-model="form.proveedor_id"
                :disabled="modo === 'ver'"
                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              >
                <option value="">Sin proveedor</option>
                <option
                  v-for="proveedor in proveedores"
                  :key="proveedor.id"
                  :value="proveedor.id"
                >
                  {{ proveedor.nombre }}
                </option>
              </select>
            </div>

            <!-- Stock Mínimo -->
            <MaterialInput
              v-model="form.stock_minimo"
              label="Stock mínimo"
              type="number"
              placeholder="5"
              :disabled="modo === 'ver'"
            />
          </div>

          <!-- Descripción -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Descripción
            </label>
            <textarea
              v-model="form.descripcion"
              :disabled="modo === 'ver'"
              rows="3"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              placeholder="Descripción detallada del producto..."
            ></textarea>
          </div>

          <!-- Campos específicos para productos automotrices -->
          <div v-if="form.categoria === 'automotriz'" class="border-t pt-4">
            <h4 class="text-md font-medium text-gray-900 mb-4">Información Automotriz</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <MaterialInput
                v-model="form.marca"
                label="Marca"
                placeholder="Ej: Meguiar's"
                :disabled="modo === 'ver'"
              />
              
              <MaterialInput
                v-model="form.tipo_producto"
                label="Tipo de producto"
                placeholder="Ej: Shampoo, Cera, Aceite"
                :disabled="modo === 'ver'"
              />
            </div>
          </div>

          <!-- Campos específicos para productos de despensa -->
          <div v-if="form.categoria === 'despensa'" class="border-t pt-4">
            <h4 class="text-md font-medium text-gray-900 mb-4">Información de Despensa</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de vencimiento</label>
                <input
                  v-model="form.fecha_vencimiento"
                  type="date"
                  :disabled="modo === 'ver'"
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Categoría alimentaria</label>
                <select
                  v-model="form.categoria_alimentaria"
                  :disabled="modo === 'ver'"
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                >
                  <option value="">Seleccionar</option>
                  <option value="bebidas">Bebidas</option>
                  <option value="snacks">Snacks</option>
                  <option value="limpieza">Productos de limpieza</option>
                  <option value="higiene">Productos de higiene</option>
                  <option value="otros">Otros</option>
                </select>
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
  name: 'ModalProducto',
  components: {
    MaterialInput,
    MaterialButton,
    XMarkIcon
  },
  props: {
    producto: {
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
    const proveedores = ref([])
    
    const form = ref({
      categoria: '',
      nombre: '',
      codigo: '',
      precio: '',
      stock: '',
      unidad_medida: 'unidad',
      proveedor_id: '',
      stock_minimo: '5',
      descripcion: '',
      // Campos automotrices
      marca: '',
      tipo_producto: '',
      // Campos despensa
      fecha_vencimiento: '',
      categoria_alimentaria: ''
    })

    // Cargar proveedores
    const cargarProveedores = async () => {
      try {
        const response = await axios.get('/api/proveedores')
        proveedores.value = response.data
      } catch (error) {
        console.error('Error cargando proveedores:', error)
      }
    }

    // Cargar datos del producto si está editando
    watch(() => props.producto, (producto) => {
      if (producto) {
        form.value = {
          categoria: producto.categoria || '',
          nombre: producto.nombre || '',
          codigo: producto.codigo || '',
          precio: producto.precio || '',
          stock: producto.stock || '',
          unidad_medida: producto.unidad_medida || 'unidad',
          proveedor_id: producto.proveedor_id || '',
          stock_minimo: producto.stock_minimo || '5',
          descripcion: producto.descripcion || '',
          // Campos automotrices
          marca: producto.marca || '',
          tipo_producto: producto.tipo_producto || '',
          // Campos despensa
          fecha_vencimiento: producto.fecha_vencimiento || '',
          categoria_alimentaria: producto.categoria_alimentaria || ''
        }
      } else {
        // Formulario nuevo
        form.value = {
          categoria: '',
          nombre: '',
          codigo: '',
          precio: '',
          stock: '',
          unidad_medida: 'unidad',
          proveedor_id: '',
          stock_minimo: '5',
          descripcion: '',
          marca: '',
          tipo_producto: '',
          fecha_vencimiento: '',
          categoria_alimentaria: ''
        }
      }
    }, { immediate: true })

    const guardar = async () => {
      guardando.value = true
      try {
        const datos = {
          ...form.value,
          precio: parseFloat(form.value.precio),
          stock: parseInt(form.value.stock),
          stock_minimo: parseInt(form.value.stock_minimo)
        }
        
        // Limpiar campos que no corresponden a la categoría
        if (datos.categoria === 'automotriz') {
          delete datos.fecha_vencimiento
          delete datos.categoria_alimentaria
        } else if (datos.categoria === 'despensa') {
          delete datos.marca
          delete datos.tipo_producto
        }
        
        emit('guardar', datos)
      } finally {
        guardando.value = false
      }
    }

    onMounted(() => {
      cargarProveedores()
    })

    return {
      form,
      guardando,
      proveedores,
      guardar
    }
  }
}
</script>
