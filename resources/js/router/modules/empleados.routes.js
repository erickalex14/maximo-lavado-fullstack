export default [
  {
    path: 'empleados',
    name: 'Empleados',
    component: () => import('@/views/empleados/EmpleadosView.vue'),
    meta: {
      title: 'Empleados',
      icon: 'users'
    }
  },
  {
    path: 'empleados/crear',
    name: 'CrearEmpleado',
    component: () => import('@/views/empleados/CreateEmpleadoView.vue'),
    meta: {
      title: 'Crear Empleado',
      parent: 'Empleados'
    }
  },
  {
    path: 'empleados/:id',
    name: 'DetalleEmpleado',
    component: () => import('@/views/empleados/DetailEmpleadoView.vue'),
    meta: {
      title: 'Detalle Empleado',
      parent: 'Empleados'
    }
  },
  {
    path: 'empleados/:id/editar',
    name: 'EditarEmpleado',
    component: () => import('@/views/empleados/EditEmpleadoView.vue'),
    meta: {
      title: 'Editar Empleado',
      parent: 'Empleados'
    }
  }
];
