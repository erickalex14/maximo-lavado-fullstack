// Icon components export
export { default as DashboardIcon } from './DashboardIcon.vue'
export { default as UsersIcon } from './UsersIcon.vue'
export { default as CarIcon } from './CarIcon.vue'
export { default as CarWashIcon } from './CarWashIcon.vue'
export { default as ShoppingCartIcon } from './ShoppingCartIcon.vue'
export { default as SettingsIcon } from './SettingsIcon.vue'
export { default as InventoryIcon } from './InventoryIcon.vue'
export { default as FinanceIcon } from './FinanceIcon.vue'
export { default as TeamIcon } from './TeamIcon.vue'
export { default as SuppliersIcon } from './SuppliersIcon.vue'
export { default as ReportsIcon } from './ReportsIcon.vue'

import DashboardIcon from './DashboardIcon.vue'
import UsersIcon from './UsersIcon.vue'
import CarIcon from './CarIcon.vue'
import CarWashIcon from './CarWashIcon.vue'
import ShoppingCartIcon from './ShoppingCartIcon.vue'
import SettingsIcon from './SettingsIcon.vue'
import InventoryIcon from './InventoryIcon.vue'
import FinanceIcon from './FinanceIcon.vue'
import TeamIcon from './TeamIcon.vue'
import SuppliersIcon from './SuppliersIcon.vue'
import ReportsIcon from './ReportsIcon.vue'

// Icon mapping for dynamic usage
export const iconMap = {
  'dashboard': DashboardIcon,
  'users': UsersIcon,
  'car': CarIcon,
  'car-wash': CarWashIcon,
  'shopping-cart': ShoppingCartIcon,
  'settings': SettingsIcon,
  'inventory': InventoryIcon,
  'finance': FinanceIcon,
  'team': TeamIcon,
  'suppliers': SuppliersIcon,
  'reports': ReportsIcon,
  // Aliases for convenience
  'cliente': UsersIcon,
  'clientes': UsersIcon,
  'vehiculo': CarIcon,
  'vehiculos': CarIcon,
  'lavado': CarWashIcon,
  'lavados': CarWashIcon,
  'venta': ShoppingCartIcon,
  'ventas': ShoppingCartIcon,
  'configuracion': SettingsIcon,
  'productos': InventoryIcon,
  'finanzas': FinanceIcon,
  'empleados': TeamIcon,
  'proveedores': SuppliersIcon,
  'reportes': ReportsIcon
}

// Helper function to get icon component by name
export const getIcon = (iconName) => {
  return iconMap[iconName] || null
}
