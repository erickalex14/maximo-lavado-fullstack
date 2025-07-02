<template>
  <AuthLayout>
    <template #header>
      <h3 class="auth-title">Bienvenido a Máximo Lavado</h3>
      <p class="auth-description">Ingresa tus credenciales para acceder al sistema</p>
    </template>

    <!-- Login Form -->
    <BaseForm
      ref="loginForm"
      @submit="handleLogin"
      :loading="isLoading"
      class="login-form"
    >
      <!-- Email Field -->
      <div class="form-group">
        <BaseInput
          v-model="form.email"
          type="email"
          label="Correo Electrónico"
          placeholder="tu@email.com"
          :error="errors.email"
          :disabled="isLoading"
          required
          autofocus
        >
          <template #icon>
            <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
              <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
              <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
            </svg>
          </template>
        </BaseInput>
      </div>

      <!-- Password Field -->
      <div class="form-group">
        <BaseInput
          v-model="form.password"
          :type="showPassword ? 'text' : 'password'"
          label="Contraseña"
          placeholder="Tu contraseña"
          :error="errors.password"
          :disabled="isLoading"
          required
        >
          <template #icon>
            <svg class="input-icon" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
            </svg>
          </template>
          <template #suffix>
            <button
              type="button"
              @click="togglePasswordVisibility"
              class="password-toggle"
              :title="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'"
            >
              <svg v-if="showPassword" class="toggle-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd" />
                <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z" />
              </svg>
              <svg v-else class="toggle-icon" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
              </svg>
            </button>
          </template>
        </BaseInput>
      </div>

      <!-- Remember & Forgot Password -->
      <div class="form-options">
        <BaseCheckbox
          v-model="form.remember"
          label="Recordarme"
          :disabled="isLoading"
        />
        <router-link 
          to="/auth/forgot-password" 
          class="forgot-password-link"
          :class="{ disabled: isLoading }"
        >
          ¿Olvidaste tu contraseña?
        </router-link>
      </div>

      <!-- Login Button -->
      <BaseButton
        type="submit"
        variant="primary"
        size="lg"
        :loading="isLoading"
        :disabled="!isFormValid"
        class="login-button"
      >
        <template #icon>
          <svg class="button-icon" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd" />
          </svg>
        </template>
        {{ isLoading ? 'Iniciando sesión...' : 'Iniciar Sesión' }}
      </BaseButton>

      <!-- Error Message -->
      <BaseAlert
        v-if="loginError"
        variant="danger"
        :dismissible="true"
        @dismiss="loginError = null"
        class="login-error"
      >
        {{ loginError }}
      </BaseAlert>
    </BaseForm>

    <!-- Demo Credentials (only in development) -->
    <div v-if="isDevelopment" class="demo-credentials">
      <div class="demo-header">
        <svg class="demo-icon" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
        </svg>
        <span>Credenciales de prueba</span>
      </div>
      <div class="demo-accounts">
        <button
          v-for="account in demoAccounts"
          :key="account.role"
          @click="fillDemoCredentials(account)"
          class="demo-account"
          :disabled="isLoading"
        >
          <div class="demo-role">{{ account.role }}</div>
          <div class="demo-email">{{ account.email }}</div>
        </button>
      </div>
    </div>

    <template #footer>
      <div class="auth-footer-content">
        <p class="auth-footer-text">
          © {{ currentYear }} Máximo Lavado. Todos los derechos reservados.
        </p>
        <div class="auth-footer-links">
          <router-link to="/terms" class="footer-link">Términos</router-link>
          <span class="separator">•</span>
          <router-link to="/privacy" class="footer-link">Privacidad</router-link>
          <span class="separator">•</span>
          <router-link to="/support" class="footer-link">Soporte</router-link>
        </div>
      </div>
    </template>
  </AuthLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { BaseForm, BaseInput, BaseButton, BaseCheckbox, BaseAlert } from '@/components/common'

// Router and stores
const router = useRouter()
const authStore = useAuthStore()
const toastStore = useToastStore()

// Reactive data
const loginForm = ref(null)
const isLoading = ref(false)
const showPassword = ref(false)
const loginError = ref(null)

const form = ref({
  email: '',
  password: '',
  remember: false
})

const errors = ref({
  email: null,
  password: null
})

// Demo accounts for development
const demoAccounts = ref([
  {
    role: 'Administrador',
    email: 'admin@maximolavado.com',
    password: 'admin123'
  },
  {
    role: 'Empleado',
    email: 'empleado@maximolavado.com',
    password: 'empleado123'
  }
])

// Computed
const currentYear = computed(() => new Date().getFullYear())
const isDevelopment = computed(() => import.meta.env.DEV)

const isFormValid = computed(() => {
  return form.value.email && 
         form.value.password && 
         form.value.email.includes('@') &&
         form.value.password.length >= 6
})

// Methods
const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}

const fillDemoCredentials = (account) => {
  form.value.email = account.email
  form.value.password = account.password
  form.value.remember = true
  
  // Clear any existing errors
  clearErrors()
}

const clearErrors = () => {
  errors.value = {
    email: null,
    password: null
  }
  loginError.value = null
}

const validateForm = () => {
  clearErrors()
  let isValid = true

  // Email validation
  if (!form.value.email) {
    errors.value.email = 'El correo electrónico es requerido'
    isValid = false
  } else if (!form.value.email.includes('@')) {
    errors.value.email = 'El correo electrónico no es válido'
    isValid = false
  }

  // Password validation
  if (!form.value.password) {
    errors.value.password = 'La contraseña es requerida'
    isValid = false
  } else if (form.value.password.length < 6) {
    errors.value.password = 'La contraseña debe tener al menos 6 caracteres'
    isValid = false
  }

  return isValid
}

const handleLogin = async () => {
  if (!validateForm()) {
    return
  }

  isLoading.value = true
  loginError.value = null

  try {
    await authStore.login({
      email: form.value.email,
      password: form.value.password,
      remember: form.value.remember
    })

    // Success message
    toastStore.success('¡Bienvenido a Máximo Lavado!', 'Inicio de sesión exitoso')

    // Redirect to dashboard
    const redirectTo = router.currentRoute.value.query.redirect || '/dashboard'
    await router.push(redirectTo)

  } catch (error) {
    console.error('Login error:', error)
    
    // Handle different error types
    if (error.response?.status === 401) {
      loginError.value = 'Credenciales incorrectas. Verifica tu email y contraseña.'
    } else if (error.response?.status === 429) {
      loginError.value = 'Demasiados intentos de inicio de sesión. Intenta más tarde.'
    } else if (error.response?.status === 422) {
      // Validation errors from server
      const serverErrors = error.response.data.errors
      if (serverErrors.email) {
        errors.value.email = serverErrors.email[0]
      }
      if (serverErrors.password) {
        errors.value.password = serverErrors.password[0]
      }
    } else {
      loginError.value = 'Error al iniciar sesión. Verifica tu conexión e intenta nuevamente.'
    }
    
    toastStore.error(loginError.value || 'Error al iniciar sesión')
  } finally {
    isLoading.value = false
  }
}

// Auto-focus email field on mount
onMounted(() => {
  // Check if user is already authenticated
  if (authStore.isAuthenticated) {
    router.push('/dashboard')
  }
})
</script>

<style scoped>
.login-form {
  width: 100%;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group:last-of-type {
  margin-bottom: 1rem;
}

.input-icon {
  width: 1.25rem;
  height: 1.25rem;
  color: var(--color-text-400);
}

.password-toggle {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  background: none;
  border: none;
  color: var(--color-text-400);
  cursor: pointer;
  border-radius: 0.25rem;
  transition: color 0.2s ease;
}

.password-toggle:hover {
  color: var(--color-text-600);
}

.toggle-icon {
  width: 1.125rem;
  height: 1.125rem;
}

.form-options {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
  gap: 1rem;
}

.forgot-password-link {
  font-size: 0.875rem;
  color: var(--color-primary-600);
  text-decoration: none;
  transition: color 0.2s ease;
}

.forgot-password-link:hover {
  color: var(--color-primary-700);
  text-decoration: underline;
}

.forgot-password-link.disabled {
  color: var(--color-text-400);
  pointer-events: none;
}

.login-button {
  width: 100%;
  margin-bottom: 1rem;
}

.button-icon {
  width: 1.125rem;
  height: 1.125rem;
}

.login-error {
  margin-top: 1rem;
}

/* Demo Credentials */
.demo-credentials {
  margin-top: 2rem;
  padding: 1rem;
  background-color: var(--color-bg-50);
  border: 1px solid var(--color-border-200);
  border-radius: 0.5rem;
}

.demo-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--color-text-700);
}

.demo-icon {
  width: 1rem;
  height: 1rem;
  color: var(--color-info-500);
}

.demo-accounts {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.demo-account {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  padding: 0.75rem;
  background: white;
  border: 1px solid var(--color-border-200);
  border-radius: 0.375rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.demo-account:hover {
  border-color: var(--color-primary-300);
  background-color: var(--color-primary-25);
}

.demo-account:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.demo-role {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--color-text-900);
}

.demo-email {
  font-size: 0.75rem;
  color: var(--color-text-500);
  margin-top: 0.125rem;
}

/* Auth Footer */
.auth-footer-content {
  text-align: center;
}

.auth-footer-text {
  margin: 0 0 0.75rem 0;
  font-size: 0.875rem;
  color: var(--color-text-500);
}

.auth-footer-links {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-size: 0.75rem;
}

.footer-link {
  color: var(--color-text-400);
  text-decoration: none;
  transition: color 0.2s ease;
}

.footer-link:hover {
  color: var(--color-primary-600);
}

.separator {
  color: var(--color-text-300);
}

/* Mobile Responsive */
@media (max-width: 640px) {
  .form-options {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }
  
  .auth-footer-links {
    flex-direction: column;
    gap: 0.25rem;
  }
  
  .separator {
    display: none;
  }
}
</style>
