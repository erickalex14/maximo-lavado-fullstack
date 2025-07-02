<template>
  <div 
    :class="cardClasses"
    :role="role"
    :aria-labelledby="ariaLabelledby"
    :aria-describedby="ariaDescribedby"
  >
    <!-- HEADER -->
    <header 
      v-if="$slots.header || title" 
      class="card-header"
      :id="headerId"
    >
      <slot name="header">
        <div class="flex items-center justify-between">
          <!-- TITLE -->
          <div class="flex items-center">
            <!-- ICON -->
            <component 
              v-if="icon" 
              :is="icon" 
              class="w-5 h-5 mr-3 text-gray-500"
              aria-hidden="true"
            />
            
            <!-- TITLE TEXT -->
            <h3 v-if="title" class="text-heading-4">
              {{ title }}
            </h3>
          </div>
          
          <!-- ACTIONS -->
          <div v-if="$slots.actions" class="flex items-center space-x-2">
            <slot name="actions"></slot>
          </div>
        </div>
        
        <!-- SUBTITLE -->
        <p v-if="subtitle" class="text-caption mt-1">
          {{ subtitle }}
        </p>
      </slot>
    </header>

    <!-- BODY -->
    <div 
      :class="bodyClasses"
      :id="bodyId"
    >
      <slot></slot>
    </div>

    <!-- FOOTER -->
    <footer 
      v-if="$slots.footer" 
      class="card-footer"
    >
      <slot name="footer"></slot>
    </footer>
  </div>
</template>

<script setup>
import { computed } from 'vue'

// PROPS
const props = defineProps({
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
      'default', 'elevated', 'outlined', 'filled',
      'primary', 'success', 'warning', 'error', 'info'
    ].includes(value)
  },
  
  // TAMAÑO
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  
  // ESTADOS
  loading: {
    type: Boolean,
    default: false
  },
  
  disabled: {
    type: Boolean,
    default: false
  },
  
  // INTERACCIONES
  clickable: {
    type: Boolean,
    default: false
  },
  
  hoverable: {
    type: Boolean,
    default: false
  },
  
  // LAYOUT
  noPadding: {
    type: Boolean,
    default: false
  },
  
  noShadow: {
    type: Boolean,
    default: false
  },
  
  // ACCESIBILIDAD
  role: {
    type: String,
    default: null
  },
  
  ariaLabelledby: {
    type: String,
    default: null
  },
  
  ariaDescribedby: {
    type: String,
    default: null
  }
})

// EVENTS
const emit = defineEmits(['click'])

// COMPUTED
const cardId = computed(() => {
  return `card-${Math.random().toString(36).substr(2, 9)}`
})

const headerId = computed(() => {
  return props.ariaLabelledby || `${cardId.value}-header`
})

const bodyId = computed(() => {
  return props.ariaDescribedby || `${cardId.value}-body`
})

const cardClasses = computed(() => {
  const classes = ['card', 'transition-all', 'duration-200', 'ease-in-out']
  
  // VARIANTES
  switch (props.variant) {
    case 'elevated':
      classes.push('shadow-elevated')
      break
    case 'outlined':
      classes.push('border-2', 'shadow-none')
      break
    case 'filled':
      classes.push('bg-gray-50', 'border-gray-100')
      break
    case 'primary':
      classes.push('bg-blue-50', 'border-blue-200')
      break
    case 'success':
      classes.push('bg-green-50', 'border-green-200')
      break
    case 'warning':
      classes.push('bg-yellow-50', 'border-yellow-200')
      break
    case 'error':
      classes.push('bg-red-50', 'border-red-200')
      break
    case 'info':
      classes.push('bg-cyan-50', 'border-cyan-200')
      break
    default:
      // Default styles already applied via 'card' class
      break
  }
  
  // TAMAÑOS
  const sizeClasses = {
    sm: 'text-sm',
    md: 'text-base',
    lg: 'text-lg'
  }
  classes.push(sizeClasses[props.size])
  
  // SIN SOMBRA
  if (props.noShadow) {
    classes.push('shadow-none')
  }
  
  // INTERACCIONES
  if (props.clickable) {
    classes.push(
      'cursor-pointer',
      'hover:shadow-card',
      'focus:outline-none',
      'focus:ring-2',
      'focus:ring-offset-2',
      'focus:ring-blue-500'
    )
  }
  
  if (props.hoverable) {
    classes.push('hover-lift')
  }
  
  // ESTADOS
  if (props.loading) {
    classes.push('component-loading')
  }
  
  if (props.disabled) {
    classes.push('component-disabled')
  }
  
  return classes.join(' ')
})

const bodyClasses = computed(() => {
  const classes = []
  
  if (props.noPadding) {
    // No agregar padding
    classes.push('overflow-hidden')
  } else {
    classes.push('card-body')
  }
  
  return classes.join(' ')
})

// METHODS
const handleClick = (event) => {
  if (props.clickable && !props.disabled && !props.loading) {
    emit('click', event)
  }
}
</script>

<style scoped>
/* HOVER EFFECTS ESPECÍFICOS PARA CARDS CLICKABLES */
.card.cursor-pointer:hover {
  transform: translateY(-2px);
}

.card.cursor-pointer:active {
  transform: translateY(0);
}

/* LOADING STATE OVERLAY */
.component-loading::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(1px);
  z-index: 1;
  border-radius: inherit;
}

/* LOADING SPINNER PARA CARDS */
.component-loading::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 20px;
  height: 20px;
  margin: -10px 0 0 -10px;
  border: 2px solid transparent;
  border-top: 2px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  z-index: 2;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* FOCUS STYLES MEJORADOS */
.card:focus-visible {
  outline: 2px solid transparent;
  outline-offset: 2px;
}

/* RESPONSIVE ADJUSTMENTS */
@media (max-width: 640px) {
  .card-header,
  .card-body,
  .card-footer {
    padding-left: 1rem;
    padding-right: 1rem;
  }
}
</style>
