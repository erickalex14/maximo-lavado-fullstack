<template>
  <div
    :class="[
      'fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0',
      isOpen ? 'translate-x-0' : '-translate-x-full lg:w-16'
    ]"
  >
    <!-- Logo y título -->
    <div class="flex items-center h-16 px-4 border-b border-gray-200">
      <div class="flex items-center space-x-3">
        <div class="w-8 h-8 bg-primary-500 rounded-lg flex items-center justify-center">
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
        </div>
        <div v-show="isOpen || !isMobile" class="lg:block">
          <h1 class="text-lg font-semibold text-gray-900">AutoWash</h1>
          <p class="text-xs text-gray-500">Sistema de Gestión</p>
        </div>
      </div>
    </div>

    <!-- Navegación -->
    <nav class="mt-4 px-2 space-y-1">
      <router-link
        v-for="item in navigationItems"
        :key="item.name"
        :to="item.to"
        :class="[
          'sidebar-link group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200',
          $route.name === item.name 
            ? 'bg-primary-100 text-primary-700 border-r-2 border-primary-500' 
            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'
        ]"
        @click="$emit('close')"
      >
        <component 
          :is="item.icon" 
          :class="[
            'flex-shrink-0 w-5 h-5',
            $route.name === item.name ? 'text-primary-500' : 'text-gray-400 group-hover:text-gray-600'
          ]"
        />
        <span 
          v-show="isOpen || !isMobile" 
          class="ml-3 lg:block"
        >
          {{ item.label }}
        </span>
      </router-link>
    </nav>

    <!-- Footer del sidebar -->
    <div class="absolute bottom-0 w-full p-4 border-t border-gray-200">
      <div v-show="isOpen || !isMobile" class="text-xs text-gray-500 text-center lg:block">
        © {{ new Date().getFullYear() }} AutoWash
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import {
  HomeIcon,
  UsersIcon,
  UserGroupIcon,
  TruckIcon,
  BeakerIcon,
  CubeIcon,
  BuildingOfficeIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
  ScaleIcon,
  DocumentChartBarIcon,
  DocumentTextIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: true
  }
});

const emit = defineEmits(['toggle', 'close']);

const route = useRoute();

// Detectar si es móvil
const isMobile = computed(() => {
  return window.innerWidth < 1024;
});

// Items de navegación
const navigationItems = [
  {
    name: 'Dashboard',
    label: 'Dashboard',
    to: { name: 'Dashboard' },
    icon: HomeIcon
  },
  {
    name: 'Empleados',
    label: 'Empleados',
    to: { name: 'Empleados' },
    icon: UsersIcon
  },
  {
    name: 'Clientes',
    label: 'Clientes',
    to: { name: 'Clientes' },
    icon: UserGroupIcon
  },
  {
    name: 'Vehiculos',
    label: 'Vehículos',
    to: { name: 'Vehiculos' },
    icon: TruckIcon
  },
  {
    name: 'Lavados',
    label: 'Lavados',
    to: { name: 'Lavados' },
    icon: BeakerIcon
  },
  {
    name: 'Productos',
    label: 'Productos',
    to: { name: 'Productos' },
    icon: CubeIcon
  },
  {
    name: 'Proveedores',
    label: 'Proveedores',
    to: { name: 'Proveedores' },
    icon: BuildingOfficeIcon
  },
  {
    name: 'Ingresos',
    label: 'Ingresos',
    to: { name: 'Ingresos' },
    icon: ArrowTrendingUpIcon
  },
  {
    name: 'Egresos',
    label: 'Egresos',
    to: { name: 'Egresos' },
    icon: ArrowTrendingDownIcon
  },
  {
    name: 'Balance',
    label: 'Balance',
    to: { name: 'Balance' },
    icon: ScaleIcon
  },
  {
    name: 'Reportes',
    label: 'Reportes',
    to: { name: 'Reportes' },
    icon: DocumentChartBarIcon
  },
  {
    name: 'Facturacion',
    label: 'Facturación',
    to: { name: 'Facturacion' },
    icon: DocumentTextIcon
  }
];
</script>
