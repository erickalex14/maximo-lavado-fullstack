<template>
  <!-- BACKDROP OVERLAY -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity ease-out duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="modelValue"
        class="fixed inset-0 z-50 overflow-y-auto"
        :style="{ zIndex: zIndex }"
        @click="handleBackdropClick"
      >
        <!-- BACKDROP -->
        <div
          class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm"
          :class="backdropClasses"
          aria-hidden="true"
        ></div>

        <!-- MODAL CONTAINER -->
        <div class="flex min-h-full items-center justify-center p-4 sm:p-6 lg:p-8">
          <Transition
            enter-active-class="transition-all ease-out duration-300"
            enter-from-class="opacity-0 scale-95 translate-y-4"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-active-class="transition-all ease-in duration-200"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 translate-y-4"
          >
            <div
              v-if="modelValue"
              :class="modalClasses"
              role="dialog"
              :aria-modal="true"
              :aria-labelledby="titleId"
              :aria-describedby="descriptionId"
              @click.stop
            >
              <!-- HEADER -->
              <header
                v-if="$slots.header || title || showCloseButton"
                class="modal-header"
              >
                <div class="flex items-center justify-between">
                  <!-- TITLE SECTION -->
                  <div class="flex items-center">
                    <!-- ICON -->
                    <component
                      v-if="icon"
                      :is="icon"
                      :class="iconClasses"
                      aria-hidden="true"
                    />
                    
                    <!-- TITLE -->
                    <h2
                      v-if="title"
                      :id="titleId"
                      class="text-heading-3"
                    >
                      {{ title }}
                    </h2>
                    
                    <!-- CUSTOM HEADER SLOT -->
                    <slot v-else name="header"></slot>
                  </div>

                  <!-- CLOSE BUTTON -->
                  <button
                    v-if="showCloseButton"
                    type="button"
                    class="close-button"
                    @click="closeModal"
                    :aria-label="closeButtonLabel"
                  >
                    <svg
                      class="w-5 h-5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                      aria-hidden="true"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                      />
                    </svg>
                  </button>
                </div>
                
                <!-- SUBTITLE -->
                <p
                  v-if="subtitle"
                  :id="descriptionId"
                  class="text-caption mt-2"
                >
                  {{ subtitle }}
                </p>
              </header>

              <!-- BODY -->
              <div
                :class="bodyClasses"
                :id="!subtitle ? descriptionId : undefined"
              >
                <slot></slot>
              </div>

              <!-- FOOTER -->
              <footer
                v-if="$slots.footer || showDefaultActions"
                class="modal-footer"
              >
                <slot name="footer">
                  <div v-if="showDefaultActions" class="flex justify-end space-x-3">
                    <BaseButton
                      variant="secondary"
                      @click="closeModal"
                    >
                      {{ cancelText }}
                    </BaseButton>
                    <BaseButton
                      :variant="confirmVariant"
                      :loading="confirmLoading"
                      @click="confirmAction"
                    >
                      {{ confirmText }}
                    </BaseButton>
                  </div>
                </slot>
              </footer>
            </div>
          </Transition>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, watch } from 'vue'
import BaseButton from './BaseButton.vue'

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
  
  subtitle: {
    type: String,
    default: null
  },
  
  icon: {
    type: [String, Object],
    default: null
  },
  
  // VARIANTES VISUALES
  variant: {
    type: String,
    default: 'default',
    validator: (value) => [
      'default', 'primary', 'success', 'warning', 'error', 'info'
    ].includes(value)
  },
  
  // TAMAÑO
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl', 'full'].includes(value)
  },
  
  // COMPORTAMIENTO
  persistent: {
    type: Boolean,
    default: false
  },
  
  scrollable: {
    type: Boolean,
    default: false
  },
  
  centered: {
    type: Boolean,
    default: true
  },
  
  // BOTONES Y ACCIONES
  showCloseButton: {
    type: Boolean,
    default: true
  },
  
  showDefaultActions: {
    type: Boolean,
    default: false
  },
  
  cancelText: {
    type: String,
    default: 'Cancelar'
  },
  
  confirmText: {
    type: String,
    default: 'Confirmar'
  },
  
  confirmVariant: {
    type: String,
    default: 'primary'
  },
  
  confirmLoading: {
    type: Boolean,
    default: false
  },
  
  // ACCESIBILIDAD
  closeButtonLabel: {
    type: String,
    default: 'Cerrar modal'
  },
  
  // Z-INDEX
  zIndex: {
    type: Number,
    default: 1050
  }
})

// EVENTS
const emit = defineEmits([
  'update:modelValue',
  'close',
  'confirm',
  'cancel',
  'opened',
  'closed'
])

// COMPUTED
const modalId = computed(() => {
  return `modal-${Math.random().toString(36).substr(2, 9)}`
})

const titleId = computed(() => {
  return `${modalId.value}-title`
})

const descriptionId = computed(() => {
  return `${modalId.value}-description`
})

const modalClasses = computed(() => {
  const classes = [
    'modal-container',
    'relative',
    'transform',
    'overflow-hidden',
    'rounded-lg',
    'bg-white',
    'text-left',
    'shadow-xl',
    'transition-all'
  ]
  
  // TAMAÑOS
  const sizeClasses = {
    xs: 'w-full max-w-xs',
    sm: 'w-full max-w-sm',
    md: 'w-full max-w-md',
    lg: 'w-full max-w-lg',
    xl: 'w-full max-w-xl',
    '2xl': 'w-full max-w-2xl',
    '3xl': 'w-full max-w-3xl',
    '4xl': 'w-full max-w-4xl',
    '5xl': 'w-full max-w-5xl',
    '6xl': 'w-full max-w-6xl',
    full: 'w-full max-w-none m-4'
  }
  classes.push(sizeClasses[props.size] || sizeClasses.md)
  
  // VARIANTES DE COLOR
  const variantClasses = {
    primary: 'border-t-4 border-blue-500',
    success: 'border-t-4 border-green-500',
    warning: 'border-t-4 border-yellow-500',
    error: 'border-t-4 border-red-500',
    info: 'border-t-4 border-cyan-500'
  }
  
  if (variantClasses[props.variant]) {
    classes.push(variantClasses[props.variant])
  }
  
  // SCROLL
  if (props.scrollable) {
    classes.push('max-h-[90vh]', 'flex', 'flex-col')
  }
  
  return classes.join(' ')
})

const bodyClasses = computed(() => {
  const classes = ['modal-body']
  
  if (props.scrollable) {
    classes.push('flex-1', 'overflow-y-auto')
  }
  
  return classes.join(' ')
})

const backdropClasses = computed(() => {
  const classes = []
  
  // Diferentes estilos de backdrop según la variante
  const variantBackdrops = {
    error: 'bg-red-900 bg-opacity-50',
    warning: 'bg-yellow-900 bg-opacity-50',
    success: 'bg-green-900 bg-opacity-50'
  }
  
  if (variantBackdrops[props.variant]) {
    classes.push(variantBackdrops[props.variant])
  }
  
  return classes.join(' ')
})

const iconClasses = computed(() => {
  const classes = ['w-6', 'h-6', 'mr-3', 'flex-shrink-0']
  
  // Colores según variante
  const variantIconColors = {
    primary: 'text-blue-600',
    success: 'text-green-600',
    warning: 'text-yellow-600',
    error: 'text-red-600',
    info: 'text-cyan-600'
  }
  
  if (variantIconColors[props.variant]) {
    classes.push(variantIconColors[props.variant])
  } else {
    classes.push('text-gray-500')
  }
  
  return classes.join(' ')
})

// METHODS
const closeModal = () => {
  if (!props.persistent) {
    emit('update:modelValue', false)
    emit('close')
    emit('cancel')
  }
}

const confirmAction = () => {
  emit('confirm')
}

const handleBackdropClick = () => {
  if (!props.persistent) {
    closeModal()
  }
}

const handleEscapeKey = (event) => {
  if (event.key === 'Escape' && props.modelValue && !props.persistent) {
    closeModal()
  }
}

const trapFocus = () => {
  if (!props.modelValue) return
  
  nextTick(() => {
    const modal = document.querySelector(`[aria-labelledby="${titleId.value}"]`)
    if (modal) {
      const focusableElements = modal.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
      )
      
      if (focusableElements.length > 0) {
        focusableElements[0].focus()
      }
    }
  })
}

// WATCHERS
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    emit('opened')
    trapFocus()
    // Prevenir scroll del body
    document.body.style.overflow = 'hidden'
  } else {
    emit('closed')
    // Restaurar scroll del body
    document.body.style.overflow = ''
  }
})

// LIFECYCLE
onMounted(() => {
  document.addEventListener('keydown', handleEscapeKey)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleEscapeKey)
  // Asegurar que el scroll se restaure
  document.body.style.overflow = ''
})
</script>

<style scoped>
/* MODAL HEADER */
.modal-header {
  padding: 1.5rem 1.5rem 1rem;
  border-bottom: 1px solid #e5e7eb;
}

/* MODAL BODY */
.modal-body {
  padding: 1.5rem;
  color: #374151;
  line-height: 1.6;
}

/* MODAL FOOTER */
.modal-footer {
  padding: 1rem 1.5rem 1.5rem;
  border-top: 1px solid #e5e7eb;
  background-color: #f9fafb;
}

/* CLOSE BUTTON */
.close-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  color: #9ca3af;
  background-color: transparent;
  border: 0;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  line-height: 1.25rem;
  transition: background-color 200ms, color 200ms;
}

.close-button:hover {
  background-color: #e5e7eb;
  color: #4b5563;
}

.close-button:focus {
  outline: none;
  box-shadow: 0 0 0 2px #d1d5db;
}

/* RESPONSIVE ADJUSTMENTS */
@media (max-width: 640px) {
  .modal-header,
  .modal-body,
  .modal-footer {
    padding-left: 1rem;
    padding-right: 1rem;
  }
  
  .modal-container {
    margin: 1rem;
    max-height: calc(100vh - 2rem);
  }
}

/* ANIMATIONS */
.modal-container {
  will-change: transform, opacity;
}

/* BACKDROP BLUR SUPPORT */
@supports (backdrop-filter: blur(4px)) {
  .backdrop-blur-sm {
    backdrop-filter: blur(4px);
  }
}

/* FOCUS TRAP STYLES */
.modal-container:focus {
  outline: none;
}

/* ACCESSIBILITY IMPROVEMENTS */
@media (prefers-reduced-motion: reduce) {
  .transition-all,
  .transition-opacity {
    transition: none !important;
  }
}
</style>
