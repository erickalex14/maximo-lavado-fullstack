<template>
  <div v-if="visible" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl mx-4 max-h-screen overflow-y-auto">
      <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">
          Detalles de Factura
        </h3>
      </div>

      <div v-if="factura" class="px-6 py-4 space-y-6">
        <!-- Información básica de la factura -->
        <div class="form-section-title">
          Información de la Factura
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="form-label">
              Número de Factura
            </label>
            <div class="form-display-value">
              {{ factura.numero_factura }}
            </div>
          </div>
          
          <div>
            <label class="form-label">
              Cliente
            </label>
            <div class="form-display-value">
              {{ factura.cliente?.nombre || 'N/A' }}
            </div>
          </div>
          
          <div>
            <label class="form-label">
              Fecha
            </label>
            <div class="form-display-value">
              {{ formatDate(factura.fecha) }}
            </div>
          </div>
          
          <div>
            <label class="form-label">
              Total
            </label>
            <div class="form-display-value">
              ${{ formatCurrency(factura.total) }}
            </div>
          </div>
        </div>
        
        <div v-if="factura.descripcion">
          <label class="form-label">
            Descripción
          </label>
          <div class="form-display-value">
            {{ factura.descripcion }}
          </div>
        </div>

        <!-- Información del cliente -->
        <div v-if="factura.cliente" class="mt-6">
          <div class="form-section-title">
            Información del Cliente
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="form-label">
                Nombre
              </label>
              <div class="form-display-value">
                {{ factura.cliente.nombre }}
              </div>
            </div>
            
            <div v-if="factura.cliente.email">
              <label class="form-label">
                Email
              </label>
              <div class="form-display-value">
                {{ factura.cliente.email }}
              </div>
            </div>
            
            <div v-if="factura.cliente.telefono">
              <label class="form-label">
                Teléfono
              </label>
              <div class="form-display-value">
                {{ factura.cliente.telefono }}
              </div>
            </div>
            
            <div v-if="factura.cliente.direccion">
              <label class="form-label">
                Dirección
              </label>
              <div class="form-display-value">
                {{ factura.cliente.direccion }}
              </div>
            </div>
          </div>
        </div>

        <!-- Detalles de la factura -->
        <div v-if="factura.detalles && factura.detalles.length > 0" class="mt-6">
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
                    <div class="flex flex-col">
                      <span class="font-medium">
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
                      </span>
                    </div>
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
              <tfoot class="bg-gray-50">
                <tr>
                  <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-900">
                    Total:
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-gray-900">
                    ${{ formatCurrency(factura.total) }}
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- Información de auditoría -->
        <div class="mt-6">
          <div class="form-section-title">
            Información de Auditoría
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="form-label">
                Fecha de Creación
              </label>
              <div class="form-display-value">
                {{ formatDateTime(factura.created_at) }}
              </div>
            </div>
            
            <div>
              <label class="form-label">
                Última Actualización
              </label>
              <div class="form-display-value">
                {{ formatDateTime(factura.updated_at) }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="px-6 py-4 border-t bg-gray-50 flex justify-end space-x-3">
        <button
          type="button"
          @click="handleClose"
          class="btn btn-outline-secondary"
        >
          Cerrar
        </button>
        
        <button
          v-if="factura"
          type="button"
          @click="handleEdit"
          class="btn btn-primary"
        >
          Editar Factura
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { Factura } from '@/types';

// Props
interface Props {
  visible: boolean;
  factura?: Factura | null;
}

const props = withDefaults(defineProps<Props>(), {
  factura: null
});

// Emits
const emit = defineEmits<{
  'update:visible': [value: boolean];
  'edit': [factura: Factura];
}>();

// Computed
const isVisible = computed({
  get: () => props.visible,
  set: (value) => emit('update:visible', value)
});

// Methods
function formatCurrency(value: number): string {
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('es-CO');
}

function formatDateTime(date: string): string {
  return new Date(date).toLocaleString('es-CO');
}

function handleClose() {
  emit('update:visible', false);
}

function handleEdit() {
  if (props.factura) {
    emit('edit', props.factura);
  }
}
</script>

<style scoped>
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

.form-display-value {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  background-color: #f9fafb;
  color: #374151;
  min-height: 2.5rem;
  display: flex;
  align-items: center;
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

.btn-outline-secondary {
  border: 1px solid #d1d5db;
  color: #374151;
  background-color: white;
}

.btn-outline-secondary:hover {
  background-color: #f9fafb;
}
</style>
