<template>
  <div class="form-group" :class="{ 'form-group--error': hasError }">
    <!-- LABEL -->
    <label 
      v-if="label" 
      :for="inputId" 
      class="form-label"
      :class="{ 'text-red-700': hasError }"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1" aria-label="Requerido">*</span>
    </label>

    <!-- INPUT CONTAINER -->
    <div class="relative">
      <!-- LEADING ICON -->
      <div 
        v-if="iconLeft" 
        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
      >
        <component 
          :is="iconLeft" 
          class="h-5 w-5 text-gray-400"
          :class="{ 'text-red-500': hasError }"
          aria-hidden="true" 
        />
      </div>

      <!-- INPUT ELEMENT -->
      <input
        :id="inputId"
        ref="inputRef"
        v-model="inputValue"
        :type="inputType"
        :name="name"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :autocomplete="autocomplete"
        :min="min"
        :max="max"
        :step="step"
        :maxlength="maxlength"
        :minlength="minlength"
        :pattern="pattern"
        :class="inputClasses"
        :aria-describedby="ariaDescribedby"
        :aria-invalid="hasError"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
        @keydown.enter="handleEnter"
        @keydown.escape="handleEscape"
      />

      <!-- TRAILING ICON / CLEAR BUTTON -->
      <div 
        v-if="iconRight || (clearable && inputValue)" 
        class="absolute inset-y-0 right-0 pr-3 flex items-center"
      >
        <!-- CLEAR BUTTON -->
        <button
          v-if="clearable && inputValue && !readonly && !disabled"
          type="button"
          class="text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600 transition-colors duration-200"
          @click="clearInput"
          :aria-label="`Limpiar ${label || 'campo'}`"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <!-- TRAILING ICON -->
        <component 
          v-else-if="iconRight" 
          :is="iconRight" 
          class="h-5 w-5 text-gray-400"
          :class="{ 'text-red-500': hasError }"
          aria-hidden="true" 
        />
      </div>

      <!-- LOADING SPINNER -->
      <div 
        v-if="loading" 
        class="absolute inset-y-0 right-0 pr-3 flex items-center"
      >
        <svg 
          class="animate-spin h-5 w-5 text-gray-400" 
          xmlns="http://www.w3.org/2000/svg" 
          fill="none" 
          viewBox="0 0 24 24"
          aria-hidden="true"
        >
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>
    </div>

    <!-- HELP TEXT / ERROR MESSAGE -->
    <div v-if="helpText || hasError" class="mt-1 space-y-1">
      <!-- ERROR MESSAGE -->
      <p 
        v-if="hasError" 
        :id="`${inputId}-error`"
        class="form-error"
        role="alert"
      >
        {{ errorMessage }}
      </p>
      
      <!-- HELP TEXT -->
      <p 
        v-else-if="helpText" 
        :id="`${inputId}-help`"
        class="form-help"
      >
        {{ helpText }}
      </p>
    </div>

    <!-- CHARACTER COUNT -->
    <div 
      v-if="showCharacterCount && maxlength" 
      class="mt-1 text-right"
    >
      <span 
        class="text-xs"
        :class="{
          'text-gray-500': characterCount <= maxlength * 0.8,
          'text-yellow-600': characterCount > maxlength * 0.8 && characterCount < maxlength,
          'text-red-600': characterCount >= maxlength
        }"
      >
        {{ characterCount }}/{{ maxlength }}
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, nextTick, watch } from 'vue'

// PROPS
const props = defineProps({
  // MODELO DE DATOS
  modelValue: {
    type: [String, Number],
    default: ''
  },
  
  // CONFIGURACIÓN BÁSICA
  type: {
    type: String,
    default: 'text',
    validator: (value) => [
      'text', 'email', 'password', 'number', 'tel', 'url', 'search', 
      'date', 'time', 'datetime-local', 'month', 'week'
    ].includes(value)
  },
  
  name: {
    type: String,
    default: null
  },
  
  label: {
    type: String,
    default: null
  },
  
  placeholder: {
    type: String,
    default: null
  },
  
  // VALIDACIÓN
  required: {
    type: Boolean,
    default: false
  },
  
  disabled: {
    type: Boolean,
    default: false
  },
  
  readonly: {
    type: Boolean,
    default: false
  },
  
  // ATRIBUTOS HTML
  autocomplete: {
    type: String,
    default: null
  },
  
  min: {
    type: [String, Number],
    default: null
  },
  
  max: {
    type: [String, Number],
    default: null
  },
  
  step: {
    type: [String, Number],
    default: null
  },
  
  maxlength: {
    type: Number,
    default: null
  },
  
  minlength: {
    type: Number,
    default: null
  },
  
  pattern: {
    type: String,
    default: null
  },
  
  // ICONOS Y DECORACIÓN
  iconLeft: {
    type: [String, Object],
    default: null
  },
  
  iconRight: {
    type: [String, Object],
    default: null
  },
  
  clearable: {
    type: Boolean,
    default: false
  },
  
  // ESTADOS
  loading: {
    type: Boolean,
    default: false
  },
  
  // TAMAÑO
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  
  // MENSAJES
  helpText: {
    type: String,
    default: null
  },
  
  errorMessage: {
    type: String,
    default: null
  },
  
  // FUNCIONALIDADES ADICIONALES
  showCharacterCount: {
    type: Boolean,
    default: false
  },
  
  // VALIDACIÓN EN TIEMPO REAL
  validateOnBlur: {
    type: Boolean,
    default: true
  },
  
  validateOnInput: {
    type: Boolean,
    default: false
  }
})

// EVENTS
const emit = defineEmits([
  'update:modelValue',
  'input',
  'blur',
  'focus',
  'enter',
  'escape',
  'clear'
])

// REFS
const inputRef = ref(null)

// COMPUTED
const inputId = computed(() => {
  return props.name || `input-${Math.random().toString(36).substr(2, 9)}`
})

const inputValue = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
})

const inputType = computed(() => {
  // Mapeo de tipos para mejor UX
  if (props.type === 'number') {
    return 'text' // Usamos text para mayor control de validación
  }
  return props.type
})

const hasError = computed(() => {
  return Boolean(props.errorMessage)
})

const characterCount = computed(() => {
  return String(props.modelValue || '').length
})

const ariaDescribedby = computed(() => {
  const ids = []
  if (hasError.value) {
    ids.push(`${inputId.value}-error`)
  } else if (props.helpText) {
    ids.push(`${inputId.value}-help`)
  }
  return ids.length > 0 ? ids.join(' ') : null
})

const inputClasses = computed(() => {
  const baseClasses = [
    'form-input',
    'transition-colors',
    'duration-200',
    'ease-in-out'
  ]
  
  // TAMAÑOS
  const sizeClasses = {
    sm: 'px-3 py-1.5 text-sm',
    md: 'px-3 py-2 text-sm',
    lg: 'px-4 py-3 text-base'
  }
  baseClasses.push(sizeClasses[props.size])
  
  // ESPACIADO PARA ICONOS
  if (props.iconLeft) {
    baseClasses.push('pl-10')
  }
  
  if (props.iconRight || props.clearable || props.loading) {
    baseClasses.push('pr-10')
  }
  
  // ESTADOS
  if (hasError.value) {
    baseClasses.push(
      'border-red-300',
      'text-red-900',
      'placeholder-red-300',
      'focus:outline-none',
      'focus:ring-red-500',
      'focus:border-red-500'
    )
  } else {
    baseClasses.push(
      'border-gray-300',
      'placeholder-gray-400',
      'focus:ring-blue-500',
      'focus:border-blue-500'
    )
  }
  
  if (props.disabled) {
    baseClasses.push(
      'disabled:bg-gray-50',
      'disabled:text-gray-500',
      'disabled:cursor-not-allowed'
    )
  }
  
  if (props.readonly) {
    baseClasses.push(
      'bg-gray-50',
      'text-gray-700',
      'cursor-default'
    )
  }
  
  return baseClasses.join(' ')
})

// METHODS
const handleInput = (event) => {
  let value = event.target.value
  
  // Validación específica para números
  if (props.type === 'number') {
    // Permitir solo números, punto decimal y signo negativo
    value = value.replace(/[^0-9.-]/g, '')
    
    // Convertir a número si es válido
    if (value !== '' && !isNaN(value)) {
      value = Number(value)
      
      // Aplicar límites
      if (props.min !== null && value < props.min) {
        value = props.min
      }
      if (props.max !== null && value > props.max) {
        value = props.max
      }
    }
  }
  
  inputValue.value = value
  emit('input', value)
  
  if (props.validateOnInput) {
    // Aquí se podría implementar validación en tiempo real
    validateInput()
  }
}

const handleBlur = (event) => {
  emit('blur', event)
  
  if (props.validateOnBlur) {
    validateInput()
  }
}

const handleFocus = (event) => {
  emit('focus', event)
}

const handleEnter = (event) => {
  emit('enter', event)
}

const handleEscape = (event) => {
  if (inputRef.value) {
    inputRef.value.blur()
  }
  emit('escape', event)
}

const clearInput = () => {
  inputValue.value = ''
  emit('clear')
  
  // Enfocar el input después de limpiar
  nextTick(() => {
    if (inputRef.value) {
      inputRef.value.focus()
    }
  })
}

const focus = () => {
  if (inputRef.value) {
    inputRef.value.focus()
  }
}

const blur = () => {
  if (inputRef.value) {
    inputRef.value.blur()
  }
}

const select = () => {
  if (inputRef.value) {
    inputRef.value.select()
  }
}

const validateInput = () => {
  // Implementar validación personalizada aquí
  // Por ahora solo es un placeholder
  return true
}

// WATCHERS
watch(() => props.modelValue, (newValue) => {
  // Aplicar formato si es necesario
  if (props.type === 'number' && newValue !== '' && !isNaN(newValue)) {
    // Asegurar que esté dentro de los límites
    let numValue = Number(newValue)
    
    if (props.min !== null && numValue < props.min) {
      inputValue.value = props.min
    } else if (props.max !== null && numValue > props.max) {
      inputValue.value = props.max
    }
  }
})

// EXPOSE METHODS
defineExpose({
  focus,
  blur,
  select,
  inputRef
})
</script>

<style scoped>
/* CUSTOM STYLES PARA MEJORAR LA ACCESIBILIDAD */
.form-group--error .form-input:focus {
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

/* TRANSICIONES SUAVES PARA ICONOS */
.form-group svg {
  transition: color var(--transition-fast);
}

/* PLACEHOLDER ANIMATION */
.form-input:focus::placeholder {
  opacity: 0.5;
  transform: translateY(-2px);
  transition: all var(--transition-fast);
}

/* NÚMERO INPUT SIN SPINNER */
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type="number"] {
  appearance: textfield;
  -moz-appearance: textfield;
}
</style>
