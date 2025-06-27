import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// Import components
import Login from '@/pages/Login.vue'
import Dashboard from '@/pages/Dashboard.vue'
import Clientes from '@/pages/Clientes.vue'
import Lavados from '@/pages/Lavados.vue'
import Productos from '@/pages/Productos.vue'
import Vehiculos from '@/pages/Vehiculos.vue'
import Reportes from '@/pages/Reportes.vue'

// Configure routes
const routes = [
  { path: '/', redirect: '/dashboard' },
  { path: '/login', name: 'login', component: Login },
  { path: '/dashboard', name: 'dashboard', component: Dashboard, meta: { requiresAuth: true } },
  { path: '/clientes', name: 'clientes', component: Clientes, meta: { requiresAuth: true } },
  { path: '/vehiculos', name: 'vehiculos', component: Vehiculos, meta: { requiresAuth: true } },
  { path: '/lavados', name: 'lavados', component: Lavados, meta: { requiresAuth: true } },
  { path: '/lavados/nuevo', name: 'lavados-nuevo', component: Lavados, meta: { requiresAuth: true } },
  { path: '/productos', name: 'productos', component: Productos, meta: { requiresAuth: true } },
  { path: '/reportes', name: 'reportes', component: Reportes, meta: { requiresAuth: true } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Navigation guard
router.beforeEach(async (to, from, next) => {
  console.log('Navigating to:', to.path)
  
  // Check if route requires authentication
  if (to.matched.some(record => record.meta.requiresAuth)) {
    const authStore = useAuthStore()
    
    try {
      // Check if user is already authenticated
      if (!authStore.isAuthenticated) {
        await authStore.fetchUser()
      }
      
      if (authStore.isAuthenticated) {
        next()
      } else {
        console.log('User not authenticated, redirecting to login')
        next('/login')
      }
    } catch (error) {
      console.error('Auth check failed:', error)
      next('/login')
    }
  } else {
    // If going to login and already authenticated, redirect to dashboard
    if (to.path === '/login') {
      const authStore = useAuthStore()
      if (authStore.isAuthenticated) {
        next('/dashboard')
        return
      }
    }
    next()
  }
})

export default router
