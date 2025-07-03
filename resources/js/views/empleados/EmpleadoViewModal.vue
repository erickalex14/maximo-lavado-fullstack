<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
      <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">
          Detalles del Empleado
        </h3>
      </div>

      <div class="px-6 py-4 space-y-4">
        <!-- Nombres -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Nombres
          </label>
          <p class="text-gray-900">{{ empleado?.nombres || 'N/A' }}</p>
        </div>

        <!-- Apellidos -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Apellidos
          </label>
          <p class="text-gray-900">{{ empleado?.apellidos || 'N/A' }}</p>
        </div>

        <!-- Nombre Completo -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Nombre Completo
          </label>
          <p class="text-gray-900 font-medium">
            {{ empleado ? `${empleado.nombres} ${empleado.apellidos}` : 'N/A' }}
          </p>
        </div>

        <!-- Teléfono -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Teléfono
          </label>
          <p class="text-gray-900">{{ empleado?.telefono || 'N/A' }}</p>
        </div>

        <!-- Cédula -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Cédula
          </label>
          <p class="text-gray-900">{{ empleado?.cedula || 'N/A' }}</p>
        </div>

        <!-- Tipo de Salario -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Tipo de Salario
          </label>
          <span
            :class="{
              'bg-blue-100 text-blue-800': empleado?.tipo_salario === 'mensual',
              'bg-green-100 text-green-800': empleado?.tipo_salario === 'diario',
              'bg-purple-100 text-purple-800': empleado?.tipo_salario === 'quincenal',
              'bg-yellow-100 text-yellow-800': empleado?.tipo_salario === 'semanal',
              'bg-gray-100 text-gray-800': !empleado?.tipo_salario
            }"
            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
          >
            {{ formatTipoSalario(empleado?.tipo_salario) }}
          </span>
        </div>

        <!-- Salario -->
        <div>
          <label class="block text-sm font-medium text-gray-500 mb-1">
            Salario
          </label>
          <p class="text-gray-900 font-medium">
            {{ empleado?.salario ? `$${empleado.salario.toLocaleString()}` : 'N/A' }}
          </p>
        </div>

        <!-- Fechas -->
        <div class="grid grid-cols-1 gap-4 pt-4 border-t">
          <div>
            <label class="block text-sm font-medium text-gray-500 mb-1">
              Fecha de Registro
            </label>
            <p class="text-gray-900">
              {{ empleado?.created_at ? new Date(empleado.created_at).toLocaleDateString() : 'N/A' }}
            </p>
          </div>
          <div v-if="empleado?.updated_at && empleado.updated_at !== empleado.created_at">
            <label class="block text-sm font-medium text-gray-500 mb-1">
              Última Actualización
            </label>
            <p class="text-gray-900">
              {{ new Date(empleado.updated_at).toLocaleDateString() }}
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
          @click="empleado && $emit('edit', empleado)"
          :disabled="!empleado"
          class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-50"
        >
          Editar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Empleado } from '@/types';

interface Props {
  isOpen: boolean;
  empleado?: Empleado | null;
}

defineProps<Props>();
defineEmits<{
  close: [];
  edit: [empleado: Empleado];
}>();

// Formatear tipo de salario
const formatTipoSalario = (tipo?: string) => {
  switch (tipo) {
    case 'mensual':
      return 'Mensual';
    case 'diario':
      return 'Diario';
    case 'quincenal':
      return 'Quincenal';
    case 'semanal':
      return 'Semanal';
    default:
      return 'N/A';
  }
};
</script>
