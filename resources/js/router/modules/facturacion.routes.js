export default [
  {
    path: 'facturacion',
    name: 'Facturacion',
    component: () => import('@/views/facturacion/FacturacionView.vue'),
    meta: { title: 'Facturación', icon: 'document-text' }
  }
];
