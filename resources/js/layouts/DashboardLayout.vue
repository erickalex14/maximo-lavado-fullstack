<template>
  <div class="min-h-screen bg-surface-50">
    <!-- Sidebar -->
    <AppSidebar 
      :is-open="sidebarOpen" 
      @toggle="toggleSidebar"
      @close="closeSidebar"
    />

    <!-- Main content area -->
    <div 
      class="transition-all duration-300 ease-in-out"
      :class="sidebarOpen ? 'lg:ml-64' : 'lg:ml-16'"
    >
      <!-- Top navigation bar -->
      <AppTopbar 
        @toggle-sidebar="toggleSidebar"
        :sidebar-open="sidebarOpen"
      />

      <!-- Page content -->
      <main class="p-4 lg:p-6">
        <router-view />
      </main>
    </div>

    <!-- Mobile sidebar overlay -->
    <div 
      v-if="sidebarOpen && isMobile"
      class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
      @click="closeSidebar"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import AppSidebar from '@/components/layout/AppSidebar.vue';
import AppTopbar from '@/components/layout/AppTopbar.vue';

// Estado del sidebar
const sidebarOpen = ref(true);
const isMobile = ref(false);

// Métodos para manejar el sidebar
const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const closeSidebar = () => {
  sidebarOpen.value = false;
};

// Detectar si es móvil
const checkMobile = () => {
  isMobile.value = window.innerWidth < 1024;
  if (isMobile.value) {
    sidebarOpen.value = false;
  }
};

// Lifecycle hooks
onMounted(() => {
  checkMobile();
  window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile);
});
</script>
