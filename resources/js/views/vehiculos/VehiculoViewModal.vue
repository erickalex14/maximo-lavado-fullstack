<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
      <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">
          Detalles del Vehículo
        </h3>
      </div>

      <div class="px-6 py-4 space-y-4">
        <!-- Cliente -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Cliente
          </label>
          <p class="text-gray-900 font-medium">
            {{ vehiculo?.cliente?.nombre || 'N/A' }}
          </p>
          <p v-if="vehiculo?.cliente?.cedula" class="text-sm text-gray-500">
            Cédula: {{ vehiculo.cliente.cedula }}
          </p>
        </div>

        <!-- Tipo de Vehículo -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">Tipo de Vehículo</label>
          <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
            {{ vehiculo?.tipo_vehiculo?.nombre || 'N/A' }}
          </span>
        </div>

        <!-- Matrícula -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Matrícula
          </label>
          <p class="text-gray-900">
            {{ vehiculo?.matricula || 'No registrada' }}
          </p>
        </div>

        <!-- Descripción -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Descripción
          </label>
          <p class="text-gray-900">
            {{ vehiculo?.descripcion || 'Sin descripción' }}
          </p>
        </div>

        <!-- Estadísticas de lavados -->
        <div v-if="vehiculo?.lavados" class="pt-4 border-t">
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Historial de Lavados
          </label>
          <div class="bg-gray-50 rounded-lg p-3">
            <p class="text-sm text-gray-700">
              Total de lavados: <span class="font-medium">{{ vehiculo.lavados.length }}</span>
            </p>
            <p v-if="vehiculo.lavados.length > 0" class="text-sm text-gray-700">
              Último lavado: <span class="font-medium">
                {{ new Date(vehiculo.lavados[vehiculo.lavados.length - 1].fecha).toLocaleDateString() }}
              </span>
            </p>
          </div>
        </div>

        <!-- Fechas -->
        <div class="grid grid-cols-1 gap-4 pt-4 border-t">
          <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">
              Fecha de Registro
            </label>
            <p class="text-gray-900">
              {{ vehiculo?.created_at ? new Date(vehiculo.created_at).toLocaleDateString() : 'N/A' }}
            </p>
          </div>
          <div v-if="vehiculo?.updated_at && vehiculo.updated_at !== vehiculo.created_at">
            <label class="block text-sm font-medium text-gray-500 mb-1">
              Última Actualización
            </label>
            <p class="text-gray-900">
              {{ new Date(vehiculo.updated_at).toLocaleDateString() }}
            </p>
          </div>
        </div>
      </div>

      <!-- Botones -->
      <div class="px-6 py-4 bg-gray-50 flex justify-end gap-3">
        <button
          @click="$emit('close')"
          class="px-4 py-2 text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg"
        >
          Cerrar
        </button>
        <button
          @click="vehiculo && $emit('edit', vehiculo)"
          :disabled="!vehiculo"
          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-50"
        >
          Editar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Vehiculo } from '@/types';

interface Props {
  isOpen: boolean;
  vehiculo?: Vehiculo | null;
}

defineProps<Props>();
defineEmits<{
  close: [];
  edit: [vehiculo: Vehiculo];
}>();

// (Badge simplificado usa nombre directo del tipo)
</script>
