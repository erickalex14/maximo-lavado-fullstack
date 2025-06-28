export default [
  {
    path: 'vehiculos',
    name: 'Vehiculos',
    component: () => import('@/views/vehiculos/VehiculosView.vue'),
    meta: { title: 'Vehículos', icon: 'truck' }
  },
  {
    path: 'vehiculos/crear',
    name: 'CrearVehiculo',
    component: () => import('@/views/vehiculos/CreateVehiculoView.vue'),
    meta: { title: 'Crear Vehículo', parent: 'Vehiculos' }
  }
];
