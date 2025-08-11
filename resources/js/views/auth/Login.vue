<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <!-- Header -->
      <div class="text-center">
        <div class="mx-auto h-20 w-20 rounded-xl flex items-center justify-center overflow-hidden bg-white shadow">
          <img src="/images/maximo-lavado-logo.png" alt="Máximo Lavado" class="object-contain h-full w-full" />
        </div>
        <h2 class="mt-6 text-3xl font-bold text-surface-900">
          Máximo Lavado
        </h2>
        <p class="mt-2 text-sm text-surface-600">
          Inicia sesión en tu cuenta
        </p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleLogin" class="mt-8 space-y-6">
        <div class="card">
          <div class="card-body space-y-4">
            <!-- Error message -->
            <div v-if="authStore.error" class="bg-red-50 border border-red-200 rounded-lg p-4">
              <div class="flex">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
                <div class="ml-3">
                  <p class="text-sm text-red-700">
                    {{ authStore.error }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Email -->
            <div>
              <label for="email" class="block text-sm font-medium text-surface-700 mb-1">
                Correo electrónico
              </label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                autocomplete="email"
                required
                class="input-base"
                :class="{ 'border-red-300': errors.email }"
                placeholder="tu@email.com"
              />
              <p v-if="errors.email" class="mt-1 text-sm text-red-600">
                {{ errors.email }}
              </p>
            </div>

            <!-- Password -->
            <div>
              <label for="password" class="block text-sm font-medium text-surface-700 mb-1">
                Contraseña
              </label>
              <div class="relative">
                <input
                  id="password"
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  autocomplete="current-password"
                  required
                  class="input-base pr-10"
                  :class="{ 'border-red-300': errors.password }"
                  placeholder="Tu contraseña"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center"
                >
                  <svg v-if="showPassword" class="h-5 w-5 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <svg v-else class="h-5 w-5 text-surface-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L17.121 17.121" />
                  </svg>
                </button>
              </div>
              <p v-if="errors.password" class="mt-1 text-sm text-red-600">
                {{ errors.password }}
              </p>
            </div>

            <!-- Remember me -->
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <input
                  id="remember"
                  v-model="form.remember"
                  type="checkbox"
                  class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-surface-300 rounded"
                />
                <label for="remember" class="ml-2 block text-sm text-surface-700">
                  Recordarme
                </label>
              </div>
              <div class="text-sm">
                <a href="#" class="font-medium text-primary-600 hover:text-primary-500">
                  ¿Olvidaste tu contraseña?
                </a>
              </div>
            </div>

            <!-- Submit button -->
            <button
              type="submit"
              :disabled="authStore.loading"
              class="w-full btn-primary flex items-center justify-center"
            >
              <LoadingSpinner v-if="authStore.loading" class="mr-2" />
              {{ authStore.loading ? 'Iniciando sesión...' : 'Iniciar sesión' }}
            </button>
          </div>
        </div>
      </form>

      <!-- Footer -->
      <div class="text-center">
        <p class="text-xs text-surface-500">
          © 2025 Máximo Lavado. Sistema de gestión integral.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';
import type { LoginCredentials } from '@/types';

const router = useRouter();
const authStore = useAuthStore();

// Form state
const form = reactive<LoginCredentials & { remember: boolean }>({
  email: '',
  password: '',
  remember: false,
});

const showPassword = ref(false);
const errors = ref<Record<string, string>>({});

// Methods
const validateForm = (): boolean => {
  errors.value = {};

  if (!form.email) {
    errors.value.email = 'El correo electrónico es requerido';
  } else if (!/\S+@\S+\.\S+/.test(form.email)) {
    errors.value.email = 'El correo electrónico no es válido';
  }

  if (!form.password) {
    errors.value.password = 'La contraseña es requerida';
  } else if (form.password.length < 6) {
    errors.value.password = 'La contraseña debe tener al menos 6 caracteres';
  }

  return Object.keys(errors.value).length === 0;
};

const handleLogin = async (): Promise<void> => {
  if (!validateForm()) {
    return;
  }

  authStore.clearError();

  const success = await authStore.login({
    email: form.email,
    password: form.password,
  });

  if (success) {
    router.push('/');
  }
};
</script>
