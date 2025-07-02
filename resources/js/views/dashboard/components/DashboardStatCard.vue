<template>
  <BaseCard class="dashboard-stat-card">
    <div class="flex items-center justify-between">
      <div class="flex-1">
        <div class="flex items-center gap-3 mb-2">
          <div :class="[
            'flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center',
            iconColorClass
          ]">
            <Icon :name="icon" class="w-5 h-5" />
          </div>
          <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400 truncate">
            {{ title }}
          </h3>
        </div>
        <div class="flex items-end justify-between">
          <p class="text-2xl font-bold text-gray-900 dark:text-white">
            {{ value }}
          </p>
          <div v-if="change" :class="[
            'flex items-center text-sm font-medium',
            trendColorClass
          ]">
            <Icon 
              :name="trend === 'up' ? 'arrow-trending-up' : 'arrow-trending-down'" 
              class="w-4 h-4 mr-1" 
            />
            {{ change }}
          </div>
        </div>
      </div>
    </div>
  </BaseCard>
</template>

<script setup>
import { computed } from 'vue'
import Icon from '@/components/icons/index.js'
import { BaseCard } from '@/components/common'

const props = defineProps({
  icon: {
    type: String,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  change: {
    type: String,
    default: null
  },
  trend: {
    type: String,
    default: 'up',
    validator: (value) => ['up', 'down'].includes(value)
  },
  color: {
    type: String,
    default: 'blue',
    validator: (value) => ['blue', 'green', 'purple', 'orange', 'red', 'yellow'].includes(value)
  }
})

const iconColorClass = computed(() => {
  const colorMap = {
    blue: 'bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400',
    green: 'bg-green-100 text-green-600 dark:bg-green-900/20 dark:text-green-400',
    purple: 'bg-purple-100 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400',
    orange: 'bg-orange-100 text-orange-600 dark:bg-orange-900/20 dark:text-orange-400',
    red: 'bg-red-100 text-red-600 dark:bg-red-900/20 dark:text-red-400',
    yellow: 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/20 dark:text-yellow-400'
  }
  return colorMap[props.color] || colorMap.blue
})

const trendColorClass = computed(() => {
  if (props.trend === 'up') {
    return 'text-green-600 dark:text-green-400'
  } else {
    return 'text-red-600 dark:text-red-400'
  }
})
</script>

<style scoped>
.dashboard-stat-card {
  transition: all 0.2s ease-in-out;
}

.dashboard-stat-card:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.dark .dashboard-stat-card:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
}
</style>
