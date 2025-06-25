<template>
  <button
    :class="buttonClasses"
    :disabled="disabled || loading"
    @click="$emit('click', $event)"
    class="ripple"
  >
    <span v-if="loading" class="flex items-center">
      <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
        <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" class="opacity-75"></path>
      </svg>
      {{ loadingText || text }}
    </span>
    <span v-else class="flex items-center">
      <component 
        v-if="icon" 
        :is="icon" 
        :class="iconClasses"
      />
      {{ text }}
    </span>
  </button>
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'MaterialButton',
  emits: ['click'],
  props: {
    text: {
      type: String,
      required: true
    },
    variant: {
      type: String,
      default: 'filled', // filled, outlined, text
      validator: (value) => ['filled', 'outlined', 'text'].includes(value)
    },
    size: {
      type: String,
      default: 'medium', // small, medium, large
      validator: (value) => ['small', 'medium', 'large'].includes(value)
    },
    disabled: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    },
    loadingText: {
      type: String,
      default: ''
    },
    icon: {
      type: Object,
      default: null
    },
    fullWidth: {
      type: Boolean,
      default: false
    }
  },
  setup(props) {
    const baseClasses = 'inline-flex items-center justify-center font-medium rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed'
    
    const sizeClasses = computed(() => {
      switch (props.size) {
        case 'small':
          return 'px-4 py-2 text-sm'
        case 'large':
          return 'px-8 py-4 text-base'
        default:
          return 'px-6 py-3 text-sm'
      }
    })
    
    const variantClasses = computed(() => {
      switch (props.variant) {
        case 'outlined':
          return 'border-2 border-primary-500 text-primary-600 bg-transparent hover:bg-primary-50 focus:ring-primary-500'
        case 'text':
          return 'text-primary-600 bg-transparent hover:bg-primary-50 focus:ring-primary-500'
        default: // filled
          return 'bg-primary-500 text-white hover:bg-primary-600 focus:ring-primary-500 shadow-material-1 hover:shadow-material-2'
      }
    })
    
    const buttonClasses = computed(() => {
      const classes = [baseClasses, sizeClasses.value, variantClasses.value]
      if (props.fullWidth) {
        classes.push('w-full')
      }
      return classes.join(' ')
    })
    
    const iconClasses = computed(() => {
      const size = props.size === 'large' ? 'h-5 w-5' : 'h-4 w-4'
      const margin = props.text ? 'mr-2' : ''
      return `${size} ${margin}`
    })
    
    return {
      buttonClasses,
      iconClasses
    }
  }
}
</script>
