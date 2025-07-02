<template>
  <div class="checkbox-container">
    <div class="checkbox-wrapper" :class="wrapperClasses">
      <input
        :id="computedId"
        ref="checkbox"
        type="checkbox"
        :checked="modelValue"
        :disabled="disabled"
        :required="required"
        :value="value"
        :name="name"
        :class="checkboxClasses"
        v-bind="$attrs"
        @change="handleChange"
        @focus="focused = true"
        @blur="focused = false"
      >
      
      <label
        v-if="label || $slots.label"
        :for="computedId"
        :class="labelClasses"
      >
        <slot name="label">{{ label }}</slot>
        <span v-if="required" class="required-indicator">*</span>
      </label>
      
      <div v-if="showCheckmark" class="checkmark" :class="checkmarkClasses">
        <svg
          class="checkmark-icon"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
            clip-rule="evenodd"
          />
        </svg>
      </div>
    </div>
    
    <div v-if="description || $slots.description" class="checkbox-description">
      <slot name="description">{{ description }}</slot>
    </div>
    
    <div v-if="error" class="checkbox-error">
      {{ error }}
    </div>
  </div>
</template>

<script setup>
import { computed, ref, useAttrs, watch } from 'vue'

// Props
const props = defineProps({
  modelValue: {
    type: [Boolean, Array],
    default: false
  },
  value: {
    type: [String, Number, Boolean],
    default: null
  },
  label: {
    type: String,
    default: ''
  },
  description: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'success', 'warning', 'danger'].includes(value)
  },
  name: {
    type: String,
    default: ''
  },
  id: {
    type: String,
    default: ''
  },
  indeterminate: {
    type: Boolean,
    default: false
  }
})

// Emits
const emit = defineEmits(['update:modelValue', 'change'])

// Reactive data
const focused = ref(false)
const checkbox = ref(null)

// Attrs
const attrs = useAttrs()

// Computed
const computedId = computed(() => {
  return props.id || `checkbox-${Math.random().toString(36).substr(2, 9)}`
})

const isChecked = computed(() => {
  if (Array.isArray(props.modelValue)) {
    return props.modelValue.includes(props.value)
  }
  return props.modelValue
})

const showCheckmark = computed(() => {
  return isChecked.value && !props.indeterminate
})

const wrapperClasses = computed(() => {
  const classes = ['checkbox-base']
  
  // Size
  classes.push(`checkbox-${props.size}`)
  
  // Variant
  classes.push(`checkbox-${props.variant}`)
  
  // States
  if (props.disabled) classes.push('checkbox-disabled')
  if (focused.value) classes.push('checkbox-focused')
  if (props.error) classes.push('checkbox-error-state')
  if (isChecked.value) classes.push('checkbox-checked')
  if (props.indeterminate) classes.push('checkbox-indeterminate')
  
  return classes
})

const checkboxClasses = computed(() => {
  return ['checkbox-input']
})

const labelClasses = computed(() => {
  const classes = ['checkbox-label']
  
  if (props.disabled) classes.push('checkbox-label-disabled')
  
  return classes
})

const checkmarkClasses = computed(() => {
  const classes = ['checkmark-base']
  
  if (props.indeterminate) classes.push('checkmark-indeterminate')
  
  return classes
})

// Methods
const handleChange = (event) => {
  const checked = event.target.checked
  
  if (Array.isArray(props.modelValue)) {
    const newValue = [...props.modelValue]
    if (checked) {
      if (!newValue.includes(props.value)) {
        newValue.push(props.value)
      }
    } else {
      const index = newValue.indexOf(props.value)
      if (index > -1) {
        newValue.splice(index, 1)
      }
    }
    emit('update:modelValue', newValue)
    emit('change', newValue)
  } else {
    emit('update:modelValue', checked)
    emit('change', checked)
  }
}

// Watch for indeterminate changes
watch(() => props.indeterminate, (newValue) => {
  if (checkbox.value) {
    checkbox.value.indeterminate = newValue
  }
}, { immediate: true })

// Focus method
const focus = () => {
  checkbox.value?.focus()
}

// Blur method
const blur = () => {
  checkbox.value?.blur()
}

// Expose methods
defineExpose({
  focus,
  blur
})
</script>

<style scoped>
/* Base checkbox styles */
.checkbox-container {
  width: 100%;
}

.checkbox-wrapper {
  position: relative;
  display: flex;
  align-items: flex-start;
}

.checkbox-base {
  position: relative;
}

.checkbox-input {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

.checkbox-label {
  margin-left: 0.75rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  user-select: none;
  color: var(--color-text-700);
}

.checkbox-label-disabled {
  color: var(--color-text-400);
  cursor: not-allowed;
}

.checkbox-description {
  margin-top: 0.25rem;
  margin-left: 1.75rem;
  font-size: 0.875rem;
  color: var(--color-text-500);
}

.checkbox-error {
  margin-top: 0.25rem;
  margin-left: 1.75rem;
  font-size: 0.875rem;
  color: var(--color-error-600);
}

.required-indicator {
  color: var(--color-error-500);
  margin-left: 0.25rem;
}

/* Checkmark container */
.checkmark {
  position: absolute;
  left: 0;
  top: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid;
  border-radius: 0.25rem;
  transition: all 0.2s ease;
  background-color: white;
}

.checkmark-base {
  border-color: var(--color-border-300);
}

.checkmark-icon {
  width: 0.75rem;
  height: 0.75rem;
  opacity: 0;
  transition: opacity 0.2s ease;
  color: white;
}

/* Size variants */
.checkbox-sm .checkmark {
  width: 1rem;
  height: 1rem;
}

.checkbox-sm .checkmark-icon {
  width: 0.625rem;
  height: 0.625rem;
}

.checkbox-sm .checkbox-label {
  font-size: 0.75rem;
  margin-left: 0.5rem;
}

.checkbox-sm .checkbox-description,
.checkbox-sm .checkbox-error {
  margin-left: 1.5rem;
  font-size: 0.75rem;
}

.checkbox-md .checkmark {
  width: 1.25rem;
  height: 1.25rem;
}

.checkbox-md .checkmark-icon {
  width: 0.75rem;
  height: 0.75rem;
}

.checkbox-lg .checkmark {
  width: 1.5rem;
  height: 1.5rem;
}

.checkbox-lg .checkmark-icon {
  width: 1rem;
  height: 1rem;
}

.checkbox-lg .checkbox-label {
  font-size: 1rem;
  margin-left: 1rem;
}

.checkbox-lg .checkbox-description,
.checkbox-lg .checkbox-error {
  margin-left: 2.5rem;
}

/* Color variants */
.checkbox-primary.checkbox-checked .checkmark {
  background-color: var(--color-primary-600);
  border-color: var(--color-primary-600);
}

.checkbox-secondary.checkbox-checked .checkmark {
  background-color: var(--color-secondary-600);
  border-color: var(--color-secondary-600);
}

.checkbox-success.checkbox-checked .checkmark {
  background-color: var(--color-success-600);
  border-color: var(--color-success-600);
}

.checkbox-warning.checkbox-checked .checkmark {
  background-color: var(--color-warning-600);
  border-color: var(--color-warning-600);
}

.checkbox-danger.checkbox-checked .checkmark {
  background-color: var(--color-error-600);
  border-color: var(--color-error-600);
}

/* Checked state */
.checkbox-checked .checkmark-icon {
  opacity: 1;
}

/* Indeterminate state */
.checkbox-indeterminate .checkmark {
  background-color: var(--color-primary-600);
  border-color: var(--color-primary-600);
}

.checkbox-indeterminate .checkmark::after {
  content: '';
  position: absolute;
  width: 0.5rem;
  height: 0.125rem;
  background-color: white;
  border-radius: 0.125rem;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
}

/* Focus state */
.checkbox-focused .checkmark {
  box-shadow: 0 0 0 2px var(--color-primary-500), 0 0 0 4px rgba(59, 130, 246, 0.1);
}

/* Disabled state */
.checkbox-disabled .checkmark {
  background-color: var(--color-bg-100);
  border-color: var(--color-border-300);
  cursor: not-allowed;
}

.checkbox-disabled.checkbox-checked .checkmark {
  background-color: var(--color-text-400);
  border-color: var(--color-text-400);
}

.checkbox-disabled .checkbox-label {
  cursor: not-allowed;
}

/* Error state */
.checkbox-error-state .checkmark {
  border-color: var(--color-error-500);
}

.checkbox-error-state.checkbox-checked .checkmark {
  background-color: var(--color-error-600);
  border-color: var(--color-error-600);
}

/* Hover effects */
.checkbox-wrapper:hover:not(.checkbox-disabled) .checkmark {
  border-color: var(--color-border-400);
}

.checkbox-primary.checkbox-wrapper:hover:not(.checkbox-disabled).checkbox-checked .checkmark {
  background-color: var(--color-primary-700);
  border-color: var(--color-primary-700);
}

.checkbox-secondary.checkbox-wrapper:hover:not(.checkbox-disabled).checkbox-checked .checkmark {
  background-color: var(--color-secondary-700);
  border-color: var(--color-secondary-700);
}

.checkbox-success.checkbox-wrapper:hover:not(.checkbox-disabled).checkbox-checked .checkmark {
  background-color: var(--color-success-700);
  border-color: var(--color-success-700);
}

.checkbox-warning.checkbox-wrapper:hover:not(.checkbox-disabled).checkbox-checked .checkmark {
  background-color: var(--color-warning-700);
  border-color: var(--color-warning-700);
}

.checkbox-danger.checkbox-wrapper:hover:not(.checkbox-disabled).checkbox-checked .checkmark {
  background-color: var(--color-error-700);
  border-color: var(--color-error-700);
}
</style>
