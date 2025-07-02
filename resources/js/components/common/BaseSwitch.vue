<template>
  <div class="switch-container">
    <div class="switch-wrapper" :class="wrapperClasses">
      <input
        :id="computedId"
        ref="switchInput"
        type="checkbox"
        :checked="modelValue"
        :disabled="disabled"
        :required="required"
        :name="name"
        :class="switchClasses"
        v-bind="$attrs"
        @change="handleChange"
        @focus="focused = true"
        @blur="focused = false"
      >
      
      <div class="switch-track" :class="trackClasses">
        <div class="switch-thumb" :class="thumbClasses">
          <div v-if="loading" class="switch-loading">
            <svg class="loading-spinner" viewBox="0 0 24 24">
              <circle
                class="loading-circle"
                cx="12"
                cy="12"
                r="10"
                fill="none"
                stroke="currentColor"
                stroke-width="4"
              />
            </svg>
          </div>
          
          <div v-else-if="showIcons" class="switch-icon">
            <svg v-if="modelValue" class="icon-on" viewBox="0 0 20 20" fill="currentColor">
              <path
                fill-rule="evenodd"
                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                clip-rule="evenodd"
              />
            </svg>
            <svg v-else class="icon-off" viewBox="0 0 20 20" fill="currentColor">
              <path
                fill-rule="evenodd"
                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
        </div>
        
        <div v-if="showLabels" class="switch-labels">
          <span class="label-off" :class="{ 'label-active': !modelValue }">
            {{ offLabel }}
          </span>
          <span class="label-on" :class="{ 'label-active': modelValue }">
            {{ onLabel }}
          </span>
        </div>
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
    
    <div v-if="description || $slots.description" class="switch-description">
      <slot name="description">{{ description }}</slot>
    </div>
    
    <div v-if="error" class="switch-error">
      {{ error }}
    </div>
  </div>
</template>

<script setup>
import { computed, ref, useAttrs } from 'vue'

// Props
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
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
  loading: {
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
  showIcons: {
    type: Boolean,
    default: false
  },
  showLabels: {
    type: Boolean,
    default: false
  },
  onLabel: {
    type: String,
    default: 'ON'
  },
  offLabel: {
    type: String,
    default: 'OFF'
  }
})

// Emits
const emit = defineEmits(['update:modelValue', 'change'])

// Reactive data
const focused = ref(false)
const switchInput = ref(null)

// Attrs
const attrs = useAttrs()

// Computed
const computedId = computed(() => {
  return props.id || `switch-${Math.random().toString(36).substr(2, 9)}`
})

const wrapperClasses = computed(() => {
  const classes = ['switch-base']
  
  // Size
  classes.push(`switch-${props.size}`)
  
  // Variant
  classes.push(`switch-${props.variant}`)
  
  // States
  if (props.disabled) classes.push('switch-disabled')
  if (props.loading) classes.push('switch-loading-state')
  if (focused.value) classes.push('switch-focused')
  if (props.error) classes.push('switch-error-state')
  if (props.modelValue) classes.push('switch-on')
  else classes.push('switch-off')
  
  return classes
})

const switchClasses = computed(() => {
  return ['switch-input']
})

const trackClasses = computed(() => {
  const classes = ['switch-track-base']
  
  return classes
})

const thumbClasses = computed(() => {
  const classes = ['switch-thumb-base']
  
  return classes
})

const labelClasses = computed(() => {
  const classes = ['switch-label']
  
  if (props.disabled) classes.push('switch-label-disabled')
  
  return classes
})

// Methods
const handleChange = async (event) => {
  if (props.disabled || props.loading) return
  
  const newValue = event.target.checked
  emit('update:modelValue', newValue)
  emit('change', newValue)
}

// Focus method
const focus = () => {
  switchInput.value?.focus()
}

// Blur method
const blur = () => {
  switchInput.value?.blur()
}

// Toggle method
const toggle = () => {
  if (!props.disabled && !props.loading) {
    const newValue = !props.modelValue
    emit('update:modelValue', newValue)
    emit('change', newValue)
  }
}

// Expose methods
defineExpose({
  focus,
  blur,
  toggle
})
</script>

<style scoped>
/* Base switch styles */
.switch-container {
  width: 100%;
}

.switch-wrapper {
  position: relative;
  display: flex;
  align-items: flex-start;
}

.switch-base {
  position: relative;
}

.switch-input {
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

.switch-label {
  margin-left: 0.75rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  user-select: none;
  color: var(--color-text-700);
}

.switch-label-disabled {
  color: var(--color-text-400);
  cursor: not-allowed;
}

.switch-description {
  margin-top: 0.25rem;
  margin-left: 3rem;
  font-size: 0.875rem;
  color: var(--color-text-500);
}

.switch-error {
  margin-top: 0.25rem;
  margin-left: 3rem;
  font-size: 0.875rem;
  color: var(--color-error-600);
}

.required-indicator {
  color: var(--color-error-500);
  margin-left: 0.25rem;
}

/* Switch track */
.switch-track {
  position: relative;
  display: flex;
  align-items: center;
  border-radius: 9999px;
  transition: all 0.2s ease;
  cursor: pointer;
  background-color: var(--color-border-300);
}

.switch-track-base {
  background-color: var(--color-border-300);
}

/* Switch thumb */
.switch-thumb {
  position: absolute;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background-color: white;
  box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
  transform: translateX(0);
}

.switch-thumb-base {
  background-color: white;
}

/* Switch labels */
.switch-labels {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
  pointer-events: none;
}

.label-off,
.label-on {
  opacity: 0.6;
  transition: opacity 0.2s ease;
}

.label-active {
  opacity: 1;
}

/* Switch icon */
.switch-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-text-600);
}

.icon-on,
.icon-off {
  width: 0.75rem;
  height: 0.75rem;
}

/* Loading spinner */
.switch-loading {
  display: flex;
  align-items: center;
  justify-content: center;
}

.loading-spinner {
  width: 0.75rem;
  height: 0.75rem;
  animation: spin 1s linear infinite;
}

.loading-circle {
  stroke-dasharray: 31.416;
  stroke-dashoffset: 31.416;
  animation: loading 2s ease-in-out infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@keyframes loading {
  0% { stroke-dasharray: 0 31.416; }
  50% { stroke-dasharray: 15.708 15.708; }
  100% { stroke-dasharray: 31.416 0; }
}

/* Size variants */
.switch-sm .switch-track {
  width: 2.5rem;
  height: 1.25rem;
}

.switch-sm .switch-thumb {
  width: 1rem;
  height: 1rem;
  left: 0.125rem;
}

.switch-sm.switch-on .switch-thumb {
  transform: translateX(1.25rem);
}

.switch-sm .switch-label {
  font-size: 0.75rem;
  margin-left: 0.5rem;
}

.switch-sm .switch-description,
.switch-sm .switch-error {
  margin-left: 3.25rem;
  font-size: 0.75rem;
}

.switch-md .switch-track {
  width: 3rem;
  height: 1.5rem;
}

.switch-md .switch-thumb {
  width: 1.25rem;
  height: 1.25rem;
  left: 0.125rem;
}

.switch-md.switch-on .switch-thumb {
  transform: translateX(1.5rem);
}

.switch-lg .switch-track {
  width: 3.5rem;
  height: 1.75rem;
}

.switch-lg .switch-thumb {
  width: 1.5rem;
  height: 1.5rem;
  left: 0.125rem;
}

.switch-lg.switch-on .switch-thumb {
  transform: translateX(1.75rem);
}

.switch-lg .switch-label {
  font-size: 1rem;
  margin-left: 1rem;
}

.switch-lg .switch-description,
.switch-lg .switch-error {
  margin-left: 4.75rem;
}

/* Color variants - ON state */
.switch-primary.switch-on .switch-track {
  background-color: var(--color-primary-600);
}

.switch-secondary.switch-on .switch-track {
  background-color: var(--color-secondary-600);
}

.switch-success.switch-on .switch-track {
  background-color: var(--color-success-600);
}

.switch-warning.switch-on .switch-track {
  background-color: var(--color-warning-600);
}

.switch-danger.switch-on .switch-track {
  background-color: var(--color-error-600);
}

/* Focus state */
.switch-focused .switch-track {
  box-shadow: 0 0 0 2px var(--color-primary-500), 0 0 0 4px rgba(59, 130, 246, 0.1);
}

/* Disabled state */
.switch-disabled .switch-track {
  background-color: var(--color-bg-100);
  cursor: not-allowed;
}

.switch-disabled .switch-thumb {
  background-color: var(--color-bg-200);
}

.switch-disabled .switch-label {
  cursor: not-allowed;
}

/* Loading state */
.switch-loading-state .switch-track {
  cursor: wait;
}

/* Error state */
.switch-error-state .switch-track {
  border: 2px solid var(--color-error-500);
}

/* Hover effects */
.switch-wrapper:hover:not(.switch-disabled):not(.switch-loading-state) .switch-track {
  background-color: var(--color-border-400);
}

.switch-primary.switch-wrapper:hover:not(.switch-disabled):not(.switch-loading-state).switch-on .switch-track {
  background-color: var(--color-primary-700);
}

.switch-secondary.switch-wrapper:hover:not(.switch-disabled):not(.switch-loading-state).switch-on .switch-track {
  background-color: var(--color-secondary-700);
}

.switch-success.switch-wrapper:hover:not(.switch-disabled):not(.switch-loading-state).switch-on .switch-track {
  background-color: var(--color-success-700);
}

.switch-warning.switch-wrapper:hover:not(.switch-disabled):not(.switch-loading-state).switch-on .switch-track {
  background-color: var(--color-warning-700);
}

.switch-danger.switch-wrapper:hover:not(.switch-disabled):not(.switch-loading-state).switch-on .switch-track {
  background-color: var(--color-error-700);
}
</style>
