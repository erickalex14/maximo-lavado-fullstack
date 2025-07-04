<template>
  <div v-if="visible" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl mx-4 max-h-screen overflow-y-auto">
      <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ modalTitle }}
        </h3>
      </div>

      <form @submit.prevent="handleSubmit" class="px-6 py-4 space-y-6">
        <!-- Información básica de la factura -->
        <div class="form-section-title">
          Información de la Factura
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="form-label">
              Número de Factura *
            </label>
            <input
              v-if="mode !== 'view'"
              v-model="form.numero_factura"
              type="text"
              class="form-input"
              :class="{ error: errors.numero_factura }"
              placeholder="FAC-001"
              required
            />
            <div v-else class="form-display-value">
              {{ form.numero_factura }}
            </div>
            <div v-if="errors.numero_factura" class="error-message">
              {{ errors.numero_factura }}
            </div>
          </div>
          
          <div>
            <label class="form-label">
              Cliente *
            </label>
            <select
              v-if="mode !== 'view'"
              v-model="form.cliente_id"
              class="form-select"
              :class="{ error: errors.cliente_id }"
              required
            >
              <option value="">Seleccionar cliente</option>
              <option
                v-for="cliente in clientes"
                :key="cliente.cliente_id"
                :value="cliente.cliente_id"
              >
                {{ cliente.nombre }}
              </option>
            </select>
            <div v-else class="form-display-value">
              {{ clienteSeleccionado?.nombre || 'N/A' }}
            </div>
            <div v-if="errors.cliente_id" class="error-message">
              {{ errors.cliente_id }}
            </div>
          </div>
          
          <div>
            <label class="form-label">
              Fecha *
            </label>
            <input
              v-if="mode !== 'view'"
              v-model="form.fecha"
              type="date"
              class="form-input"
              :class="{ error: errors.fecha }"
              required
            />
            <div v-else class="form-display-value">
              {{ formatDate(form.fecha) }}
            </div>
            <div v-if="errors.fecha" class="error-message">
              {{ errors.fecha }}
            </div>
          </div>
          
          <div>
            <label class="form-label">
              Total *
            </label>
            <input
              v-if="mode !== 'view'"
              v-model.number="form.total"
              type="number"
              min="0"
              step="0.01"
              class="form-input"
              :class="{ error: errors.total }"
              placeholder="0.00"
              required
            />
            <div v-else class="form-display-value">
              ${{ formatCurrency(form.total) }}
            </div>
            <div v-if="errors.total" class="error-message">
              {{ errors.total }}
            </div>
          </div>
        </div>
        
        <div>
          <label class="form-label">
            Descripción
          </label>
          <textarea
            v-if="mode !== 'view'"
            v-model="form.descripcion"
            rows="3"
            class="form-input"
            :class="{ error: errors.descripcion }"
            placeholder="Descripción de la factura..."
          ></textarea>
          <div v-else class="form-display-value">
            {{ form.descripcion || 'Sin descripción' }}
          </div>
          <div v-if="errors.descripcion" class="error-message">
            {{ errors.descripcion }}
          </div>
        </div>

        <!-- Detalles de la factura (solo en vista) -->
        <div v-if="mode === 'view' && factura?.detalles && factura.detalles.length > 0" class="mt-6">
          <div class="form-section-title">
            Detalles de la Factura
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Concepto
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Cantidad
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Precio Unit.
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Subtotal
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="detalle in factura.detalles" :key="detalle.factura_detalle_id">
                  <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span v-if="detalle.lavado">
                      Lavado - {{ detalle.lavado.tipo_lavado }}
                    </span>
                    <span v-else-if="detalle.venta_producto_automotriz">
                      {{ detalle.venta_producto_automotriz.producto_automotriz?.nombre }}
                    </span>
                    <span v-else-if="detalle.venta_producto_despensa">
                      {{ detalle.venta_producto_despensa.producto_despensa?.nombre }}
                    </span>
                    <span v-else>
                      Concepto sin especificar
                    </span>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ detalle.cantidad }}
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${{ formatCurrency(detalle.precio_unitario) }}
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    ${{ formatCurrency(detalle.subtotal) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </form>

      <!-- Actions -->
      <div class="px-6 py-4 border-t bg-gray-50 flex justify-end space-x-3">
        <button
          type="button"
          @click="handleClose"
          class="btn btn-outline-secondary"
        >
          {{ mode === 'view' ? 'Cerrar' : 'Cancelar' }}
        </button>
        
        <button
          v-if="mode !== 'view'"
          type="submit"
          @click="handleSubmit"
          :disabled="loading"
          class="btn btn-primary"
        >
          <svg v-if="loading" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ mode === 'create' ? 'Crear Factura' : 'Guardar Cambios' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { useFacturaStore } from '@/stores/factura';
import type { Factura, Cliente } from '@/types';
import type { CreateFacturaRequest, UpdateFacturaRequest } from '@/services/factura.service';

// Props
interface Props {
  visible: boolean;
  mode: 'create' | 'edit' | 'view';
  factura?: Factura | null;
}

const props = withDefaults(defineProps<Props>(), {
  mode: 'create',
  factura: null
});

// Emits
const emit = defineEmits<{
  'update:visible': [value: boolean];
  'saved': [];
}>();

// Store
const facturaStore = useFacturaStore();

// State
const loading = ref(false);
const form = ref<CreateFacturaRequest>({
  numero_factura: '',
  cliente_id: 0,
  fecha: new Date().toISOString().split('T')[0],
  descripcion: '',
  total: 0
});

const errors = ref<Record<string, string>>({});

// Computed
const isVisible = computed({
  get: () => props.visible,
  set: (value) => emit('update:visible', value)
});

const modalTitle = computed(() => {
  switch (props.mode) {
    case 'create':
      return 'Nueva Factura';
    case 'edit':
      return 'Editar Factura';
    case 'view':
      return 'Detalles de Factura';
    default:
      return 'Factura';
  }
});

const clientes = computed(() => facturaStore.clientes);

const clienteSeleccionado = computed(() => {
  return clientes.value.find(c => c.cliente_id === form.value.cliente_id);
});

// Methods
function resetForm() {
  form.value = {
    numero_factura: '',
    cliente_id: 0,
    fecha: new Date().toISOString().split('T')[0],
    descripcion: '',
    total: 0
  };
  errors.value = {};
}

function loadFacturaData() {
  if (props.factura) {
    form.value = {
      numero_factura: props.factura.numero_factura,
      cliente_id: props.factura.cliente_id,
      fecha: props.factura.fecha,
      descripcion: props.factura.descripcion || '',
      total: props.factura.total
    };
  }
}

function validateForm(): boolean {
  errors.value = {};

  if (!form.value.numero_factura?.trim()) {
    errors.value.numero_factura = 'El número de factura es requerido';
  }

  if (!form.value.cliente_id) {
    errors.value.cliente_id = 'El cliente es requerido';
  }

  if (!form.value.fecha) {
    errors.value.fecha = 'La fecha es requerida';
  }

  if (!form.value.total || form.value.total <= 0) {
    errors.value.total = 'El total debe ser mayor a 0';
  }

  return Object.keys(errors.value).length === 0;
}

async function handleSubmit() {
  if (!validateForm()) return;

  try {
    loading.value = true;

    if (props.mode === 'create') {
      await facturaStore.createFactura(form.value);
    } else if (props.mode === 'edit' && props.factura) {
      const updateData: UpdateFacturaRequest = { ...form.value };
      await facturaStore.updateFactura(props.factura.factura_id, updateData);
    }

    emit('saved');
    handleClose();
  } catch (error: any) {
    console.error('Error al guardar factura:', error);
    
    // Manejar errores de validación del servidor
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    }
  } finally {
    loading.value = false;
  }
}

function formatCurrency(value: number): string {
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('es-CO');
}

function handleCancel() {
  handleClose();
}

function handleClose() {
  resetForm();
  emit('update:visible', false);
}

// Watchers
watch(() => props.visible, (newValue) => {
  if (newValue) {
    if (props.mode === 'edit' || props.mode === 'view') {
      loadFacturaData();
    } else {
      resetForm();
    }
  }
});

watch(() => props.factura, () => {
  if (props.visible && (props.mode === 'edit' || props.mode === 'view')) {
    loadFacturaData();
  }
});

// Lifecycle
onMounted(async () => {
  // Cargar clientes si no están cargados
  if (clientes.value.length === 0) {
    await facturaStore.fetchClientes();
  }
});
</script>

<style scoped>
.form-section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-section-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.form-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.5rem;
}

.form-input,
.form-select {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  background-color: white;
  color: #111827;
}

.form-input:focus,
.form-select:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
  border-color: #3b82f6;
}

.form-input.error,
.form-select.error {
  border-color: #ef4444;
}

.form-input.error:focus,
.form-select.error:focus {
  box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.5);
  border-color: #ef4444;
}

.form-display-value {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  background-color: #f9fafb;
  color: #374151;
}

.error-message {
  color: #ef4444;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-weight: 500;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  border: none;
  cursor: pointer;
}

.btn-primary {
  background-color: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background-color: #2563eb;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-outline-secondary {
  border: 1px solid #d1d5db;
  color: #374151;
  background-color: white;
}

.btn-outline-secondary:hover {
  background-color: #f9fafb;
}
</style>
