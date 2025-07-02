<template>
  <teleport to="body">
    <div
      class="fixed inset-0 pointer-events-none z-50 flex items-start justify-end p-6"
      aria-live="assertive"
    >
      <div class="flex flex-col space-y-4 w-full max-w-sm">
        <transition-group
          name="toast"
          tag="div"
          class="space-y-4"
        >
          <BaseToast
            v-for="toast in toasts"
            :key="toast.id"
            :type="toast.type"
            :title="toast.title"
            :message="toast.message"
            :duration="toast.duration"
            :show-close="true"
            class="pointer-events-auto"
            @close="removeToast(toast.id)"
          />
        </transition-group>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { computed } from 'vue'
import { useToastStore } from '@/stores/toast'
import BaseToast from '@/components/common/BaseToast.vue'

// Store
const toastStore = useToastStore()

// Computed
const toasts = computed(() => toastStore.toasts)

// Methods
const removeToast = (id) => {
  toastStore.remove(id)
}
</script>

<style scoped>
/* Animaciones para las transiciones de toast */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.toast-move {
  transition: transform 0.3s ease;
}
</style>
