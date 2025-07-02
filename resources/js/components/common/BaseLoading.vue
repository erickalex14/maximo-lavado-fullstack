<template>
  <div
    :class="containerClasses"
    :aria-label="ariaLabel"
    role="status"
  >
    <!-- SPINNER TYPES -->
    
    <!-- CIRCULAR SPINNER -->
    <div v-if="type === 'spinner'" :class="spinnerClasses">
      <svg
        :class="iconClasses"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
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
    </div>
    
    <!-- DOTS SPINNER -->
    <div v-else-if="type === 'dots'" :class="dotsContainerClasses">
      <div
        v-for="i in 3"
        :key="i"
        :class="dotClasses"
        :style="{ animationDelay: `${(i - 1) * 0.1}s` }"
      ></div>
    </div>
    
    <!-- PULSE SPINNER -->
    <div v-else-if="type === 'pulse'" :class="pulseClasses"></div>
    
    <!-- BARS SPINNER -->
    <div v-else-if="type === 'bars'" :class="barsContainerClasses">
      <div
        v-for="i in 4"
        :key="i"
        :class="barClasses"
        :style="{ animationDelay: `${(i - 1) * 0.1}s` }"
      ></div>
    </div>
    
    <!-- RING SPINNER -->
    <div v-else-if="type === 'ring'" :class="ringClasses"></div>
    
    <!-- TEXT -->
    <div v-if="text || $slots.default" :class="textClasses">
      <slot>{{ text }}</slot>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

// PROPS
const props = defineProps({
  // TIPO DE SPINNER
  type: {
    type: String,
    default: 'spinner',
    validator: (value) => ['spinner', 'dots', 'pulse', 'bars', 'ring'].includes(value)
  },
  
  // TAMAÑO
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl', '2xl'].includes(value)
  },
  
  // COLOR/VARIANTE
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => [
      'primary', 'secondary', 'success', 'warning', 'error', 'info', 'white'
    ].includes(value)
  },
  
  // TEXTO
  text: {
    type: String,
    default: null
  },
  
  // CENTRADO
  centered: {
    type: Boolean,
    default: false
  },
  
  // OVERLAY (PARA CUBRIR CONTENIDO)
  overlay: {
    type: Boolean,
    default: false
  },
  
  // VELOCIDAD DE ANIMACIÓN
  speed: {
    type: String,
    default: 'normal',
    validator: (value) => ['slow', 'normal', 'fast'].includes(value)
  },
  
  // ACCESIBILIDAD
  ariaLabel: {
    type: String,
    default: 'Cargando contenido'
  }
})

// COMPUTED
const containerClasses = computed(() => {
  const classes = []
  
  if (props.centered) {
    classes.push('flex', 'items-center', 'justify-center')
  } else {
    classes.push('flex', 'items-center')
  }
  
  if (props.overlay) {
    classes.push(
      'fixed',
      'inset-0',
      'z-50',
      'bg-white',
      'bg-opacity-75',
      'backdrop-blur-sm'
    )
  }
  
  // SPACING ENTRE SPINNER Y TEXTO
  if (props.text || props.$slots.default) {
    classes.push('space-x-3')
  }
  
  return classes.join(' ')
})

const spinnerClasses = computed(() => {
  const classes = ['animate-spin']
  
  // VELOCIDAD
  const speedClasses = {
    slow: 'duration-1000',
    normal: 'duration-700',
    fast: 'duration-500'
  }
  classes.push(speedClasses[props.speed])
  
  return classes.join(' ')
})

const iconClasses = computed(() => {
  const classes = []
  
  // TAMAÑOS
  const sizeClasses = {
    xs: 'w-3 h-3',
    sm: 'w-4 h-4',
    md: 'w-6 h-6',
    lg: 'w-8 h-8',
    xl: 'w-12 h-12',
    '2xl': 'w-16 h-16'
  }
  classes.push(sizeClasses[props.size])
  
  // COLORES
  const colorClasses = {
    primary: 'text-blue-600',
    secondary: 'text-gray-600',
    success: 'text-green-600',
    warning: 'text-yellow-600',
    error: 'text-red-600',
    info: 'text-cyan-600',
    white: 'text-white'
  }
  classes.push(colorClasses[props.variant])
  
  return classes.join(' ')
})

const dotsContainerClasses = computed(() => {
  const classes = ['flex', 'space-x-1']
  return classes.join(' ')
})

const dotClasses = computed(() => {
  const classes = ['rounded-full', 'animate-pulse']
  
  // TAMAÑOS
  const sizeClasses = {
    xs: 'w-1 h-1',
    sm: 'w-1.5 h-1.5',
    md: 'w-2 h-2',
    lg: 'w-3 h-3',
    xl: 'w-4 h-4',
    '2xl': 'w-5 h-5'
  }
  classes.push(sizeClasses[props.size])
  
  // COLORES
  const colorClasses = {
    primary: 'bg-blue-600',
    secondary: 'bg-gray-600',
    success: 'bg-green-600',
    warning: 'bg-yellow-600',
    error: 'bg-red-600',
    info: 'bg-cyan-600',
    white: 'bg-white'
  }
  classes.push(colorClasses[props.variant])
  
  return classes.join(' ')
})

const pulseClasses = computed(() => {
  const classes = ['rounded-full', 'animate-ping']
  
  // TAMAÑOS
  const sizeClasses = {
    xs: 'w-3 h-3',
    sm: 'w-4 h-4',
    md: 'w-6 h-6',
    lg: 'w-8 h-8',
    xl: 'w-12 h-12',
    '2xl': 'w-16 h-16'
  }
  classes.push(sizeClasses[props.size])
  
  // COLORES
  const colorClasses = {
    primary: 'bg-blue-600',
    secondary: 'bg-gray-600',
    success: 'bg-green-600',
    warning: 'bg-yellow-600',
    error: 'bg-red-600',
    info: 'bg-cyan-600',
    white: 'bg-white'
  }
  classes.push(colorClasses[props.variant])
  
  return classes.join(' ')
})

const barsContainerClasses = computed(() => {
  const classes = ['flex', 'items-end', 'space-x-1']
  return classes.join(' ')
})

const barClasses = computed(() => {
  const classes = ['animate-pulse']
  
  // TAMAÑOS
  const sizeClasses = {
    xs: 'w-0.5 h-3',
    sm: 'w-0.5 h-4',
    md: 'w-1 h-6',
    lg: 'w-1 h-8',
    xl: 'w-1.5 h-12',
    '2xl': 'w-2 h-16'
  }
  classes.push(sizeClasses[props.size])
  
  // COLORES
  const colorClasses = {
    primary: 'bg-blue-600',
    secondary: 'bg-gray-600',
    success: 'bg-green-600',
    warning: 'bg-yellow-600',
    error: 'bg-red-600',
    info: 'bg-cyan-600',
    white: 'bg-white'
  }
  classes.push(colorClasses[props.variant])
  
  return classes.join(' ')
})

const ringClasses = computed(() => {
  const classes = ['border-4', 'border-opacity-25', 'rounded-full', 'animate-spin']
  
  // TAMAÑOS
  const sizeClasses = {
    xs: 'w-3 h-3',
    sm: 'w-4 h-4',
    md: 'w-6 h-6',
    lg: 'w-8 h-8',
    xl: 'w-12 h-12',
    '2xl': 'w-16 h-16'
  }
  classes.push(sizeClasses[props.size])
  
  // COLORES DEL RING
  const colorClasses = {
    primary: 'border-blue-600 border-t-blue-600',
    secondary: 'border-gray-600 border-t-gray-600',
    success: 'border-green-600 border-t-green-600',
    warning: 'border-yellow-600 border-t-yellow-600',
    error: 'border-red-600 border-t-red-600',
    info: 'border-cyan-600 border-t-cyan-600',
    white: 'border-white border-t-white'
  }
  classes.push(colorClasses[props.variant])
  
  return classes.join(' ')
})

const textClasses = computed(() => {
  const classes = ['font-medium']
  
  // TAMAÑOS DE TEXTO
  const textSizeClasses = {
    xs: 'text-xs',
    sm: 'text-sm',
    md: 'text-sm',
    lg: 'text-base',
    xl: 'text-lg',
    '2xl': 'text-xl'
  }
  classes.push(textSizeClasses[props.size])
  
  // COLORES DE TEXTO
  const textColorClasses = {
    primary: 'text-blue-600',
    secondary: 'text-gray-600',
    success: 'text-green-600',
    warning: 'text-yellow-600',
    error: 'text-red-600',
    info: 'text-cyan-600',
    white: 'text-white'
  }
  classes.push(textColorClasses[props.variant])
  
  return classes.join(' ')
})
</script>

<style scoped>
/* CUSTOM ANIMATIONS */

/* DOTS ANIMATION */
@keyframes dotPulse {
  0%, 80%, 100% {
    transform: scale(0.8);
    opacity: 0.5;
  }
  40% {
    transform: scale(1);
    opacity: 1;
  }
}

.animate-pulse {
  animation: dotPulse 1.5s ease-in-out infinite;
}

/* BARS ANIMATION */
@keyframes barStretch {
  0%, 40%, 100% {
    transform: scaleY(0.4);
  }
  20% {
    transform: scaleY(1);
  }
}

.animate-pulse:nth-child(1) {
  animation: barStretch 1.2s infinite ease-in-out;
}

.animate-pulse:nth-child(2) {
  animation: barStretch 1.2s infinite ease-in-out 0.1s;
}

.animate-pulse:nth-child(3) {
  animation: barStretch 1.2s infinite ease-in-out 0.2s;
}

.animate-pulse:nth-child(4) {
  animation: barStretch 1.2s infinite ease-in-out 0.3s;
}

/* SPINNER SPEED VARIATIONS */
.duration-1000 {
  animation-duration: 1000ms;
}

.duration-700 {
  animation-duration: 700ms;
}

.duration-500 {
  animation-duration: 500ms;
}

/* OVERLAY BACKDROP */
.backdrop-blur-sm {
  backdrop-filter: blur(4px);
}

/* ACCESSIBILITY IMPROVEMENTS */
@media (prefers-reduced-motion: reduce) {
  .animate-spin,
  .animate-pulse,
  .animate-ping {
    animation: none;
  }
  
  /* STATIC LOADING INDICATOR FOR REDUCED MOTION */
  .animate-spin::after {
    content: "⋯";
    display: inline-block;
    font-size: 1.5em;
    line-height: 1;
    animation: fadeInOut 2s ease-in-out infinite;
  }
}

@keyframes fadeInOut {
  0%, 100% { opacity: 0.3; }
  50% { opacity: 1; }
}

/* OVERLAY Z-INDEX MANAGEMENT */
.z-50 {
  z-index: var(--z-modal, 1050);
}
</style>
