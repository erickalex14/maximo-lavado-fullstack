<template>
  <div class="material-input-group">
    <label 
      v-if="label" 
      :for="inputId"
      class="block text-sm font-medium text-gray-700 mb-2"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>
    
    <div class="relative">
      <div v-if="prefixIcon" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <component :is="prefixIcon" class="h-5 w-5 text-gray-400" />
      </div>
      
      <input
        :id="inputId"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :autocomplete="autocomplete"
        :class="inputClasses"
        @input="$emit('update:modelValue', $event.target.value)"
        @focus="$emit('focus', $event)"
        @blur="$emit('blur', $event)"
        @keydown.enter="$emit('enter', $event)"
      />
      
      <div v-if="suffixIcon || type === 'password'" class="absolute inset-y-0 right-0 pr-3 flex items-center">
        <button
          v-if="type === 'password'"
          type="button"
          @click="togglePasswordVisibility"
          class="text-gray-400 hover:text-gray-600 focus:outline-none"
        >
          <EyeIcon v-if="showPassword" class="h-5 w-5" />
          <EyeSlashIcon v-else class="h-5 w-5" />
        </button>
        <component v-else-if="suffixIcon" :is="suffixIcon" class="h-5 w-5 text-gray-400" />
      </div>
    </div>
    
    <div v-if="error || hint" class="mt-2">
      <p v-if="error" class="text-sm text-red-600 flex items-center">
        <ExclamationCircleIcon class="h-4 w-4 mr-1" />
        {{ error }}
      </p>
      <p v-else-if="hint" class="text-sm text-gray-500">
        {{ hint }}
      </p>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { EyeIcon, EyeSlashIcon, ExclamationCircleIcon } from '@heroicons/vue/24/outline'

export default {
  name: 'MaterialInput',
  components: {
    EyeIcon,
    EyeSlashIcon,
    ExclamationCircleIcon
  },
  emits: ['update:modelValue', 'focus', 'blur', 'enter'],
  props: {
    modelValue: {
      type: [String, Number],
      default: ''
    },
    type: {
      type: String,
      default: 'text'
    },
    label: {
      type: String,
      default: ''
    },
    placeholder: {
      type: String,
      default: ''
    },
    disabled: {
      type: Boolean,
      default: false
    },
    required: {
      type: Boolean,
      default: false
    },
    error: {
      type: String,
      default: ''
    },
    hint: {
      type: String,
      default: ''
    },
    prefixIcon: {
      type: Object,
      default: null
    },
    suffixIcon: {
      type: Object,
      default: null
    },
    autocomplete: {
      type: String,
      default: ''
    }
  },
  setup(props) {
    const inputId = ref(`input-${Math.random().toString(36).substr(2, 9)}`)
    const showPassword = ref(false)
    const actualType = ref(props.type)
    
    const togglePasswordVisibility = () => {
      showPassword.value = !showPassword.value
      actualType.value = showPassword.value ? 'text' : 'password'
    }
    
    const inputClasses = computed(() => {
      const base = 'material-input'
      const hasPrefix = props.prefixIcon ? 'pl-10' : ''
      const hasSuffix = props.suffixIcon || props.type === 'password' ? 'pr-10' : ''
      const errorClass = props.error ? 'material-input-error' : ''
      const disabledClass = props.disabled ? 'bg-gray-50 cursor-not-allowed' : ''
      
      return [base, hasPrefix, hasSuffix, errorClass, disabledClass].filter(Boolean).join(' ')
    })
    
    onMounted(() => {
      actualType.value = props.type
    })
    
    return {
      inputId,
      showPassword,
      actualType,
      togglePasswordVisibility,
      inputClasses
    }
  }
}
</script>
