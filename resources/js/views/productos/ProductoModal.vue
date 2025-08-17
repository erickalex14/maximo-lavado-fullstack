<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
      <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ modo === 'edit' ? `Editar ${tipoLabel}` : `Nuevo ${tipoLabel}` }}
        </h3>
      </div>

      <form @submit.prevent="submitForm" class="px-6 py-4 space-y-4">
        <!-- Código (solo para automotriz) -->
        <div v-if="tipo === 'automotriz'">
          <label for="codigo" class="block text-sm font-medium text-gray-700 mb-1">
            Código *
          </label>
          <input
            id="codigo"
            v-model="form.codigo"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            placeholder="Código único del producto"
          />
          <span v-if="errors.codigo" class="text-red-500 text-sm">{{ errors.codigo[0] }}</span>
        </div>

        <!-- Nombre -->
        <div>
          <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
            Nombre *
          </label>
          <input
            id="nombre"
            v-model="form.nombre"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            placeholder="Nombre del producto"
          />
          <span v-if="errors.nombre" class="text-red-500 text-sm">{{ errors.nombre[0] }}</span>
        </div>

        <!-- Descripción -->
        <div>
          <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">
            Descripción
          </label>
          <textarea
            id="descripcion"
            v-model="form.descripcion"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            placeholder="Descripción del producto"
          ></textarea>
          <span v-if="errors.descripcion" class="text-red-500 text-sm">{{ errors.descripcion[0] }}</span>
        </div>

        <!-- Precio de Venta -->
        <div>
          <label for="precio_venta" class="block text-sm font-medium text-gray-700 mb-1">
            Precio de Venta *
          </label>
          <input
            id="precio_venta"
            v-model.number="form.precio_venta"
            type="number"
            step="0.01"
            min="0"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            placeholder="0.00"
          />
          <span v-if="errors.precio_venta" class="text-red-500 text-sm">{{ errors.precio_venta[0] }}</span>
        </div>

        <!-- Stock -->
        <div>
          <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">
            Stock
          </label>
          <input
            id="stock"
            v-model.number="form.stock"
            type="number"
            min="0"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            placeholder="0"
          />
          <span v-if="errors.stock" class="text-red-500 text-sm">{{ errors.stock[0] }}</span>
        </div>

        <!-- Estado -->
        <div>
          <div class="flex items-center">
            <input
              id="activo"
              v-model="form.activo"
              type="checkbox"
              class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label for="activo" class="ml-2 block text-sm text-gray-700">
              Producto activo
            </label>
          </div>
          <span v-if="errors.activo" class="text-red-500 text-sm">{{ errors.activo[0] }}</span>
        </div>

        <!-- Botones -->
        <div class="flex justify-end gap-3 pt-4">
          <button
            type="button"
            @click="$emit('close')"
            class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg disabled:opacity-50"
          >
            <span v-if="loading">Guardando...</span>
            <span v-else>{{ modo === 'edit' ? 'Actualizar' : 'Crear' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { useProductoStore } from '@/stores/producto';
import type { 
  ProductoAutomotriz, 
  ProductoDespensa, 
  CreateProductoAutomotrizRequest, 
  CreateProductoDespensaRequest 
} from '@/types';

interface Props {
  isOpen: boolean;
  tipo: 'automotriz' | 'despensa';
  producto?: ProductoAutomotriz | ProductoDespensa | null;
  modo: 'create' | 'edit';
}

const props = defineProps<Props>();
const emit = defineEmits<{
  close: [];
  success: [];
}>();

const productoStore = useProductoStore();

// Computed properties
const isEditing = computed(() => props.modo === 'edit'); // Conserva para lógica interna, pero UI usa props.modo
const tipoLabel = computed(() => props.tipo === 'automotriz' ? 'Producto Automotriz' : 'Producto de Despensa');

// ID del producto según el tipo
const productId = computed<number | null>(() => {
  const p: any = props.producto || null;
  if (!p) return null;
  if (props.tipo === 'automotriz') {
    return (
      p.producto_automotriz_id ??
      p.id ??
      p.producto_id ??
      p.productoAutomotrizId ??
      p.productoId ??
      null
    );
  }
  return (
    p.producto_despensa_id ??
    p.id ??
    p.producto_id ??
    p.productoDespensaId ??
    p.productoId ??
    null
  );
});

// Estado del formulario
const form = ref<(CreateProductoAutomotrizRequest | CreateProductoDespensaRequest) & { codigo?: string }>({
  codigo: '',
  nombre: '',
  descripcion: '',
  precio_venta: 0,
  stock: 0,
  activo: true
});

const loading = ref(false);
const errors = ref<Record<string, string[]>>({});

// Función para resetear el formulario
const resetForm = () => {
  form.value = {
    codigo: '',
    nombre: '',
    descripcion: '',
    precio_venta: 0,
    stock: 0,
    activo: true
  };
  errors.value = {};
};

// Llenar formulario cuando se edita
watch(() => props.producto, (producto) => {
  if (producto) {
    if (props.tipo === 'automotriz' && 'codigo' in producto) {
      form.value = {
        codigo: producto.codigo || '',
        nombre: producto.nombre || '',
        descripcion: producto.descripcion || '',
        precio_venta: producto.precio_venta || 0,
        stock: producto.stock || 0,
        activo: producto.activo ?? true
      };
    } else {
      form.value = {
        nombre: producto.nombre || '',
        descripcion: producto.descripcion || '',
        precio_venta: producto.precio_venta || 0,
        stock: producto.stock || 0,
        activo: producto.activo ?? true
      };
    }
  } else {
    resetForm();
  }
  errors.value = {};
}, { immediate: true });

// Resetear cuando cambia el tipo
watch(() => props.tipo, () => {
  resetForm();
});

// Enviar formulario
const submitForm = async () => {
  try {
    loading.value = true;
    errors.value = {};

    // Diagnóstico para confirmar qué camino sigue
    console.debug('ProductoModal.submitForm', {
      tipo: props.tipo,
      modo: props.modo,
      productId: productId.value,
      hasProducto: !!props.producto,
      productoKeys: props.producto ? Object.keys(props.producto as any) : []
    });


    if (props.tipo === 'automotriz') {
  const { codigo = '', nombre, descripcion, precio_venta, stock, activo } = form.value;
  const updateData = { codigo, nombre, descripcion, precio_venta, stock, activo };
      if (props.modo === 'edit' && productId.value) {
        await productoStore.updateProductoAutomotriz(productId.value, updateData);
      } else {
        await productoStore.createProductoAutomotriz(updateData);
      }
    } else {
      const { codigo, ...formData } = form.value as CreateProductoDespensaRequest & { codigo?: string };
      if (props.modo === 'edit' && productId.value) {
        await productoStore.updateProductoDespensa(productId.value, formData);
      } else {
        await productoStore.createProductoDespensa(formData);
      }
    }

    emit('success');
  } catch (error: any) {
    console.error('Error saving producto:', error);
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    }
  } finally {
    loading.value = false;
  }
};
</script>
