export default [
  {
    path: 'reportes',
    name: 'Reportes',
    component: () => import('@/views/reportes/ReportesView.vue'),
    meta: { title: 'Reportes', icon: 'document-chart-bar' }
  }
];
