<template>
  <div class="fixed top-4 right-4 z-50 space-y-3 max-w-sm">
    <TransitionGroup
      name="notification"
      tag="div"
      class="space-y-3"
    >
      <div
        v-for="notification in notificationStore.notifications"
        :key="notification.id"
        class="bg-white rounded-lg shadow-lg border-l-4 p-4"
        :class="getNotificationClasses(notification.type)"
      >
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <component 
              :is="getIcon(notification.type)"
              class="h-5 w-5"
              :class="getIconClasses(notification.type)"
            />
          </div>
          
          <div class="ml-3 flex-1">
            <h4 
              v-if="notification.title"
              class="text-sm font-medium"
              :class="getTitleClasses(notification.type)"
            >
              {{ notification.title }}
            </h4>
            <p 
              class="text-sm"
              :class="getMessageClasses(notification.type)"
            >
              {{ notification.message }}
            </p>
            
            <!-- Actions if any -->
            <div 
              v-if="notification.actions && notification.actions.length"
              class="mt-3 flex space-x-2"
            >
              <button
                v-for="action in notification.actions"
                :key="action.label"
                @click="handleAction(action, notification.id)"
                class="text-xs font-medium px-2 py-1 rounded"
                :class="action.classes || 'bg-gray-100 text-gray-800 hover:bg-gray-200'"
              >
                {{ action.label }}
              </button>
            </div>
          </div>
          
          <div class="ml-4 flex-shrink-0">
            <button
              @click="notificationStore.removeNotification(notification.id)"
              class="inline-flex text-gray-400 hover:text-gray-600 focus:outline-none"
            >
              <XMarkIcon class="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { useNotificationStore } from '@/stores/notification';
import { 
  CheckCircleIcon, 
  ExclamationTriangleIcon, 
  XCircleIcon, 
  InformationCircleIcon,
  XMarkIcon 
} from '@heroicons/vue/24/outline';

const notificationStore = useNotificationStore();

const getIcon = (type) => {
  const icons = {
    success: CheckCircleIcon,
    error: XCircleIcon,
    warning: ExclamationTriangleIcon,
    info: InformationCircleIcon
  };
  return icons[type] || InformationCircleIcon;
};

const getNotificationClasses = (type) => {
  const classes = {
    success: 'border-green-400',
    error: 'border-red-400',
    warning: 'border-yellow-400',
    info: 'border-blue-400'
  };
  return classes[type] || classes.info;
};

const getIconClasses = (type) => {
  const classes = {
    success: 'text-green-400',
    error: 'text-red-400',
    warning: 'text-yellow-400',
    info: 'text-blue-400'
  };
  return classes[type] || classes.info;
};

const getTitleClasses = (type) => {
  const classes = {
    success: 'text-green-800',
    error: 'text-red-800',
    warning: 'text-yellow-800',
    info: 'text-blue-800'
  };
  return classes[type] || classes.info;
};

const getMessageClasses = (type) => {
  const classes = {
    success: 'text-green-700',
    error: 'text-red-700',
    warning: 'text-yellow-700',
    info: 'text-blue-700'
  };
  return classes[type] || classes.info;
};

const handleAction = (action, notificationId) => {
  if (action.handler) {
    action.handler();
  }
  if (action.dismissOnClick !== false) {
    notificationStore.removeNotification(notificationId);
  }
};
</script>

<style scoped>
.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.notification-move {
  transition: transform 0.3s ease;
}
</style>
