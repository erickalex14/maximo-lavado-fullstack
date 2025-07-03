import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

// Importar componentes de manera lazy para code splitting
const Dashboard = () => import('@/views/Dashboard.vue');
const Login = () => import('@/views/auth/Login.vue');

// Clientes
const ClienteIndex = () => import('@/views/clientes/Index.vue');
const ClienteCreate = () => import('@/views/clientes/Create.vue');
const ClienteEdit = () => import('@/views/clientes/Edit.vue');
const ClienteShow = () => import('@/views/clientes/Show.vue');

// Empleados
const EmpleadoIndex = () => import('@/views/empleados/Index.vue');
const EmpleadoCreate = () => import('@/views/empleados/Create.vue');
const EmpleadoEdit = () => import('@/views/empleados/Edit.vue');
const EmpleadoShow = () => import('@/views/empleados/Show.vue');

// Vehículos
const VehiculoIndex = () => import('@/views/vehiculos/Index.vue');
const VehiculoCreate = () => import('@/views/vehiculos/Create.vue');
const VehiculoEdit = () => import('@/views/vehiculos/Edit.vue');
const VehiculoShow = () => import('@/views/vehiculos/Show.vue');

// Lavados
const LavadoIndex = () => import('@/views/lavados/Index.vue');
const LavadoCreate = () => import('@/views/lavados/Create.vue');
const LavadoEdit = () => import('@/views/lavados/Edit.vue');
const LavadoShow = () => import('@/views/lavados/Show.vue');

// Productos
const ProductoIndex = () => import('@/views/productos/Index.vue');
const ProductoAutomotrizIndex = () => import('@/views/productos/automotrices/Index.vue');
const ProductoAutomotrizCreate = () => import('@/views/productos/automotrices/Create.vue');
const ProductoAutomotrizEdit = () => import('@/views/productos/automotrices/Edit.vue');
const ProductoDespensaIndex = () => import('@/views/productos/despensa/Index.vue');
const ProductoDespensaCreate = () => import('@/views/productos/despensa/Create.vue');
const ProductoDespensaEdit = () => import('@/views/productos/despensa/Edit.vue');

// Proveedores
const ProveedorIndex = () => import('@/views/proveedores/Index.vue');
const ProveedorCreate = () => import('@/views/proveedores/Create.vue');
const ProveedorEdit = () => import('@/views/proveedores/Edit.vue');
const ProveedorShow = () => import('@/views/proveedores/Show.vue');

// Finanzas
const IngresoIndex = () => import('@/views/finanzas/ingresos/Index.vue');
const EgresoIndex = () => import('@/views/finanzas/egresos/Index.vue');
const GastoGeneralIndex = () => import('@/views/finanzas/gastos-generales/Index.vue');
const BalanceIndex = () => import('@/views/finanzas/balance/Index.vue');

// Facturas y Ventas
const FacturaIndex = () => import('@/views/facturas/Index.vue');
const FacturaCreate = () => import('@/views/facturas/Create.vue');
const FacturaShow = () => import('@/views/facturas/Show.vue');
const VentaIndex = () => import('@/views/ventas/Index.vue');

// Reportes
const ReporteIndex = () => import('@/views/reportes/Index.vue');

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
        component: ClienteIndex,
        meta: { title: 'Clientes' }
      },
      {
        path: '/clientes/crear',
        name: 'clientes.create',
        component: ClienteCreate,
        meta: { title: 'Crear Cliente' }
      },
      {
        path: '/clientes/:id/editar',
        name: 'clientes.edit',
        component: ClienteEdit,
        meta: { title: 'Editar Cliente' }
      },
      {
        path: '/clientes/:id',
        name: 'clientes.show',
        component: ClienteShow,
        meta: { title: 'Ver Cliente' }
      },

      // Empleados
      {
        path: '/empleados',
        name: 'empleados.index',
        component: EmpleadoIndex,
        meta: { title: 'Empleados' }
      },
      {
        path: '/empleados/crear',
        name: 'empleados.create',
        component: EmpleadoCreate,
        meta: { title: 'Crear Empleado' }
      },
      {
        path: '/empleados/:id/editar',
        name: 'empleados.edit',
        component: EmpleadoEdit,
        meta: { title: 'Editar Empleado' }
      },
      {
        path: '/empleados/:id',
        name: 'empleados.show',
        component: EmpleadoShow,
        meta: { title: 'Ver Empleado' }
      },

      // Vehículos
      {
        path: '/vehiculos',
        name: 'vehiculos.index',
        component: VehiculoIndex,
        meta: { title: 'Vehículos' }
      },
      {
        path: '/vehiculos/crear',
        name: 'vehiculos.create',
        component: VehiculoCreate,
        meta: { title: 'Crear Vehículo' }
      },
      {
        path: '/vehiculos/:id/editar',
        name: 'vehiculos.edit',
        component: VehiculoEdit,
        meta: { title: 'Editar Vehículo' }
      },
      {
        path: '/vehiculos/:id',
        name: 'vehiculos.show',
        component: VehiculoShow,
        meta: { title: 'Ver Vehículo' }
      },

      // Lavados
      {
        path: '/lavados',
        name: 'lavados.index',
        component: LavadoIndex,
        meta: { title: 'Lavados' }
      },
      {
        path: '/lavados/crear',
        name: 'lavados.create',
        component: LavadoCreate,
        meta: { title: 'Registrar Lavado' }
      },
      {
        path: '/lavados/:id/editar',
        name: 'lavados.edit',
        component: LavadoEdit,
        meta: { title: 'Editar Lavado' }
      },
      {
        path: '/lavados/:id',
        name: 'lavados.show',
        component: LavadoShow,
        meta: { title: 'Ver Lavado' }
      },

      // Productos
      {
        path: '/productos',
        name: 'productos.index',
        component: ProductoIndex,
        meta: { title: 'Productos' }
      },
      
      // Productos Automotrices
      {
        path: '/productos/automotrices',
        name: 'productos.automotrices.index',
        component: ProductoAutomotrizIndex,
        meta: { title: 'Productos Automotrices' }
      },
      {
        path: '/productos/automotrices/crear',
        name: 'productos.automotrices.create',
        component: ProductoAutomotrizCreate,
        meta: { title: 'Crear Producto Automotriz' }
      },
      {
        path: '/productos/automotrices/:id/editar',
        name: 'productos.automotrices.edit',
        component: ProductoAutomotrizEdit,
        meta: { title: 'Editar Producto Automotriz' }
      },

      // Productos Despensa
      {
        path: '/productos/despensa',
        name: 'productos.despensa.index',
        component: ProductoDespensaIndex,
        meta: { title: 'Productos de Despensa' }
      },
      {
        path: '/productos/despensa/crear',
        name: 'productos.despensa.create',
        component: ProductoDespensaCreate,
        meta: { title: 'Crear Producto de Despensa' }
      },
      {
        path: '/productos/despensa/:id/editar',
        name: 'productos.despensa.edit',
        component: ProductoDespensaEdit,
        meta: { title: 'Editar Producto de Despensa' }
      },

      // Proveedores
      {
        path: '/proveedores',
        name: 'proveedores.index',
        component: ProveedorIndex,
        meta: { title: 'Proveedores' }
      },
      {
        path: '/proveedores/crear',
        name: 'proveedores.create',
        component: ProveedorCreate,
        meta: { title: 'Crear Proveedor' }
      },
      {
        path: '/proveedores/:id/editar',
        name: 'proveedores.edit',
        component: ProveedorEdit,
        meta: { title: 'Editar Proveedor' }
      },
      {
        path: '/proveedores/:id',
        name: 'proveedores.show',
        component: ProveedorShow,
        meta: { title: 'Ver Proveedor' }
      },

      // Finanzas
      {
        path: '/finanzas/ingresos',
        name: 'finanzas.ingresos.index',
        component: IngresoIndex,
        meta: { title: 'Ingresos' }
      },
      {
        path: '/finanzas/egresos',
        name: 'finanzas.egresos.index',
        component: EgresoIndex,
        meta: { title: 'Egresos' }
      },
      {
        path: '/finanzas/gastos-generales',
        name: 'finanzas.gastos-generales.index',
        component: GastoGeneralIndex,
        meta: { title: 'Gastos Generales' }
      },
      {
        path: '/finanzas/balance',
        name: 'finanzas.balance.index',
        component: BalanceIndex,
        meta: { title: 'Balance General' }
      },

      // Facturas
      {
        path: '/facturas',
        name: 'facturas.index',
        component: FacturaIndex,
        meta: { title: 'Facturas' }
      },
      {
        path: '/facturas/crear',
        name: 'facturas.create',
        component: FacturaCreate,
        meta: { title: 'Crear Factura' }
      },
      {
        path: '/facturas/:id',
        name: 'facturas.show',
        component: FacturaShow,
        meta: { title: 'Ver Factura' }
      },

      // Ventas
      {
        path: '/ventas',
        name: 'ventas.index',
        component: VentaIndex,
        meta: { title: 'Ventas' }
      },

      // Reportes
      {
        path: '/reportes',
        name: 'reportes.index',
        component: ReporteIndex,
        meta: { title: 'Reportes' }
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
  
  // Rutas que requieren autenticación
  if (to.meta.requiresAuth) {
    if (!authStore.isAuthenticated) {
      // Si no hay token, verificar si existe en localStorage
      if (!await authStore.fetchUser()) {
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
