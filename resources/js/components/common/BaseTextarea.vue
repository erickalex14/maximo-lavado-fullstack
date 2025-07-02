<template>
  <div class="base-textarea" :class="{ 'base-textarea--disabled': disabled }">
    <!-- Label -->
    <label
      v-if="label"
      :for="textareaId"
      class="form-label"
      :class="{ 'text-gray-400': disabled }"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>

    <!-- Textarea container -->
    <div class="relative">
      <!-- Textarea -->
      <textarea
        :id="textareaId"
        ref="textareaRef"
        v-model="localValue"
        class="textarea-input"
        :class="[
          textareaClasses,
          {
            'textarea-input--error': hasError,
            'textarea-input--disabled': disabled,
            'textarea-input--resizable': resizable
          }
        ]"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :rows="rows"
        :minlength="minLength"
        :maxlength="maxLength"
        :required="required"
        :autocomplete="autocomplete"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
        @keydown="handleKeydown"
      />

      <!-- Character counter -->
      <div
        v-if="showCounter"
        class="textarea-counter"
        :class="{
          'text-red-500': isOverLimit,
          'text-yellow-500': isNearLimit,
          'text-gray-500': !isNearLimit && !isOverLimit
        }"
      >
        {{ characterCount }}{{ maxLength ? `/${maxLength}` : '' }}
      </div>

      <!-- Loading overlay -->
      <div v-if="loading" class="textarea-loading">
        <BaseLoading type="spinner" size="sm" />
      </div>
    </div>

    <!-- Error message -->
    <p v-if="errorMessage" class="form-error">
      {{ errorMessage }}
    </p>

    <!-- Help text -->
    <p v-if="helpText && !errorMessage" class="form-help">
      {{ helpText }}
    </p>

    <!-- Word/character info -->
    <div v-if="showWordCount" class="textarea-info">
      <span class="text-xs text-gray-500">
        {{ wordCount }} {{ wordCount === 1 ? 'palabra' : 'palabras' }}
      </span>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch, nextTick } from 'vue'
import BaseLoading from './BaseLoading.vue'

export default {
  name: 'BaseTextarea',
  components: {
    BaseLoading
  },
  props: {
    // v-model
    modelValue: {
      type: String,
      default: ''
    },
    
    // Basic props
    label: String,
    placeholder: String,
    
    // Validation
    required: Boolean,
    errorMessage: String,
    helpText: String,
    minLength: Number,
    maxLength: Number,
    
    // State
    disabled: Boolean,
    readonly: Boolean,
    loading: Boolean,
    
    // Textarea specific
    rows: {
      type: Number,
      default: 4
    },
    autoResize: {
      type: Boolean,
      default: false
    },
    resizable: {
      type: Boolean,
      default: true
    },
    
    // Features
    showCounter: {
      type: Boolean,
      default: false
    },
    showWordCount: {
      type: Boolean,
      default: false
    },
    
    // Styling
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    variant: {
      type: String,
      default: 'default',
      validator: (value) => ['default', 'bordered', 'filled'].includes(value)
    },
    
    // Input attributes
    autocomplete: String
  },
  
  emits: ['update:modelValue', 'blur', 'focus', 'input'],
  
  setup(props, { emit }) {
    // Refs
    const textareaRef = ref(null)
    
    // State
    const localValue = ref(props.modelValue)
    const isFocused = ref(false)
    
    // Generate unique ID
    const textareaId = `textarea-${Math.random().toString(36).substr(2, 9)}`
    
    // Computed
    const hasError = computed(() => !!props.errorMessage)
    
    const characterCount = computed(() => {
      return localValue.value?.length || 0
    })
    
    const wordCount = computed(() => {
      if (!localValue.value) return 0
      return localValue.value.trim().split(/\s+/).filter(word => word.length > 0).length
    })
    
    const isOverLimit = computed(() => {
      return props.maxLength && characterCount.value > props.maxLength
    })
    
    const isNearLimit = computed(() => {
      if (!props.maxLength) return false
      const remaining = props.maxLength - characterCount.value
      const threshold = Math.min(props.maxLength * 0.1, 50) // 10% or 50 chars
      return remaining <= threshold && remaining > 0
    })
    
    const textareaClasses = computed(() => {
      const classes = ['input-base']
      
      // Size variants
      if (props.size === 'sm') classes.push('text-sm p-2')
      else if (props.size === 'lg') classes.push('text-lg p-4')
      else classes.push('p-3')
      
      // Variant styles
      if (props.variant === 'bordered') classes.push('border-2')
      else if (props.variant === 'filled') classes.push('bg-gray-50')
      
      return classes
    })
    
    // Methods
    const handleInput = (event) => {
      const value = event.target.value
      
      // Enforce max length if specified
      if (props.maxLength && value.length > props.maxLength) {
        const truncated = value.slice(0, props.maxLength)
        localValue.value = truncated
        event.target.value = truncated
        emit('update:modelValue', truncated)
      } else {
        localValue.value = value
        emit('update:modelValue', value)
      }
      
      emit('input', event)
      
      // Auto-resize if enabled
      if (props.autoResize) {
        autoResize()
      }
    }
    
    const handleFocus = (event) => {
      isFocused.value = true
      emit('focus', event)
    }
    
    const handleBlur = (event) => {
      isFocused.value = false
      emit('blur', event)
    }
    
    const handleKeydown = (event) => {
      // Allow tab insertion if not disabled
      if (event.key === 'Tab' && !event.shiftKey && !props.disabled && !props.readonly) {
        // You can enable tab insertion here if needed
        // event.preventDefault()
        // insertTab()
      }
    }
    
    const autoResize = () => {
      if (!textareaRef.value || !props.autoResize) return
      
      nextTick(() => {
        const textarea = textareaRef.value
        textarea.style.height = 'auto'
        textarea.style.height = `${textarea.scrollHeight}px`
      })
    }
    
    const insertTab = () => {
      if (!textareaRef.value) return
      
      const textarea = textareaRef.value
      const start = textarea.selectionStart
      const end = textarea.selectionEnd
      const value = textarea.value
      
      const newValue = value.substring(0, start) + '\t' + value.substring(end)
      localValue.value = newValue
      emit('update:modelValue', newValue)
      
      nextTick(() => {
        textarea.selectionStart = textarea.selectionEnd = start + 1
      })
    }
    
    const focus = () => {
      textareaRef.value?.focus()
    }
    
    const blur = () => {
      textareaRef.value?.blur()
    }
    
    const select = () => {
      textareaRef.value?.select()
    }
    
    const setSelectionRange = (start, end) => {
      textareaRef.value?.setSelectionRange(start, end)
    }
    
    // Watchers
    watch(() => props.modelValue, (newValue) => {
      if (newValue !== localValue.value) {
        localValue.value = newValue
        if (props.autoResize) {
          nextTick(() => autoResize())
        }
      }
    })
    
    watch(() => props.autoResize, (newValue) => {
      if (newValue) {
        nextTick(() => autoResize())
      }
    })
    
    return {
      // Refs
      textareaRef,
      
      // State
      localValue,
      isFocused,
      textareaId,
      
      // Computed
      hasError,
      characterCount,
      wordCount,
      isOverLimit,
      isNearLimit,
      textareaClasses,
      
      // Methods
      handleInput,
      handleFocus,
      handleBlur,
      handleKeydown,
      focus,
      blur,
      select,
      setSelectionRange
    }
  }
}
</script>

<style scoped>
.base-textarea {
  @apply w-full;
}

.base-textarea--disabled {
  @apply opacity-50;
}

.textarea-input {
  @apply block w-full min-h-[4rem] resize-y;
  @apply border border-gray-300 rounded-md shadow-sm;
  @apply focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500;
  @apply transition-colors duration-200;
  @apply font-sans;
}

.textarea-input--error {
  @apply border-red-300 focus:border-red-500 focus:ring-red-500;
}

.textarea-input--disabled {
  @apply bg-gray-50 cursor-not-allowed resize-none;
}

.textarea-input--resizable {
  @apply resize-y;
}

.textarea-input:not(.textarea-input--resizable) {
  @apply resize-none;
}

.textarea-counter {
  @apply absolute bottom-2 right-2 text-xs font-medium;
  @apply bg-white bg-opacity-80 px-1 rounded;
}

.textarea-loading {
  @apply absolute inset-0 bg-white bg-opacity-50 flex items-center justify-center;
  @apply rounded-md;
}

.textarea-info {
  @apply mt-1;
}

/* Auto-resize specific styles */
.textarea-input[data-auto-resize="true"] {
  @apply resize-none overflow-hidden;
  @apply min-h-[4rem] max-h-96;
}
</style>
