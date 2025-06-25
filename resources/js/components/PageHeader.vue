<template>
  <div class="bg-white shadow-material-1 rounded-lg mb-6">
    <div class="px-6 py-4 sm:px-8">
      <div class="flex items-center justify-between">
        <!-- Title and Breadcrumb -->
        <div>
          <!-- Breadcrumb -->
          <nav v-if="breadcrumbs && breadcrumbs.length > 0" class="flex mb-2" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2">
              <li v-for="(crumb, index) in breadcrumbs" :key="index" class="flex items-center">
                <router-link
                  v-if="crumb.href && index < breadcrumbs.length - 1"
                  :to="crumb.href"
                  class="text-sm text-gray-500 hover:text-gray-700 transition-colors"
                >
                  {{ crumb.name }}
                </router-link>
                <span
                  v-else
                  class="text-sm"
                  :class="index === breadcrumbs.length - 1 ? 'text-gray-900 font-medium' : 'text-gray-500'"
                >
                  {{ crumb.name }}
                </span>
                <ChevronRightIcon
                  v-if="index < breadcrumbs.length - 1"
                  class="h-4 w-4 text-gray-400 mx-2"
                />
              </li>
            </ol>
          </nav>

          <!-- Title and Description -->
          <div class="flex items-center space-x-3">
            <div
              v-if="icon"
              class="h-10 w-10 rounded-lg flex items-center justify-center"
              :class="iconBgClass"
            >
              <component :is="icon" class="h-5 w-5" :class="iconClass" />
            </div>
            <div>
              <h1 class="text-2xl font-bold text-gray-900">
                {{ title }}
              </h1>
              <p v-if="description" class="text-sm text-gray-600 mt-1">
                {{ description }}
              </p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div v-if="$slots.actions" class="flex items-center space-x-3">
          <slot name="actions" />
        </div>
      </div>

      <!-- Stats Row -->
      <div v-if="stats && stats.length > 0" class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div
          v-for="stat in stats"
          :key="stat.name"
          class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors"
        >
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">{{ stat.name }}</p>
              <p class="text-2xl font-bold text-gray-900">{{ stat.value }}</p>
              <p v-if="stat.change" class="text-xs" :class="stat.changeType === 'increase' ? 'text-green-600' : 'text-red-600'">
                {{ stat.change }}
              </p>
            </div>
            <div
              v-if="stat.icon"
              class="h-10 w-10 rounded-lg flex items-center justify-center"
              :class="stat.iconBg || 'bg-primary-100'"
            >
              <component :is="stat.icon" class="h-5 w-5" :class="stat.iconColor || 'text-primary-600'" />
            </div>
          </div>
        </div>
      </div>

      <!-- Custom Content -->
      <div v-if="$slots.default" class="mt-6">
        <slot />
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'
import { ChevronRightIcon } from '@heroicons/vue/24/outline'

export default {
  name: 'PageHeader',
  components: {
    ChevronRightIcon
  },
  props: {
    title: {
      type: String,
      required: true
    },
    description: {
      type: String,
      default: null
    },
    icon: {
      type: [Object, Function],
      default: null
    },
    iconColor: {
      type: String,
      default: 'primary'
    },
    breadcrumbs: {
      type: Array,
      default: null
    },
    stats: {
      type: Array,
      default: null
    }
  },
  setup(props) {
    const iconBgClass = computed(() => {
      const colorMap = {
        primary: 'bg-primary-100',
        green: 'bg-green-100',
        blue: 'bg-blue-100',
        yellow: 'bg-yellow-100',
        red: 'bg-red-100',
        purple: 'bg-purple-100',
        gray: 'bg-gray-100'
      }
      return colorMap[props.iconColor] || colorMap.primary
    })

    const iconClass = computed(() => {
      const colorMap = {
        primary: 'text-primary-600',
        green: 'text-green-600',
        blue: 'text-blue-600',
        yellow: 'text-yellow-600',
        red: 'text-red-600',
        purple: 'text-purple-600',
        gray: 'text-gray-600'
      }
      return colorMap[props.iconColor] || colorMap.primary
    })

    return {
      iconBgClass,
      iconClass
    }
  }
}
</script>
