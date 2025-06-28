<template>
  <div class="material-card hover-lift">
    <div class="card-body">
      <div class="flex items-center">
        <div class="flex-shrink-0">
          <div 
            class="w-12 h-12 rounded-lg flex items-center justify-center"
            :class="iconBackgroundClass"
          >
            <component 
              :is="icon" 
              class="w-6 h-6"
              :class="iconColorClass"
            />
          </div>
        </div>
        
        <div class="ml-4 flex-1">
          <p class="text-sm font-medium text-gray-500 truncate">
            {{ title }}
          </p>
          <p class="text-2xl font-bold text-gray-900">
            {{ value }}
          </p>
        </div>
        
        <div v-if="change !== undefined" class="flex-shrink-0">
          <div 
            class="flex items-center text-sm font-medium"
            :class="changeColorClass"
          >
            <component 
              :is="changeIcon" 
              class="w-4 h-4 mr-1"
            />
            {{ Math.abs(change) }}%
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { 
  ArrowUpIcon, 
  ArrowDownIcon, 
  MinusIcon 
} from '@heroicons/vue/24/solid';

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  change: {
    type: Number,
    default: undefined
  },
  icon: {
    type: Object,
    required: true
  },
  color: {
    type: String,
    default: 'blue',
    validator: (value) => ['blue', 'green', 'red', 'yellow', 'purple', 'orange'].includes(value)
  }
});

const colorClasses = {
  blue: {
    background: 'bg-blue-100',
    icon: 'text-blue-600'
  },
  green: {
    background: 'bg-green-100',
    icon: 'text-green-600'
  },
  red: {
    background: 'bg-red-100',
    icon: 'text-red-600'
  },
  yellow: {
    background: 'bg-yellow-100',
    icon: 'text-yellow-600'
  },
  purple: {
    background: 'bg-purple-100',
    icon: 'text-purple-600'
  },
  orange: {
    background: 'bg-orange-100',
    icon: 'text-orange-600'
  }
};

const iconBackgroundClass = computed(() => {
  return colorClasses[props.color]?.background || colorClasses.blue.background;
});

const iconColorClass = computed(() => {
  return colorClasses[props.color]?.icon || colorClasses.blue.icon;
});

const changeIcon = computed(() => {
  if (props.change === undefined || props.change === 0) return MinusIcon;
  return props.change > 0 ? ArrowUpIcon : ArrowDownIcon;
});

const changeColorClass = computed(() => {
  if (props.change === undefined || props.change === 0) {
    return 'text-gray-500';
  }
  return props.change > 0 ? 'text-green-600' : 'text-red-600';
});
</script>
