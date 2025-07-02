// Base Components Index
// Este archivo facilita la importación de componentes base en toda la aplicación

// Form Controls
export { default as BaseButton } from './BaseButton.vue'
export { default as BaseInput } from './BaseInput.vue'
export { default as BaseTextarea } from './BaseTextarea.vue'
export { default as BaseSelect } from './BaseSelect.vue'
export { default as BaseSearchInput } from './BaseSearchInput.vue'
export { default as BaseCheckbox } from './BaseCheckbox.vue'
export { default as BaseRadio } from './BaseRadio.vue'
export { default as BaseSwitch } from './BaseSwitch.vue'
export { default as BaseDatePicker } from './BaseDatePicker.vue'
export { default as BaseFileUpload } from './BaseFileUpload.vue'

// Layout & Containers
export { default as BaseCard } from './BaseCard.vue'
export { default as BaseModal } from './BaseModal.vue'
export { default as BaseForm } from './BaseForm.vue'

// Navigation & Interaction
export { default as BaseDropdown } from './BaseDropdown.vue'
export { default as BaseTabs } from './BaseTabs.vue'

// Data Display
export { default as BaseTable } from './BaseTable.vue'
export { default as BaseBadge } from './BaseBadge.vue'
export { default as BasePagination } from './BasePagination.vue'
export { default as BaseStats } from './BaseStats.vue'

// Feedback
export { default as BaseAlert } from './BaseAlert.vue'
export { default as BaseToast } from './BaseToast.vue'
export { default as BaseLoading } from './BaseLoading.vue'

// Utility function to register all components globally
export const registerBaseComponents = (app) => {
  // Form Controls
  app.component('BaseButton', () => import('./BaseButton.vue'))
  app.component('BaseInput', () => import('./BaseInput.vue'))
  app.component('BaseTextarea', () => import('./BaseTextarea.vue'))
  app.component('BaseSelect', () => import('./BaseSelect.vue'))
  app.component('BaseSearchInput', () => import('./BaseSearchInput.vue'))
  app.component('BaseCheckbox', () => import('./BaseCheckbox.vue'))
  app.component('BaseRadio', () => import('./BaseRadio.vue'))
  app.component('BaseSwitch', () => import('./BaseSwitch.vue'))
  app.component('BaseDatePicker', () => import('./BaseDatePicker.vue'))
  app.component('BaseFileUpload', () => import('./BaseFileUpload.vue'))
  
  // Layout & Containers
  app.component('BaseCard', () => import('./BaseCard.vue'))
  app.component('BaseModal', () => import('./BaseModal.vue'))
  app.component('BaseForm', () => import('./BaseForm.vue'))
  
  // Navigation & Interaction
  app.component('BaseDropdown', () => import('./BaseDropdown.vue'))
  app.component('BaseTabs', () => import('./BaseTabs.vue'))
  
  // Data Display
  app.component('BaseTable', () => import('./BaseTable.vue'))
  app.component('BaseBadge', () => import('./BaseBadge.vue'))
  app.component('BasePagination', () => import('./BasePagination.vue'))
  app.component('BaseStats', () => import('./BaseStats.vue'))
  
  // Feedback
  app.component('BaseAlert', () => import('./BaseAlert.vue'))
  app.component('BaseToast', () => import('./BaseToast.vue'))
  app.component('BaseLoading', () => import('./BaseLoading.vue'))
}

// Component categories for better organization
export const FORM_COMPONENTS = [
  'BaseButton',
  'BaseInput', 
  'BaseTextarea',
  'BaseSelect',
  'BaseSearchInput',
  'BaseCheckbox',
  'BaseRadio',
  'BaseSwitch',
  'BaseDatePicker',
  'BaseFileUpload',
  'BaseForm'
]

export const LAYOUT_COMPONENTS = [
  'BaseCard',
  'BaseModal'
]

export const NAVIGATION_COMPONENTS = [
  'BaseDropdown',
  'BaseTabs'
]

export const DATA_COMPONENTS = [
  'BaseTable',
  'BaseBadge',
  'BasePagination',
  'BaseStats'
]

export const FEEDBACK_COMPONENTS = [
  'BaseAlert',
  'BaseToast', 
  'BaseLoading'
]

// All component names
export const ALL_COMPONENTS = [
  ...FORM_COMPONENTS,
  ...LAYOUT_COMPONENTS,
  ...NAVIGATION_COMPONENTS,
  ...DATA_COMPONENTS,
  ...FEEDBACK_COMPONENTS
]
