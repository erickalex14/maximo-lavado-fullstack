<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl mx-4 flex flex-col max-h-[90vh]">
      <div class="px-6 py-4 border-b flex items-center justify-between">
        <h3 class="text-lg font-semibold">Tipos de Vehículo</h3>
        <button @click="$emit('close')" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>

      <div class="p-4 flex gap-4 border-b bg-gray-50">
        <div class="flex-1 flex gap-2">
          <input v-model="search" @input="debouncedSearch" type="text" placeholder="Buscar..." class="flex-1 px-3 py-2 border rounded" />
          <select v-model.number="perPage" @change="changePerPage" class="px-3 py-2 border rounded">
            <option :value="10">10</option>
            <option :value="15">15</option>
            <option :value="25">25</option>
          </select>
        </div>
        <button @click="showCreate = !showCreate" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded">
          {{ showCreate ? 'Cerrar' : 'Nuevo Tipo' }}
        </button>
      </div>

      <transition name="fade">
        <form v-if="showCreate" @submit.prevent="createTipo" class="p-4 grid grid-cols-1 md:grid-cols-3 gap-4 border-b">
          <div>
            <label class="text-xs font-medium text-gray-600">Nombre *</label>
            <input v-model="form.nombre" required class="mt-1 w-full px-3 py-2 border rounded" />
          </div>
          <div class="md:col-span-2">
            <label class="text-xs font-medium text-gray-600">Descripción</label>
            <input v-model="form.descripcion" class="mt-1 w-full px-3 py-2 border rounded" />
          </div>
          <div class="flex items-center gap-2 md:col-span-1">
            <input id="reqMat" type="checkbox" v-model="form.requiere_matricula" class="h-4 w-4 text-indigo-600 border-gray-300 rounded" />
            <label for="reqMat" class="text-xs font-medium text-gray-600 select-none">Requiere matrícula</label>
          </div>
          <div class="md:col-span-3 flex justify-end gap-3">
            <button type="button" @click="resetForm" class="px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 rounded">Limpiar</button>
            <button type="submit" :disabled="loading" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded disabled:opacity-50">
              {{ loading ? 'Guardando...' : (editingId ? 'Actualizar' : 'Guardar') }}
            </button>
          </div>
        </form>
      </transition>

      <div class="flex-1 overflow-y-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-100 text-gray-600 text-xs uppercase">
            <tr>
              <th class="px-4 py-2 text-left">Nombre</th>
              <th class="px-4 py-2 text-left">Descripción</th>
              <th class="px-4 py-2 text-left">Estado</th>
              <th class="px-4 py-2 text-left">Req. Matrícula</th>
              <th class="px-4 py-2 text-right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading && !tipos.length">
              <td colspan="4" class="px-4 py-6 text-center">Cargando...</td>
            </tr>
              <tr v-for="t in tipos" :key="t.tipo_vehiculo_id" class="border-t hover:bg-gray-50">
                <td class="px-4 py-2 font-medium">{{ t.nombre }}</td>
                <td class="px-4 py-2">{{ t.descripcion || '—' }}</td>
                <td class="px-4 py-2">
                  <span :class="t.activo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" class="px-2 py-0.5 rounded text-xs font-semibold">
                    {{ t.activo ? 'Activo' : 'Inactivo' }}
                  </span>
                </td>
                <td class="px-4 py-2">
                  <span :class="(t.requiere_matricula ? 'bg-slate-200 text-slate-700' : 'bg-amber-100 text-amber-700')" class="px-2 py-0.5 rounded text-[10px] font-medium">
                    {{ t.requiere_matricula ? 'Sí' : 'No' }}
                  </span>
                </td>
                <td class="px-4 py-2 text-right flex justify-end gap-2">
                <button @click="toggleActivo(t)" class="text-xs px-2 py-1 rounded border" :class="t.activo ? 'border-yellow-500 text-yellow-600' : 'border-green-500 text-green-600'">
                  {{ t.activo ? 'Desactivar' : 'Activar' }}
                </button>
                <button @click="prepareEdit(t)" class="text-xs px-2 py-1 rounded border border-indigo-500 text-indigo-600">Editar</button>
                <button @click="remove(t)" class="text-xs px-2 py-1 rounded border border-red-500 text-red-600">Eliminar</button>
              </td>
            </tr>
            <tr v-if="!loading && !tipos.length">
              <td colspan="4" class="px-4 py-6 text-center text-gray-500">Sin tipos registrados</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="pagination.total > pagination.per_page" class="px-6 py-3 border-t bg-gray-50 flex items-center justify-between text-xs">
        <div>Mostrando {{ pagination.from }} - {{ pagination.to }} de {{ pagination.total }}</div>
        <div class="flex gap-2 items-center">
          <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="px-2 py-1 border rounded disabled:opacity-40">Prev</button>
          <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded">{{ pagination.current_page }}/{{ pagination.last_page }}</span>
          <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="px-2 py-1 border rounded disabled:opacity-40">Next</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { useTipoVehiculoStore } from '@/stores/tipoVehiculo';

interface Props { isOpen: boolean }
const props = defineProps<Props>();
const emit = defineEmits<{ close: [] }>();

const tipoVehiculoStore = useTipoVehiculoStore();
const tipos = tipoVehiculoStore.tipos; // reactive ref
const pagination = tipoVehiculoStore.pagination; // es un ref dentro del store pero expuesto como objeto reactivo
const loading = ref(false);

const search = ref('');
const perPage = ref(15);
const showCreate = ref(false);
const editingId = ref<number | null>(null);
const form = ref({ nombre: '', descripcion: '', requiere_matricula: true });

let searchTimeout: any;
function debouncedSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(async () => {
    await tipoVehiculoStore.search(search.value);
  }, 350);
}

function resetForm() { form.value = { nombre: '', descripcion: '', requiere_matricula: true }; editingId.value = null; }

async function createTipo() {
  try {
    loading.value = true;
    if (!form.value.nombre.trim()) return;
    if (editingId.value) {
  await tipoVehiculoStore.update(editingId.value, { nombre: form.value.nombre, descripcion: form.value.descripcion, requiere_matricula: form.value.requiere_matricula });
    } else {
  await tipoVehiculoStore.create({ nombre: form.value.nombre, descripcion: form.value.descripcion, requiere_matricula: form.value.requiere_matricula, activo: true });
    }
    resetForm(); showCreate.value = false;
  } catch (e) { console.error('create/update tipoVehiculo', e); }
  finally { loading.value = false; }
}

function prepareEdit(t: any) {
  showCreate.value = true;
  editingId.value = t.tipo_vehiculo_id;
  form.value = { nombre: t.nombre, descripcion: t.descripcion || '', requiere_matricula: !!t.requiere_matricula };
}

async function toggleActivo(t: any) { await tipoVehiculoStore.toggleActivo(t.tipo_vehiculo_id); }
async function remove(t: any) { if (confirm('Eliminar tipo "' + t.nombre + '"?')) await tipoVehiculoStore.remove(t.tipo_vehiculo_id); }
async function changePage(p: number) { if (p>=1 && p<= pagination.last_page) await tipoVehiculoStore.changePage(p); }
async function changePerPage() { await tipoVehiculoStore.changePerPage(perPage.value); }

watch(() => props.isOpen, async (open) => { if (open) { await tipoVehiculoStore.fetchTipos(); } });

onMounted(async () => { if (props.isOpen) await tipoVehiculoStore.fetchTipos(); });
</script>

<style scoped>
.fade-enter-active,.fade-leave-active{transition:all .18s ease}
.fade-enter-from,.fade-leave-to{opacity:0;transform:translateY(-4px)}
</style>
