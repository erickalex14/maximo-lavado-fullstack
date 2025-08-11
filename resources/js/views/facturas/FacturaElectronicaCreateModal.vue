<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-xl mx-4 max-h-[90vh] overflow-y-auto">
      <div class="px-6 py-4 border-b flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">Nueva Factura Electrónica</h3>
        <button @click="close" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>
      <form @submit.prevent="handleSubmit" class="px-6 py-5 space-y-5">
        <div class="space-y-1">
          <label class="text-sm font-medium text-gray-700">ID Venta *</label>
          <input v-model.number="form.venta_id" type="number" min="1" class="w-full px-3 py-2 border rounded-md border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" :class="{ 'border-red-500': errors.venta_id }" placeholder="Ej: 123" required />
          <p v-if="errors.venta_id" class="text-xs text-red-600">{{ errors.venta_id }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-1">
            <label class="text-sm font-medium text-gray-700">Razón Social</label>
            <input v-model="form.cliente_razon_social" type="text" class="w-full px-3 py-2 border rounded-md border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="Opcional" />
          </div>
          <div class="space-y-1">
            <label class="text-sm font-medium text-gray-700">Identificación</label>
            <input v-model="form.cliente_identificacion" type="text" class="w-full px-3 py-2 border rounded-md border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="Ced/RUC" />
          </div>
          <div class="space-y-1">
            <label class="text-sm font-medium text-gray-700">Email</label>
            <input v-model="form.cliente_email" type="email" class="w-full px-3 py-2 border rounded-md border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="email@cliente.com" />
          </div>
          <div class="space-y-1">
            <label class="text-sm font-medium text-gray-700">Teléfono</label>
            <input v-model="form.cliente_telefono" type="text" class="w-full px-3 py-2 border rounded-md border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="099..." />
          </div>
          <div class="md:col-span-2 space-y-1">
            <label class="text-sm font-medium text-gray-700">Dirección</label>
            <input v-model="form.cliente_direccion" type="text" class="w-full px-3 py-2 border rounded-md border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="Opcional" />
          </div>
        </div>
        <div v-if="serverError" class="bg-red-50 text-red-700 text-sm rounded px-3 py-2">{{ serverError }}</div>
      </form>
      <div class="px-6 py-4 border-t bg-gray-50 flex justify-end gap-3">
        <button type="button" @click="close" class="inline-flex items-center gap-2 px-4 py-2 rounded-md font-medium text-sm border border-gray-300 bg-white text-gray-700 hover:bg-gray-100">Cancelar</button>
        <button type="button" @click="handleSubmit" :disabled="submitting" class="inline-flex items-center gap-2 px-4 py-2 rounded-md font-medium text-sm bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-60">
          <svg v-if="submitting" class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
          Crear
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import type { CreateFacturaElectronicaRequest } from '@/types';
import { useFacturaElectronicaStore } from '@/stores/facturaElectronica';

interface Props { visible: boolean }
const props = defineProps<Props>();
const emit = defineEmits<{ 'update:visible':[boolean]; created:[number] }>();

const feStore = useFacturaElectronicaStore();

const form = reactive<CreateFacturaElectronicaRequest>({
  venta_id: 0,
  cliente_razon_social: '',
  cliente_identificacion: '',
  cliente_email: '',
  cliente_telefono: '',
  cliente_direccion: ''
});

const errors = reactive<Record<string,string>>({});
const serverError = ref('');
const submitting = ref(false);

function validate() {
  Object.keys(errors).forEach(k=>delete errors[k]);
  if (!form.venta_id || form.venta_id < 1) errors.venta_id = 'Requerido';
  return Object.keys(errors).length === 0;
}

async function handleSubmit(){
  if (!validate()) return;
  submitting.value = true;
  serverError.value='';
  try {
    const created = await feStore.createFacturaElectronica(form as any);
    if (created) {
      emit('created', created.id);
      close();
    }
  } catch (e:any){
    serverError.value = e?.response?.data?.message || 'Error creando factura';
  } finally { submitting.value = false; }
}

function close(){ emit('update:visible', false); }

watch(()=>props.visible, v=>{ if(v){ reset(); } });

function reset(){
  form.venta_id = 0;
  form.cliente_razon_social='';
  form.cliente_identificacion='';
  form.cliente_email='';
  form.cliente_telefono='';
  form.cliente_direccion='';
  serverError.value='';
  Object.keys(errors).forEach(k=>delete errors[k]);
}
</script>
