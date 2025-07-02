<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="buttonClasses"
    @click="handleClick"
    :aria-label="ariaLabel"
    :aria-describedby="ariaDescribedby"
  >
    <!-- Loading Spinner -->
    <div v-if="loading" class="flex items-center justify-center">
      <svg 
        class="animate-spin -ml-1 mr-2 h-4 w-4" 
        xmlns="http://www.w3.org/2000/svg" 
        fill="none" 
        viewBox="0 0 24 24"
        aria-hidden="true"
      >
        <circle 
          class="opacity-25" 
          cx="12" 
          cy="12" 
          r="10" 
          stroke="currentColor" 
          stroke-width="4"
        ></circle>
        <path 
          class="opacity-75" 
          fill="currentColor" 
          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
        ></path>
      </svg>
      <span>{{ loadingText }}</span>
    </div>

    <!-- Icon + Text Content -->
    <div v-else class="flex items-center justify-center">
      <!-- Leading Icon -->
      <component 
        v-if="iconLeft" 
        :is="iconLeft" 
        :class="iconLeftClasses"
        aria-hidden="true"
      />
      
      <!-- Button Text -->
      <span v-if="$slots.default" :class="textClasses">
        <slot></slot>
      </span>
      
      <!-- Trailing Icon -->
      <component 
        v-if="iconRight" 
        :is="iconRight" 
        :class="iconRightClasses"
        aria-hidden="true"
      />
    </div>
  </button>
</template>

<script setup>
import { computed } from 'vue'

// PROPS
const props = defineProps({
  // TIPO DE BOTÓN
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'success', 'warning', 'danger', 'info', 'ghost', 'link'].includes(value)
  },
  
  // TAMAÑO DEL BOTÓN
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  
  // TIPO DE ELEMENTO HTML
  type: {
    type: String,
    default: 'button',
    validator: (value) => ['button', 'submit', 'reset'].includes(value)
  },
  
  // ESTADOS
  disabled: {
    type: Boolean,
    default: false
  },
  
  loading: {
    type: Boolean,
    default: false
  },
  
  // ICONOS
  iconLeft: {
    type: [String, Object],
    default: null
  },
  
  iconRight: {
    type: [String, Object],
    default: null
  },
  
  // SOLO ICONO (SIN TEXTO)
  iconOnly: {
    type: Boolean,
    default: false
  },
  
  // ANCHO COMPLETO
  fullWidth: {
    type: Boolean,
    default: false
  },
  
  // BORDES REDONDEADOS
  rounded: {
    type: String,
    default: 'md',
    validator: (value) => ['none', 'sm', 'md', 'lg', 'full'].includes(value)
  },
  
  // ACCESIBILIDAD
  ariaLabel: {
    type: String,
    default: null
  },
  
  ariaDescribedby: {
    type: String,
    default: null
  },
  
  // TEXTO DURANTE CARGA
  loadingText: {
    type: String,
    default: 'Cargando...'
  }
})

// EVENTS
const emit = defineEmits(['click'])

// COMPUTED CLASSES

// CLASES BASE DEL BOTÓN
const buttonClasses = computed(() => {
  const baseClasses = [
    'inline-flex',
    'items-center',
    'justify-center',
    'font-medium',
    'transition-all',
    'duration-200',
    'ease-in-out',
    'focus:outline-none',
    'focus:ring-2',
    'focus:ring-offset-2',
    'disabled:opacity-50',
    'disabled:cursor-not-allowed',
    'disabled:pointer-events-none'
  ]
  
  // ANCHO
  if (props.fullWidth) {
    baseClasses.push('w-full')
  }
  
  // TAMAÑOS
  const sizeClasses = {
    xs: 'px-2 py-1 text-xs min-h-[24px]',
    sm: 'px-3 py-1.5 text-sm min-h-[32px]',
    md: 'px-4 py-2 text-sm min-h-[40px]',
    lg: 'px-6 py-3 text-base min-h-[48px]',
    xl: 'px-8 py-4 text-lg min-h-[56px]'
  }
  
  // PARA BOTONES SOLO ICONO
  const iconOnlySizes = {
    xs: 'p-1 w-6 h-6',
    sm: 'p-1.5 w-8 h-8',
    md: 'p-2 w-10 h-10',
    lg: 'p-3 w-12 h-12',
    xl: 'p-4 w-14 h-14'
  }
  
  if (props.iconOnly) {
    baseClasses.push(iconOnlySizes[props.size])
  } else {
    baseClasses.push(sizeClasses[props.size])
  }
  
  // BORDES REDONDEADOS
  const roundedClasses = {
    none: 'rounded-none',
    sm: 'rounded-sm',
    md: 'rounded-md',
    lg: 'rounded-lg',
    full: 'rounded-full'
  }
  baseClasses.push(roundedClasses[props.rounded])
  
  // VARIANTES DE COLOR
  const variantClasses = {
    primary: [
      'bg-blue-600',
      'hover:bg-blue-700',
      'active:bg-blue-800',
      'text-white',
      'border',
      'border-blue-600',
      'focus:ring-blue-500',
      'shadow-sm',
      'hover:shadow-md'
    ],
    secondary: [
      'bg-gray-100',
      'hover:bg-gray-200',
      'active:bg-gray-300',
      'text-gray-900',
      'border',
      'border-gray-300',
      'focus:ring-gray-500',
      'shadow-sm',
      'hover:shadow-md'
    ],
    success: [
      'bg-green-600',
      'hover:bg-green-700',
      'active:bg-green-800',
      'text-white',
      'border',
      'border-green-600',
      'focus:ring-green-500',
      'shadow-sm',
      'hover:shadow-md'
    ],
    warning: [
      'bg-yellow-500',
      'hover:bg-yellow-600',
      'active:bg-yellow-700',
      'text-white',
      'border',
      'border-yellow-500',
      'focus:ring-yellow-500',
      'shadow-sm',
      'hover:shadow-md'
    ],
    danger: [
      'bg-red-600',
      'hover:bg-red-700',
      'active:bg-red-800',
      'text-white',
      'border',
      'border-red-600',
      'focus:ring-red-500',
      'shadow-sm',
      'hover:shadow-md'
    ],
    info: [
      'bg-cyan-600',
      'hover:bg-cyan-700',
      'active:bg-cyan-800',
      'text-white',
      'border',
      'border-cyan-600',
      'focus:ring-cyan-500',
      'shadow-sm',
      'hover:shadow-md'
    ],
    ghost: [
      'bg-transparent',
      'hover:bg-gray-100',
      'active:bg-gray-200',
      'text-gray-700',
      'border',
      'border-transparent',
      'focus:ring-gray-500'
    ],
    link: [
      'bg-transparent',
      'hover:bg-transparent',
      'text-blue-600',
      'hover:text-blue-800',
      'underline',
      'border-none',
      'focus:ring-blue-500',
      'shadow-none'
    ]
  }
  
  baseClasses.push(...variantClasses[props.variant])
  
  return baseClasses.join(' ')
})

// CLASES PARA ICONOS
const iconLeftClasses = computed(() => {
  const classes = ['flex-shrink-0']
  
  if (!props.iconOnly && props.$slots.default) {
    const spacing = {
      xs: 'mr-1',
      sm: 'mr-1.5',
      md: 'mr-2',
      lg: 'mr-2.5',
      xl: 'mr-3'
    }
    classes.push(spacing[props.size])
  }
  
  const iconSizes = {
    xs: 'w-3 h-3',
    sm: 'w-4 h-4',
    md: 'w-5 h-5',
    lg: 'w-5 h-5',
    xl: 'w-6 h-6'
  }
  classes.push(iconSizes[props.size])
  
  return classes.join(' ')
})

const iconRightClasses = computed(() => {
  const classes = ['flex-shrink-0']
  
  if (!props.iconOnly && props.$slots.default) {
    const spacing = {
      xs: 'ml-1',
      sm: 'ml-1.5',
      md: 'ml-2',
      lg: 'ml-2.5',
      xl: 'ml-3'
    }
    classes.push(spacing[props.size])
  }
  
  const iconSizes = {
    xs: 'w-3 h-3',
    sm: 'w-4 h-4',
    md: 'w-5 h-5',
    lg: 'w-5 h-5',
    xl: 'w-6 h-6'
  }
  classes.push(iconSizes[props.size])
  
  return classes.join(' ')
})

const textClasses = computed(() => {
  if (props.iconOnly) return 'sr-only' // Screen reader only
  return 'truncate'
})

// METHODS
const handleClick = (event) => {
  if (!props.disabled && !props.loading) {
    emit('click', event)
  }
}
</script>

<style scoped>
/* CUSTOM FOCUS STYLES PARA ACCESIBILIDAD MEJORADA */
button:focus-visible {
  outline: 2px solid transparent;
  outline-offset: 2px;
}

/* ANIMACIONES SUAVES */
button {
  transform: translateY(0);
  transition: transform 0.1s ease-in-out;
}

button:active:not(:disabled) {
  transform: translateY(1px);
}
</style>
