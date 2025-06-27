<template>
  <div
    v-if="isLoading"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
  >
    <div class="bg-white rounded-lg p-6 shadow-xl flex items-center space-x-4">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
      <div class="text-gray-700 font-medium">{{ loadingMessage }}</div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useLoadingStore } from '@/stores/loading'

const loadingStore = useLoadingStore()

const isLoading = computed(() => loadingStore.isLoading)
const loadingMessage = computed(() => loadingStore.loadingMessage)
</script>
