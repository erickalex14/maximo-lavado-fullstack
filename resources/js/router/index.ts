import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

// Importar componentes de manera lazy para code splitting
const Dashboard = () => import('@/views/Dashboard.vue');
const Login = () => import('@/views/auth/Login.vue');

// Clientes
const Clientes = () => import('@/views/clientes/Clientes.vue');

// Empleados  
const Empleados = () => import('@/views/empleados/Empleados.vue');

// Vehículos
const Vehiculos = () => import('@/views/vehiculos/Vehiculos.vue');

// Lavados
const Lavados = () => import('@/views/lavados/Lavados.vue');

// Productos
const Productos = () => import('@/views/productos/Productos.vue');

// Proveedores
const Proveedores = () => import('@/views/proveedores/Proveedores.vue');

// Finanzas
const Finanzas = () => import('@/views/finanzas/Finanzas.vue');

// Facturas
const Facturas = () => import('@/views/facturas/Facturas.vue');

// Ventas
const Ventas = () => import('@/views/ventas/Ventas.vue');
const VentaCrear = () => import('@/views/ventas/VentaCrear.vue');

// Reportes
const Reportes = () => import('@/views/reportes/Reportes.vue');

// Layout principal
const AppLayout = () => import('@/components/layout/AppLayout.vue');

const routes = [
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { requiresGuest: true }
  },
  {
    path: '/',
    component: AppLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: Dashboard,
        meta: { title: 'Dashboard' }
      },
      
      // Clientes
      {
        path: '/clientes',
        name: 'clientes.index',
        component: Clientes,
        meta: { title: 'Clientes' }
      },

      // Empleados
      {
        path: '/empleados',
        name: 'empleados.index',
        component: Empleados,
        meta: { title: 'Empleados' }
      },

      // Vehículos
      {
        path: '/vehiculos',
        name: 'vehiculos.index',
        component: Vehiculos,
        meta: { title: 'Vehículos' }
      },

      // Lavados
      {
        path: '/lavados',
        name: 'lavados.index',
        component: Lavados,
        meta: { title: 'Lavados' }
      },

      // Productos
      {
        path: '/productos',
        name: 'productos.index',
        component: Productos,
        meta: { title: 'Productos' }
      },

      // Proveedores
      {
        path: '/proveedores',
        name: 'proveedores.index',
        component: Proveedores,
        meta: { title: 'Proveedores' }
      },

      // Finanzas
      {
        path: '/finanzas',
        name: 'finanzas.index',
        component: Finanzas,
        meta: { title: 'Finanzas' }
      },

      // Facturas
      {
        path: '/facturas',
        name: 'facturas.index',
        component: Facturas,
        meta: { title: 'Facturas' }
      },

      // Ventas
      {
        path: '/ventas',
        name: 'ventas.index',
        component: Ventas,
        meta: { title: 'Ventas' }
      },
      {
        path: '/ventas/crear',
        name: 'ventas.create',
        component: VentaCrear,
        meta: { title: 'Nueva Venta' }
      },

      // Reportes
      {
        path: '/reportes',
        name: 'reportes.index',
        component: Reportes,
        meta: { title: 'Reportes' }
      },

      // Usuarios
      {
        path: '/usuarios',
        name: 'usuarios.index',
        component: () => import('@/views/usuarios/Usuarios.vue'),
        meta: { title: 'Usuarios' }
      },
    ]
  },
  
  // Ruta 404
  {
    path: '/:pathMatch(.*)*',
    redirect: '/'
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();
  
  console.log('Router guard:', {
    route: to.path,
    requiresAuth: to.meta.requiresAuth,
    requiresGuest: to.meta.requiresGuest,
    isAuthenticated: authStore.isAuthenticated,
    hasToken: !!localStorage.getItem('auth_token')
  });
  
  // Rutas que requieren autenticación
  if (to.meta.requiresAuth) {
    // Si no está autenticado, intentar obtener el usuario
    if (!authStore.isAuthenticated) {
      // Verificar si hay token en localStorage
      const hasToken = !!localStorage.getItem('auth_token');
      if (!hasToken) {
        console.log('No token found, redirecting to login');
        next('/login');
        return;
      }
      
      try {
        console.log('Attempting to fetch user...');
        // Dar un tiempo límite para la autenticación
        const timeoutPromise = new Promise((_, reject) => {
          setTimeout(() => reject(new Error('Timeout')), 10000);
        });
        
        const authenticated = await Promise.race([
          authStore.fetchUser(),
          timeoutPromise
        ]);
        
        if (!authenticated) {
          console.log('Failed to authenticate, redirecting to login');
          authStore.$reset();
          next('/login');
          return;
        }
        console.log('User authenticated successfully');
      } catch (error) {
        console.error('Error al verificar autenticación:', error);
        authStore.$reset();
        next('/login');
        return;
      }
    }
  }
  
  // Rutas que requieren ser invitado (no autenticado)
  if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next('/');
    return;
  }
  
  // Establecer título de la página
  if (to.meta.title) {
    document.title = `${to.meta.title} - Maximo Lavado`;
  } else {
    document.title = 'Maximo Lavado';
  }
  
  next();
});

export default router;
