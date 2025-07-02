<template>
  <div class="base-select" :class="{ 'base-select--disabled': disabled }">
    <!-- Label -->
    <label
      v-if="label"
      :for="selectId"
      class="form-label"
      :class="{ 'text-gray-400': disabled }"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>

    <!-- Select container -->
    <div class="relative">
      <!-- Select button -->
      <button
        :id="selectId"
        ref="selectButton"
        type="button"
        class="select-button"
        :class="[
          selectClasses,
          {
            'select-button--open': isOpen,
            'select-button--error': hasError,
            'select-button--disabled': disabled
          }
        ]"
        :disabled="disabled"
        :aria-haspopup="'listbox'"
        :aria-expanded="isOpen"
        :aria-labelledby="label ? `${selectId}-label` : undefined"
        @click="toggleDropdown"
        @keydown="handleKeydown"
      >
        <!-- Selected option or placeholder -->
        <span class="select-display">
          <slot
            v-if="selectedOption"
            name="selected"
            :option="selectedOption"
            :label="getOptionLabel(selectedOption)"
          >
            {{ getOptionLabel(selectedOption) }}
          </slot>
          <span v-else class="text-gray-500">{{ placeholder }}</span>
        </span>

        <!-- Loading spinner -->
        <BaseLoading
          v-if="loading"
          type="spinner"
          size="sm"
          class="ml-2"
        />

        <!-- Dropdown arrow -->
        <svg
          v-else
          class="select-arrow"
          :class="{ 'rotate-180': isOpen }"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M19 9l-7 7-7-7"
          />
        </svg>
      </button>

      <!-- Dropdown menu -->
      <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 translate-y-1"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-1"
      >
        <div
          v-show="isOpen"
          ref="dropdown"
          class="select-dropdown"
          role="listbox"
          :aria-labelledby="selectId"
        >
          <!-- Search input -->
          <div v-if="searchable" class="p-2 border-b border-gray-200">
            <input
              ref="searchInput"
              v-model="searchQuery"
              type="text"
              class="input-base text-sm"
              :placeholder="searchPlaceholder"
              @keydown.stop
            />
          </div>

          <!-- Options list -->
          <div class="max-h-60 overflow-auto">
            <!-- Empty state -->
            <div
              v-if="filteredOptions.length === 0"
              class="px-3 py-2 text-sm text-gray-500 text-center"
            >
              {{ searchQuery ? 'No se encontraron resultados' : 'No hay opciones disponibles' }}
            </div>

            <!-- Options -->
            <button
              v-for="(option, index) in filteredOptions"
              :key="getOptionValue(option)"
              type="button"
              class="select-option"
              :class="{
                'select-option--selected': isSelected(option),
                'select-option--highlighted': highlightedIndex === index
              }"
              role="option"
              :aria-selected="isSelected(option)"
              @click="selectOption(option)"
              @mouseenter="highlightedIndex = index"
            >
              <slot
                name="option"
                :option="option"
                :selected="isSelected(option)"
                :highlighted="highlightedIndex === index"
              >
                <span class="truncate">{{ getOptionLabel(option) }}</span>
                
                <!-- Selected checkmark -->
                <svg
                  v-if="isSelected(option)"
                  class="w-4 h-4 text-blue-600 ml-auto flex-shrink-0"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                    clip-rule="evenodd"
                  />
                </svg>
              </slot>
            </button>
          </div>

          <!-- Custom footer slot -->
          <div v-if="$slots.footer" class="border-t border-gray-200">
            <slot name="footer" />
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
  </div>
</template>

<script>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import BaseLoading from './BaseLoading.vue'

export default {
  name: 'BaseSelect',
  components: {
    BaseLoading
  },
  props: {
    // v-model
    modelValue: {
      type: [String, Number, Object, Array],
      default: null
    },
    
    // Options
    options: {
      type: Array,
      default: () => []
    },
    
    // Option configuration
    optionLabel: {
      type: [String, Function],
      default: 'label'
    },
    optionValue: {
      type: [String, Function],
      default: 'value'
    },
    
    // Basic props
    label: String,
    placeholder: {
      type: String,
      default: 'Seleccione una opción'
    },
    
    // Validation
    required: Boolean,
    errorMessage: String,
    helpText: String,
    
    // State
    disabled: Boolean,
    loading: Boolean,
    
    // Features
    searchable: Boolean,
    searchPlaceholder: {
      type: String,
      default: 'Buscar...'
    },
    clearable: Boolean,
    multiple: Boolean,
    
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
    }
  },
  
  emits: ['update:modelValue', 'change', 'search', 'open', 'close'],
  
  setup(props, { emit }) {
    // Refs
    const selectButton = ref(null)
    const dropdown = ref(null)
    const searchInput = ref(null)
    
    // State
    const isOpen = ref(false)
    const searchQuery = ref('')
    const highlightedIndex = ref(-1)
    
    // Generate unique ID
    const selectId = `select-${Math.random().toString(36).substr(2, 9)}`
    
    // Computed
    const hasError = computed(() => !!props.errorMessage)
    
    const selectedOption = computed(() => {
      if (!props.modelValue) return null
      
      if (props.multiple) {
        return props.options.filter(option => 
          props.modelValue.includes(getOptionValue(option))
        )
      }
      
      return props.options.find(option => 
        getOptionValue(option) === props.modelValue
      )
    })
    
    const filteredOptions = computed(() => {
      if (!props.searchable || !searchQuery.value) {
        return props.options
      }
      
      const query = searchQuery.value.toLowerCase()
      return props.options.filter(option => {
        const label = getOptionLabel(option).toLowerCase()
        return label.includes(query)
      })
    })
    
    const selectClasses = computed(() => {
      const classes = ['input-base']
      
      // Size variants
      if (props.size === 'sm') classes.push('text-sm py-1.5')
      else if (props.size === 'lg') classes.push('text-lg py-3')
      
      return classes
    })
    
    // Methods
    const getOptionLabel = (option) => {
      if (!option) return ''
      
      if (typeof props.optionLabel === 'function') {
        return props.optionLabel(option)
      }
      
      if (typeof option === 'object' && option !== null) {
        return option[props.optionLabel] || option.label || option.name || String(option)
      }
      
      return String(option)
    }
    
    const getOptionValue = (option) => {
      if (typeof props.optionValue === 'function') {
        return props.optionValue(option)
      }
      
      if (typeof option === 'object' && option !== null) {
        return option[props.optionValue] || option.value || option.id || option
      }
      
      return option
    }
    
    const isSelected = (option) => {
      if (props.multiple) {
        return props.modelValue?.includes(getOptionValue(option)) || false
      }
      
      return getOptionValue(option) === props.modelValue
    }
    
    const toggleDropdown = () => {
      if (props.disabled || props.loading) return
      
      if (isOpen.value) {
        closeDropdown()
      } else {
        openDropdown()
      }
    }
    
    const openDropdown = async () => {
      isOpen.value = true
      emit('open')
      
      await nextTick()
      
      if (props.searchable && searchInput.value) {
        searchInput.value.focus()
      }
      
      // Reset highlighted index
      highlightedIndex.value = selectedOption.value ? 
        filteredOptions.value.findIndex(option => isSelected(option)) : -1
    }
    
    const closeDropdown = () => {
      isOpen.value = false
      searchQuery.value = ''
      highlightedIndex.value = -1
      emit('close')
      
      // Return focus to button
      nextTick(() => {
        selectButton.value?.focus()
      })
    }
    
    const selectOption = (option) => {
      const value = getOptionValue(option)
      
      if (props.multiple) {
        const currentValues = props.modelValue || []
        let newValues
        
        if (currentValues.includes(value)) {
          newValues = currentValues.filter(v => v !== value)
        } else {
          newValues = [...currentValues, value]
        }
        
        emit('update:modelValue', newValues)
        emit('change', newValues, option)
      } else {
        emit('update:modelValue', value)
        emit('change', value, option)
        closeDropdown()
      }
    }
    
    const handleKeydown = (event) => {
      switch (event.key) {
        case 'Enter':
        case ' ':
          event.preventDefault()
          if (!isOpen.value) {
            openDropdown()
          } else if (highlightedIndex.value >= 0) {
            selectOption(filteredOptions.value[highlightedIndex.value])
          }
          break
          
        case 'Escape':
          event.preventDefault()
          closeDropdown()
          break
          
        case 'ArrowDown':
          event.preventDefault()
          if (!isOpen.value) {
            openDropdown()
          } else {
            highlightedIndex.value = Math.min(
              highlightedIndex.value + 1,
              filteredOptions.value.length - 1
            )
          }
          break
          
        case 'ArrowUp':
          event.preventDefault()
          if (isOpen.value) {
            highlightedIndex.value = Math.max(highlightedIndex.value - 1, 0)
          }
          break
          
        case 'Tab':
          if (isOpen.value) {
            closeDropdown()
          }
          break
      }
    }
    
    const handleClickOutside = (event) => {
      if (isOpen.value && 
          !selectButton.value?.contains(event.target) && 
          !dropdown.value?.contains(event.target)) {
        closeDropdown()
      }
    }
    
    // Watchers
    watch(searchQuery, (newQuery) => {
      emit('search', newQuery)
      highlightedIndex.value = -1
    })
    
    // Lifecycle
    onMounted(() => {
      document.addEventListener('click', handleClickOutside)
    })
    
    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside)
    })
    
    return {
      // Refs
      selectButton,
      dropdown,
      searchInput,
      
      // State
      isOpen,
      searchQuery,
      highlightedIndex,
      selectId,
      
      // Computed
      hasError,
      selectedOption,
      filteredOptions,
      selectClasses,
      
      // Methods
      getOptionLabel,
      getOptionValue,
      isSelected,
      toggleDropdown,
      openDropdown,
      closeDropdown,
      selectOption,
      handleKeydown
    }
  }
}
</script>

<style scoped>
.base-select {
  @apply w-full;
}

.base-select--disabled {
  @apply opacity-50 cursor-not-allowed;
}

.select-button {
  @apply w-full flex items-center justify-between;
  @apply bg-white border border-gray-300 rounded-md px-3 py-2;
  @apply text-left focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500;
  @apply transition-colors duration-200;
}

.select-button--open {
  @apply border-blue-500 ring-2 ring-blue-500;
}

.select-button--error {
  @apply border-red-300 focus:border-red-500 focus:ring-red-500;
}

.select-button--disabled {
  @apply bg-gray-50 cursor-not-allowed;
}

.select-display {
  @apply flex-1 truncate;
}

.select-arrow {
  @apply w-5 h-5 text-gray-400 transition-transform duration-200 flex-shrink-0 ml-2;
}

.select-dropdown {
  @apply absolute z-50 w-full mt-1 bg-white rounded-md shadow-lg border border-gray-200;
  @apply max-h-60 overflow-hidden;
}

.select-option {
  @apply w-full text-left px-3 py-2 text-sm;
  @apply hover:bg-gray-50 focus:bg-gray-50 focus:outline-none;
  @apply flex items-center justify-between;
  @apply transition-colors duration-150;
}

.select-option--selected {
  @apply bg-blue-50 text-blue-700;
}

.select-option--highlighted {
  @apply bg-gray-100;
}
</style>
