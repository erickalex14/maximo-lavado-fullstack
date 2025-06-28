export default [
  {
    path: 'productos',
    name: 'Productos',
    component: () => import('@/views/productos/ProductosView.vue'),
    meta: { title: 'Productos', icon: 'cube' }
  },
  {
    path: 'productos/automotrices',
    name: 'ProductosAutomotrices',
    component: () => import('@/views/productos/ProductosAutomotricesView.vue'),
    meta: { title: 'Productos Automotrices', parent: 'Productos' }
  },
  {
    path: 'productos/despensa',
    name: 'ProductosDespensa',
    component: () => import('@/views/productos/ProductosDespensaView.vue'),
    meta: { title: 'Productos de Despensa', parent: 'Productos' }
  }
];
