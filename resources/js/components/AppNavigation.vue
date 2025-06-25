<template>
  <nav class="bg-white shadow-material-1 relative z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <!-- Logo and Brand -->
        <div class="flex items-center">
          <div class="flex-shrink-0 flex items-center space-x-3">
            <div class="h-10 w-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-material-1">
              <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
            </div>
            <div>
              <h1 class="text-lg font-bold text-gray-900">
                LavaAutos Pro
              </h1>
              <p class="text-xs text-gray-500 -mt-0.5">
                Sistema de Gestión
              </p>
            </div>
          </div>
        </div>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex md:items-center md:space-x-1">
          <router-link
            v-for="item in navigationItems"
            :key="item.name"
            :to="item.href"
            class="group flex items-center px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:bg-gray-50 hover:text-primary-600"
            :class="[
              $route.path === item.href
                ? 'bg-primary-50 text-primary-600 border border-primary-200'
                : 'text-gray-600 hover:text-gray-900'
            ]"
          >
            <component 
              :is="item.icon" 
              class="h-5 w-5 mr-2 transition-colors"
              :class="[
                $route.path === item.href 
                  ? 'text-primary-500' 
                  : 'text-gray-400 group-hover:text-gray-500'
              ]"
            />
            {{ item.name }}
          </router-link>
        </div>

        <!-- User Menu -->
        <div class="flex items-center space-x-4">
          <!-- Notifications -->
          <button class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">
            <BellIcon class="h-5 w-5" />
          </button>

          <!-- User Dropdown -->
          <div class="relative" v-click-outside="() => userMenuOpen = false">
            <button
              @click="userMenuOpen = !userMenuOpen"
              class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <div class="h-8 w-8 bg-gradient-to-br from-primary-400 to-primary-500 rounded-full flex items-center justify-center">
                <span class="text-white text-sm font-medium">
                  {{ userInitials }}
                </span>
              </div>
              <div class="hidden sm:block text-left">
                <p class="text-sm font-medium text-gray-900">{{ user?.name || 'Usuario' }}</p>
                <p class="text-xs text-gray-500">{{ user?.email || '' }}</p>
              </div>
              <ChevronDownIcon class="h-4 w-4 text-gray-400" />
            </button>

            <!-- Dropdown Menu -->
            <transition
              enter-active-class="transition ease-out duration-200"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <div
                v-show="userMenuOpen"
                class="origin-top-right absolute right-0 mt-2 w-56 rounded-lg bg-white shadow-material-2 ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
              >
                <div class="p-2">
                  <a
                    v-for="item in userMenuItems"
                    :key="item.name"
                    href="#"
                    @click="item.action"
                    class="flex items-center px-3 py-2 text-sm text-gray-700 rounded-md hover:bg-gray-50 hover:text-gray-900 transition-colors"
                  >
                    <component :is="item.icon" class="h-4 w-4 mr-3 text-gray-400" />
                    {{ item.name }}
                  </a>
                </div>
              </div>
            </transition>
          </div>
        </div>

        <!-- Mobile menu button -->
        <div class="md:hidden flex items-center">
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="p-2 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-colors"
          >
            <Bars3Icon v-if="!mobileMenuOpen" class="h-6 w-6" />
            <XMarkIcon v-else class="h-6 w-6" />
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div v-show="mobileMenuOpen" class="md:hidden border-t border-gray-200 bg-white shadow-material-1">
        <div class="px-4 py-3 space-y-1">
          <router-link
            v-for="item in navigationItems"
            :key="item.name"
            :to="item.href"
            @click="mobileMenuOpen = false"
            class="flex items-center px-3 py-3 rounded-lg text-base font-medium transition-colors"
            :class="[
              $route.path === item.href
                ? 'bg-primary-50 text-primary-600 border border-primary-200'
                : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
            ]"
          >
            <component :is="item.icon" class="h-5 w-5 mr-3" />
            {{ item.name }}
          </router-link>
        </div>
      </div>
    </transition>
  </nav>
</template>

<script>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import {
  HomeIcon,
  UserGroupIcon,
  TruckIcon,
  DocumentTextIcon,
  CurrencyDollarIcon,
  ShoppingCartIcon,
  ChartBarIcon,
  CogIcon,
  BellIcon,
  UserIcon,
  ArrowRightOnRectangleIcon,
  Bars3Icon,
  XMarkIcon,
  ChevronDownIcon
} from '@heroicons/vue/24/outline'

export default {
  name: 'AppNavigation',
  components: {
    HomeIcon,
    UserGroupIcon,
    TruckIcon,
    DocumentTextIcon,
    CurrencyDollarIcon,
    ShoppingCartIcon,
    ChartBarIcon,
    CogIcon,
    BellIcon,
    UserIcon,
    ArrowRightOnRectangleIcon,
    Bars3Icon,
    XMarkIcon,
    ChevronDownIcon
  },
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()
    const userMenuOpen = ref(false)
    const mobileMenuOpen = ref(false)

    const navigationItems = [
      { name: 'Dashboard', href: '/dashboard', icon: HomeIcon },
      { name: 'Clientes', href: '/clientes', icon: UserGroupIcon },
      { name: 'Vehículos', href: '/vehiculos', icon: TruckIcon },
      { name: 'Lavados', href: '/lavados', icon: DocumentTextIcon },
      { name: 'Facturación', href: '/facturas', icon: CurrencyDollarIcon },
      { name: 'Productos', href: '/productos', icon: ShoppingCartIcon },
      { name: 'Reportes', href: '/reportes', icon: ChartBarIcon },
      { name: 'Configuración', href: '/configuracion', icon: CogIcon }
    ]

    const user = computed(() => authStore.user)
    
    const userInitials = computed(() => {
      if (!user.value?.name) return 'U'
      return user.value.name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2)
    })

    const userMenuItems = [
      {
        name: 'Mi Perfil',
        icon: UserIcon,
        action: () => {
          userMenuOpen.value = false
          router.push('/perfil')
        }
      },
      {
        name: 'Configuración',
        icon: CogIcon,
        action: () => {
          userMenuOpen.value = false
          router.push('/configuracion')
        }
      },
      {
        name: 'Cerrar Sesión',
        icon: ArrowRightOnRectangleIcon,
        action: async () => {
          userMenuOpen.value = false
          await authStore.logout()
          router.push('/login')
        }
      }
    ]

    return {
      navigationItems,
      userMenuItems,
      userMenuOpen,
      mobileMenuOpen,
      user,
      userInitials
    }
  },
  directives: {
    'click-outside': {
      mounted(el, binding) {
        el.clickOutsideEvent = function(event) {
          if (!(el === event.target || el.contains(event.target))) {
            binding.value()
          }
        }
        document.addEventListener('click', el.clickOutsideEvent)
      },
      unmounted(el) {
        document.removeEventListener('click', el.clickOutsideEvent)
      }
    }
  }
}
</script>
