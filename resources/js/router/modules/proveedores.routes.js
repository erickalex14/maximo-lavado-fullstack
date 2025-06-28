export default [
  {
    path: 'proveedores',
    name: 'Proveedores',
    component: () => import('@/views/proveedores/ProveedoresView.vue'),
    meta: { title: 'Proveedores', icon: 'building-office' }
  }
];
