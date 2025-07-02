import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

// Importar layouts
import AuthLayout from '@/layouts/AuthLayout.vue';
import AppLayout from '@/layouts/AppLayout.vue';

// Importar rutas modulares
import authRoutes from './modules/auth.routes';
import dashboardRoutes from './modules/dashboard.routes';
import empleadosRoutes from './modules/empleados.routes';
import clientesRoutes from './modules/clientes.routes';
import vehiculosRoutes from './modules/vehiculos.routes';
import lavadosRoutes from './modules/lavados.routes';
import productosRoutes from './modules/productos.routes';
import proveedoresRoutes from './modules/proveedores.routes';
import ingresosRoutes from './modules/ingresos.routes';
import egresosRoutes from './modules/egresos.routes';
import balanceRoutes from './modules/balance.routes';
import reportesRoutes from './modules/reportes.routes';
import facturacionRoutes from './modules/facturacion.routes';

const routes = [
  {
    path: '/',
    redirect: (to) => {
      // Esta lógica se manejará en el beforeEach guard
      return '/dashboard';
    }
  },
  // Rutas de autenticación
  {
    path: '/auth',
    component: AuthLayout,
    children: authRoutes
  },
  // Rutas del dashboard (requieren autenticación)
  {
    path: '/dashboard',
    component: AppLayout,
    meta: { requiresAuth: true },
    children: [
      ...dashboardRoutes,
      ...empleadosRoutes,
      ...clientesRoutes,
      ...vehiculosRoutes,
      ...lavadosRoutes,
      ...productosRoutes,
      ...proveedoresRoutes,
      ...ingresosRoutes,
      ...egresosRoutes,
      ...balanceRoutes,
      ...reportesRoutes,
      ...facturacionRoutes
    ]
  },
  // Ruta 404
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('@/views/error/NotFound.vue')
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Guard de autenticación
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();
  
  // Si está yendo a la ruta raíz, decidir a dónde redirigir basado en autenticación
  if (to.path === '/') {
    const token = localStorage.getItem('auth_token');
    if (!token) {
      return next('/auth/login');
    }
    
    try {
      const isValid = await authStore.checkAuth();
      if (isValid) {
        return next('/dashboard');
      } else {
        return next('/auth/login');
      }
    } catch (error) {
      console.error('Error verificando token:', error);
      return next('/auth/login');
    }
  }
  
  // Si está intentando acceder al login y ya está autenticado, redirigir al dashboard
  if (to.name === 'Login') {
    // Solo verificar autenticación si hay token
    const token = localStorage.getItem('auth_token');
    if (token && authStore.isAuthenticated) {
      return next('/dashboard');
    }
    // Si hay token pero no está verificado en el store, verificar
    if (token && !authStore.isAuthenticated) {
      try {
        const isValid = await authStore.checkAuth();
        if (isValid) {
          return next('/dashboard');
        }
      } catch (error) {
        console.error('Error verificando autenticación en login:', error);
      }
    }
    // Si no hay token o no es válido, permitir acceso al login
    return next();
  }
  
  // Verificar si la ruta requiere autenticación
  if (to.matched.some(record => record.meta.requiresAuth)) {
    const token = localStorage.getItem('auth_token');
    if (!token) {
      return next('/auth/login');
    }
    
    // Si hay token pero no está autenticado en el store, verificar
    if (!authStore.isAuthenticated) {
      try {
        const isValid = await authStore.checkAuth();
        if (!isValid) {
          return next('/auth/login');
        }
      } catch (error) {
        console.error('Error verificando autenticación:', error);
        return next('/auth/login');
      }
    }
  }
  
  next();
});

export default router;
