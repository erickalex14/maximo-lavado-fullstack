<template>
  <Transition
    v-if="visible"
    enter-active-class="transition ease-out duration-300"
    enter-from-class="opacity-0 transform scale-95"
    enter-to-class="opacity-100 transform scale-100"
    leave-active-class="transition ease-in duration-200"
    leave-from-class="opacity-100 transform scale-100"
    leave-to-class="opacity-0 transform scale-95"
  >
    <div
      v-show="visible"
      class="base-alert"
      :class="[
        alertClasses,
        {
          'base-alert--dismissible': dismissible,
          'base-alert--bordered': bordered,
          'base-alert--elevated': elevated
        }
      ]"
      role="alert"
      :aria-live="variant === 'error' ? 'assertive' : 'polite'"
    >
      <!-- Icon -->
      <div v-if="showIcon" class="alert-icon">
        <slot name="icon">
          <!-- Success icon -->
          <svg
            v-if="variant === 'success'"
            class="alert-icon-svg"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
              clip-rule="evenodd"
            />
          </svg>
          
          <!-- Warning icon -->
          <svg
            v-else-if="variant === 'warning'"
            class="alert-icon-svg"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path
              fill-rule="evenodd"
              d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
              clip-rule="evenodd"
            />
          </svg>
          
          <!-- Error icon -->
          <svg
            v-else-if="variant === 'error'"
            class="alert-icon-svg"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
              clip-rule="evenodd"
            />
          </svg>
          
          <!-- Info icon -->
          <svg
            v-else-if="variant === 'info'"
            class="alert-icon-svg"
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path
              fill-rule="evenodd"
              d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
              clip-rule="evenodd"
            />
          </svg>
        </slot>
      </div>

      <!-- Content -->
      <div class="alert-content">
        <!-- Title -->
        <h4 v-if="title" class="alert-title">
          {{ title }}
        </h4>

        <!-- Message -->
        <div class="alert-message" :class="{ 'mt-1': title }">
          <slot>
            <p v-if="message">{{ message }}</p>
          </slot>
        </div>

        <!-- Actions -->
        <div v-if="$slots.actions || actions.length > 0" class="alert-actions">
          <slot name="actions">
            <button
              v-for="action in actions"
              :key="action.key || action.label"
              type="button"
              class="alert-action-button"
              :class="[
                action.variant === 'primary' ? 'btn-primary' : 'btn-outline',
                action.size === 'sm' ? 'btn-sm' : ''
              ]"
              @click="handleActionClick(action)"
            >
              {{ action.label }}
            </button>
          </slot>
        </div>
      </div>

      <!-- Dismiss button -->
      <button
        v-if="dismissible"
        type="button"
        class="alert-dismiss"
        :aria-label="dismissLabel"
        @click="dismiss"
      >
        <slot name="dismiss-icon">
          <svg
            class="alert-dismiss-icon"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </slot>
      </button>

      <!-- Progress bar for auto-dismiss -->
      <div
        v-if="autoDismiss && visible"
        class="alert-progress"
        :style="{ animationDuration: `${autoDismissDelay}ms` }"
      />
    </div>
  </Transition>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

export default {
  name: 'BaseAlert',
  props: {
    // Content
    title: String,
    message: String,
    
    // Variant
    variant: {
      type: String,
      default: 'info',
      validator: (value) => ['success', 'warning', 'error', 'info'].includes(value)
    },
    
    // Behavior
    dismissible: {
      type: Boolean,
      default: true
    },
    autoDismiss: {
      type: Boolean,
      default: false
    },
    autoDismissDelay: {
      type: Number,
      default: 5000
    },
    persistent: {
      type: Boolean,
      default: false
    },
    
    // Visibility
    modelValue: {
      type: Boolean,
      default: true
    },
    
    // Actions
    actions: {
      type: Array,
      default: () => []
    },
    
    // Styling
    showIcon: {
      type: Boolean,
      default: true
    },
    bordered: {
      type: Boolean,
      default: false
    },
    elevated: {
      type: Boolean,
      default: false
    },
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    
    // Accessibility
    dismissLabel: {
      type: String,
      default: 'Cerrar alerta'
    }
  },
  
  emits: ['update:modelValue', 'dismiss', 'action'],
  
  setup(props, { emit }) {
    // State
    const visible = ref(props.modelValue)
    const autoDismissTimer = ref(null)
    
    // Computed
    const alertClasses = computed(() => {
      const classes = ['alert-base']
      
      // Variant classes
      classes.push(`alert-${props.variant}`)
      
      // Size classes
      if (props.size === 'sm') classes.push('alert-sm')
      else if (props.size === 'lg') classes.push('alert-lg')
      
      return classes
    })
    
    // Methods
    const dismiss = () => {
      if (!props.persistent) {
        visible.value = false
        emit('update:modelValue', false)
        emit('dismiss')
        clearAutoDismissTimer()
      }
    }
    
    const handleActionClick = (action) => {
      emit('action', action)
      
      if (action.handler && typeof action.handler === 'function') {
        action.handler()
      }
      
      // Auto-dismiss after action if specified
      if (action.dismiss !== false && !props.persistent) {
        dismiss()
      }
    }
    
    const clearAutoDismissTimer = () => {
      if (autoDismissTimer.value) {
        clearTimeout(autoDismissTimer.value)
        autoDismissTimer.value = null
      }
    }
    
    const startAutoDismissTimer = () => {
      if (props.autoDismiss && !props.persistent) {
        clearAutoDismissTimer()
        autoDismissTimer.value = setTimeout(() => {
          dismiss()
        }, props.autoDismissDelay)
      }
    }
    
    // Watchers
    watch(() => props.modelValue, (newValue) => {
      visible.value = newValue
      if (newValue) {
        startAutoDismissTimer()
      } else {
        clearAutoDismissTimer()
      }
    })
    
    watch(visible, (newValue) => {
      if (newValue !== props.modelValue) {
        emit('update:modelValue', newValue)
      }
    })
    
    // Lifecycle
    onMounted(() => {
      if (visible.value && props.autoDismiss) {
        startAutoDismissTimer()
      }
    })
    
    onUnmounted(() => {
      clearAutoDismissTimer()
    })
    
    return {
      // State
      visible,
      
      // Computed
      alertClasses,
      
      // Methods
      dismiss,
      handleActionClick
    }
  }
}
</script>

<style scoped>
.base-alert {
  @apply relative flex items-start p-4 rounded-md;
  @apply transition-all duration-200;
}

.base-alert--dismissible {
  @apply pr-10;
}

.base-alert--bordered {
  @apply border-l-4;
}

.base-alert--elevated {
  @apply shadow-md;
}

/* Variant styles */
.alert-success {
  @apply bg-green-50 text-green-800 border-green-400;
}

.alert-warning {
  @apply bg-yellow-50 text-yellow-800 border-yellow-400;
}

.alert-error {
  @apply bg-red-50 text-red-800 border-red-400;
}

.alert-info {
  @apply bg-blue-50 text-blue-800 border-blue-400;
}

/* Size variants */
.alert-sm {
  @apply p-3 text-sm;
}

.alert-lg {
  @apply p-6 text-lg;
}

/* Icon */
.alert-icon {
  @apply flex-shrink-0 mr-3;
}

.alert-icon-svg {
  @apply w-5 h-5;
}

.alert-sm .alert-icon-svg {
  @apply w-4 h-4;
}

.alert-lg .alert-icon-svg {
  @apply w-6 h-6;
}

/* Content */
.alert-content {
  @apply flex-1 min-w-0;
}

.alert-title {
  @apply font-semibold;
}

.alert-sm .alert-title {
  @apply text-sm;
}

.alert-lg .alert-title {
  @apply text-lg;
}

.alert-message {
  @apply text-sm;
}

.alert-lg .alert-message {
  @apply text-base;
}

/* Actions */
.alert-actions {
  @apply mt-3 flex flex-wrap gap-2;
}

.alert-action-button {
  @apply btn btn-sm;
}

/* Dismiss button */
.alert-dismiss {
  @apply absolute top-4 right-4 flex-shrink-0;
  @apply text-current opacity-60 hover:opacity-100;
  @apply focus:outline-none focus:opacity-100;
  @apply transition-opacity duration-150;
}

.alert-sm .alert-dismiss {
  @apply top-3 right-3;
}

.alert-lg .alert-dismiss {
  @apply top-6 right-6;
}

.alert-dismiss-icon {
  @apply w-4 h-4;
}

.alert-sm .alert-dismiss-icon {
  @apply w-3 h-3;
}

.alert-lg .alert-dismiss-icon {
  @apply w-5 h-5;
}

/* Progress bar for auto-dismiss */
.alert-progress {
  @apply absolute bottom-0 left-0 h-1 bg-current opacity-30;
  @apply rounded-b-md;
  animation: alert-progress linear forwards;
}

@keyframes alert-progress {
  from {
    width: 100%;
  }
  to {
    width: 0%;
  }
}
</style>
