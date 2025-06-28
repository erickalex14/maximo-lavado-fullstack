export default [
  {
    path: 'clientes',
    name: 'Clientes',
    component: () => import('@/views/clientes/ClientesView.vue'),
    meta: {
      title: 'Clientes',
      icon: 'user-group'
    }
  },
  {
    path: 'clientes/crear',
    name: 'CrearCliente',
    component: () => import('@/views/clientes/CreateClienteView.vue'),
    meta: {
      title: 'Crear Cliente',
      parent: 'Clientes'
    }
  },
  {
    path: 'clientes/:id',
    name: 'DetalleCliente',
    component: () => import('@/views/clientes/DetailClienteView.vue'),
    meta: {
      title: 'Detalle Cliente',
      parent: 'Clientes'
    }
  },
  {
    path: 'clientes/:id/editar',
    name: 'EditarCliente',
    component: () => import('@/views/clientes/EditClienteView.vue'),
    meta: {
      title: 'Editar Cliente',
      parent: 'Clientes'
    }
  }
];
