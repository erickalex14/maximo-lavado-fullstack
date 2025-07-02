<template>
  <form
    :class="formClasses"
    :novalidate="novalidate"
    @submit="handleSubmit"
    @reset="handleReset"
  >
    <fieldset :disabled="disabled || loading" :class="fieldsetClasses">
      <legend v-if="legend" class="form-legend">{{ legend }}</legend>
      
      <div v-if="title || description" class="form-header">
        <h2 v-if="title" class="form-title">{{ title }}</h2>
        <p v-if="description" class="form-description">{{ description }}</p>
      </div>
      
      <div class="form-content" :class="contentClasses">
        <slot :errors="errors" :hasErrors="hasErrors" :isValid="isValid" />
      </div>
      
      <div v-if="showActions" class="form-actions" :class="actionsClasses">
        <slot name="actions" :errors="errors" :hasErrors="hasErrors" :isValid="isValid">
          <BaseButton
            v-if="showCancelButton"
            type="button"
            variant="secondary"
            :size="buttonSize"
            :disabled="disabled"
            @click="handleCancel"
          >
            {{ cancelText }}
          </BaseButton>
          
          <BaseButton
            v-if="showResetButton"
            type="reset"
            variant="outline"
            :size="buttonSize"
            :disabled="disabled || loading"
          >
            {{ resetText }}
          </BaseButton>
          
          <BaseButton
            type="submit"
            :variant="submitVariant"
            :size="buttonSize"
            :disabled="disabled || (validateOnSubmit && hasErrors)"
            :loading="loading"
          >
            {{ loading ? loadingText : submitText }}
          </BaseButton>
        </slot>
      </div>
    </fieldset>
    
    <div v-if="showErrorSummary && hasErrors" class="form-error-summary">
      <div class="error-summary-header">
        <svg class="error-icon" viewBox="0 0 20 20" fill="currentColor">
          <path
            fill-rule="evenodd"
            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
            clip-rule="evenodd"
          />
        </svg>
        <span class="error-summary-title">{{ errorSummaryTitle }}</span>
      </div>
      <ul class="error-summary-list">
        <li v-for="(error, field) in errors" :key="field" class="error-summary-item">
          <button
            type="button"
            class="error-summary-link"
            @click="focusField(field)"
          >
            {{ getFieldLabel(field) }}: {{ error }}
          </button>
        </li>
      </ul>
    </div>
  </form>
</template>

<script setup>
import { computed, ref, provide, onMounted, nextTick } from 'vue'
import BaseButton from './BaseButton.vue'

// Props
const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  description: {
    type: String,
    default: ''
  },
  legend: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  novalidate: {
    type: Boolean,
    default: true
  },
  validateOnSubmit: {
    type: Boolean,
    default: true
  },
  validateOnChange: {
    type: Boolean,
    default: false
  },
  validateOnBlur: {
    type: Boolean,
    default: true
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  layout: {
    type: String,
    default: 'vertical',
    validator: (value) => ['vertical', 'horizontal', 'inline'].includes(value)
  },
  spacing: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  showActions: {
    type: Boolean,
    default: true
  },
  showCancelButton: {
    type: Boolean,
    default: false
  },
  showResetButton: {
    type: Boolean,
    default: false
  },
  submitText: {
    type: String,
    default: 'Enviar'
  },
  cancelText: {
    type: String,
    default: 'Cancelar'
  },
  resetText: {
    type: String,
    default: 'Limpiar'
  },
  loadingText: {
    type: String,
    default: 'Enviando...'
  },
  submitVariant: {
    type: String,
    default: 'primary'
  },
  buttonSize: {
    type: String,
    default: 'md'
  },
  showErrorSummary: {
    type: Boolean,
    default: false
  },
  errorSummaryTitle: {
    type: String,
    default: 'Por favor corrige los siguientes errores:'
  },
  fieldLabels: {
    type: Object,
    default: () => ({})
  }
})

// Emits
const emit = defineEmits(['submit', 'cancel', 'reset', 'validation-changed'])

// Reactive data
const errors = ref({})
const fields = ref({})
const formElement = ref(null)

// Computed
const hasErrors = computed(() => {
  return Object.keys(errors.value).length > 0
})

const isValid = computed(() => {
  return !hasErrors.value
})

const formClasses = computed(() => {
  const classes = ['base-form']
  
  // Size
  classes.push(`form-${props.size}`)
  
  // Layout
  classes.push(`form-${props.layout}`)
  
  // Spacing
  classes.push(`form-spacing-${props.spacing}`)
  
  // States
  if (props.disabled) classes.push('form-disabled')
  if (props.loading) classes.push('form-loading')
  if (hasErrors.value) classes.push('form-has-errors')
  
  return classes
})

const fieldsetClasses = computed(() => {
  const classes = ['form-fieldset']
  
  return classes
})

const contentClasses = computed(() => {
  const classes = ['form-content-base']
  
  return classes
})

const actionsClasses = computed(() => {
  const classes = ['form-actions-base']
  
  return classes
})

// Methods
const handleSubmit = async (event) => {
  event.preventDefault()
  
  if (props.validateOnSubmit) {
    await validateAll()
  }
  
  if (!hasErrors.value) {
    const formData = new FormData(event.target)
    const data = Object.fromEntries(formData.entries())
    
    emit('submit', {
      event,
      data,
      formData,
      isValid: isValid.value,
      errors: errors.value
    })
  }
}

const handleReset = (event) => {
  clearErrors()
  emit('reset', event)
}

const handleCancel = (event) => {
  emit('cancel', event)
}

const registerField = (fieldName, fieldData) => {
  fields.value[fieldName] = fieldData
}

const unregisterField = (fieldName) => {
  delete fields.value[fieldName]
  delete errors.value[fieldName]
}

const setFieldError = (fieldName, error) => {
  if (error) {
    errors.value[fieldName] = error
  } else {
    delete errors.value[fieldName]
  }
  
  emit('validation-changed', {
    field: fieldName,
    error,
    errors: errors.value,
    isValid: isValid.value
  })
}

const clearFieldError = (fieldName) => {
  setFieldError(fieldName, null)
}

const clearErrors = () => {
  errors.value = {}
  emit('validation-changed', {
    field: null,
    error: null,
    errors: errors.value,
    isValid: isValid.value
  })
}

const validateField = async (fieldName) => {
  const field = fields.value[fieldName]
  if (!field || !field.validate) return true
  
  try {
    const isValid = await field.validate()
    if (isValid) {
      clearFieldError(fieldName)
    }
    return isValid
  } catch (error) {
    setFieldError(fieldName, error.message || error)
    return false
  }
}

const validateAll = async () => {
  const validationPromises = Object.keys(fields.value).map(fieldName => 
    validateField(fieldName)
  )
  
  const results = await Promise.all(validationPromises)
  return results.every(result => result)
}

const focusField = (fieldName) => {
  const field = fields.value[fieldName]
  if (field && field.focus) {
    nextTick(() => {
      field.focus()
    })
  }
}

const getFieldLabel = (fieldName) => {
  return props.fieldLabels[fieldName] || fieldName
}

const setErrors = (newErrors) => {
  errors.value = { ...newErrors }
  emit('validation-changed', {
    field: null,
    error: null,
    errors: errors.value,
    isValid: isValid.value
  })
}

const reset = () => {
  if (formElement.value) {
    formElement.value.reset()
  }
  clearErrors()
}

const submit = () => {
  if (formElement.value) {
    formElement.value.requestSubmit()
  }
}

// Provide form context to child components
provide('form', {
  registerField,
  unregisterField,
  setFieldError,
  clearFieldError,
  validateField,
  validateOnChange: props.validateOnChange,
  validateOnBlur: props.validateOnBlur,
  disabled: props.disabled,
  size: props.size
})

// Expose methods
defineExpose({
  validateField,
  validateAll,
  setFieldError,
  clearFieldError,
  clearErrors,
  setErrors,
  focusField,
  reset,
  submit,
  hasErrors,
  isValid,
  errors
})
</script>

<style scoped>
/* Base form styles */
.base-form {
  width: 100%;
}

.form-fieldset {
  border: none;
  margin: 0;
  padding: 0;
  min-width: 0;
}

.form-legend {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--color-text-900);
  margin-bottom: 1rem;
  padding: 0;
}

/* Form header */
.form-header {
  margin-bottom: 1.5rem;
}

.form-title {
  margin: 0 0 0.5rem 0;
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--color-text-900);
}

.form-description {
  margin: 0;
  font-size: 0.875rem;
  color: var(--color-text-600);
  line-height: 1.5;
}

/* Form content */
.form-content {
  margin-bottom: 1.5rem;
}

.form-content-base {
  display: flex;
  flex-direction: column;
}

/* Layout variants */
.form-vertical .form-content-base {
  gap: 1rem;
}

.form-horizontal .form-content-base {
  gap: 1.5rem;
}

.form-inline .form-content-base {
  flex-direction: row;
  flex-wrap: wrap;
  align-items: end;
  gap: 1rem;
}

/* Spacing variants */
.form-spacing-sm .form-content-base {
  gap: 0.75rem;
}

.form-spacing-md .form-content-base {
  gap: 1rem;
}

.form-spacing-lg .form-content-base {
  gap: 1.5rem;
}

/* Size variants */
.form-sm {
  font-size: 0.875rem;
}

.form-sm .form-title {
  font-size: 1.25rem;
}

.form-sm .form-legend {
  font-size: 1rem;
}

.form-lg {
  font-size: 1rem;
}

.form-lg .form-title {
  font-size: 1.75rem;
}

.form-lg .form-legend {
  font-size: 1.25rem;
}

/* Form actions */
.form-actions {
  display: flex;
  gap: 0.75rem;
  padding-top: 1rem;
  border-top: 1px solid var(--color-border-200);
}

.form-actions-base {
  justify-content: flex-end;
  align-items: center;
}

.form-horizontal .form-actions-base {
  justify-content: flex-start;
}

.form-inline .form-actions-base {
  justify-content: flex-start;
  padding-top: 0;
  border-top: none;
}

/* State styles */
.form-disabled {
  opacity: 0.6;
  pointer-events: none;
}

.form-loading {
  position: relative;
}

.form-loading::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(1px);
  z-index: 10;
  pointer-events: auto;
}

/* Error summary */
.form-error-summary {
  margin-bottom: 1.5rem;
  padding: 1rem;
  background-color: var(--color-error-50);
  border: 1px solid var(--color-error-200);
  border-radius: 0.375rem;
}

.error-summary-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.error-icon {
  width: 1.25rem;
  height: 1.25rem;
  color: var(--color-error-600);
  flex-shrink: 0;
}

.error-summary-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-error-800);
}

.error-summary-list {
  margin: 0;
  padding: 0;
  list-style: none;
}

.error-summary-item {
  margin-bottom: 0.25rem;
}

.error-summary-item:last-child {
  margin-bottom: 0;
}

.error-summary-link {
  display: block;
  padding: 0.25rem 0;
  font-size: 0.875rem;
  color: var(--color-error-700);
  text-decoration: underline;
  background: none;
  border: none;
  cursor: pointer;
  text-align: left;
  width: 100%;
}

.error-summary-link:hover {
  color: var(--color-error-900);
}

.error-summary-link:focus {
  outline: 2px solid var(--color-error-500);
  outline-offset: 2px;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .form-inline .form-content-base {
    flex-direction: column;
    align-items: stretch;
  }
  
  .form-actions-base {
    flex-direction: column-reverse;
    align-items: stretch;
  }
  
  .form-actions-base > * {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .form-horizontal .form-content-base {
    flex-direction: column;
  }
  
  .form-horizontal .form-actions-base {
    flex-direction: column-reverse;
    align-items: stretch;
  }
}
</style>
