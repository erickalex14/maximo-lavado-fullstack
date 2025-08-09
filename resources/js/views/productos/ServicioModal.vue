<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 space-y-6">
      <div class="flex justify-between items-center">
        <h3 class="text-xl font-semibold text-gray-900">{{ modo === 'edit' ? 'Editar Servicio' : 'Nuevo Servicio' }}</h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">✕</button>
      </div>
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
          <input v-model="form.nombre" type="text" required class="w-full form-input" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
            <textarea v-model="form.descripcion" rows="3" class="w-full form-textarea" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Vehículo</label>
          <select v-model.number="form.tipo_vehiculo_id" required class="w-full form-input">
            <option value="" disabled>Seleccione...</option>
            <option v-for="tv in tiposVehiculo" :key="tv.tipo_vehiculo_id" :value="tv.tipo_vehiculo_id">{{ tv.nombre }}</option>
          </select>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Precio Base</label>
            <input v-model.number="form.precio_base" type="number" min="0" step="0.01" required class="w-full form-input" />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Duración Est. (min)</label>
            <input v-model.number="form.duracion_estimada" type="number" min="0" step="1" class="w-full form-input" />
          </div>
        </div>
        <div class="flex items-center gap-2">
          <input id="activo" type="checkbox" v-model="form.activo" class="h-4 w-4 text-blue-600 border-gray-300 rounded" />
          <label for="activo" class="text-sm text-gray-700">Activo</label>
        </div>
        <div class="flex justify-end gap-3 pt-4">
          <button type="button" @click="$emit('close')" class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-50">Cancelar</button>
          <button type="submit" :disabled="loading" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
            {{ loading ? 'Guardando...' : (modo === 'edit' ? 'Guardar Cambios' : 'Crear') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
<script setup lang="ts">
import { reactive, watch, computed, onMounted, ref } from 'vue';
import { useServicioStore } from '@/stores/servicio';
import { useTipoVehiculoStore } from '@/stores/tipoVehiculo';
import type { Servicio, CreateServicioRequest, UpdateServicioRequest, TipoVehiculo } from '@/types';

interface Props { isOpen: boolean; servicio: Servicio | null; modo: 'create' | 'edit'; }
const props = defineProps<Props>();
const emit = defineEmits<{ close: []; success: [] }>();
const servicioStore = useServicioStore();
const tipoVehiculoStore = useTipoVehiculoStore();
const tiposVehiculo = ref<TipoVehiculo[]>([]);
const loading = computed(() => servicioStore.isLoading);

interface ServicioForm extends CreateServicioRequest { tipo_vehiculo_id?: number | null; id?: number }
const blank: ServicioForm = { nombre: '', descripcion: '', precio_base: 0, duracion_estimada: undefined, activo: true, tipo_vehiculo_id: null };
const form = reactive<ServicioForm>({ ...blank });

watch(() => props.servicio, (s) => {
  if (s) Object.assign(form, { id: s.id, nombre: s.nombre, descripcion: s.descripcion || '', precio_base: s.precio_base, duracion_estimada: (s as any).duracion_estimada ?? undefined, activo: s.activo, tipo_vehiculo_id: (s as any).tipo_vehiculo_id });
  else Object.assign(form, { ...blank });
}, { immediate: true });

async function handleSubmit() {
  try {
    if (props.modo === 'edit' && form.id) {
      const payload: UpdateServicioRequest & { tipo_vehiculo_id?: number | null } = { nombre: form.nombre, descripcion: form.descripcion, precio_base: form.precio_base, duracion_estimada: form.duracion_estimada, activo: form.activo, tipo_vehiculo_id: form.tipo_vehiculo_id } as any;
      await servicioStore.update(form.id, payload);
    } else {
      const payload: CreateServicioRequest & { tipo_vehiculo_id?: number | null } = { nombre: form.nombre, descripcion: form.descripcion, precio_base: form.precio_base, duracion_estimada: form.duracion_estimada, activo: form.activo, tipo_vehiculo_id: form.tipo_vehiculo_id } as any;
      await servicioStore.create(payload);
    }
    emit('success');
  } catch (e) { console.error('save servicio error', e); }
}

onMounted(async () => {
  // Usar lista ya cargada si existe
  if ((tipoVehiculoStore as any).tiposAll?.length) {
    tiposVehiculo.value = (tipoVehiculoStore as any).tiposAll;
  } else if ((tipoVehiculoStore as any).fetchAll) {
    await (tipoVehiculoStore as any).fetchAll();
    tiposVehiculo.value = (tipoVehiculoStore as any).tiposAll || [];
  } else if ((tipoVehiculoStore as any).fetchTipos) { // fallback a paginados
    await (tipoVehiculoStore as any).fetchTipos();
    tiposVehiculo.value = (tipoVehiculoStore as any).tipos || [];
  }
});
</script>
<style scoped>
.form-input, .form-textarea { padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; }
.form-input:focus, .form-textarea:focus { outline: 2px solid #3b82f6; outline-offset: 1px; }
</style>
