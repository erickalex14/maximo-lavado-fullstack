export default [
  {
    path: 'egresos',
    name: 'Egresos',
    component: () => import('@/views/egresos/EgresosView.vue'),
    meta: { title: 'Egresos', icon: 'arrow-trending-down' }
  }
];
