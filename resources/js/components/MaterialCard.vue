<template>
  <div :class="cardClasses" class="fade-in">
    <div v-if="title || subtitle || $slots.header" class="material-card-header">
      <slot name="header">
        <h3 v-if="title" class="text-lg font-semibold text-gray-900">
          {{ title }}
        </h3>
        <p v-if="subtitle" class="text-sm text-gray-600 mt-1">
          {{ subtitle }}
        </p>
      </slot>
    </div>
    
    <div class="material-card-content">
      <slot></slot>
    </div>
    
    <div v-if="$slots.actions" class="material-card-actions">
      <slot name="actions"></slot>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'MaterialCard',
  props: {
    title: {
      type: String,
      default: ''
    },
    subtitle: {
      type: String,
      default: ''
    },
    elevated: {
      type: Boolean,
      default: false
    },
    outlined: {
      type: Boolean,
      default: false
    },
    padding: {
      type: String,
      default: 'normal', // none, small, normal, large
      validator: (value) => ['none', 'small', 'normal', 'large'].includes(value)
    }
  },
  setup(props) {
    const cardClasses = computed(() => {
      const base = props.elevated ? 'material-card-elevated' : 'material-card'
      const outlined = props.outlined ? 'border-2' : ''
      return [base, outlined].filter(Boolean).join(' ')
    })
    
    return {
      cardClasses
    }
  }
}
</script>

<style scoped>
.material-card-header {
  @apply px-6 py-4 border-b border-gray-200;
}

.material-card-content {
  @apply p-6;
}

.material-card-actions {
  @apply px-6 py-4 border-t border-gray-200 flex justify-end space-x-3;
}

/* Padding variants */
.padding-none .material-card-content {
  @apply p-0;
}

.padding-small .material-card-content {
  @apply p-4;
}

.padding-large .material-card-content {
  @apply p-8;
}
</style>
