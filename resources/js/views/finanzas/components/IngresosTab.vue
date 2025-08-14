<template>
  <div class="space-y-4">
    <!-- Filtros específicos de ingresos -->
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-end">
      <div class="flex-1">
        <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
        <input
          v-model="searchTerm"
          @input="handleSearch"
          type="text"
          placeholder="Buscar por fuente o descripción..."
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
        />
      </div>
      <div class="flex gap-2">
        <button
          @click="openCreateModal"
          class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200 flex items-center gap-2"
        >
          <PlusIcon class="h-5 w-5" />
          Agregar
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
    </div>

    <!-- Tabla de ingresos -->
    <div v-else-if="ingresos.length > 0" class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
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
            <tr v-for="ingreso in ingresos" :key="ingreso.ingreso_id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(ingreso.fecha) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ getTypeLabel(ingreso.tipo) }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ ingreso.descripcion || 'Sin descripción' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                ${{ formatCurrency(ingreso.monto) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <div class="flex items-center gap-2">
                  <button
                    @click="viewIngreso(ingreso)"
                    class="text-blue-600 hover:text-blue-800 font-medium"
                  >
                    Ver
                  </button>
                  <button
                    @click="editIngreso(ingreso)"
                    class="text-green-600 hover:text-green-800 font-medium"
                  >
                    Editar
                  </button>
                  <button
                    @click="confirmDeleteIngreso(ingreso)"
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
        <p class="text-lg font-medium text-gray-900">No hay ingresos registrados</p>
        <p class="text-gray-600">Comienza agregando tu primer ingreso</p>
      </div>
      <button
        @click="openCreateModal"
        class="mt-4 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200"
      >
        Agregar Primer Ingreso
      </button>
    </div>

    <!-- Modales -->
    <IngresoModal
      :isOpen="showIngresoModal"
      :ingreso="selectedIngreso"
      @close="closeIngresoModal"
      @save="handleSaveIngreso"
    />

    <IngresoViewModal
      :isOpen="showIngresoViewModal"
      :ingreso="selectedIngreso"
      @close="closeIngresoViewModal"
      @edit="editIngreso"
    />

    <!-- Modal de confirmación de eliminación -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Confirmar eliminación</h3>
        <p class="text-gray-600 mb-6">
          ¿Estás seguro de que deseas eliminar este ingreso? Esta acción no se puede deshacer.
        </p>
        <div class="flex justify-end gap-3">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Cancelar
          </button>
          <button
            @click="handleDeleteIngreso"
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
import type { Ingreso } from '@/types';

// Componentes
import IngresoModal from '../IngresoModal.vue';
import IngresoViewModal from '../IngresoViewModal.vue';

// Iconos
import { PlusIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

// Store
const finanzasStore = useFinanzasStore();

// Estado local
const searchTerm = ref('');
const showIngresoModal = ref(false);
const showIngresoViewModal = ref(false);
const showDeleteModal = ref(false);
const selectedIngreso = ref<Ingreso | null>(null);
const ingresoToDelete = ref<Ingreso | null>(null);

// Computed
const ingresos = computed(() => finanzasStore.ingresos);
const pagination = computed(() => finanzasStore.paginationIngresos);
const isLoading = computed(() => finanzasStore.isLoading);

// Métodos
const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('es-CO');
};

const formatCurrency = (amount: number): string => {
  const num = Number(amount);
  if (isNaN(num)) return '0.00';
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(num);
};

const getTypeLabel = (tipo: string): string => {
  const labels: Record<string, string> = {
    venta: 'Venta',
    servicio: 'Servicio',
    lavado: 'Lavado',
    producto_automotriz: 'Producto Automotriz',
    producto_despensa: 'Producto de Despensa'
  };
  return labels[tipo] || tipo || 'N/A';
};

const handleSearch = () => {
  finanzasStore.filtersIngresos.search = searchTerm.value;
  finanzasStore.filtersIngresos.page = 1;
  finanzasStore.fetchIngresos(finanzasStore.filtersIngresos);
};

const changePage = (page: number) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    finanzasStore.filtersIngresos.page = page;
    finanzasStore.fetchIngresos(finanzasStore.filtersIngresos);
  }
};

const openCreateModal = () => {
  selectedIngreso.value = null;
  showIngresoModal.value = true;
};

const editIngreso = (ingreso: Ingreso) => {
  selectedIngreso.value = ingreso;
  showIngresoModal.value = true;
};

const viewIngreso = (ingreso: Ingreso) => {
  selectedIngreso.value = ingreso;
  showIngresoViewModal.value = true;
};

const confirmDeleteIngreso = (ingreso: Ingreso) => {
  ingresoToDelete.value = ingreso;
  showDeleteModal.value = true;
};

const closeIngresoModal = () => {
  showIngresoModal.value = false;
  selectedIngreso.value = null;
};

const closeIngresoViewModal = () => {
  showIngresoViewModal.value = false;
  selectedIngreso.value = null;
};

const handleSaveIngreso = async () => {
  closeIngresoModal();
  await finanzasStore.fetchIngresos(finanzasStore.filtersIngresos);
};

const handleDeleteIngreso = async () => {
  if (ingresoToDelete.value) {
    try {
      await finanzasStore.deleteIngreso(ingresoToDelete.value.ingreso_id);
      await finanzasStore.fetchIngresos(finanzasStore.filtersIngresos);
    } catch (error) {
      console.error('Error deleting ingreso:', error);
    } finally {
      showDeleteModal.value = false;
      ingresoToDelete.value = null;
    }
  }
};

// Watchers
watch(() => finanzasStore.filtersIngresos.page, () => {
  finanzasStore.fetchIngresos(finanzasStore.filtersIngresos);
});

// Lifecycle
onMounted(() => {
  if (ingresos.value.length === 0) {
    finanzasStore.fetchIngresos();
  }
});
</script>
