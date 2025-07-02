<template>
  <div class="app-layout">
    <!-- Sidebar -->
    <AppSidebar
      v-model:collapsed="sidebarCollapsed"
      :user="currentUser"
      :navigation="navigationItems"
      @navigate="handleNavigation"
    />
    
    <!-- Main Content Area -->
    <div class="main-content" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
      <!-- Top Header -->
      <AppHeader
        :user="currentUser"
        :sidebar-collapsed="sidebarCollapsed"
        :breadcrumbs="breadcrumbs"
        @toggle-sidebar="toggleSidebar"
        @logout="handleLogout"
        @profile="handleProfile"
      />
      
      <!-- Page Content -->
      <main class="page-content">
        <div class="content-container">
          <!-- Page Header with actions -->
          <div v-if="showPageHeader" class="page-header">
            <div class="page-title-section">
              <h1 v-if="pageTitle" class="page-title">{{ pageTitle }}</h1>
              <p v-if="pageDescription" class="page-description">{{ pageDescription }}</p>
            </div>
            
            <div v-if="$slots.actions" class="page-actions">
              <slot name="actions" />
            </div>
          </div>
          
          <!-- Alerts/Notifications -->
          <div v-if="alerts.length > 0" class="page-alerts">
            <BaseAlert
              v-for="alert in alerts"
              :key="alert.id"
              :variant="alert.variant"
              :title="alert.title"
              :dismissible="alert.dismissible"
              @dismiss="removeAlert(alert.id)"
            >
              {{ alert.message }}
            </BaseAlert>
          </div>
          
          <!-- Main Content Slot -->
          <div class="page-main">
            <slot />
          </div>
        </div>
      </main>
      
      <!-- Footer -->
      <AppFooter v-if="showFooter" />
    </div>
    
    <!-- Overlays -->
    <div v-if="sidebarCollapsed && isMobile" class="sidebar-overlay" @click="closeSidebar" />
    
    <!-- Global Loading -->
    <BaseLoading
      v-if="globalLoading"
      variant="spinner"
      text="Cargando..."
      overlay
    />
    
    <!-- Toast Notifications -->
    <AppToastContainer />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, provide } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useAppStore } from '@/stores/app'
import AppSidebar from './components/AppSidebar.vue'
import AppHeader from './components/AppHeader.vue'
import AppFooter from './components/AppFooter.vue'
import AppToastContainer from './components/AppToastContainer.vue'
import { BaseAlert, BaseLoading } from '@/components/common'

// Props
const props = defineProps({
  pageTitle: {
    type: String,
    default: ''
  },
  pageDescription: {
    type: String,
    default: ''
  },
  showPageHeader: {
    type: Boolean,
    default: true
  },
  showFooter: {
    type: Boolean,
    default: true
  },
  maxWidth: {
    type: String,
    default: '1200px'
  },
  padding: {
    type: String,
    default: 'default',
    validator: (value) => ['none', 'sm', 'default', 'lg'].includes(value)
  }
})

// Stores
const authStore = useAuthStore()
const appStore = useAppStore()
const router = useRouter()
const route = useRoute()

// Reactive data
const sidebarCollapsed = ref(false)
const isMobile = ref(false)

// Computed
const currentUser = computed(() => authStore.user)
const globalLoading = computed(() => appStore.loading)
const alerts = computed(() => appStore.alerts)

const breadcrumbs = computed(() => {
  const crumbs = []
  const matched = route.matched
  
  matched.forEach((match, index) => {
    if (match.meta?.breadcrumb) {
      crumbs.push({
        text: match.meta.breadcrumb,
        href: index === matched.length - 1 ? null : match.path,
        active: index === matched.length - 1
      })
    }
  })
  
  return crumbs
})

const navigationItems = computed(() => [
  {
    title: 'Dashboard',
    icon: 'dashboard',
    route: '/dashboard',
    active: route.path === '/dashboard'
  },
  {
    title: 'Lavados',
    icon: 'car-wash',
    route: '/lavados',
    active: route.path.startsWith('/lavados'),
    children: [
      { title: 'Lista de Lavados', route: '/lavados' },
      { title: 'Nuevo Lavado', route: '/lavados/nuevo' },
      { title: 'Historial', route: '/lavados/historial' }
    ]
  },
  {
    title: 'Clientes',
    icon: 'users',
    route: '/clientes',
    active: route.path.startsWith('/clientes'),
    children: [
      { title: 'Lista de Clientes', route: '/clientes' },
      { title: 'Nuevo Cliente', route: '/clientes/nuevo' }
    ]
  },
  {
    title: 'Vehículos',
    icon: 'car',
    route: '/vehiculos',
    active: route.path.startsWith('/vehiculos')
  },
  {
    title: 'Productos',
    icon: 'inventory',
    route: '/productos',
    active: route.path.startsWith('/productos'),
    children: [
      { title: 'Automotrices', route: '/productos/automotrices' },
      { title: 'Despensa', route: '/productos/despensa' }
    ]
  },
  {
    title: 'Ventas',
    icon: 'shopping-cart',
    route: '/ventas',
    active: route.path.startsWith('/ventas')
  },
  {
    title: 'Finanzas',
    icon: 'finance',
    route: '/finanzas',
    active: route.path.startsWith('/finanzas'),
    children: [
      { title: 'Ingresos', route: '/finanzas/ingresos' },
      { title: 'Egresos', route: '/finanzas/egresos' },
      { title: 'Balance', route: '/finanzas/balance' },
      { title: 'Gastos Generales', route: '/finanzas/gastos-generales' }
    ]
  },
  {
    title: 'Empleados',
    icon: 'team',
    route: '/empleados',
    active: route.path.startsWith('/empleados')
  },
  {
    title: 'Proveedores',
    icon: 'suppliers',
    route: '/proveedores',
    active: route.path.startsWith('/proveedores')
  },
  {
    title: 'Reportes',
    icon: 'reports',
    route: '/reportes',
    active: route.path.startsWith('/reportes'),
    children: [
      { title: 'Reportes de Ventas', route: '/reportes/ventas' },
      { title: 'Reportes Financieros', route: '/reportes/financieros' },
      { title: 'Reportes de Clientes', route: '/reportes/clientes' }
    ]
  },
  {
    title: 'Configuración',
    icon: 'settings',
    route: '/configuracion',
    active: route.path.startsWith('/configuracion')
  }
])

// Methods
const toggleSidebar = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

const closeSidebar = () => {
  if (isMobile.value) {
    sidebarCollapsed.value = true
  }
}

const handleNavigation = (item) => {
  router.push(item.route)
  
  // Close sidebar on mobile after navigation
  if (isMobile.value) {
    sidebarCollapsed.value = true
  }
}

const handleLogout = async () => {
  try {
    await authStore.logout()
    router.push('/auth/login')
  } catch (error) {
    console.error('Error during logout:', error)
  }
}

const handleProfile = () => {
  router.push('/profile')
}

const removeAlert = (alertId) => {
  appStore.removeAlert(alertId)
}

const checkMobile = () => {
  isMobile.value = window.innerWidth < 768
  
  // Auto-collapse sidebar on mobile
  if (isMobile.value) {
    sidebarCollapsed.value = true
  }
}

// Lifecycle
onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
})

// Provide layout context to child components
provide('layout', {
  addAlert: appStore.addAlert,
  removeAlert,
  setLoading: appStore.setLoading,
  pageTitle: props.pageTitle,
  pageDescription: props.pageDescription
})
</script>

<style scoped>
.app-layout {
  display: flex;
  min-height: 100vh;
  background-color: var(--color-bg-50);
}

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
  margin-left: 16rem; /* Sidebar width */
  transition: margin-left 0.3s ease;
}

.main-content.sidebar-collapsed {
  margin-left: 4rem; /* Collapsed sidebar width */
}

.page-content {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.content-container {
  flex: 1;
  padding: 1.5rem;
  max-width: v-bind('props.maxWidth');
  margin: 0 auto;
  width: 100%;
}

/* Padding variants */
.content-container.padding-none {
  padding: 0;
}

.content-container.padding-sm {
  padding: 0.75rem;
}

.content-container.padding-lg {
  padding: 2rem;
}

.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 2rem;
  gap: 1rem;
}

.page-title-section {
  flex: 1;
  min-width: 0;
}

.page-title {
  margin: 0 0 0.5rem 0;
  font-size: 2rem;
  font-weight: 700;
  color: var(--color-text-900);
  line-height: 1.2;
}

.page-description {
  margin: 0;
  font-size: 1rem;
  color: var(--color-text-600);
  line-height: 1.5;
}

.page-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.page-alerts {
  margin-bottom: 1.5rem;
}

.page-alerts > * {
  margin-bottom: 0.75rem;
}

.page-alerts > *:last-child {
  margin-bottom: 0;
}

.page-main {
  flex: 1;
}

.sidebar-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 40;
}

/* Mobile responsive */
@media (max-width: 768px) {
  .main-content {
    margin-left: 0;
  }
  
  .main-content.sidebar-collapsed {
    margin-left: 0;
  }
  
  .content-container {
    padding: 1rem;
  }
  
  .page-header {
    flex-direction: column;
    align-items: stretch;
  }
  
  .page-actions {
    justify-content: flex-end;
  }
  
  .page-title {
    font-size: 1.5rem;
  }
}

@media (max-width: 480px) {
  .content-container {
    padding: 0.75rem;
  }
  
  .page-title {
    font-size: 1.25rem;
  }
  
  .page-actions {
    flex-direction: column;
    align-items: stretch;
  }
}

/* Print styles */
@media print {
  .app-layout {
    display: block;
  }
  
  .main-content {
    margin-left: 0;
  }
  
  .page-header {
    border-bottom: 1px solid var(--color-border-300);
    padding-bottom: 1rem;
    margin-bottom: 1rem;
  }
}
</style>
