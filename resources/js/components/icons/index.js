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
export { default as CurrencyDollarIcon } from './CurrencyDollarIcon.vue'
export { default as SparklesIcon } from './SparklesIcon.vue'
export { default as ClockIcon } from './ClockIcon.vue'
export { default as RefreshIcon } from './RefreshIcon.vue'
export { default as DocumentTextIcon } from './DocumentTextIcon.vue'
export { default as ChartBarIcon } from './ChartBarIcon.vue'
export { default as ArrowRightIcon } from './ArrowRightIcon.vue'
export { default as PlusIcon } from './PlusIcon.vue'
export { default as UserPlusIcon } from './UserPlusIcon.vue'
export { default as CubeIcon } from './CubeIcon.vue'
export { default as Cog6ToothIcon } from './Cog6ToothIcon.vue'
export { default as ArrowTrendingUpIcon } from './ArrowTrendingUpIcon.vue'
export { default as ArrowTrendingDownIcon } from './ArrowTrendingDownIcon.vue'
export { default as SunIcon } from './SunIcon.vue'
export { default as StarIcon } from './StarIcon.vue'
export { default as ShieldCheckIcon } from './ShieldCheckIcon.vue'

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
import CurrencyDollarIcon from './CurrencyDollarIcon.vue'
import SparklesIcon from './SparklesIcon.vue'
import ClockIcon from './ClockIcon.vue'
import RefreshIcon from './RefreshIcon.vue'
import DocumentTextIcon from './DocumentTextIcon.vue'
import ChartBarIcon from './ChartBarIcon.vue'
import ArrowRightIcon from './ArrowRightIcon.vue'
import PlusIcon from './PlusIcon.vue'
import UserPlusIcon from './UserPlusIcon.vue'
import CubeIcon from './CubeIcon.vue'
import Cog6ToothIcon from './Cog6ToothIcon.vue'
import ArrowTrendingUpIcon from './ArrowTrendingUpIcon.vue'
import ArrowTrendingDownIcon from './ArrowTrendingDownIcon.vue'
import SunIcon from './SunIcon.vue'
import StarIcon from './StarIcon.vue'
import ShieldCheckIcon from './ShieldCheckIcon.vue'

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
  'currency-dollar': CurrencyDollarIcon,
  'sparkles': SparklesIcon,
  'clock': ClockIcon,
  'refresh': RefreshIcon,
  'document-text': DocumentTextIcon,
  'chart-bar': ChartBarIcon,
  'arrow-right': ArrowRightIcon,
  'plus': PlusIcon,
  'user-plus': UserPlusIcon,
  'cube': CubeIcon,
  'cog-6-tooth': Cog6ToothIcon,
  'arrow-trending-up': ArrowTrendingUpIcon,
  'arrow-trending-down': ArrowTrendingDownIcon,
  'sun': SunIcon,
  'star': StarIcon,
  'shield-check': ShieldCheckIcon,
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
