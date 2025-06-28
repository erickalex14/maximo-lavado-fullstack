<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-50 to-primary-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <!-- Logo del sistema -->
      <div class="text-center mb-8">
        <div class="mx-auto w-16 h-16 bg-primary-500 rounded-full flex items-center justify-center mb-4">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-800">
          Sistema de Lavado de Autos
        </h1>
        <p class="text-gray-600 mt-2">
          Panel de Administración
        </p>
      </div>

      <!-- Contenido del layout (formulario de login) -->
      <div class="bg-white rounded-lg shadow-xl p-6">
        <router-view />
      </div>

      <!-- Footer -->
      <div class="text-center mt-6 text-sm text-gray-500">
        © {{ new Date().getFullYear() }} Sistema de Lavado de Autos. Todos los derechos reservados.
      </div>
    </div>
  </div>
</template>

<script setup>
// Este layout es para las páginas de autenticación
</script>
