<template>
  <header class="bg-white shadow-sm border-b border-surface-200">
    <div class="px-6 py-4">
      <div class="flex items-center justify-between">
        <!-- Left side -->
        <div class="flex items-center space-x-4">
          <!-- Sidebar toggle -->
          <button
            @click="$emit('toggle-sidebar')"
            class="p-2 rounded-lg hover:bg-surface-100 transition-colors"
          >
            <svg class="w-5 h-5 text-surface-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <!-- Page title -->
          <h1 class="text-xl font-semibold text-surface-900">
            {{ pageTitle }}
          </h1>
        </div>

        <!-- Right side -->
        <div class="flex items-center space-x-4">
          <!-- Notifications -->
          <button class="p-2 rounded-lg hover:bg-surface-100 transition-colors relative">
            <svg class="w-5 h-5 text-surface-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M15 17h5l-5 5v-5zM4 19h1l10-10V4a4 4 0 00-8 0v6l-3 3v6z" />
            </svg>
            <!-- Notification badge -->
            <span class="absolute -top-1 -right-1 h-3 w-3 bg-red-500 rounded-full"></span>
          </button>

          <!-- User menu -->
          <div class="relative" ref="userMenuRef">
            <button
              @click="showUserMenu = !showUserMenu"
              class="flex items-center space-x-3 p-2 rounded-lg hover:bg-surface-100 transition-colors"
            >
              <!-- Avatar -->
              <div class="w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center">
                <span class="text-white text-sm font-medium">
                  {{ userInitials }}
                </span>
              </div>
              <!-- User info -->
              <div class="hidden sm:block text-left">
                <p class="text-sm font-medium text-surface-900">{{ authStore.userName }}</p>
                <p class="text-xs text-surface-500">{{ authStore.userEmail }}</p>
              </div>
              <!-- Chevron -->
              <svg class="w-4 h-4 text-surface-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- User dropdown -->
            <Transition name="fade">
              <div
                v-if="showUserMenu"
                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-surface-200 z-50"
              >
                <div class="py-1">
                  <a href="#" class="block px-4 py-2 text-sm text-surface-700 hover:bg-surface-50">
                    Mi Perfil
                  </a>
                  <a href="#" class="block px-4 py-2 text-sm text-surface-700 hover:bg-surface-50">
                    Configuración
                  </a>
                  <hr class="my-1 border-surface-200">
                  <button
                    @click="handleLogout"
                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                  >
                    Cerrar Sesión
                  </button>
                </div>
              </div>
            </Transition>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

// Emits
defineEmits<{
  'toggle-sidebar': []
}>();

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

// Estado local
const showUserMenu = ref(false);
const userMenuRef = ref<HTMLElement>();

// Computed
const pageTitle = computed(() => {
  return route.meta.title as string || 'Dashboard';
});

const userInitials = computed(() => {
  const name = authStore.userName;
  if (!name) return 'U';
  
  const parts = name.split(' ');
  if (parts.length === 1) {
    return parts[0].charAt(0).toUpperCase();
  }
  
  return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
});

// Methods
const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};

// Click outside to close menu
const handleClickOutside = (event: Event) => {
  if (userMenuRef.value && !userMenuRef.value.contains(event.target as Node)) {
    showUserMenu.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
