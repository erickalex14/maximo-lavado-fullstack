<template>
  <div
    v-if="visible"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    @click="handleCancel"
  >
    <div
      class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
      @click.stop
    >
      <div class="p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ modalTitle }}
          </h3>
          <button
            @click="handleCancel"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
    <form @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Información del Producto -->
      <div class="form-section">
        <h3 class="form-section-title">Información del Producto</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="form-label" for="producto_id">
              Producto de Despensa *
            </label>
            <select
              id="producto_id"
              v-model="form.producto_id"
              :disabled="mode === 'view'"
              class="form-select"
              :class="{ 'error': errors.producto_id }"
              required
            >
              <option value="">Seleccionar producto...</option>
              <option
                v-for="producto in productosDespensa"
                :key="producto.id"
                :value="producto.id"
              >
                {{ producto.nombre }} 
                (Stock: {{ producto.stock }}) - ${{ formatCurrency(producto.precio_venta) }}
              </option>
            </select>
            <span v-if="errors.producto_id" class="error-message">{{ errors.producto_id }}</span>
          </div>

          <div>
            <label class="form-label" for="cantidad">
              Cantidad *
            </label>
            <input
              id="cantidad"
              v-model.number="form.cantidad"
              :disabled="mode === 'view'"
              type="number"
              min="1"
              class="form-input"
              :class="{ 'error': errors.cantidad }"
              placeholder="Ingrese la cantidad"
              required
            />
            <span v-if="errors.cantidad" class="error-message">{{ errors.cantidad }}</span>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="form-label" for="precio_unitario">
              Precio Unitario *
            </label>
            <input
              id="precio_unitario"
              v-model.number="form.precio_unitario"
              :disabled="mode === 'view'"
              type="number"
              step="0.01"
              min="0"
              class="form-input"
              :class="{ 'error': errors.precio_unitario }"
              placeholder="Precio por unidad"
              required
            />
            <span v-if="errors.precio_unitario" class="error-message">{{ errors.precio_unitario }}</span>
          </div>

          <div>
            <label class="form-label">
              Total Calculado
            </label>
            <div class="form-display-value">
              ${{ formatCurrency(totalCalculado) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Información del Cliente -->
      <div class="form-section">
        <h3 class="form-section-title">Información del Cliente</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="form-label" for="cliente_id">
              Cliente (Opcional)
            </label>
            <select
              id="cliente_id"
              v-model="form.cliente_id"
              :disabled="mode === 'view'"
              class="form-select"
              :class="{ 'error': errors.cliente_id }"
            >
              <option value="">Cliente general...</option>
              <option
                v-for="cliente in clientes"
                :key="cliente.cliente_id"
                :value="cliente.cliente_id"
              >
                {{ cliente.nombre }} - {{ cliente.cedula }}
              </option>
            </select>
            <span v-if="errors.cliente_id" class="error-message">{{ errors.cliente_id }}</span>
          </div>

          <div>
            <label class="form-label" for="fecha">
              Fecha de Venta *
            </label>
            <input
              id="fecha"
              v-model="form.fecha"
              :disabled="mode === 'view'"
              type="date"
              class="form-input"
              :class="{ 'error': errors.fecha }"
              required
            />
            <span v-if="errors.fecha" class="error-message">{{ errors.fecha }}</span>
          </div>
        </div>
      </div>

      <!-- Información adicional en modo vista -->
      <div v-if="mode === 'view' && venta" class="form-section">
        <h3 class="form-section-title">Información Adicional</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="form-label">Fecha de Registro</label>
            <div class="form-display-value">
              {{ formatDateTime(venta.created_at) }}
            </div>
          </div>

          <div v-if="venta.updated_at !== venta.created_at">
            <label class="form-label">Última Actualización</label>
            <div class="form-display-value">
              {{ formatDateTime(venta.updated_at) }}
            </div>
          </div>
        </div>
      </div>
    </form>
        
        <div v-if="mode !== 'view'" class="flex justify-end gap-3 mt-6">
          <button
            type="button"
            @click="handleCancel"
            class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Cancelar
          </button>
          <button
            @click="handleSubmit"
            :disabled="loading"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
          >
            {{ loading ? 'Guardando...' : (mode === 'create' ? 'Crear' : 'Actualizar') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { useVentaStore } from '@/stores/venta';
import type { VentaProductoDespensa, CreateVentaDespensaRequest, UpdateVentaDespensaRequest } from '@/types';

// Props
interface Props {
  visible: boolean;
  mode: 'create' | 'edit' | 'view';
  venta?: VentaProductoDespensa;
}

const props = withDefaults(defineProps<Props>(), {
  mode: 'create',
  venta: undefined
});

// Emits
const emit = defineEmits<{
  'update:visible': [value: boolean];
  'saved': [venta: VentaProductoDespensa];
}>();

// Store
const ventaStore = useVentaStore();

// State
const loading = ref(false);
const errors = ref<Record<string, string>>({});

const form = ref<CreateVentaDespensaRequest>({
  producto_id: 0,
  cliente_id: null,
  cantidad: 1,
  precio_unitario: 0,
  fecha: new Date().toISOString().split('T')[0]
});

// Computed
const modalTitle = computed(() => {
  switch (props.mode) {
    case 'create': return 'Nueva Venta de Despensa';
    case 'edit': return 'Editar Venta de Despensa';
    case 'view': return 'Detalle de Venta de Despensa';
    default: return 'Venta de Despensa';
  }
});

const productosDespensa = computed(() => ventaStore.productosDespensa);
const clientes = computed(() => ventaStore.clientes);

const totalCalculado = computed(() => {
  return form.value.cantidad * form.value.precio_unitario;
});

// Watchers
watch(() => props.visible, (newVisible) => {
  if (newVisible) {
    resetForm();
    loadFormData();
    errors.value = {};
  }
});

// Autocompletar precio cuando se selecciona un producto
watch(() => form.value.producto_id, (newProductoId) => {
  if (newProductoId && props.mode !== 'view') {
    const producto = productosDespensa.value.find(p => p.id === newProductoId);
    if (producto) {
      form.value.precio_unitario = producto.precio_venta;
    }
  }
});

// Methods
function resetForm() {
  form.value = {
    producto_id: 0,
    cliente_id: null,
    cantidad: 1,
    precio_unitario: 0,
    fecha: new Date().toISOString().split('T')[0]
  };
}

function loadFormData() {
  if (props.venta && (props.mode === 'edit' || props.mode === 'view')) {
    form.value = {
      producto_id: props.venta.producto_id,
      cliente_id: props.venta.cliente_id || null,
      cantidad: props.venta.cantidad,
      precio_unitario: props.venta.precio_unitario,
      fecha: props.venta.fecha
    };
  }
}

function validateForm(): boolean {
  errors.value = {};

  if (!form.value.producto_id) {
    errors.value.producto_id = 'El producto es requerido';
  }

  if (!form.value.cantidad || form.value.cantidad <= 0) {
    errors.value.cantidad = 'La cantidad debe ser mayor a 0';
  }

  if (!form.value.precio_unitario || form.value.precio_unitario <= 0) {
    errors.value.precio_unitario = 'El precio unitario debe ser mayor a 0';
  }

  if (!form.value.fecha) {
    errors.value.fecha = 'La fecha es requerida';
  }

  // Validar stock disponible
  if (form.value.producto_id && form.value.cantidad > 0) {
    const producto = productosDespensa.value.find(p => p.id === form.value.producto_id);
    if (producto && form.value.cantidad > producto.stock) {
      errors.value.cantidad = `Stock insuficiente. Disponible: ${producto.stock}`;
    }
  }

  return Object.keys(errors.value).length === 0;
}

async function handleSubmit() {
  if (props.mode === 'view') return;
  
  if (!validateForm()) return;

  try {
    loading.value = true;
    
    let result: VentaProductoDespensa | null = null;
    
    if (props.mode === 'create') {
      result = await ventaStore.createVentaDespensa(form.value);
    } else if (props.mode === 'edit' && props.venta) {
      const updateData: UpdateVentaDespensaRequest = { ...form.value };
      result = await ventaStore.updateVentaDespensa(props.venta.id, updateData);
    }

    if (result) {
      emit('saved', result);
    }
  } catch (error: any) {
    console.error('Error al guardar venta de despensa:', error);
    
    // Manejar errores de validación del backend
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    }
  } finally {
    loading.value = false;
  }
}

function handleCancel() {
  emit('update:visible', false);
}

function formatCurrency(value: number): string {
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
}

function formatDateTime(dateString: string): string {
  return new Date(dateString).toLocaleString('es-CO');
}

// Lifecycle
onMounted(async () => {
  // Cargar datos necesarios si no están cargados
  if (productosDespensa.value.length === 0) {
    await ventaStore.fetchProductosDisponibles();
  }
  if (clientes.value.length === 0) {
    await ventaStore.fetchClientes();
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

/* Dark mode styles - if needed */
@media (prefers-color-scheme: dark) {
  .form-section-title {
    color: #f9fafb;
    border-color: #374151;
  }

  .form-label {
    color: #d1d5db;
  }

  .form-input,
  .form-select {
    background-color: #1f2937;
    border-color: #374151;
    color: #f9fafb;
  }

  .form-display-value {
    background-color: #1f2937;
    border-color: #374151;
    color: #d1d5db;
  }
}
</style>
