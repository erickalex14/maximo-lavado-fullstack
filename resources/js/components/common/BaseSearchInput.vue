<template>
  <div class="base-search-input" :class="{ 'base-search-input--disabled': disabled }">
    <!-- Label -->
    <label
      v-if="label"
      :for="inputId"
      class="form-label"
      :class="{ 'text-gray-400': disabled }"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>

    <!-- Search input container -->
    <div class="relative">
      <!-- Search icon -->
      <div class="search-icon-container">
        <svg
          class="search-icon"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
          />
        </svg>
      </div>

      <!-- Input field -->
      <input
        :id="inputId"
        ref="inputRef"
        v-model="localValue"
        type="text"
        class="search-input"
        :class="[
          inputClasses,
          {
            'search-input--error': hasError,
            'search-input--disabled': disabled,
            'search-input--loading': loading
          }
        ]"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :autocomplete="autocomplete"
        @input="handleInput"
        @keydown="handleKeydown"
        @focus="handleFocus"
        @blur="handleBlur"
      />

      <!-- Right side content -->
      <div class="search-actions">
        <!-- Loading spinner -->
        <BaseLoading
          v-if="loading"
          type="spinner"
          size="sm"
          class="mr-2"
        />

        <!-- Clear button -->
        <button
          v-if="clearable && localValue && !loading"
          type="button"
          class="clear-button"
          :disabled="disabled"
          @click="clearInput"
        >
          <svg
            class="w-4 h-4"
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
        </button>

        <!-- Custom action slot -->
        <slot name="action" :value="localValue" :clear="clearInput" />
      </div>

      <!-- Suggestions dropdown -->
      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 translate-y-1"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-1"
      >
        <div
          v-if="showSuggestions && filteredSuggestions.length > 0"
          class="suggestions-dropdown"
        >
          <button
            v-for="(suggestion, index) in filteredSuggestions"
            :key="getSuggestionValue(suggestion)"
            type="button"
            class="suggestion-item"
            :class="{
              'suggestion-item--highlighted': highlightedIndex === index
            }"
            @click="selectSuggestion(suggestion)"
            @mouseenter="highlightedIndex = index"
          >
            <slot
              name="suggestion"
              :suggestion="suggestion"
              :query="localValue"
              :highlighted="highlightedIndex === index"
            >
              <!-- Search icon -->
              <svg
                class="w-4 h-4 text-gray-400 mr-3 flex-shrink-0"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
              </svg>
              
              <!-- Suggestion text with highlighting -->
              <span class="flex-1 text-left">
                <span v-html="highlightMatch(getSuggestionLabel(suggestion), localValue)" />
              </span>
            </slot>
          </button>

          <!-- No suggestions message -->
          <div
            v-if="localValue && filteredSuggestions.length === 0"
            class="suggestion-item text-gray-500 cursor-default"
          >
            No se encontraron sugerencias
          </div>
        </div>
      </Transition>
    </div>

    <!-- Error message -->
    <p v-if="errorMessage" class="form-error">
      {{ errorMessage }}
    </p>

    <!-- Help text -->
    <p v-if="helpText && !errorMessage" class="form-help">
      {{ helpText }}
    </p>

    <!-- Search info -->
    <div v-if="showSearchInfo && localValue" class="search-info">
      <span class="text-xs text-gray-500">
        {{ searchInfoText }}
      </span>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import BaseLoading from './BaseLoading.vue'

export default {
  name: 'BaseSearchInput',
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
    placeholder: {
      type: String,
      default: 'Buscar...'
    },
    
    // Validation
    required: Boolean,
    errorMessage: String,
    helpText: String,
    
    // State
    disabled: Boolean,
    readonly: Boolean,
    loading: Boolean,
    
    // Search features
    suggestions: {
      type: Array,
      default: () => []
    },
    suggestionLabel: {
      type: [String, Function],
      default: 'label'
    },
    suggestionValue: {
      type: [String, Function],
      default: 'value'
    },
    
    // Behavior
    clearable: {
      type: Boolean,
      default: true
    },
    debounce: {
      type: Number,
      default: 300
    },
    minLength: {
      type: Number,
      default: 1
    },
    maxSuggestions: {
      type: Number,
      default: 10
    },
    
    // Info
    showSearchInfo: Boolean,
    searchInfoText: String,
    
    // Styling
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    variant: {
      type: String,
      default: 'default',
      validator: (value) => ['default', 'rounded', 'pill'].includes(value)
    },
    
    // Input attributes
    autocomplete: {
      type: String,
      default: 'off'
    }
  },
  
  emits: [
    'update:modelValue',
    'search',
    'suggestion-select',
    'clear',
    'focus',
    'blur'
  ],
  
  setup(props, { emit }) {
    // Refs
    const inputRef = ref(null)
    
    // State
    const localValue = ref(props.modelValue)
    const isFocused = ref(false)
    const highlightedIndex = ref(-1)
    const debounceTimer = ref(null)
    
    // Generate unique ID
    const inputId = `search-input-${Math.random().toString(36).substr(2, 9)}`
    
    // Computed
    const hasError = computed(() => !!props.errorMessage)
    
    const showSuggestions = computed(() => {
      return isFocused.value && 
             localValue.value.length >= props.minLength &&
             props.suggestions.length > 0
    })
    
    const filteredSuggestions = computed(() => {
      if (!localValue.value || localValue.value.length < props.minLength) {
        return []
      }
      
      const query = localValue.value.toLowerCase()
      const filtered = props.suggestions.filter(suggestion => {
        const label = getSuggestionLabel(suggestion).toLowerCase()
        return label.includes(query)
      })
      
      return filtered.slice(0, props.maxSuggestions)
    })
    
    const inputClasses = computed(() => {
      const classes = ['input-base']
      
      // Size variants
      if (props.size === 'sm') classes.push('text-sm py-1.5 pl-8')
      else if (props.size === 'lg') classes.push('text-lg py-3 pl-10')
      else classes.push('pl-9')
      
      // Variant styles
      if (props.variant === 'rounded') classes.push('rounded-lg')
      else if (props.variant === 'pill') classes.push('rounded-full')
      
      return classes
    })
    
    // Methods
    const getSuggestionLabel = (suggestion) => {
      if (typeof props.suggestionLabel === 'function') {
        return props.suggestionLabel(suggestion)
      }
      
      if (typeof suggestion === 'object' && suggestion !== null) {
        return suggestion[props.suggestionLabel] || suggestion.label || suggestion.name || String(suggestion)
      }
      
      return String(suggestion)
    }
    
    const getSuggestionValue = (suggestion) => {
      if (typeof props.suggestionValue === 'function') {
        return props.suggestionValue(suggestion)
      }
      
      if (typeof suggestion === 'object' && suggestion !== null) {
        return suggestion[props.suggestionValue] || suggestion.value || suggestion.id || suggestion
      }
      
      return suggestion
    }
    
    const highlightMatch = (text, query) => {
      if (!query) return text
      
      const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi')
      return text.replace(regex, '<mark class="bg-yellow-200 text-yellow-800 rounded px-1">$1</mark>')
    }
    
    const handleInput = (event) => {
      const value = event.target.value
      localValue.value = value
      emit('update:modelValue', value)
      
      // Reset highlighted index
      highlightedIndex.value = -1
      
      // Debounce search emission
      if (debounceTimer.value) {
        clearTimeout(debounceTimer.value)
      }
      
      debounceTimer.value = setTimeout(() => {
        emit('search', value)
      }, props.debounce)
    }
    
    const handleKeydown = (event) => {
      if (!showSuggestions.value) return
      
      switch (event.key) {
        case 'ArrowDown':
          event.preventDefault()
          highlightedIndex.value = Math.min(
            highlightedIndex.value + 1,
            filteredSuggestions.value.length - 1
          )
          break
          
        case 'ArrowUp':
          event.preventDefault()
          highlightedIndex.value = Math.max(highlightedIndex.value - 1, -1)
          break
          
        case 'Enter':
          event.preventDefault()
          if (highlightedIndex.value >= 0) {
            selectSuggestion(filteredSuggestions.value[highlightedIndex.value])
          }
          break
          
        case 'Escape':
          event.preventDefault()
          inputRef.value?.blur()
          break
      }
    }
    
    const handleFocus = () => {
      isFocused.value = true
      emit('focus')
    }
    
    const handleBlur = () => {
      // Delay to allow suggestion clicks
      setTimeout(() => {
        isFocused.value = false
        highlightedIndex.value = -1
        emit('blur')
      }, 150)
    }
    
    const selectSuggestion = (suggestion) => {
      const value = getSuggestionLabel(suggestion)
      localValue.value = value
      emit('update:modelValue', value)
      emit('suggestion-select', suggestion, value)
      
      // Close suggestions
      isFocused.value = false
      highlightedIndex.value = -1
      
      // Focus input
      nextTick(() => {
        inputRef.value?.focus()
      })
    }
    
    const clearInput = () => {
      localValue.value = ''
      emit('update:modelValue', '')
      emit('clear')
      
      nextTick(() => {
        inputRef.value?.focus()
      })
    }
    
    const focus = () => {
      inputRef.value?.focus()
    }
    
    const blur = () => {
      inputRef.value?.blur()
    }
    
    // Watchers
    watch(() => props.modelValue, (newValue) => {
      if (newValue !== localValue.value) {
        localValue.value = newValue
      }
    })
    
    // Cleanup
    onUnmounted(() => {
      if (debounceTimer.value) {
        clearTimeout(debounceTimer.value)
      }
    })
    
    return {
      // Refs
      inputRef,
      
      // State
      localValue,
      isFocused,
      highlightedIndex,
      inputId,
      
      // Computed
      hasError,
      showSuggestions,
      filteredSuggestions,
      inputClasses,
      
      // Methods
      getSuggestionLabel,
      getSuggestionValue,
      highlightMatch,
      handleInput,
      handleKeydown,
      handleFocus,
      handleBlur,
      selectSuggestion,
      clearInput,
      focus,
      blur
    }
  }
}
</script>

<style scoped>
.base-search-input {
  @apply w-full;
}

.base-search-input--disabled {
  @apply opacity-50;
}

.search-icon-container {
  @apply absolute left-3 top-1/2 transform -translate-y-1/2 pointer-events-none;
}

.search-icon {
  @apply w-4 h-4 text-gray-400;
}

.search-input {
  @apply pr-10;
}

.search-input--error {
  @apply border-red-300 focus:border-red-500 focus:ring-red-500;
}

.search-input--disabled {
  @apply bg-gray-50 cursor-not-allowed;
}

.search-input--loading {
  @apply pr-16;
}

.search-actions {
  @apply absolute right-3 top-1/2 transform -translate-y-1/2 flex items-center;
}

.clear-button {
  @apply text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600;
  @apply transition-colors duration-150;
}

.suggestions-dropdown {
  @apply absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg border border-gray-200;
  @apply max-h-60 overflow-auto;
}

.suggestion-item {
  @apply w-full text-left px-3 py-2 text-sm flex items-center;
  @apply hover:bg-gray-50 focus:bg-gray-50 focus:outline-none;
  @apply transition-colors duration-150;
}

.suggestion-item--highlighted {
  @apply bg-gray-100;
}

.search-info {
  @apply mt-1;
}
</style>
