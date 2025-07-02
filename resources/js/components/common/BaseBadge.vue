<template>
  <span
    :class="badgeClasses"
    :aria-label="ariaLabel"
    :title="title"
  >
    <!-- LEADING ICON -->
    <component
      v-if="iconLeft"
      :is="iconLeft"
      :class="iconLeftClasses"
      aria-hidden="true"
    />
    
    <!-- CONTENT -->
    <span v-if="$slots.default">
      <slot></slot>
    </span>
    <span v-else-if="text">
      {{ text }}
    </span>
    
    <!-- STATUS INDICATOR DOT -->
    <span
      v-if="showDot"
      :class="dotClasses"
      aria-hidden="true"
    ></span>
    
    <!-- TRAILING ICON -->
    <component
      v-if="iconRight"
      :is="iconRight"
      :class="iconRightClasses"
      aria-hidden="true"
    />
    
    <!-- CLOSE BUTTON -->
    <button
      v-if="closable"
      type="button"
      :class="closeButtonClasses"
      @click="handleClose"
      :aria-label="closeButtonLabel"
    >
      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </span>
</template>

<script setup>
import { computed } from 'vue'

// PROPS
const props = defineProps({
  // CONTENIDO
  text: {
    type: [String, Number],
    default: null
  },
  
  // VARIANTES
  variant: {
    type: String,
    default: 'default',
    validator: (value) => [
      'default', 'primary', 'secondary', 'success', 'warning', 'error', 'info'
    ].includes(value)
  },
  
  // TAMAÑOS
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl'].includes(value)
  },
  
  // FORMAS
  shape: {
    type: String,
    default: 'rounded',
    validator: (value) => ['rounded', 'pill', 'square'].includes(value)
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
  
  // DOT INDICATOR
  showDot: {
    type: Boolean,
    default: false
  },
  
  dotColor: {
    type: String,
    default: null
  },
  
  // INTERACCIONES
  closable: {
    type: Boolean,
    default: false
  },
  
  clickable: {
    type: Boolean,
    default: false
  },
  
  // ESTADOS VISUALES
  outline: {
    type: Boolean,
    default: false
  },
  
  soft: {
    type: Boolean,
    default: false
  },
  
  // ACCESIBILIDAD
  ariaLabel: {
    type: String,
    default: null
  },
  
  title: {
    type: String,
    default: null
  },
  
  closeButtonLabel: {
    type: String,
    default: 'Cerrar'
  }
})

// EVENTS
const emit = defineEmits(['close', 'click'])

// COMPUTED
const badgeClasses = computed(() => {
  const classes = ['badge', 'inline-flex', 'items-center', 'justify-center', 'font-medium', 'transition-all', 'duration-200']
  
  // TAMAÑOS
  const sizeClasses = {
    xs: 'px-1.5 py-0.5 text-xs min-h-[18px]',
    sm: 'px-2 py-0.5 text-xs min-h-[20px]',
    md: 'px-2.5 py-0.5 text-sm min-h-[24px]',
    lg: 'px-3 py-1 text-sm min-h-[28px]',
    xl: 'px-4 py-1.5 text-base min-h-[32px]'
  }
  classes.push(sizeClasses[props.size])
  
  // FORMAS
  const shapeClasses = {
    rounded: 'rounded-md',
    pill: 'rounded-full',
    square: 'rounded-none'
  }
  classes.push(shapeClasses[props.shape])
  
  // VARIANTES DE COLOR
  if (props.outline) {
    // VARIANTES OUTLINE
    const outlineVariants = {
      default: 'border border-gray-300 text-gray-700 bg-transparent hover:bg-gray-50',
      primary: 'border border-blue-300 text-blue-700 bg-transparent hover:bg-blue-50',
      secondary: 'border border-gray-300 text-gray-700 bg-transparent hover:bg-gray-50',
      success: 'border border-green-300 text-green-700 bg-transparent hover:bg-green-50',
      warning: 'border border-yellow-300 text-yellow-700 bg-transparent hover:bg-yellow-50',
      error: 'border border-red-300 text-red-700 bg-transparent hover:bg-red-50',
      info: 'border border-cyan-300 text-cyan-700 bg-transparent hover:bg-cyan-50'
    }
    classes.push(outlineVariants[props.variant])
  } else if (props.soft) {
    // VARIANTES SOFT
    const softVariants = {
      default: 'bg-gray-100 text-gray-800 hover:bg-gray-200',
      primary: 'bg-blue-100 text-blue-800 hover:bg-blue-200',
      secondary: 'bg-gray-100 text-gray-800 hover:bg-gray-200',
      success: 'bg-green-100 text-green-800 hover:bg-green-200',
      warning: 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200',
      error: 'bg-red-100 text-red-800 hover:bg-red-200',
      info: 'bg-cyan-100 text-cyan-800 hover:bg-cyan-200'
    }
    classes.push(softVariants[props.variant])
  } else {
    // VARIANTES SÓLIDAS
    const solidVariants = {
      default: 'bg-gray-500 text-white hover:bg-gray-600',
      primary: 'bg-blue-600 text-white hover:bg-blue-700',
      secondary: 'bg-gray-600 text-white hover:bg-gray-700',
      success: 'bg-green-600 text-white hover:bg-green-700',
      warning: 'bg-yellow-500 text-white hover:bg-yellow-600',
      error: 'bg-red-600 text-white hover:bg-red-700',
      info: 'bg-cyan-600 text-white hover:bg-cyan-700'
    }
    classes.push(solidVariants[props.variant])
  }
  
  // CLICKABLE
  if (props.clickable || props.closable) {
    classes.push('cursor-pointer', 'hover:shadow-sm')
  }
  
  return classes.join(' ')
})

const iconLeftClasses = computed(() => {
  const classes = ['flex-shrink-0']
  
  // SPACING
  if (props.text || props.$slots.default) {
    const spacing = {
      xs: 'mr-1',
      sm: 'mr-1',
      md: 'mr-1.5',
      lg: 'mr-2',
      xl: 'mr-2'
    }
    classes.push(spacing[props.size])
  }
  
  // ICON SIZES
  const iconSizes = {
    xs: 'w-3 h-3',
    sm: 'w-3 h-3',
    md: 'w-4 h-4',
    lg: 'w-4 h-4',
    xl: 'w-5 h-5'
  }
  classes.push(iconSizes[props.size])
  
  return classes.join(' ')
})

const iconRightClasses = computed(() => {
  const classes = ['flex-shrink-0']
  
  // SPACING
  if (props.text || props.$slots.default) {
    const spacing = {
      xs: 'ml-1',
      sm: 'ml-1',
      md: 'ml-1.5',
      lg: 'ml-2',
      xl: 'ml-2'
    }
    classes.push(spacing[props.size])
  }
  
  // ICON SIZES
  const iconSizes = {
    xs: 'w-3 h-3',
    sm: 'w-3 h-3',
    md: 'w-4 h-4',
    lg: 'w-4 h-4',
    xl: 'w-5 h-5'
  }
  classes.push(iconSizes[props.size])
  
  return classes.join(' ')
})

const dotClasses = computed(() => {
  const classes = ['w-2 h-2 rounded-full flex-shrink-0']
  
  // SPACING
  if (props.text || props.$slots.default) {
    classes.push('mr-2')
  }
  
  // DOT COLOR
  if (props.dotColor) {
    classes.push(`bg-${props.dotColor}`)
  } else {
    // Color del dot basado en la variante
    const dotColors = {
      default: 'bg-gray-400',
      primary: 'bg-blue-400',
      secondary: 'bg-gray-400',
      success: 'bg-green-400',
      warning: 'bg-yellow-400',
      error: 'bg-red-400',
      info: 'bg-cyan-400'
    }
    classes.push(dotColors[props.variant])
  }
  
  return classes.join(' ')
})

const closeButtonClasses = computed(() => {
  const classes = ['ml-1.5', 'flex-shrink-0', 'rounded-full', 'transition-colors', 'duration-200']
  
  // TAMAÑOS
  const sizeClasses = {
    xs: 'w-3 h-3',
    sm: 'w-3 h-3',
    md: 'w-4 h-4',
    lg: 'w-4 h-4',
    xl: 'w-5 h-5'
  }
  classes.push(sizeClasses[props.size])
  
  // HOVER COLORS SEGÚN VARIANTE
  if (props.outline || props.soft) {
    classes.push('hover:bg-black hover:bg-opacity-10')
  } else {
    classes.push('hover:bg-white hover:bg-opacity-20')
  }
  
  return classes.join(' ')
})

// METHODS
const handleClose = (event) => {
  event.stopPropagation()
  emit('close', event)
}

const handleClick = (event) => {
  if (props.clickable) {
    emit('click', event)
  }
}
</script>

<style scoped>
/* CUSTOM HOVER EFFECTS */
.badge:hover {
  transform: translateY(-1px);
}

.badge:active {
  transform: translateY(0);
}

/* FOCUS STYLES */
.badge:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
}

/* CLOSE BUTTON FOCUS */
.badge button:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.5);
}

/* ANIMATION FOR DOT */
.badge .w-2.h-2.rounded-full {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

/* RESPONSIVE ADJUSTMENTS */
@media (max-width: 640px) {
  .badge {
    font-size: 0.75rem;
  }
}
</style>
