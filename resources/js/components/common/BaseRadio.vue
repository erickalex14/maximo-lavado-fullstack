<template>
  <div class="radio-container">
    <div class="radio-wrapper" :class="wrapperClasses">
      <input
        :id="computedId"
        ref="radio"
        type="radio"
        :checked="isChecked"
        :disabled="disabled"
        :required="required"
        :value="value"
        :name="name"
        :class="radioClasses"
        v-bind="$attrs"
        @change="handleChange"
        @focus="focused = true"
        @blur="focused = false"
      >
      
      <div class="radio-button" :class="radioButtonClasses">
        <div v-if="isChecked" class="radio-dot" :class="radioDotClasses"></div>
      </div>
      
      <label
        v-if="label || $slots.label"
        :for="computedId"
        :class="labelClasses"
      >
        <slot name="label">{{ label }}</slot>
        <span v-if="required" class="required-indicator">*</span>
      </label>
    </div>
    
    <div v-if="description || $slots.description" class="radio-description">
      <slot name="description">{{ description }}</slot>
    </div>
    
    <div v-if="error" class="radio-error">
      {{ error }}
    </div>
  </div>
</template>

<script setup>
import { computed, ref, useAttrs } from 'vue'

// Props
const props = defineProps({
  modelValue: {
    type: [String, Number, Boolean],
    default: null
  },
  value: {
    type: [String, Number, Boolean],
    required: true
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
  }
})

// Emits
const emit = defineEmits(['update:modelValue', 'change'])

// Reactive data
const focused = ref(false)
const radio = ref(null)

// Attrs
const attrs = useAttrs()

// Computed
const computedId = computed(() => {
  return props.id || `radio-${Math.random().toString(36).substr(2, 9)}`
})

const isChecked = computed(() => {
  return props.modelValue === props.value
})

const wrapperClasses = computed(() => {
  const classes = ['radio-base']
  
  // Size
  classes.push(`radio-${props.size}`)
  
  // Variant
  classes.push(`radio-${props.variant}`)
  
  // States
  if (props.disabled) classes.push('radio-disabled')
  if (focused.value) classes.push('radio-focused')
  if (props.error) classes.push('radio-error-state')
  if (isChecked.value) classes.push('radio-checked')
  
  return classes
})

const radioClasses = computed(() => {
  return ['radio-input']
})

const radioButtonClasses = computed(() => {
  const classes = ['radio-button-base']
  
  return classes
})

const radioDotClasses = computed(() => {
  const classes = ['radio-dot-base']
  
  return classes
})

const labelClasses = computed(() => {
  const classes = ['radio-label']
  
  if (props.disabled) classes.push('radio-label-disabled')
  
  return classes
})

// Methods
const handleChange = (event) => {
  if (!props.disabled) {
    emit('update:modelValue', props.value)
    emit('change', props.value)
  }
}

// Focus method
const focus = () => {
  radio.value?.focus()
}

// Blur method
const blur = () => {
  radio.value?.blur()
}

// Expose methods
defineExpose({
  focus,
  blur
})
</script>

<style scoped>
/* Base radio styles */
.radio-container {
  width: 100%;
}

.radio-wrapper {
  position: relative;
  display: flex;
  align-items: flex-start;
}

.radio-base {
  position: relative;
}

.radio-input {
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

.radio-label {
  margin-left: 0.75rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  user-select: none;
  color: var(--color-text-700);
}

.radio-label-disabled {
  color: var(--color-text-400);
  cursor: not-allowed;
}

.radio-description {
  margin-top: 0.25rem;
  margin-left: 1.75rem;
  font-size: 0.875rem;
  color: var(--color-text-500);
}

.radio-error {
  margin-top: 0.25rem;
  margin-left: 1.75rem;
  font-size: 0.875rem;
  color: var(--color-error-600);
}

.required-indicator {
  color: var(--color-error-500);
  margin-left: 0.25rem;
}

/* Radio button container */
.radio-button {
  position: absolute;
  left: 0;
  top: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid;
  border-radius: 50%;
  transition: all 0.2s ease;
  background-color: white;
}

.radio-button-base {
  border-color: var(--color-border-300);
}

.radio-dot {
  border-radius: 50%;
  transition: all 0.2s ease;
  background-color: white;
}

.radio-dot-base {
  background-color: white;
}

/* Size variants */
.radio-sm .radio-button {
  width: 1rem;
  height: 1rem;
}

.radio-sm .radio-dot {
  width: 0.375rem;
  height: 0.375rem;
}

.radio-sm .radio-label {
  font-size: 0.75rem;
  margin-left: 0.5rem;
}

.radio-sm .radio-description,
.radio-sm .radio-error {
  margin-left: 1.5rem;
  font-size: 0.75rem;
}

.radio-md .radio-button {
  width: 1.25rem;
  height: 1.25rem;
}

.radio-md .radio-dot {
  width: 0.5rem;
  height: 0.5rem;
}

.radio-lg .radio-button {
  width: 1.5rem;
  height: 1.5rem;
}

.radio-lg .radio-dot {
  width: 0.625rem;
  height: 0.625rem;
}

.radio-lg .radio-label {
  font-size: 1rem;
  margin-left: 1rem;
}

.radio-lg .radio-description,
.radio-lg .radio-error {
  margin-left: 2.5rem;
}

/* Color variants */
.radio-primary.radio-checked .radio-button {
  background-color: var(--color-primary-600);
  border-color: var(--color-primary-600);
}

.radio-secondary.radio-checked .radio-button {
  background-color: var(--color-secondary-600);
  border-color: var(--color-secondary-600);
}

.radio-success.radio-checked .radio-button {
  background-color: var(--color-success-600);
  border-color: var(--color-success-600);
}

.radio-warning.radio-checked .radio-button {
  background-color: var(--color-warning-600);
  border-color: var(--color-warning-600);
}

.radio-danger.radio-checked .radio-button {
  background-color: var(--color-error-600);
  border-color: var(--color-error-600);
}

/* Focus state */
.radio-focused .radio-button {
  box-shadow: 0 0 0 2px var(--color-primary-500), 0 0 0 4px rgba(59, 130, 246, 0.1);
}

/* Disabled state */
.radio-disabled .radio-button {
  background-color: var(--color-bg-100);
  border-color: var(--color-border-300);
  cursor: not-allowed;
}

.radio-disabled.radio-checked .radio-button {
  background-color: var(--color-text-400);
  border-color: var(--color-text-400);
}

.radio-disabled .radio-label {
  cursor: not-allowed;
}

/* Error state */
.radio-error-state .radio-button {
  border-color: var(--color-error-500);
}

.radio-error-state.radio-checked .radio-button {
  background-color: var(--color-error-600);
  border-color: var(--color-error-600);
}

/* Hover effects */
.radio-wrapper:hover:not(.radio-disabled) .radio-button {
  border-color: var(--color-border-400);
}

.radio-primary.radio-wrapper:hover:not(.radio-disabled).radio-checked .radio-button {
  background-color: var(--color-primary-700);
  border-color: var(--color-primary-700);
}

.radio-secondary.radio-wrapper:hover:not(.radio-disabled).radio-checked .radio-button {
  background-color: var(--color-secondary-700);
  border-color: var(--color-secondary-700);
}

.radio-success.radio-wrapper:hover:not(.radio-disabled).radio-checked .radio-button {
  background-color: var(--color-success-700);
  border-color: var(--color-success-700);
}

.radio-warning.radio-wrapper:hover:not(.radio-disabled).radio-checked .radio-button {
  background-color: var(--color-warning-700);
  border-color: var(--color-warning-700);
}

.radio-danger.radio-wrapper:hover:not(.radio-disabled).radio-checked .radio-button {
  background-color: var(--color-error-700);
  border-color: var(--color-error-700);
}
</style>
