<template>
  <div v-if="visible" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 max-h-screen overflow-y-auto">
      <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ modalTitle }}
        </h3>
      </div>

      <form @submit.prevent="handleSubmit" class="px-6 py-4 space-y-4">
        <!-- Nombre -->
        <div>
          <label class="form-label">
            Nombre Completo *
          </label>
          <input
            v-if="mode !== 'view'"
            v-model="form.name"
            type="text"
            class="form-input"
            :class="{ error: errors.name }"
            placeholder="Ingrese el nombre completo"
            required
          />
          <div v-else class="form-display-value">
            {{ form.name }}
          </div>
          <div v-if="errors.name" class="error-message">
            {{ errors.name }}
          </div>
        </div>

        <!-- Email -->
        <div>
          <label class="form-label">
            Correo Electrónico *
          </label>
          <input
            v-if="mode !== 'view'"
            v-model="form.email"
            type="email"
            class="form-input"
            :class="{ error: errors.email }"
            placeholder="usuario@ejemplo.com"
            required
          />
          <div v-else class="form-display-value">
            {{ form.email }}
          </div>
          <div v-if="errors.email" class="error-message">
            {{ errors.email }}
          </div>
        </div>

        <!-- Estado de verificación (solo en vista) -->
        <div v-if="mode === 'view'">
          <label class="form-label">
            Estado de Verificación
          </label>
          <div class="form-display-value">
            <span v-if="user?.email_verified_at" class="status-badge status-verified">
              <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              Verificado
            </span>
            <span v-else class="status-badge status-pending">
              <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
              Sin verificar
            </span>
          </div>
        </div>

        <!-- Contraseña (solo al crear) -->
        <div v-if="mode === 'create'">
          <label class="form-label">
            Contraseña *
          </label>
          <div class="relative">
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              class="form-input pr-10"
              :class="{ error: errors.password }"
              placeholder="Mínimo 8 caracteres"
              required
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute inset-y-0 right-0 pr-3 flex items-center"
            >
              <svg v-if="showPassword" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
              </svg>
              <svg v-else class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </button>
          </div>
          <div v-if="errors.password" class="error-message">
            {{ errors.password }}
          </div>
        </div>

        <!-- Confirmar contraseña (solo al crear) -->
        <div v-if="mode === 'create'">
          <label class="form-label">
            Confirmar Contraseña *
          </label>
          <div class="relative">
            <input
              v-model="form.password_confirmation"
              :type="showPasswordConfirm ? 'text' : 'password'"
              class="form-input pr-10"
              :class="{ error: errors.password_confirmation }"
              placeholder="Confirme la contraseña"
              required
            />
            <button
              type="button"
              @click="showPasswordConfirm = !showPasswordConfirm"
              class="absolute inset-y-0 right-0 pr-3 flex items-center"
            >
              <svg v-if="showPasswordConfirm" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
              </svg>
              <svg v-else class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </button>
          </div>
          <div v-if="errors.password_confirmation" class="error-message">
            {{ errors.password_confirmation }}
          </div>
        </div>

        <!-- Información de fechas (solo en vista) -->
        <div v-if="mode === 'view' && user">
          <div class="grid grid-cols-1 gap-4">
            <div>
              <label class="form-label">
                Fecha de Registro
              </label>
              <div class="form-display-value">
                {{ formatDateTime(user.created_at) }}
              </div>
            </div>
            
            <div v-if="user.updated_at !== user.created_at">
              <label class="form-label">
                Última Actualización
              </label>
              <div class="form-display-value">
                {{ formatDateTime(user.updated_at) }}
              </div>
            </div>

            <div v-if="user.email_verified_at">
              <label class="form-label">
                Email Verificado
              </label>
              <div class="form-display-value">
                {{ formatDateTime(user.email_verified_at) }}
              </div>
            </div>
          </div>
        </div>
      </form>

      <!-- Actions -->
      <div class="px-6 py-4 border-t bg-gray-50 flex justify-end space-x-3">
        <button
          type="button"
          @click="handleClose"
          class="btn btn-outline-secondary"
        >
          {{ mode === 'view' ? 'Cerrar' : 'Cancelar' }}
        </button>
        
        <button
          v-if="mode !== 'view'"
          type="submit"
          @click="handleSubmit"
          :disabled="loading"
          class="btn btn-primary"
        >
          <svg v-if="loading" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ mode === 'create' ? 'Crear Usuario' : 'Guardar Cambios' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useUserStore } from '@/stores/user';
import { userService } from '@/services/user.service';
import type { User, CreateUserForm, UpdateUserForm } from '@/types';

// Props
interface Props {
  visible: boolean;
  mode: 'create' | 'edit' | 'view';
  user?: User | null;
}

const props = withDefaults(defineProps<Props>(), {
  mode: 'create',
  user: null
});

// Emits
const emit = defineEmits<{
  'update:visible': [value: boolean];
  'saved': [];
}>();

// Store
const userStore = useUserStore();

// State
const loading = ref(false);
const showPassword = ref(false);
const showPasswordConfirm = ref(false);

const form = ref<CreateUserForm>({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
});

const errors = ref<Record<string, string>>({});

// Computed
const isVisible = computed({
  get: () => props.visible,
  set: (value) => emit('update:visible', value)
});

const modalTitle = computed(() => {
  switch (props.mode) {
    case 'create':
      return 'Nuevo Usuario';
    case 'edit':
      return 'Editar Usuario';
    case 'view':
      return 'Detalles del Usuario';
    default:
      return 'Usuario';
  }
});

// Methods
function resetForm() {
  form.value = {
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
  };
  errors.value = {};
  showPassword.value = false;
  showPasswordConfirm.value = false;
}

function loadUserData() {
  if (props.user) {
    form.value = {
      name: props.user.name,
      email: props.user.email,
      password: '',
      password_confirmation: ''
    };
  }
}

function validateForm(): boolean {
  const validationResult = userService.validateUser(form.value);
  errors.value = validationResult.errors;
  return validationResult.valid;
}

async function handleSubmit() {
  if (!validateForm()) return;

  try {
    loading.value = true;

    if (props.mode === 'create') {
      await userStore.createUser(form.value);
    } else if (props.mode === 'edit' && props.user) {
      const updateData: UpdateUserForm = {
        name: form.value.name,
        email: form.value.email
      };
      await userStore.updateUser(props.user.id, updateData);
    }

    emit('saved');
    handleClose();
  } catch (error: any) {
    console.error('Error al guardar usuario:', error);
    
    // Manejar errores de validación del servidor
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    }
  } finally {
    loading.value = false;
  }
}

function handleClose() {
  resetForm();
  emit('update:visible', false);
}

function formatDateTime(dateString: string): string {
  return new Date(dateString).toLocaleString('es-CO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

// Watchers
watch(() => props.visible, (newValue) => {
  if (newValue) {
    if (props.mode === 'edit' || props.mode === 'view') {
      loadUserData();
    } else {
      resetForm();
    }
  }
});

watch(() => props.user, () => {
  if (props.visible && (props.mode === 'edit' || props.mode === 'view')) {
    loadUserData();
  }
});
</script>

<style scoped>
.form-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  margin-bottom: 0.5rem;
}

.form-input {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  background-color: white;
  color: #111827;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-input:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
  border-color: #3b82f6;
}

.form-input.error {
  border-color: #ef4444;
}

.form-input.error:focus {
  box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.5);
  border-color: #ef4444;
}

.form-display-value {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  background-color: #f9fafb;
  color: #374151;
  min-height: 2.5rem;
  display: flex;
  align-items: center;
}

.error-message {
  color: #ef4444;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

/* Status badges */
.status-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.5rem;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  font-weight: 500;
}

.status-verified {
  background-color: #dbeafe;
  color: #1e40af;
}

.status-pending {
  background-color: #fef3c7;
  color: #92400e;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-weight: 500;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  border: none;
  cursor: pointer;
}

.btn-primary {
  background-color: #3b82f6;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background-color: #2563eb;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-outline-secondary {
  border: 1px solid #d1d5db;
  color: #374151;
  background-color: white;
}

.btn-outline-secondary:hover {
  background-color: #f9fafb;
}
</style>
