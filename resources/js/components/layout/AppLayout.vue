<template>
  <div class="min-h-screen bg-surface-50">
    <!-- Sidebar -->
    <AppSidebar
      :is-open="sidebarOpen"
      @close="sidebarOpen = false"
    />

    <!-- Main content -->
    <div
      class="transition-all duration-300"
      :class="[
        sidebarOpen ? 'lg:ml-64' : 'lg:ml-16'
      ]"
    >
      <!-- Header -->
      <AppHeader @toggle-sidebar="sidebarOpen = !sidebarOpen" />

      <!-- Page content -->
      <main class="p-6">
        <router-view />
      </main>
    </div>

    <!-- Loading overlay -->
    <Transition name="fade">
      <div
        v-if="loading"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      >
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
          <LoadingSpinner />
          <span class="text-surface-700">Cargando...</span>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import AppSidebar from './AppSidebar.vue';
import AppHeader from './AppHeader.vue';
import LoadingSpinner from '@/components/common/LoadingSpinner.vue';

const authStore = useAuthStore();

// Estado local
const sidebarOpen = ref(false);
const loading = ref(false);

// Responsive behavior
onMounted(() => {
  const handleResize = () => {
    if (window.innerWidth >= 1024) {
      sidebarOpen.value = true;
    } else {
      sidebarOpen.value = false;
    }
  };

  handleResize();
  window.addEventListener('resize', handleResize);

  return () => {
    window.removeEventListener('resize', handleResize);
  };
});
</script>
