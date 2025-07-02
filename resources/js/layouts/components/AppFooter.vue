<template>
  <footer class="bg-white border-t border-gray-200 px-6 py-4">
    <div class="flex flex-col sm:flex-row justify-between items-center space-y-2 sm:space-y-0">
      <!-- Copyright Info -->
      <div class="text-sm text-gray-600">
        © {{ currentYear }} Sistema Lavado de Autos. Todos los derechos reservados.
      </div>
      
      <!-- Footer Links -->
      <div class="flex items-center space-x-4 text-sm">
        <a 
          href="#" 
          class="text-gray-600 hover:text-blue-600 transition-colors duration-200"
          @click.prevent="showHelp"
        >
          Ayuda
        </a>
        <span class="text-gray-300">|</span>
        <a 
          href="#" 
          class="text-gray-600 hover:text-blue-600 transition-colors duration-200"
          @click.prevent="showPrivacy"
        >
          Privacidad
        </a>
        <span class="text-gray-300">|</span>
        <span class="text-gray-600">
          Versión {{ appVersion }}
        </span>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'

// Computed properties
const currentYear = computed(() => new Date().getFullYear())
const appVersion = computed(() => '1.0.0') // Esto podría venir de un store o config

// Methods
const showHelp = () => {
  // Aquí podrías abrir un modal de ayuda o redirigir a documentación
  console.log('Mostrar ayuda')
}

const showPrivacy = () => {
  // Aquí podrías abrir un modal de política de privacidad
  console.log('Mostrar política de privacidad')
}
</script>

<style scoped>
/* Estilos específicos para el footer si son necesarios */
</style>
