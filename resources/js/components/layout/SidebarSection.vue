<template>
  <div class="mb-4">
    <Transition name="fade">
      <h3 
        v-if="!collapsed" 
        class="px-3 text-xs font-semibold text-surface-500 uppercase tracking-wider mb-2"
      >
        {{ title }}
      </h3>
    </Transition>
    <div class="space-y-1">
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
// Props
interface Props {
  title: string;
  collapsed?: boolean;
}

defineProps<Props>();
</script>
