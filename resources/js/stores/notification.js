import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useNotificationStore = defineStore('notification', () => {
  // Estado
  const notifications = ref([]);
  let notificationIdCounter = 0;

  // Actions
  const addNotification = (notification) => {
    const id = ++notificationIdCounter;
    const newNotification = {
      id,
      type: notification.type || 'info',
      title: notification.title || '',
      message: notification.message || '',
      duration: notification.duration || 5000,
      persistent: notification.persistent || false,
      actions: notification.actions || []
    };

    notifications.value.push(newNotification);

    // Auto-remove notification after duration (if not persistent)
    if (!newNotification.persistent && newNotification.duration > 0) {
      setTimeout(() => {
        removeNotification(id);
      }, newNotification.duration);
    }

    return id;
  };

  const removeNotification = (id) => {
    const index = notifications.value.findIndex(n => n.id === id);
    if (index > -1) {
      notifications.value.splice(index, 1);
    }
  };

  const clearNotifications = () => {
    notifications.value = [];
  };

  // Helpers para diferentes tipos de notificaciones
  const success = (message, options = {}) => {
    return addNotification({
      type: 'success',
      message,
      ...options
    });
  };

  const error = (message, options = {}) => {
    return addNotification({
      type: 'error',
      message,
      duration: 0, // Error notifications are persistent by default
      persistent: true,
      ...options
    });
  };

  const warning = (message, options = {}) => {
    return addNotification({
      type: 'warning',
      message,
      ...options
    });
  };

  const info = (message, options = {}) => {
    return addNotification({
      type: 'info',
      message,
      ...options
    });
  };

  return {
    // Estado
    notifications,
    // Actions
    addNotification,
    removeNotification,
    clearNotifications,
    // Helpers
    success,
    error,
    warning,
    info
  };
});
