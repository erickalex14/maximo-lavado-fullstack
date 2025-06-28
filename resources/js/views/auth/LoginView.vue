<template>
  <div>
    <!-- Título -->
    <div class="text-center mb-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-2">
        Iniciar Sesión
      </h2>
      <p class="text-sm text-gray-600">
        Ingrese sus credenciales para acceder al sistema
      </p>
    </div>

    <!-- Formulario de login -->
    <form @submit.prevent="handleSubmit" class="space-y-4">
      <!-- Email -->
      <div class="form-group">
        <label for="email" class="form-label">
          Correo Electrónico
        </label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          :class="['material-input', { 'material-input-error': errors.email }]"
          placeholder="admin@ejemplo.com"
          required
          autocomplete="email"
        />
        <div v-if="errors.email" class="form-error">
          {{ errors.email }}
        </div>
      </div>

      <!-- Password -->
      <div class="form-group">
        <label for="password" class="form-label">
          Contraseña
        </label>
        <div class="relative">
          <input
            id="password"
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            :class="['material-input pr-10', { 'material-input-error': errors.password }]"
            placeholder="••••••••"
            required
            autocomplete="current-password"
          />
          <button
            type="button"
            @click="togglePassword"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
          >
            <EyeIcon v-if="!showPassword" class="h-5 w-5" />
            <EyeSlashIcon v-else class="h-5 w-5" />
          </button>
        </div>
        <div v-if="errors.password" class="form-error">
          {{ errors.password }}
        </div>
      </div>

      <!-- Remember me -->
      <div class="flex items-center">
        <input
          id="remember"
          v-model="form.remember"
          type="checkbox"
          class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
        />
        <label for="remember" class="ml-2 block text-sm text-gray-700">
          Recordar sesión
        </label>
      </div>

      <!-- Error general -->
      <div v-if="errors.general" class="form-error text-center">
        {{ errors.general }}
      </div>

      <!-- Botón de submit -->
      <button
        type="submit"
        :disabled="isLoading"
        class="w-full material-button-filled"
      >
        <svg 
          v-if="isLoading" 
          class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" 
          xmlns="http://www.w3.org/2000/svg" 
          fill="none" 
          viewBox="0 0 24 24"
        >
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        {{ isLoading ? 'Iniciando sesión...' : 'Iniciar Sesión' }}
      </button>
    </form>

    <!-- Links adicionales -->
    <div class="mt-6 text-center text-sm text-gray-600">
      <p>¿Problemas para acceder?</p>
      <p class="mt-1">Contacte al administrador del sistema</p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useNotificationStore } from '@/stores/notification';
import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline';

// Router y stores
const router = useRouter();
const authStore = useAuthStore();
const notificationStore = useNotificationStore();

// Estado reactivo
const isLoading = ref(false);
const showPassword = ref(false);

// Formulario
const form = reactive({
  email: '',
  password: '',
  remember: false
});

// Errores
const errors = reactive({
  email: '',
  password: '',
  general: ''
});

// Métodos
const togglePassword = () => {
  showPassword.value = !showPassword.value;
};

const clearErrors = () => {
  errors.email = '';
  errors.password = '';
  errors.general = '';
};

const validateForm = () => {
  clearErrors();
  let isValid = true;

  if (!form.email) {
    errors.email = 'El correo electrónico es requerido';
    isValid = false;
  } else if (!/\S+@\S+\.\S+/.test(form.email)) {
    errors.email = 'Ingrese un correo electrónico válido';
    isValid = false;
  }

  if (!form.password) {
    errors.password = 'La contraseña es requerida';
    isValid = false;
  } else if (form.password.length < 6) {
    errors.password = 'La contraseña debe tener al menos 6 caracteres';
    isValid = false;
  }

  return isValid;
};

const handleSubmit = async () => {
  if (!validateForm()) {
    return;
  }

  try {
    isLoading.value = true;
    clearErrors();

    const result = await authStore.login({
      email: form.email,
      password: form.password,
      remember: form.remember
    });

    if (result.success) {
      notificationStore.success('¡Bienvenido al sistema!');
      router.push({ name: 'Dashboard' });
    } else {
      errors.general = result.message || 'Error al iniciar sesión';
    }
  } catch (error) {
    console.error('Error en login:', error);
    errors.general = 'Error de conexión. Intente nuevamente.';
  } finally {
    isLoading.value = false;
  }
};

// Auto-focus en el email al montar el componente
import { onMounted } from 'vue';
onMounted(() => {
  document.getElementById('email')?.focus();
});
</script>
