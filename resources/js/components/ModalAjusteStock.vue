<template>
  <div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 transition-opacity" @click="$emit('cerrar')">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
      </div>

      <!-- Modal content -->
      <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full sm:p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">
            Ajustar Stock
          </h3>
          <button
            @click="$emit('cerrar')"
            class="rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
          >
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>

        <!-- Información del producto -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
          <h4 class="font-medium text-gray-900 mb-2">{{ producto?.nombre }}</h4>
          <div class="text-sm text-gray-600 space-y-1">
            <div>Código: {{ producto?.codigo || 'N/A' }}</div>
            <div>Stock actual: <span class="font-semibold">{{ producto?.stock || 0 }} unidades</span></div>
            <div>Categoría: {{ producto?.categoria === 'automotriz' ? 'Automotriz' : 'Despensa' }}</div>
          </div>
        </div>

        <form @submit.prevent="guardar" class="space-y-4">
          <!-- Tipo de ajuste -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Tipo de ajuste
            </label>
            <div class="grid grid-cols-2 gap-3">
              <button
                type="button"
                @click="tipoAjuste = 'entrada'"
                :class="[
                  'p-3 border rounded-lg text-center transition-colors',
                  tipoAjuste === 'entrada' 
                    ? 'border-green-500 bg-green-50 text-green-700' 
                    : 'border-gray-300 hover:border-gray-400'
                ]"
              >
                <PlusIcon class="h-5 w-5 mx-auto mb-1" />
                <div class="text-sm font-medium">Entrada</div>
                <div class="text-xs text-gray-500">Agregar stock</div>
              </button>
              <button
                type="button"
                @click="tipoAjuste = 'salida'"
                :class="[
                  'p-3 border rounded-lg text-center transition-colors',
                  tipoAjuste === 'salida' 
                    ? 'border-red-500 bg-red-50 text-red-700' 
                    : 'border-gray-300 hover:border-gray-400'
                ]"
              >
                <MinusIcon class="h-5 w-5 mx-auto mb-1" />
                <div class="text-sm font-medium">Salida</div>
                <div class="text-xs text-gray-500">Reducir stock</div>
              </button>
            </div>
          </div>

          <!-- Cantidad -->
          <MaterialInput
            v-model="cantidad"
            label="Cantidad"
            type="number"
            min="1"
            placeholder="0"
            required
          />

          <!-- Motivo -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Motivo
            </label>
            <select
              v-model="motivo"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              required
            >
              <option value="">Seleccionar motivo</option>
              <optgroup v-if="tipoAjuste === 'entrada'" label="Entradas">
                <option value="compra">Compra a proveedor</option>
                <option value="devolucion">Devolución de cliente</option>
                <option value="ajuste_inventario">Ajuste de inventario</option>
                <option value="otro_entrada">Otro motivo</option>
              </optgroup>
              <optgroup v-if="tipoAjuste === 'salida'" label="Salidas">
                <option value="venta">Venta a cliente</option>
                <option value="uso_interno">Uso interno</option>
                <option value="perdida">Pérdida/Daño</option>
                <option value="vencimiento">Producto vencido</option>
                <option value="ajuste_inventario">Ajuste de inventario</option>
                <option value="otro_salida">Otro motivo</option>
              </optgroup>
            </select>
          </div>

          <!-- Observaciones -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Observaciones
            </label>
            <textarea
              v-model="observaciones"
              rows="3"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              placeholder="Observaciones adicionales sobre el ajuste..."
            ></textarea>
          </div>

          <!-- Preview del resultado -->
          <div v-if="cantidad && tipoAjuste" class="bg-blue-50 border border-blue-200 rounded-lg p-3">
            <div class="text-sm">
              <div class="font-medium text-blue-900">Vista previa:</div>
              <div class="mt-1 text-blue-700">
                Stock actual: {{ producto?.stock || 0 }} unidades
                <br>
                {{ tipoAjuste === 'entrada' ? 'Agregar' : 'Reducir' }}: {{ cantidad }} unidades
                <br>
                <strong>Stock final: {{ calcularStockFinal() }} unidades</strong>
              </div>
              <div v-if="calcularStockFinal() < 0" class="mt-2 text-red-600 text-xs">
                ⚠️ El stock no puede ser negativo
              </div>
              <div v-if="calcularStockFinal() < (producto?.stock_minimo || 5)" class="mt-2 text-yellow-600 text-xs">
                ⚠️ El stock quedará por debajo del mínimo recomendado
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
              text="Guardar Ajuste"
              type="submit"
              :loading="guardando"
              :disabled="calcularStockFinal() < 0"
            />
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import MaterialInput from './MaterialInput.vue'
import MaterialButton from './MaterialButton.vue'
import { XMarkIcon, PlusIcon, MinusIcon } from '@heroicons/vue/24/outline'

export default {
  name: 'ModalAjusteStock',
  components: {
    MaterialInput,
    MaterialButton,
    XMarkIcon,
    PlusIcon,
    MinusIcon
  },
  props: {
    producto: {
      type: Object,
      required: true
    }
  },
  emits: ['cerrar', 'guardar'],
  setup(props, { emit }) {
    const guardando = ref(false)
    const tipoAjuste = ref('entrada') // 'entrada' o 'salida'
    const cantidad = ref('')
    const motivo = ref('')
    const observaciones = ref('')

    const calcularStockFinal = () => {
      const stockActual = props.producto?.stock || 0
      const cantidadNum = parseInt(cantidad.value) || 0
      
      if (tipoAjuste.value === 'entrada') {
        return stockActual + cantidadNum
      } else {
        return stockActual - cantidadNum
      }
    }

    const guardar = async () => {
      guardando.value = true
      try {
        const datos = {
          tipo: tipoAjuste.value,
          cantidad: parseInt(cantidad.value),
          motivo: motivo.value,
          observaciones: observaciones.value,
          stock_nuevo: calcularStockFinal()
        }
        
        emit('guardar', datos)
      } finally {
        guardando.value = false
      }
    }

    return {
      guardando,
      tipoAjuste,
      cantidad,
      motivo,
      observaciones,
      calcularStockFinal,
      guardar
    }
  }
}
</script>
