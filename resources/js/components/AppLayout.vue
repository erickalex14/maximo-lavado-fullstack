<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <AppNavigation />
    
    <!-- Main Content -->
    <main class="pb-16">
      <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div v-if="$slots.header" class="mb-8">
          <slot name="header" />
        </div>
        
        <!-- Page Content -->
        <div class="px-4 sm:px-0">
          <slot />
        </div>
      </div>
    </main>

    <!-- Loading Overlay -->
    <transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-300"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="loading"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      >
        <div class="bg-white rounded-lg p-6 flex items-center space-x-4 shadow-material-3">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
          <span class="text-gray-700 font-medium">{{ loadingMessage }}</span>
        </div>
      </div>
    </transition>

    <!-- Toast Notifications -->
    <transition-group
      name="toast"
      tag="div"
      class="fixed top-4 right-4 space-y-2 z-50"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="max-w-sm w-full bg-white shadow-material-2 rounded-lg pointer-events-auto overflow-hidden"
        :class="[
          toast.type === 'success' ? 'border-l-4 border-green-500' : '',
          toast.type === 'error' ? 'border-l-4 border-red-500' : '',
          toast.type === 'warning' ? 'border-l-4 border-yellow-500' : '',
          toast.type === 'info' ? 'border-l-4 border-blue-500' : ''
        ]"
      >
        <div class="p-4">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <CheckCircleIcon
                v-if="toast.type === 'success'"
                class="h-5 w-5 text-green-500"
              />
              <ExclamationCircleIcon
                v-else-if="toast.type === 'error'"
                class="h-5 w-5 text-red-500"
              />
              <ExclamationTriangleIcon
                v-else-if="toast.type === 'warning'"
                class="h-5 w-5 text-yellow-500"
              />
              <InformationCircleIcon
                v-else
                class="h-5 w-5 text-blue-500"
              />
            </div>
            <div class="ml-3 w-0 flex-1">
              <p class="text-sm font-medium text-gray-900">
                {{ toast.title }}
              </p>
              <p v-if="toast.message" class="mt-1 text-sm text-gray-500">
                {{ toast.message }}
              </p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
              <button
                @click="removeToast(toast.id)"
                class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-600 focus:outline-none"
              >
                <XMarkIcon class="h-5 w-5" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition-group>
  </div>
</template>

<script>
import { ref, computed } from 'vue'
import AppNavigation from './AppNavigation.vue'
import {
  CheckCircleIcon,
  ExclamationCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

export default {
  name: 'AppLayout',
  components: {
    AppNavigation,
    CheckCircleIcon,
    ExclamationCircleIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    XMarkIcon
  },
  props: {
    loading: {
      type: Boolean,
      default: false
    },
    loadingMessage: {
      type: String,
      default: 'Cargando...'
    }
  },
  setup() {
    const toasts = ref([])
    let toastId = 0

    const addToast = (type, title, message = null, duration = 5000) => {
      const id = ++toastId
      const toast = { id, type, title, message }
      toasts.value.push(toast)

      if (duration > 0) {
        setTimeout(() => {
          removeToast(id)
        }, duration)
      }

      return id
    }

    const removeToast = (id) => {
      const index = toasts.value.findIndex(toast => toast.id === id)
      if (index > -1) {
        toasts.value.splice(index, 1)
      }
    }

    // Global toast methods
    const showSuccess = (title, message, duration) => addToast('success', title, message, duration)
    const showError = (title, message, duration) => addToast('error', title, message, duration)
    const showWarning = (title, message, duration) => addToast('warning', title, message, duration)
    const showInfo = (title, message, duration) => addToast('info', title, message, duration)

    return {
      toasts,
      addToast,
      removeToast,
      showSuccess,
      showError,
      showWarning,
      showInfo
    }
  }
}
</script>

<style scoped>
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
