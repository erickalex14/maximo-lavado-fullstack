<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="flex justify-between items-center p-6 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">
          Detalles del Lavado
        </h2>
        <button
          @click="closeModal"
          class="text-gray-500 hover:text-gray-700"
        >
          <XMarkIcon class="h-6 w-6" />
        </button>
      </div>

      <!-- Content -->
      <div v-if="lavado" class="p-6">
        <!-- Información principal -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <!-- Información del lavado -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center gap-2">
              <TruckIcon class="h-5 w-5 text-blue-600" />
              Información del Lavado
            </h3>
            <div class="space-y-3">
              <div>
                <label class="text-sm font-medium text-gray-600">Fecha:</label>
                <p class="text-gray-900">{{ formatDate(lavado.fecha) }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-600">Tipo de Lavado:</label>
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ml-2"
                      :class="getTipoLavadoClass(lavado.tipo_lavado)">
                  {{ formatTipoLavado(lavado.tipo_lavado) }}
                </span>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-600">Precio:</label>
                <p class="text-gray-900 font-semibold text-lg">${{ lavado.precio.toFixed(2) }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-600">Pulverizado:</label>
                <div class="flex items-center gap-2">
                  <span v-if="lavado.pulverizado" class="text-green-600 flex items-center gap-1">
                    <CheckIcon class="h-4 w-4" />
                    Sí
                  </span>
                  <span v-else class="text-gray-500 flex items-center gap-1">
                    <XMarkIcon class="h-4 w-4" />
                    No
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Información del empleado -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center gap-2">
              <UserIcon class="h-5 w-5 text-green-600" />
              Empleado Asignado
            </h3>
            <div v-if="lavado.empleado" class="space-y-3">
              <div>
                <label class="text-sm font-medium text-gray-600">Nombre:</label>
                <p class="text-gray-900">{{ lavado.empleado.nombres }} {{ lavado.empleado.apellidos }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-600">Cédula:</label>
                <p class="text-gray-900">{{ lavado.empleado.cedula }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-600">Teléfono:</label>
                <p class="text-gray-900">{{ lavado.empleado.telefono }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-600">Tipo de Salario:</label>
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                      :class="getTipoSalarioClass(lavado.empleado.tipo_salario)">
                  {{ formatTipoSalario(lavado.empleado.tipo_salario) }}
                </span>
              </div>
            </div>
            <div v-else class="text-gray-500">
              No hay información del empleado
            </div>
          </div>
        </div>

        <!-- Información del vehículo y cliente -->
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center gap-2">
            <TruckIcon class="h-5 w-5 text-purple-600" />
            Vehículo y Cliente
          </h3>
          <div v-if="lavado.vehiculo" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Información del vehículo -->
            <div>
              <h4 class="text-md font-medium text-gray-800 mb-3">Vehículo</h4>
              <div class="space-y-2">
                <div>
                  <label class="text-sm font-medium text-gray-600">Tipo:</label>
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ml-2"
                        :class="getTipoVehiculoClass(lavado.vehiculo.tipo)">
                    {{ formatTipoVehiculo(lavado.vehiculo.tipo) }}
                  </span>
                </div>
                <div v-if="lavado.vehiculo.matricula">
                  <label class="text-sm font-medium text-gray-600">Matrícula:</label>
                  <p class="text-gray-900">{{ lavado.vehiculo.matricula }}</p>
                </div>
                <div v-if="lavado.vehiculo.descripcion">
                  <label class="text-sm font-medium text-gray-600">Descripción:</label>
                  <p class="text-gray-900">{{ lavado.vehiculo.descripcion }}</p>
                </div>
              </div>
            </div>

            <!-- Información del cliente -->
            <div v-if="lavado.vehiculo.cliente">
              <h4 class="text-md font-medium text-gray-800 mb-3">Cliente</h4>
              <div class="space-y-2">
                <div>
                  <label class="text-sm font-medium text-gray-600">Nombre:</label>
                  <p class="text-gray-900">{{ lavado.vehiculo.cliente.nombre }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-600">Cédula:</label>
                  <p class="text-gray-900">{{ lavado.vehiculo.cliente.cedula }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-600">Teléfono:</label>
                  <p class="text-gray-900">{{ lavado.vehiculo.cliente.telefono }}</p>
                </div>
                <div v-if="lavado.vehiculo.cliente.email">
                  <label class="text-sm font-medium text-gray-600">Email:</label>
                  <p class="text-gray-900">{{ lavado.vehiculo.cliente.email }}</p>
                </div>
                <div v-if="lavado.vehiculo.cliente.direccion">
                  <label class="text-sm font-medium text-gray-600">Dirección:</label>
                  <p class="text-gray-900">{{ lavado.vehiculo.cliente.direccion }}</p>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-gray-500">
            No hay información del vehículo
          </div>
        </div>

        <!-- Información del sistema -->
        <div class="bg-gray-50 p-4 rounded-lg">
          <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center gap-2">
            <InformationCircleIcon class="h-5 w-5 text-gray-600" />
            Información del Sistema
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
              <label class="font-medium text-gray-600">ID del Lavado:</label>
              <p class="text-gray-900">{{ lavado.lavado_id }}</p>
            </div>
            <div v-if="lavado.created_at">
              <label class="font-medium text-gray-600">Fecha de Registro:</label>
              <p class="text-gray-900">{{ formatDateTime(lavado.created_at) }}</p>
            </div>
            <div v-if="lavado.updated_at && lavado.updated_at !== lavado.created_at">
              <label class="font-medium text-gray-600">Última Modificación:</label>
              <p class="text-gray-900">{{ formatDateTime(lavado.updated_at) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer con acciones -->
      <div class="flex justify-end gap-3 p-6 border-t border-gray-200">
        <button
          @click="closeModal"
          class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
        >
          Cerrar
        </button>
        <button
          @click="editLavado"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center gap-2"
        >
          <PencilIcon class="h-4 w-4" />
          Editar
        </button>
        <button
          @click="deleteLavado"
          class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 flex items-center gap-2"
        >
          <TrashIcon class="h-4 w-4" />
          Eliminar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import type { Lavado } from '@/types';
import {
  XMarkIcon,
  TruckIcon,
  UserIcon,
  InformationCircleIcon,
  PencilIcon,
  TrashIcon,
  CheckIcon,
  XMarkIcon as XMarkIconSolid
} from '@heroicons/vue/24/outline';

// Props
interface Props {
  lavado: Lavado | null;
}

const props = defineProps<Props>();

// Emits
const emit = defineEmits<{
  close: [];
  edit: [lavado: Lavado];
  delete: [lavado: Lavado];
}>();

// Métodos
const closeModal = () => {
  emit('close');
};

const editLavado = () => {
  if (props.lavado) {
    emit('edit', props.lavado);
  }
};

const deleteLavado = () => {
  if (props.lavado) {
    emit('delete', props.lavado);
  }
};

// Formatters
const formatDate = (date: string) => {
  return format(new Date(date), 'dd/MM/yyyy', { locale: es });
};

const formatDateTime = (date: string) => {
  return format(new Date(date), 'dd/MM/yyyy HH:mm', { locale: es });
};

const formatTipoLavado = (tipo: string) => {
  const tipos = {
    'completo': 'Completo',
    'solo_fuera': 'Solo Por Fuera',
    'solo_por_dentro': 'Solo Por Dentro'
  };
  return tipos[tipo as keyof typeof tipos] || tipo;
};

const getTipoLavadoClass = (tipo: string) => {
  const classes = {
    'completo': 'bg-green-100 text-green-800',
    'solo_fuera': 'bg-blue-100 text-blue-800',
    'solo_por_dentro': 'bg-yellow-100 text-yellow-800'
  };
  return classes[tipo as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};

const formatTipoSalario = (tipo: string) => {
  const tipos = {
    'mensual': 'Mensual',
    'diario': 'Diario',
    'quincenal': 'Quincenal',
    'semanal': 'Semanal'
  };
  return tipos[tipo as keyof typeof tipos] || tipo;
};

const getTipoSalarioClass = (tipo: string) => {
  const classes = {
    'mensual': 'bg-blue-100 text-blue-800',
    'diario': 'bg-green-100 text-green-800',
    'quincenal': 'bg-purple-100 text-purple-800',
    'semanal': 'bg-yellow-100 text-yellow-800'
  };
  return classes[tipo as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};

const formatTipoVehiculo = (tipo: string) => {
  const tipos = {
    'moto': 'Moto',
    'camioneta': 'Camioneta',
    'auto_pequeno': 'Auto Pequeño',
    'auto_mediano': 'Auto Mediano'
  };
  return tipos[tipo as keyof typeof tipos] || tipo;
};

const getTipoVehiculoClass = (tipo: string) => {
  const classes = {
    'moto': 'bg-green-100 text-green-800',
    'camioneta': 'bg-yellow-100 text-yellow-800',
    'auto_pequeno': 'bg-blue-100 text-blue-800',
    'auto_mediano': 'bg-red-100 text-red-800'
  };
  return classes[tipo as keyof typeof classes] || 'bg-gray-100 text-gray-800';
};
</script>
