<template>
  <div v-if="isOpen && servicio" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 space-y-4">
      <div class="flex justify-between items-center mb-2">
        <h3 class="text-xl font-semibold text-gray-900">Detalle Servicio</h3>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">✕</button>
      </div>
      <div class="space-y-3 text-sm">
        <div>
          <h4 class="font-medium text-gray-700">Nombre</h4>
          <p class="text-gray-900">{{ servicio.nombre }}</p>
        </div>
        <div>
          <h4 class="font-medium text-gray-700">Descripción</h4>
          <p class="text-gray-600 whitespace-pre-line">{{ servicio.descripcion || 'Sin descripción' }}</p>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <h4 class="font-medium text-gray-700">Precio Base</h4>
            <p class="text-gray-900">${{ servicio.precio_base.toLocaleString('es-CO',{minimumFractionDigits:2}) }}</p>
          </div>
          <div>
            <h4 class="font-medium text-gray-700">Duración Est.</h4>
            <p class="text-gray-900">{{ servicio.duracion_estimada ?? 'N/D' }} min</p>
          </div>
        </div>
        <div>
          <h4 class="font-medium text-gray-700">Estado</h4>
            <span :class="servicio.activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">{{ servicio.activo?'Activo':'Inactivo' }}</span>
        </div>
        <div>
          <h4 class="font-medium text-gray-700 mb-1">Precios por Tipo de Vehículo</h4>
          <div v-if="servicio.precios?.length" class="flex flex-wrap gap-2">
            <span v-for="p in servicio.precios" :key="p.id" class="bg-gray-100 px-2 py-1 rounded text-xs font-mono">
              {{ p.tipo_vehiculo?.nombre || ('Tipo#'+p.tipo_vehiculo_id) }}: ${{ p.precio.toLocaleString('es-CO',{minimumFractionDigits:2}) }}
            </span>
          </div>
          <p v-else class="text-xs text-gray-400">Sin precios específicos</p>
        </div>
        <div class="text-xs text-gray-500 pt-2 border-t">Creado: {{ servicio.created_at }} | Actualizado: {{ servicio.updated_at }}</div>
      </div>
      <div class="flex justify-end gap-3 pt-4">
        <button @click="$emit('edit', servicio)" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Editar</button>
        <button @click="$emit('close')" class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-50">Cerrar</button>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import type { Servicio } from '@/types';
interface Props { isOpen: boolean; servicio: Servicio | null; }
defineProps<Props>();
defineEmits<{ close: []; edit: [servicio: Servicio] }>();
</script>
