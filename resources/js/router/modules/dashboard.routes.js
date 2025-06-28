export default [
  {
    path: '',
    name: 'Dashboard',
    component: () => import('@/views/dashboard/DashboardView.vue'),
    meta: {
      title: 'Dashboard',
      icon: 'dashboard'
    }
  }
];
