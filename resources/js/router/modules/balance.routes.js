export default [
  {
    path: 'balance',
    name: 'Balance',
    component: () => import('@/views/balance/BalanceView.vue'),
    meta: { title: 'Balance', icon: 'scale' }
  }
];
