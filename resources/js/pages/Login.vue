<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-blue-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <!-- Header -->
      <div class="text-center slide-up">
        <div class="mx-auto h-16 w-16 bg-primary-500 rounded-full flex items-center justify-center mb-6 shadow-material-2">
          <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0l3-3m-3 3l-3-3m3 3V9a3 3 0 013-3h4a2 2 0 012 2v1M9 21h6m-6 0a2 2 0 01-2-2V5a2 2 0 012-2h6a2 2 0 012 2v2"></path>
          </svg>
        </div>
        <h2 class="text-3xl font-bold text-gray-900 mb-2">
          Iniciar Sesión
        </h2>
        <p class="text-gray-600">
          Sistema de Lavado de Autos
        </p>
      </div>

      <!-- Login Card -->
      <MaterialCard elevated class="slide-up" style="animation-delay: 0.1s;">
        <form @submit.prevent="login" class="space-y-6">
          <!-- Error Alert -->
          <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 flex items-center space-x-3">
            <ExclamationCircleIcon class="h-5 w-5 text-red-500 flex-shrink-0" />
            <p class="text-red-700 text-sm">{{ error }}</p>
          </div>

          <!-- Email Input -->
          <MaterialInput
            v-model="form.email"
            type="email"
            label="Correo Electrónico"
            placeholder="tu@email.com"
            :prefix-icon="AtSymbolIcon"
            autocomplete="email"
            required
          />

          <!-- Password Input -->
          <MaterialInput
            v-model="form.password"
            type="password"
            label="Contraseña"
            placeholder="••••••••"
            :prefix-icon="LockClosedIcon"
            autocomplete="current-password"
            required
          />

          <!-- Login Button -->
          <MaterialButton
            :text="loading ? 'Iniciando sesión...' : 'Iniciar Sesión'"
            :loading="loading"
            :disabled="!form.email || !form.password"
            full-width
            size="large"
            @click="login"
          />
        </form>

        <!-- Demo Credentials -->
        <div class="mt-8 pt-6 border-t border-gray-200">
          <div class="text-center">
            <p class="text-sm text-gray-600 mb-3">Credenciales de demostración:</p>
            <div class="bg-gray-50 rounded-lg p-4 space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Email:</span>
                <span class="text-sm text-gray-900 font-mono">admin@lavado.com</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Contraseña:</span>
                <span class="text-sm text-gray-900 font-mono">password123</span>
              </div>
            </div>
          </div>
        </div>
      </MaterialCard>

      <!-- Footer -->
      <div class="text-center text-sm text-gray-500 slide-up" style="animation-delay: 0.2s;">
        <p>© 2025 Sistema de Lavado de Autos. Todos los derechos reservados.</p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import MaterialCard from '../components/MaterialCard.vue'
import MaterialInput from '../components/MaterialInput.vue'
import MaterialButton from '../components/MaterialButton.vue'
import { AtSymbolIcon, LockClosedIcon, ExclamationCircleIcon } from '@heroicons/vue/24/outline'

export default {
  name: 'Login',
  components: {
    MaterialCard,
    MaterialInput,
    MaterialButton,
    AtSymbolIcon,
    LockClosedIcon,
    ExclamationCircleIcon
  },
  setup() {
    const router = useRouter()
    const loading = ref(false)
    const error = ref('')
    
    const form = ref({
      email: 'admin@lavado.com',
      password: 'password123'
    })

    const login = async () => {
      loading.value = true
      error.value = ''
      
      try {
        console.log('Attempting login with:', form.value.email)
        
        // First, get CSRF cookie
        await axios.get('/sanctum/csrf-cookie').catch(() => {
          // If sanctum route doesn't exist, continue anyway
        })
        
        const response = await axios.post('/login', {
          email: form.value.email,
          password: form.value.password
        }, {
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        })
        
        console.log('Login response:', response.data)
        
        if (response.data.success) {
          console.log('Login successful, redirecting to dashboard')
          await router.push('/dashboard')
        } else {
          error.value = response.data.message || 'Error en el login'
        }
        
      } catch (err) {
        console.error('Login error:', err)
        
        if (err.response?.data?.message) {
          error.value = err.response.data.message
        } else if (err.response?.data?.errors) {
          error.value = Object.values(err.response.data.errors).flat().join(', ')
        } else {
          error.value = 'Error al conectar con el servidor'
        }
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      login,
      loading,
      error
    }
  }
}
</script>
