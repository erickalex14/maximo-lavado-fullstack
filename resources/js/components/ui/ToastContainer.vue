<template>
  <div class="fixed top-4 right-4 z-50 flex flex-col gap-2 w-80">
    <transition-group name="toast-fade" tag="div">
      <div v-for="m in messages" :key="m.id" :class="cls(m.type)" class="px-4 py-3 rounded shadow flex items-start gap-3 text-sm">
        <div class="flex-1">{{ m.text }}</div>
        <button class="opacity-70 hover:opacity-100" @click="remove(m.id)">×</button>
      </div>
    </transition-group>
  </div>
</template>
<script setup lang="ts">
import { useToast } from '@/composables/useToast';
const { messages, remove } = useToast();
function cls(type: string) {
  switch(type) {
    case 'success': return 'bg-green-600 text-white';
    case 'error': return 'bg-red-600 text-white';
    case 'warning': return 'bg-yellow-500 text-white';
    default: return 'bg-gray-800 text-white';
  }
}
</script>
<style scoped>
.toast-fade-enter-active, .toast-fade-leave-active { transition: all .25s; }
.toast-fade-enter-from, .toast-fade-leave-to { opacity:0; transform: translateY(-6px); }
</style>
