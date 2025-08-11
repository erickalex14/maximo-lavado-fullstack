<template>
  <div v-if="visible" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl mx-4 max-h-screen overflow-y-auto">
      <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">Factura Electrónica</h3>
      </div>
      <div v-if="factura" class="px-6 py-5 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-1">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Número</p>
            <div class="form-display-value !bg-gray-50">{{ factura.numero_factura }}</div>
          </div>
          <div class="space-y-1">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Estado</p>
            <div class="form-display-value !bg-gray-50 capitalize">{{ factura.estado }}</div>
          </div>
          <div class="space-y-1">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Fecha Emisión</p>
            <div class="form-display-value !bg-gray-50">{{ formatDate(factura.fecha_emision) }}</div>
          </div>
          <div class="space-y-1">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Clave Acceso</p>
            <div class="form-display-value !bg-gray-50 truncate">{{ factura.clave_acceso || '—' }}</div>
          </div>
        </div>
        <div class="form-section-title">Cliente</div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-1">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Razón Social</p>
            <div class="form-display-value !bg-gray-50">{{ factura.cliente_razon_social }}</div>
          </div>
          <div class="space-y-1">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Identificación</p>
            <div class="form-display-value !bg-gray-50">{{ factura.cliente_identificacion }}</div>
          </div>
          <div class="space-y-1">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Email</p>
            <div class="form-display-value !bg-gray-50">{{ factura.cliente_email || '—' }}</div>
          </div>
            <div class="space-y-1">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Teléfono</p>
            <div class="form-display-value !bg-gray-50">{{ factura.cliente_telefono || '—' }}</div>
          </div>
          <div class="space-y-1 md:col-span-2">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Dirección</p>
            <div class="form-display-value !bg-gray-50">{{ factura.cliente_direccion || '—' }}</div>
          </div>
        </div>
        <div class="form-section-title">Totales</div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="space-y-1">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Subtotal 0%</p>
            <div class="form-display-value !bg-gray-50">${{ formatCurrency(factura.subtotal_0) }}</div>
          </div>
          <div class="space-y-1">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Subtotal 12%</p>
            <div class="form-display-value !bg-gray-50">${{ formatCurrency(factura.subtotal_12) }}</div>
          </div>
          <div class="space-y-1">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">IVA</p>
            <div class="form-display-value !bg-gray-50">${{ formatCurrency(factura.iva) }}</div>
          </div>
          <div class="space-y-1 md:col-span-3">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total</p>
            <div class="form-display-value !bg-gray-50 font-semibold">${{ formatCurrency(factura.total) }}</div>
          </div>
        </div>
        <div class="form-section-title">Auditoría</div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-1">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Creada</p>
            <div class="form-display-value !bg-gray-50">{{ formatDateTime(factura.created_at) }}</div>
          </div>
          <div class="space-y-1">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Actualizada</p>
            <div class="form-display-value !bg-gray-50">{{ formatDateTime(factura.updated_at) }}</div>
          </div>
          <div class="space-y-1">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Autorización</p>
            <div class="form-display-value !bg-gray-50">{{ factura.autorizacion || '—' }}</div>
          </div>
          <div v-if="factura.fecha_autorizacion" class="space-y-1">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Fecha Autorización</p>
            <div class="form-display-value !bg-gray-50">{{ formatDateTime(factura.fecha_autorizacion) }}</div>
          </div>
        </div>
        <div v-if="erroresFormateados.length" class="bg-red-50 border border-red-200 rounded p-4">
          <p class="text-sm font-medium text-red-700 mb-2">Errores SRI</p>
          <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
            <li v-for="(e,i) in erroresFormateados" :key="i">{{ e }}</li>
          </ul>
        </div>
      </div>
      <div class="px-6 py-4 border-t bg-gray-50 flex justify-end">
        <button type="button" class="btn btn-outline-secondary" @click="handleClose">Cerrar</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { FacturaElectronica } from '@/types';

interface Props { visible: boolean; factura?: FacturaElectronica | null }
const props = withDefaults(defineProps<Props>(), { factura: null });
const emit = defineEmits<{ 'update:visible':[boolean] }>();

function formatCurrency(v: number) { return new Intl.NumberFormat('es-EC',{ minimumFractionDigits:2, maximumFractionDigits:2 }).format(v); }
function formatDate(date: string) { return new Date(date).toLocaleDateString('es-EC'); }
function formatDateTime(date?: string|null) { return date? new Date(date).toLocaleString('es-EC') : '—'; }
function handleClose(){ emit('update:visible', false); }

const erroresFormateados = computed(() => {
  const raw = props.factura?.errores_sri;
  if (!raw) return [] as string[];
  if (Array.isArray(raw)) return raw;
  if (typeof raw === 'string') return raw.split(/\n|\r/).map(s=>s.trim()).filter(Boolean);
  return [] as string[];
});
</script>

<style scoped>
.form-section-title { @apply text-base font-semibold text-gray-900 border-b pb-2; }
.form-label { @apply text-sm font-medium text-gray-600 mb-1; }
.form-display-value { @apply text-sm text-gray-900 px-3 py-2 rounded border border-gray-200 bg-white; }
.btn { @apply px-4 py-2 rounded-md font-medium flex items-center gap-2 transition; }
.btn-outline-secondary { @apply border border-gray-300 text-gray-700 bg-white; }
.btn-outline-secondary:hover { @apply bg-gray-100; }
</style>
