<template>
  <router-link
    :to="to"
    class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors"
    :class="[
      isActive 
        ? 'bg-primary-50 text-primary-700 border-r-2 border-primary-500' 
        : 'text-surface-600 hover:bg-surface-50 hover:text-surface-900'
    ]"
  >
    <!-- Icon -->
    <div 
      class="flex-shrink-0 mr-3" 
      :class="isActive ? 'text-primary-600' : 'text-surface-400 group-hover:text-surface-600'"
      v-html="icon"
    ></div>

    <!-- Label -->
    <Transition name="fade">
      <span v-if="!collapsed" class="truncate">{{ label }}</span>
    </Transition>

    <!-- Tooltip for collapsed state -->
    <div
      v-if="collapsed"
      class="absolute left-16 ml-2 px-2 py-1 bg-surface-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity z-50 whitespace-nowrap pointer-events-none"
    >
      {{ label }}
    </div>
  </router-link>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';

// Props
interface Props {
  to: string;
  icon: string;
  label: string;
  collapsed?: boolean;
}

const props = defineProps<Props>();
const route = useRoute();

// Computed
const isActive = computed(() => {
  if (props.to === '/') {
    return route.path === '/';
  }
  return route.path.startsWith(props.to);
});
</script>
