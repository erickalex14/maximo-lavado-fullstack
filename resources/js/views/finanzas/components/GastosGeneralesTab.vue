<template>
  <div class="space-y-4">
    <!-- Filtros específicos de gastos generales -->
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-end">
      <div class="flex-1">
        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
        <input
          v-model="searchTerm"
          @input="handleSearch"
          type="text"
          placeholder="Buscar por nombre o descripción..."
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
        />
      </div>
      <div class="flex gap-2">
        <button
          @click="openCreateModal"
          class="bg-orange-600 hover:bg-orange-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center gap-2"
        >
          <PlusIcon class="h-5 w-5" />
          Agregar
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-600"></div>
    </div>

    <!-- Tabla de gastos generales -->
    <div v-else-if="gastosGenerales.length > 0" class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Fecha
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Nombre
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
            <tr v-for="gasto in gastosGenerales" :key="gasto.gasto_general_id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(gasto.fecha) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ gasto.nombre }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ gasto.descripcion || 'Sin descripción' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-orange-600">
                ${{ formatCurrency(gasto.monto) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <div class="flex items-center gap-2">
                  <button
                    @click="viewGasto(gasto)"
                    class="text-blue-600 hover:text-blue-800 font-medium"
                  >
                    Ver
                  </button>
                  <button
                    @click="editGasto(gasto)"
                    class="text-green-600 hover:text-green-800 font-medium"
                  >
                    Editar
                  </button>
                  <button
                    @click="confirmDeleteGasto(gasto)"
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
        <p class="text-lg font-medium text-gray-900">No hay gastos generales registrados</p>
        <p class="text-gray-600">Comienza agregando tu primer gasto general</p>
      </div>
      <button
        @click="openCreateModal"
        class="mt-4 bg-orange-600 hover:bg-orange-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200"
      >
        Agregar Primer Gasto
      </button>
    </div>

    <!-- Modales -->
    <GastoGeneralModal
      :is-open="showGastoModal"
      :gasto="selectedGasto"
      @close="closeGastoModal"
      @save="handleSaveGasto"
    />

    <GastoGeneralViewModal
      :is-open="showGastoViewModal"
      :gasto="selectedGasto"
      @close="closeGastoViewModal"
      @edit="editGasto"
    />

    <!-- Modal de confirmación de eliminación -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Confirmar eliminación</h3>
        <p class="text-gray-600 mb-6">
          ¿Estás seguro de que deseas eliminar este gasto general? Esta acción no se puede deshacer.
        </p>
        <div class="flex justify-end gap-3">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Cancelar
          </button>
          <button
            @click="handleDeleteGasto"
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
import type { GastoGeneral } from '@/types';

// Componentes
import GastoGeneralModal from '../GastoGeneralModal.vue';
import GastoGeneralViewModal from '../GastoGeneralViewModal.vue';

// Iconos
import { PlusIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

// Store
const finanzasStore = useFinanzasStore();

// Estado local
const searchTerm = ref('');
const showGastoModal = ref(false);
const showGastoViewModal = ref(false);
const showDeleteModal = ref(false);
const selectedGasto = ref<GastoGeneral | null>(null);
const gastoToDelete = ref<GastoGeneral | null>(null);

// Computed
const gastosGenerales = computed(() => finanzasStore.gastosGenerales);
const pagination = computed(() => finanzasStore.paginationGastosGenerales);
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

const handleSearch = () => {
  finanzasStore.filtersGastosGenerales.search = searchTerm.value;
  finanzasStore.filtersGastosGenerales.page = 1;
  finanzasStore.fetchGastosGenerales(finanzasStore.filtersGastosGenerales);
};

const changePage = (page: number) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    finanzasStore.filtersGastosGenerales.page = page;
    finanzasStore.fetchGastosGenerales(finanzasStore.filtersGastosGenerales);
  }
};

const openCreateModal = () => {
  selectedGasto.value = null;
  showGastoModal.value = true;
};

const editGasto = (gasto: GastoGeneral) => {
  selectedGasto.value = gasto;
  showGastoModal.value = true;
};

const viewGasto = (gasto: GastoGeneral) => {
  selectedGasto.value = gasto;
  showGastoViewModal.value = true;
};

const confirmDeleteGasto = (gasto: GastoGeneral) => {
  gastoToDelete.value = gasto;
  showDeleteModal.value = true;
};

const closeGastoModal = () => {
  showGastoModal.value = false;
  selectedGasto.value = null;
};

const closeGastoViewModal = () => {
  showGastoViewModal.value = false;
  selectedGasto.value = null;
};

const handleSaveGasto = async () => {
  closeGastoModal();
  await finanzasStore.fetchGastosGenerales(finanzasStore.filtersGastosGenerales);
};

const handleDeleteGasto = async () => {
  if (gastoToDelete.value) {
    try {
      await finanzasStore.deleteGastoGeneral(gastoToDelete.value.gasto_general_id);
      await finanzasStore.fetchGastosGenerales(finanzasStore.filtersGastosGenerales);
    } catch (error) {
      console.error('Error deleting gasto general:', error);
    } finally {
      showDeleteModal.value = false;
      gastoToDelete.value = null;
    }
  }
};

// Watchers
watch(() => finanzasStore.filtersGastosGenerales.page, () => {
  finanzasStore.fetchGastosGenerales(finanzasStore.filtersGastosGenerales);
});

// Lifecycle
onMounted(() => {
  if (gastosGenerales.value.length === 0) {
    finanzasStore.fetchGastosGenerales();
  }
});
</script>
