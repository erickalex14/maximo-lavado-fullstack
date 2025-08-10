<template>
  <div class="space-y-4">
    <!-- Filtros específicos de egresos -->
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-end">
      <div class="flex-1">
        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
        <input
          v-model="searchTerm"
          @input="handleSearch"
          type="text"
          placeholder="Buscar por destino o descripción..."
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
        />
      </div>
      <div class="flex gap-2">
        <button
          @click="openCreateModal"
          class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center gap-2"
        >
          <PlusIcon class="h-5 w-5" />
          Agregar
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
    </div>

    <!-- Tabla de egresos -->
    <div v-else-if="egresos.length > 0" class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Fecha
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Tipo
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Descripción
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Monto
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Acciones
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="egreso in egresos" :key="egreso.egreso_id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(egreso.fecha) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ getTypeLabel(egreso.tipo) }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ egreso.descripcion || 'Sin descripción' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                ${{ formatCurrency(egreso.monto) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <div class="flex items-center gap-2">
                  <button
                    @click="viewEgreso(egreso)"
                    class="text-blue-600 hover:text-blue-800 font-medium"
                  >
                    Ver
                  </button>
                  <button
                    @click="editEgreso(egreso)"
                    class="text-green-600 hover:text-green-800 font-medium"
                  >
                    Editar
                  </button>
                  <button
                    @click="confirmDeleteEgreso(egreso)"
                    class="text-red-600 hover:text-red-800 font-medium"
                  >
                    Eliminar
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Paginación -->
      <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex justify-between items-center">
          <div class="text-sm text-gray-700">
            Mostrando {{ pagination.from }} a {{ pagination.to }} de {{ pagination.total }} resultados
          </div>
          <div class="flex gap-2">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page <= 1"
              class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Anterior
            </button>
            <span class="px-3 py-1 text-sm text-gray-600">
              Página {{ pagination.current_page }} de {{ pagination.last_page }}
            </span>
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page >= pagination.last_page"
              class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Siguiente
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <div class="text-gray-500 mb-4">
        <DocumentTextIcon class="h-12 w-12 mx-auto mb-4 text-gray-400" />
        <p class="text-lg font-medium text-gray-900">No hay egresos registrados</p>
        <p class="text-gray-600">Comienza agregando tu primer egreso</p>
      </div>
      <button
        @click="openCreateModal"
        class="mt-4 bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200"
      >
        Agregar Primer Egreso
      </button>
    </div>

    <!-- Modales -->
    <EgresoModal
      :isOpen="showEgresoModal"
      :egreso="selectedEgreso"
      @close="closeEgresoModal"
      @save="handleSaveEgreso"
    />

    <EgresoViewModal
      :isOpen="showEgresoViewModal"
      :egreso="selectedEgreso"
      @close="closeEgresoViewModal"
      @edit="editEgreso"
    />

    <!-- Modal de confirmación de eliminación -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Confirmar eliminación</h3>
        <p class="text-gray-600 mb-6">
          ¿Estás seguro de que deseas eliminar este egreso? Esta acción no se puede deshacer.
        </p>
        <div class="flex justify-end gap-3">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Cancelar
          </button>
          <button
            @click="handleDeleteEgreso"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
          >
            Eliminar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { useFinanzasStore } from '@/stores/finanzas';
import type { Egreso } from '@/types';

// Componentes
import EgresoModal from '../EgresoModal.vue';
import EgresoViewModal from '../EgresoViewModal.vue';

// Iconos
import { PlusIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

// Store
const finanzasStore = useFinanzasStore();

// Estado local
const searchTerm = ref('');
const showEgresoModal = ref(false);
const showEgresoViewModal = ref(false);
const showDeleteModal = ref(false);
const selectedEgreso = ref<Egreso | null>(null);
const egresoToDelete = ref<Egreso | null>(null);

// Computed
const egresos = computed(() => finanzasStore.egresos);
const pagination = computed(() => finanzasStore.paginationEgresos);
const isLoading = computed(() => finanzasStore.isLoading);

// Métodos
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('es-CO');
};

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
};

const getTypeLabel = (tipo: string): string => {
  const labels: Record<string, string> = {
    salario: 'Salario',
    proveedor: 'Proveedor',
    gasto_general: 'Gasto General'
  };
  return labels[tipo] || tipo;
};

const handleSearch = () => {
  finanzasStore.filtersEgresos.search = searchTerm.value;
  finanzasStore.filtersEgresos.page = 1;
  finanzasStore.fetchEgresos(finanzasStore.filtersEgresos);
};

const changePage = (page: number) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    finanzasStore.filtersEgresos.page = page;
    finanzasStore.fetchEgresos(finanzasStore.filtersEgresos);
  }
};

const openCreateModal = () => {
  selectedEgreso.value = null;
  showEgresoModal.value = true;
};

const editEgreso = (egreso: Egreso) => {
  selectedEgreso.value = egreso;
  showEgresoModal.value = true;
};

const viewEgreso = (egreso: Egreso) => {
  selectedEgreso.value = egreso;
  showEgresoViewModal.value = true;
};

const confirmDeleteEgreso = (egreso: Egreso) => {
  egresoToDelete.value = egreso;
  showDeleteModal.value = true;
};

const closeEgresoModal = () => {
  showEgresoModal.value = false;
  selectedEgreso.value = null;
};

const closeEgresoViewModal = () => {
  showEgresoViewModal.value = false;
  selectedEgreso.value = null;
};

const handleSaveEgreso = async () => {
  closeEgresoModal();
  await finanzasStore.fetchEgresos(finanzasStore.filtersEgresos);
};

const handleDeleteEgreso = async () => {
  if (egresoToDelete.value) {
    try {
      await finanzasStore.deleteEgreso(egresoToDelete.value.egreso_id);
      await finanzasStore.fetchEgresos(finanzasStore.filtersEgresos);
    } catch (error) {
      console.error('Error deleting egreso:', error);
    } finally {
      showDeleteModal.value = false;
      egresoToDelete.value = null;
    }
  }
};

// Watchers
watch(() => finanzasStore.filtersEgresos.page, () => {
  finanzasStore.fetchEgresos(finanzasStore.filtersEgresos);
});

// Lifecycle
onMounted(() => {
  if (egresos.value.length === 0) {
    finanzasStore.fetchEgresos();
  }
});
</script>
