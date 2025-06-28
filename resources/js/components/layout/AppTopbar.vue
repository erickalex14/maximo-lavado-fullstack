<template>
  <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-4 lg:px-6">
    <!-- Left side -->
    <div class="flex items-center space-x-4">
      <!-- Mobile menu button -->
      <button
        @click="$emit('toggle-sidebar')"
        class="p-2 rounded-lg hover:bg-gray-100 lg:hidden"
      >
        <Bars3Icon class="w-5 h-5 text-gray-600" />
      </button>

      <!-- Desktop sidebar toggle -->
      <button
        @click="$emit('toggle-sidebar')"
        class="hidden lg:flex p-2 rounded-lg hover:bg-gray-100"
      >
        <Bars3Icon v-if="!sidebarOpen" class="w-5 h-5 text-gray-600" />
        <XMarkIcon v-else class="w-5 h-5 text-gray-600" />
      </button>

      <!-- Breadcrumb -->
      <nav class="hidden md:flex items-center space-x-2 text-sm text-gray-500">
        <router-link 
          to="/dashboard" 
          class="hover:text-gray-700 transition-colors"
        >
          Dashboard
        </router-link>
        <ChevronRightIcon 
          v-if="currentPageTitle !== 'Dashboard'" 
          class="w-4 h-4" 
        />
        <span 
          v-if="currentPageTitle !== 'Dashboard'" 
          class="text-gray-900 font-medium"
        >
          {{ currentPageTitle }}
        </span>
      </nav>
    </div>

    <!-- Right side -->
    <div class="flex items-center space-x-4">
      <!-- Notifications -->
      <div class="relative">
        <button
          @click="toggleNotifications"
          class="p-2 rounded-lg hover:bg-gray-100 relative"
        >
          <BellIcon class="w-5 h-5 text-gray-600" />
          <span 
            v-if="unreadCount > 0"
            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"
          >
            {{ unreadCount > 9 ? '9+' : unreadCount }}
          </span>
        </button>

        <!-- Notifications dropdown -->
        <div
          v-if="showNotifications"
          v-click-outside="closeNotifications"
          class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
        >
          <div class="p-4 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900">Notificaciones</h3>
          </div>
          <div class="max-h-96 overflow-y-auto">
            <div 
              v-if="notifications.length === 0" 
              class="p-4 text-center text-gray-500 text-sm"
            >
              No hay notificaciones
            </div>
            <div 
              v-for="notification in notifications.slice(0, 5)" 
              :key="notification.id"
              class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer"
              @click="markAsRead(notification.id)"
            >
              <p class="text-sm text-gray-900">{{ notification.message }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ formatTime(notification.created_at) }}</p>
            </div>
          </div>
          <div class="p-3 border-t border-gray-200">
            <button 
              class="text-sm text-primary-600 hover:text-primary-700 font-medium"
              @click="viewAllNotifications"
            >
              Ver todas
            </button>
          </div>
        </div>
      </div>

      <!-- User menu -->
      <div class="relative">
        <button
          @click="toggleUserMenu"
          class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100"
        >
          <div class="w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center">
            <span class="text-white text-sm font-medium">
              {{ userInitials }}
            </span>
          </div>
          <div class="hidden md:block text-left">
            <p class="text-sm font-medium text-gray-900">{{ authStore.userName }}</p>
            <p class="text-xs text-gray-500">Administrador</p>
          </div>
          <ChevronDownIcon class="w-4 h-4 text-gray-500" />
        </button>

        <!-- User dropdown -->
        <div
          v-if="showUserMenu"
          v-click-outside="closeUserMenu"
          class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
        >
          <div class="p-2">
            <button
              @click="viewProfile"
              class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg flex items-center"
            >
              <UserIcon class="w-4 h-4 mr-3" />
              Mi Perfil
            </button>
            <button
              @click="viewSettings"
              class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg flex items-center"
            >
              <CogIcon class="w-4 h-4 mr-3" />
              Configuración
            </button>
            <hr class="my-2" />
            <button
              @click="logout"
              class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg flex items-center"
            >
              <ArrowRightOnRectangleIcon class="w-4 h-4 mr-3" />
              Cerrar Sesión
            </button>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useNotificationStore } from '@/stores/notification';
import {
  Bars3Icon,
  XMarkIcon,
  BellIcon,
  ChevronRightIcon,
  ChevronDownIcon,
  UserIcon,
  CogIcon,
  ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  sidebarOpen: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['toggle-sidebar']);

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const notificationStore = useNotificationStore();

// Estado local
const showNotifications = ref(false);
const showUserMenu = ref(false);
const notifications = ref([]); // Esto se cargaría desde la API

// Computed
const currentPageTitle = computed(() => {
  return route.meta?.title || 'Dashboard';
});

const userInitials = computed(() => {
  const name = authStore.userName;
  if (!name) return 'AD';
  
  const names = name.split(' ');
  if (names.length >= 2) {
    return (names[0][0] + names[1][0]).toUpperCase();
  }
  return name.substring(0, 2).toUpperCase();
});

const unreadCount = computed(() => {
  return notifications.value.filter(n => !n.read).length;
});

// Methods
const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value;
  showUserMenu.value = false;
};

const closeNotifications = () => {
  showNotifications.value = false;
};

const toggleUserMenu = () => {
  showUserMenu.value = !showUserMenu.value;
  showNotifications.value = false;
};

const closeUserMenu = () => {
  showUserMenu.value = false;
};

const markAsRead = (notificationId) => {
  // Implementar lógica para marcar notificación como leída
  const notification = notifications.value.find(n => n.id === notificationId);
  if (notification) {
    notification.read = true;
  }
};

const viewAllNotifications = () => {
  router.push({ name: 'Notifications' });
  closeNotifications();
};

const viewProfile = () => {
  router.push({ name: 'Profile' });
  closeUserMenu();
};

const viewSettings = () => {
  router.push({ name: 'Settings' });
  closeUserMenu();
};

const logout = async () => {
  try {
    await authStore.logout();
    notificationStore.success('Sesión cerrada correctamente');
    router.push({ name: 'Login' });
  } catch (error) {
    console.error('Error al cerrar sesión:', error);
    notificationStore.error('Error al cerrar sesión');
  } finally {
    closeUserMenu();
  }
};

const formatTime = (timestamp) => {
  return new Date(timestamp).toLocaleString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Directiva para detectar clics fuera del elemento
const vClickOutside = {
  beforeMount(el, binding) {
    el.clickOutsideEvent = function(event) {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value();
      }
    };
    document.addEventListener('click', el.clickOutsideEvent);
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent);
  }
};
</script>
