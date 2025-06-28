<template>
  <div class="min-h-screen bg-surface-50 flex items-center justify-center px-4">
    <div class="max-w-md w-full text-center">
      <!-- Ilustración 404 -->
      <div class="mb-8">
        <div class="text-8xl font-bold text-primary-500 mb-2">404</div>
        <div class="w-24 h-1 bg-primary-500 mx-auto rounded"></div>
      </div>

      <!-- Mensaje -->
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">
          Página no encontrada
        </h1>
        <p class="text-gray-600 mb-6">
          Lo sentimos, la página que está buscando no existe o ha sido movida.
        </p>
      </div>

      <!-- Acciones -->
      <div class="space-y-4">
        <router-link
          to="/dashboard"
          class="material-button-filled w-full block"
        >
          <HomeIcon class="w-5 h-5 mr-2" />
          Ir al Dashboard
        </router-link>
        
        <button
          @click="goBack"
          class="material-button-outlined w-full"
        >
          <ArrowLeftIcon class="w-5 h-5 mr-2" />
          Volver Atrás
        </button>
      </div>

      <!-- Links útiles -->
      <div class="mt-8 pt-8 border-t border-gray-200">
        <p class="text-sm text-gray-500 mb-4">Enlaces útiles:</p>
        <div class="flex flex-wrap justify-center gap-4 text-sm">
          <router-link 
            to="/dashboard/clientes" 
            class="text-primary-600 hover:text-primary-700"
          >
            Clientes
          </router-link>
          <router-link 
            to="/dashboard/empleados" 
            class="text-primary-600 hover:text-primary-700"
          >
            Empleados
          </router-link>
          <router-link 
            to="/dashboard/lavados" 
            class="text-primary-600 hover:text-primary-700"
          >
            Lavados
          </router-link>
          <router-link 
            to="/dashboard/reportes" 
            class="text-primary-600 hover:text-primary-700"
          >
            Reportes
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { HomeIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';

const router = useRouter();

const goBack = () => {
  router.go(-1);
};
</script>
