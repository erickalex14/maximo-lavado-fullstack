<template>
  <!-- Sidebar overlay (móvil) -->
  <Transition name="fade">
    <div
      v-if="isOpen && isMobile"
      class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
      @click="$emit('close')"
    ></div>
  </Transition>

  <!-- Sidebar -->
  <div
    class="fixed left-0 top-0 h-full bg-white shadow-lg border-r border-surface-200 z-50 transition-all duration-300"
    :class="[
      isOpen ? 'w-64' : 'w-16',
      isMobile && !isOpen ? '-translate-x-full' : 'translate-x-0'
    ]"
  >
    <!-- Logo -->
    <div class="p-4 border-b border-surface-200">
      <div class="flex items-center" :class="isOpen ? 'space-x-3' : 'justify-center'">
        <img
          src="/images/maximo-lavado-logo.png"
          alt="Máximo Lavado"
          class="select-none"
          :class="isOpen ? 'h-10 w-auto' : 'h-10 w-auto'"
          loading="lazy"
        />
        <Transition name="fade">
          <div v-if="isOpen" class="leading-tight">
            <h2 class="text-base font-bold text-surface-900">Máximo Lavado</h2>
            <p class="text-xs text-surface-500">Sistema de Gestión</p>
          </div>
        </Transition>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="p-4 space-y-2">
      <!-- Dashboard -->
      <SidebarLink
        to="/"
        :icon="dashboardIcon"
        label="Dashboard"
        :collapsed="!isOpen"
      />

      <!-- Gestión -->
      <SidebarSection title="Gestión" :collapsed="!isOpen">
        <SidebarLink
          to="/clientes"
          :icon="clientesIcon"
          label="Clientes"
          :collapsed="!isOpen"
        />
        <SidebarLink
          to="/empleados"
          :icon="empleadosIcon"
          label="Empleados"
          :collapsed="!isOpen"
        />
        <SidebarLink
          to="/vehiculos"
          :icon="vehiculosIcon"
          label="Vehículos"
          :collapsed="!isOpen"
        />
        <SidebarLink
          to="/lavados"
          :icon="lavadosIcon"
          label="Lavados"
          :collapsed="!isOpen"
        />
      </SidebarSection>

      <!-- Inventario -->
      <SidebarSection title="Inventario" :collapsed="!isOpen">
        <SidebarLink
          to="/productos"
          :icon="productosIcon"
          label="Productos"
          :collapsed="!isOpen"
        />
        <SidebarLink
          to="/proveedores"
          :icon="proveedoresIcon"
          label="Proveedores"
          :collapsed="!isOpen"
        />
      </SidebarSection>

      <!-- Finanzas -->
      <SidebarSection title="Finanzas" :collapsed="!isOpen">
        <SidebarLink
          to="/finanzas"
          :icon="balanceIcon"
          label="Finanzas"
          :collapsed="!isOpen"
        />
      </SidebarSection>

      <!-- Facturación -->
      <SidebarSection title="Facturación" :collapsed="!isOpen">
        <SidebarLink
          to="/facturas"
          :icon="facturasIcon"
          label="Facturas"
          :collapsed="!isOpen"
        />
        <SidebarLink
          to="/ventas"
          :icon="ventasIcon"
          label="Ventas"
          :collapsed="!isOpen"
        />
      </SidebarSection>

      <!-- Reportes -->
      <SidebarSection title="Reportes" :collapsed="!isOpen">
        <SidebarLink
          to="/reportes"
          :icon="reportesIcon"
          label="Reportes"
          :collapsed="!isOpen"
        />
      </SidebarSection>

      <!-- Administración -->
      <SidebarSection title="Administración" :collapsed="!isOpen">
        <SidebarLink
          to="/usuarios"
          :icon="usuariosIcon"
          label="Usuarios"
          :collapsed="!isOpen"
        />
      </SidebarSection>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import SidebarLink from './SidebarLink.vue';
import SidebarSection from './SidebarSection.vue';

// Props
interface Props {
  isOpen: boolean;
}

defineProps<Props>();

// Emits
defineEmits<{
  close: []
}>();

// Responsive behavior
const isMobile = ref(false);

const checkMobile = () => {
  isMobile.value = window.innerWidth < 1024;
};

onMounted(() => {
  checkMobile();
  window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile);
});

// Icons (using heroicons)
const dashboardIcon = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
</svg>`;

const clientesIcon = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
</svg>`;

const empleadosIcon = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
</svg>`;

const vehiculosIcon = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
</svg>`;

const lavadosIcon = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
</svg>`;

const productosIcon = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
</svg>`;

const proveedoresIcon = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
</svg>`;

const ingresosIcon = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
</svg>`;

const egresosIcon = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
</svg>`;

const balanceIcon = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
</svg>`;

const facturasIcon = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
</svg>`;

const ventasIcon = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
</svg>`;

const reportesIcon = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
</svg>`;

const usuariosIcon = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
</svg>`;
</script>
