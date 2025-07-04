<template>
  <div v-if="visible" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
      <div class="px-6 py-4 border-b">
        <h3 class="text-lg font-semibold text-gray-900">
          Cambiar Contraseña
        </h3>
        <p class="text-sm text-gray-600 mt-1">
          Usuario: {{ user?.name }}
        </p>
      </div>

      <form @submit.prevent="handleSubmit" class="px-6 py-4 space-y-4">
        <!-- Contraseña actual (para actualización propia) -->
        <div v-if="isOwnPassword">
          <label class="form-label">
            Contraseña Actual *
          </label>
          <div class="relative">
            <input
              v-model="form.current_password"
              :type="showCurrentPassword ? 'text' : 'password'"
              class="form-input pr-10"
              :class="{ error: errors.current_password }"
              placeholder="Ingrese su contraseña actual"
              required
            />
            <button
              type="button"
              @click="showCurrentPassword = !showCurrentPassword"
              class="absolute inset-y-0 right-0 pr-3 flex items-center"
            >
              <svg v-if="showCurrentPassword" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
              </svg>
              <svg v-else class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </button>
          </div>
          <div v-if="errors.current_password" class="error-message">
            {{ errors.current_password }}
          </div>
        </div>

        <!-- Nueva contraseña -->
        <div>
          <label class="form-label">
            {{ isOwnPassword ? 'Nueva Contraseña' : 'Contraseña' }} *
          </label>
          <div class="relative">
            <input
              v-model="form.new_password"
              :type="showNewPassword ? 'text' : 'password'"
              class="form-input pr-10"
              :class="{ error: errors.new_password }"
              placeholder="Mínimo 8 caracteres"
              required
            />
            <button
              type="button"
              @click="showNewPassword = !showNewPassword"
              class="absolute inset-y-0 right-0 pr-3 flex items-center"
            >
              <svg v-if="showNewPassword" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
              </svg>
              <svg v-else class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </button>
          </div>
          <div v-if="errors.new_password" class="error-message">
            {{ errors.new_password }}
          </div>
        </div>

        <!-- Confirmar nueva contraseña -->
        <div>
          <label class="form-label">
            Confirmar {{ isOwnPassword ? 'Nueva' : '' }} Contraseña *
          </label>
          <div class="relative">
            <input
              v-model="form.new_password_confirmation"
              :type="showConfirmPassword ? 'text' : 'password'"
              class="form-input pr-10"
              :class="{ error: errors.new_password_confirmation }"
              placeholder="Confirme la contraseña"
              required
            />
            <button
              type="button"
              @click="showConfirmPassword = !showConfirmPassword"
              class="absolute inset-y-0 right-0 pr-3 flex items-center"
            >
              <svg v-if="showConfirmPassword" class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
              </svg>
              <svg v-else class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </button>
          </div>
          <div v-if="errors.new_password_confirmation" class="error-message">
            {{ errors.new_password_confirmation }}
          </div>
        </div>

        <!-- Información adicional -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
          <div class="flex">
            <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-blue-800">Requisitos de contraseña</h3>
              <div class="mt-2 text-sm text-blue-700">
                <ul class="list-disc pl-5 space-y-1">
                  <li>Mínimo 8 caracteres</li>
                  <li>Se recomienda incluir letras, números y símbolos</li>
                </ul>
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
          Cancelar
        </button>
        
        <button
          type="submit"
          @click="handleSubmit"
          :disabled="loading"
          class="btn btn-primary"
        >
          <svg v-if="loading" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Cambiar Contraseña
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useUserStore } from '@/stores/user';
import { userService } from '@/services/user.service';
import type { User, UpdatePasswordForm, ResetPasswordForm } from '@/types';

// Props
interface Props {
  visible: boolean;
  user?: User | null;
  isOwnPassword?: boolean; // Si es true, requiere contraseña actual
}

const props = withDefaults(defineProps<Props>(), {
  user: null,
  isOwnPassword: false // Por defecto es reset de admin
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
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const form = ref<UpdatePasswordForm | ResetPasswordForm>({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
});

const errors = ref<Record<string, string>>({});

// Computed
const isVisible = computed({
  get: () => props.visible,
  set: (value) => emit('update:visible', value)
});

// Methods
function resetForm() {
  form.value = {
    current_password: '',
    new_password: '',
    new_password_confirmation: ''
  };
  errors.value = {};
  showCurrentPassword.value = false;
  showNewPassword.value = false;
  showConfirmPassword.value = false;
}

function validateForm(): boolean {
  errors.value = {};

  if (props.isOwnPassword && !form.value.current_password?.trim()) {
    errors.value.current_password = 'La contraseña actual es requerida';
  }

  if (!form.value.new_password?.trim()) {
    errors.value.new_password = 'La nueva contraseña es requerida';
  } else if (form.value.new_password.length < 8) {
    errors.value.new_password = 'La nueva contraseña debe tener al menos 8 caracteres';
  }

  if (form.value.new_password !== form.value.new_password_confirmation) {
    errors.value.new_password_confirmation = 'Las contraseñas no coinciden';
  }

  return Object.keys(errors.value).length === 0;
}

async function handleSubmit() {
  if (!validateForm() || !props.user) return;

  try {
    loading.value = true;

    if (props.isOwnPassword) {
      // Actualizar contraseña propia
      await userStore.updatePassword(props.user.id, form.value as UpdatePasswordForm);
    } else {
      // Reset de contraseña por admin
      const resetData: ResetPasswordForm = {
        new_password: form.value.new_password,
        new_password_confirmation: form.value.new_password_confirmation
      };
      await userStore.resetPassword(props.user.id, resetData);
    }

    emit('saved');
    handleClose();
  } catch (error: any) {
    console.error('Error al cambiar contraseña:', error);
    
    // Manejar errores de validación del servidor
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else if (error.response?.data?.message) {
      errors.value.general = error.response.data.message;
    }
  } finally {
    loading.value = false;
  }
}

function handleClose() {
  resetForm();
  emit('update:visible', false);
}

// Watchers
watch(() => props.visible, (newValue) => {
  if (newValue) {
    resetForm();
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

.error-message {
  color: #ef4444;
  font-size: 0.875rem;
  margin-top: 0.25rem;
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
