export default [
  {
    path: 'lavados',
    name: 'Lavados',
    component: () => import('@/views/lavados/LavadosView.vue'),
    meta: { title: 'Lavados', icon: 'beaker' }
  },
  {
    path: 'lavados/crear',
    name: 'CrearLavado',
    component: () => import('@/views/lavados/CreateLavadoView.vue'),
    meta: { title: 'Registrar Lavado', parent: 'Lavados' }
  }
];
