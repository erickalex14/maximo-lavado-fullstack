<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="modelValue"
        :class="toastClasses"
        :style="{ zIndex: zIndex }"
        role="alert"
        :aria-live="ariaLive"
        :aria-atomic="true"
      >
        <div class="flex">
          <!-- ICON -->
          <div class="flex-shrink-0">
            <component
              v-if="icon"
              :is="icon"
              :class="iconClasses"
              aria-hidden="true"
            />
            <div v-else-if="showDefaultIcon" :class="defaultIconContainerClasses">
              <!-- SUCCESS ICON -->
              <svg
                v-if="variant === 'success'"
                class="w-5 h-5 text-green-400"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                  clip-rule="evenodd"
                />
              </svg>
              
              <!-- ERROR ICON -->
              <svg
                v-else-if="variant === 'error'"
                class="w-5 h-5 text-red-400"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                  clip-rule="evenodd"
                />
              </svg>
              
              <!-- WARNING ICON -->
              <svg
                v-else-if="variant === 'warning'"
                class="w-5 h-5 text-yellow-400"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                  clip-rule="evenodd"
                />
              </svg>
              
              <!-- INFO ICON -->
              <svg
                v-else-if="variant === 'info'"
                class="w-5 h-5 text-blue-400"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                  clip-rule="evenodd"
                />
              </svg>
              
              <!-- DEFAULT ICON -->
              <svg
                v-else
                class="w-5 h-5 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
            </div>
          </div>
          
          <!-- CONTENT -->
          <div class="ml-3 w-0 flex-1">
            <!-- TITLE -->
            <p v-if="title" :class="titleClasses">
              {{ title }}
            </p>
            
            <!-- MESSAGE -->
            <div :class="messageClasses">
              <slot>
                <p v-if="message">{{ message }}</p>
              </slot>
            </div>
            
            <!-- ACTIONS -->
            <div v-if="$slots.actions || showDefaultActions" class="mt-3 flex space-x-2">
              <slot name="actions">
                <button
                  v-if="actionText"
                  type="button"
                  :class="actionButtonClasses"
                  @click="handleAction"
                >
                  {{ actionText }}
                </button>
                <button
                  v-if="showDismissAction"
                  type="button"
                  :class="dismissButtonClasses"
                  @click="handleDismiss"
                >
                  {{ dismissText }}
                </button>
              </slot>
            </div>
          </div>
          
          <!-- CLOSE BUTTON -->
          <div v-if="closable" class="ml-4 flex-shrink-0 flex">
            <button
              type="button"
              :class="closeButtonClasses"
              @click="handleClose"
              :aria-label="closeButtonLabel"
            >
              <span class="sr-only">{{ closeButtonLabel }}</span>
              <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                <path
                  fill-rule="evenodd"
                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>
          </div>
        </div>
        
        <!-- PROGRESS BAR -->
        <div
          v-if="showProgress && autoClose && duration"
          class="absolute bottom-0 left-0 h-1 bg-current opacity-30 transition-all ease-linear"
          :style="{ 
            width: progressWidth + '%',
            animationDuration: duration + 'ms',
            animationName: 'toast-progress'
          }"
        ></div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'

// PROPS
const props = defineProps({
  // CONTROL DE VISIBILIDAD
  modelValue: {
    type: Boolean,
    default: false
  },
  
  // CONTENIDO
  title: {
    type: String,
    default: null
  },
  
  message: {
    type: String,
    default: null
  },
  
  // VARIANTE
  variant: {
    type: String,
    default: 'info',
    validator: (value) => ['success', 'error', 'warning', 'info', 'default'].includes(value)
  },
  
  // POSICIÓN
  position: {
    type: String,
    default: 'top-right',
    validator: (value) => [
      'top-left', 'top-center', 'top-right',
      'bottom-left', 'bottom-center', 'bottom-right'
    ].includes(value)
  },
  
  // ICONOS
  icon: {
    type: [String, Object],
    default: null
  },
  
  showDefaultIcon: {
    type: Boolean,
    default: true
  },
  
  // AUTO CLOSE
  autoClose: {
    type: Boolean,
    default: true
  },
  
  duration: {
    type: Number,
    default: 5000
  },
  
  showProgress: {
    type: Boolean,
    default: false
  },
  
  // CIERRE
  closable: {
    type: Boolean,
    default: true
  },
  
  closeButtonLabel: {
    type: String,
    default: 'Cerrar notificación'
  },
  
  // ACCIONES
  actionText: {
    type: String,
    default: null
  },
  
  showDefaultActions: {
    type: Boolean,
    default: false
  },
  
  showDismissAction: {
    type: Boolean,
    default: false
  },
  
  dismissText: {
    type: String,
    default: 'Descartar'
  },
  
  // Z-INDEX
  zIndex: {
    type: Number,
    default: 1080
  },
  
  // ACCESIBILIDAD
  ariaLive: {
    type: String,
    default: 'polite',
    validator: (value) => ['polite', 'assertive', 'off'].includes(value)
  }
})

// EVENTS
const emit = defineEmits([
  'update:modelValue',
  'close',
  'action',
  'dismiss'
])

// REFS
const progressWidth = ref(100)
let autoCloseTimer = null
let progressTimer = null

// COMPUTED
const toastClasses = computed(() => {
  const classes = [
    'fixed',
    'max-w-sm',
    'w-full',
    'pointer-events-auto',
    'overflow-hidden',
    'rounded-lg',
    'shadow-lg',
    'ring-1',
    'ring-black',
    'ring-opacity-5',
    'relative'
  ]
  
  // POSICIÓN
  const positionClasses = {
    'top-left': 'top-4 left-4',
    'top-center': 'top-4 left-1/2 transform -translate-x-1/2',
    'top-right': 'top-4 right-4',
    'bottom-left': 'bottom-4 left-4',
    'bottom-center': 'bottom-4 left-1/2 transform -translate-x-1/2',
    'bottom-right': 'bottom-4 right-4'
  }
  classes.push(positionClasses[props.position])
  
  // VARIANTES DE COLOR
  const variantClasses = {
    success: 'bg-green-50',
    error: 'bg-red-50',
    warning: 'bg-yellow-50',
    info: 'bg-blue-50',
    default: 'bg-white'
  }
  classes.push(variantClasses[props.variant])
  
  return classes.join(' ')
})

const iconClasses = computed(() => {
  const classes = ['w-5 h-5']
  
  // COLORES SEGÚN VARIANTE
  const colorClasses = {
    success: 'text-green-400',
    error: 'text-red-400',
    warning: 'text-yellow-400',
    info: 'text-blue-400',
    default: 'text-gray-400'
  }
  classes.push(colorClasses[props.variant])
  
  return classes.join(' ')
})

const defaultIconContainerClasses = computed(() => {
  return 'flex items-center justify-center'
})

const titleClasses = computed(() => {
  const classes = ['text-sm font-medium']
  
  // COLORES SEGÚN VARIANTE
  const colorClasses = {
    success: 'text-green-800',
    error: 'text-red-800',
    warning: 'text-yellow-800',
    info: 'text-blue-800',
    default: 'text-gray-900'
  }
  classes.push(colorClasses[props.variant])
  
  return classes.join(' ')
})

const messageClasses = computed(() => {
  const classes = ['text-sm']
  
  // MARGEN SI HAY TÍTULO
  if (props.title) {
    classes.push('mt-1')
  }
  
  // COLORES SEGÚN VARIANTE
  const colorClasses = {
    success: 'text-green-700',
    error: 'text-red-700',
    warning: 'text-yellow-700',
    info: 'text-blue-700',
    default: 'text-gray-600'
  }
  classes.push(colorClasses[props.variant])
  
  return classes.join(' ')
})

const actionButtonClasses = computed(() => {
  const classes = [
    'inline-flex',
    'items-center',
    'px-3',
    'py-2',
    'rounded-md',
    'text-sm',
    'font-medium',
    'focus:outline-none',
    'focus:ring-2',
    'focus:ring-offset-2',
    'transition-colors',
    'duration-200'
  ]
  
  // COLORES SEGÚN VARIANTE
  const variantClasses = {
    success: 'bg-green-100 text-green-800 hover:bg-green-200 focus:ring-green-500',
    error: 'bg-red-100 text-red-800 hover:bg-red-200 focus:ring-red-500',
    warning: 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200 focus:ring-yellow-500',
    info: 'bg-blue-100 text-blue-800 hover:bg-blue-200 focus:ring-blue-500',
    default: 'bg-gray-100 text-gray-800 hover:bg-gray-200 focus:ring-gray-500'
  }
  classes.push(variantClasses[props.variant])
  
  return classes.join(' ')
})

const dismissButtonClasses = computed(() => {
  const classes = [
    'inline-flex',
    'items-center',
    'px-3',
    'py-2',
    'rounded-md',
    'text-sm',
    'font-medium',
    'bg-white',
    'border',
    'border-gray-300',
    'text-gray-700',
    'hover:bg-gray-50',
    'focus:outline-none',
    'focus:ring-2',
    'focus:ring-offset-2',
    'focus:ring-gray-500',
    'transition-colors',
    'duration-200'
  ]
  
  return classes.join(' ')
})

const closeButtonClasses = computed(() => {
  const classes = [
    'inline-flex',
    'rounded-md',
    'p-1.5',
    'focus:outline-none',
    'focus:ring-2',
    'focus:ring-offset-2',
    'transition-colors',
    'duration-200'
  ]
  
  // COLORES SEGÚN VARIANTE
  const variantClasses = {
    success: 'text-green-400 hover:bg-green-100 focus:ring-green-500',
    error: 'text-red-400 hover:bg-red-100 focus:ring-red-500',
    warning: 'text-yellow-400 hover:bg-yellow-100 focus:ring-yellow-500',
    info: 'text-blue-400 hover:bg-blue-100 focus:ring-blue-500',
    default: 'text-gray-400 hover:bg-gray-100 focus:ring-gray-500'
  }
  classes.push(variantClasses[props.variant])
  
  return classes.join(' ')
})

// METHODS
const handleClose = () => {
  clearTimers()
  emit('update:modelValue', false)
  emit('close')
}

const handleAction = () => {
  emit('action')
}

const handleDismiss = () => {
  emit('dismiss')
  handleClose()
}

const startAutoClose = () => {
  if (props.autoClose && props.duration) {
    // TIMER PARA CERRAR
    autoCloseTimer = setTimeout(() => {
      handleClose()
    }, props.duration)
    
    // TIMER PARA PROGRESS BAR
    if (props.showProgress) {
      progressWidth.value = 100
      const interval = 50 // Update every 50ms
      const decrement = (100 / props.duration) * interval
      
      progressTimer = setInterval(() => {
        progressWidth.value -= decrement
        if (progressWidth.value <= 0) {
          progressWidth.value = 0
          clearInterval(progressTimer)
        }
      }, interval)
    }
  }
}

const clearTimers = () => {
  if (autoCloseTimer) {
    clearTimeout(autoCloseTimer)
    autoCloseTimer = null
  }
  
  if (progressTimer) {
    clearInterval(progressTimer)
    progressTimer = null
  }
}

const pauseAutoClose = () => {
  clearTimers()
}

const resumeAutoClose = () => {
  if (props.modelValue) {
    startAutoClose()
  }
}

// WATCHERS
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    startAutoClose()
  } else {
    clearTimers()
    progressWidth.value = 100
  }
})

// LIFECYCLE
onMounted(() => {
  if (props.modelValue) {
    startAutoClose()
  }
})

onUnmounted(() => {
  clearTimers()
})
</script>

<style scoped>
/* PROGRESS BAR ANIMATION */
@keyframes toast-progress {
  from {
    width: 100%;
  }
  to {
    width: 0%;
  }
}

/* RESPONSIVE ADJUSTMENTS */
@media (max-width: 640px) {
  .fixed.max-w-sm {
    max-width: calc(100vw - 2rem);
    left: 1rem !important;
    right: 1rem !important;
    transform: none !important;
  }
  
  /* ADJUST TOP POSITION ON MOBILE */
  .top-4 {
    top: 1rem;
  }
  
  .bottom-4 {
    bottom: 1rem;
  }
}

/* ACCESSIBILITY IMPROVEMENTS */
@media (prefers-reduced-motion: reduce) {
  .transition,
  .transform {
    transition: none !important;
    transform: none !important;
  }
}

/* HIGH CONTRAST MODE SUPPORT */
@media (prefers-contrast: high) {
  .ring-1.ring-black.ring-opacity-5 {
    ring-width: 2px;
    ring-opacity: 1;
  }
}
</style>
