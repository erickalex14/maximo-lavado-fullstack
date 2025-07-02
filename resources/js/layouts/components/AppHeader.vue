<template>
  <header class="app-header">
    <div class="header-left">
      <!-- Mobile Menu Toggle -->
      <button
        class="mobile-menu-toggle"
        @click="$emit('toggle-sidebar')"
        :title="sidebarCollapsed ? 'Abrir menú' : 'Cerrar menú'"
      >
        <svg class="menu-icon" viewBox="0 0 24 24" fill="currentColor">
          <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
        </svg>
      </button>
      
      <!-- Breadcrumbs -->
      <nav v-if="breadcrumbs.length > 0" class="breadcrumbs" aria-label="Breadcrumb">
        <ol class="breadcrumb-list">
          <li
            v-for="(crumb, index) in breadcrumbs"
            :key="index"
            class="breadcrumb-item"
            :class="{ active: crumb.active }"
          >
            <router-link
              v-if="crumb.href && !crumb.active"
              :to="crumb.href"
              class="breadcrumb-link"
            >
              {{ crumb.text }}
            </router-link>
            <span v-else class="breadcrumb-text">{{ crumb.text }}</span>
            
            <svg
              v-if="index < breadcrumbs.length - 1"
              class="breadcrumb-separator"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                clip-rule="evenodd"
              />
            </svg>
          </li>
        </ol>
      </nav>
    </div>
    
    <div class="header-center">
      <!-- Global Search -->
      <div class="global-search">
        <BaseSearchInput
          v-model="searchQuery"
          placeholder="Buscar clientes, lavados, productos..."
          :suggestions="searchSuggestions"
          size="sm"
          @search="handleGlobalSearch"
        />
      </div>
    </div>
    
    <div class="header-right">
      <!-- Quick Actions -->
      <div class="quick-actions">
        <BaseButton
          variant="outline"
          size="sm"
          title="Nuevo Lavado"
          @click="$emit('quick-action', 'new-wash')"
        >
          <svg class="action-icon" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
          </svg>
          <span class="action-text">Nuevo</span>
        </BaseButton>
      </div>
      
      <!-- Notifications -->
      <div class="notifications">
        <BaseDropdown placement="bottom-end">
          <template #trigger>
            <button class="notification-button" :class="{ 'has-notifications': unreadCount > 0 }">
              <svg class="notification-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
              </svg>
              <span v-if="unreadCount > 0" class="notification-badge">{{ unreadCount }}</span>
            </button>
          </template>
          
          <div class="notification-dropdown">
            <div class="notification-header">
              <h3>Notificaciones</h3>
              <button v-if="unreadCount > 0" class="mark-all-read" @click="markAllAsRead">
                Marcar todas como leídas
              </button>
            </div>
            
            <div class="notification-list">
              <div
                v-for="notification in notifications"
                :key="notification.id"
                class="notification-item"
                :class="{ unread: !notification.read }"
                @click="markAsRead(notification.id)"
              >
                <div class="notification-content">
                  <div class="notification-title">{{ notification.title }}</div>
                  <div class="notification-message">{{ notification.message }}</div>
                  <div class="notification-time">{{ formatTime(notification.createdAt) }}</div>
                </div>
              </div>
              
              <div v-if="notifications.length === 0" class="no-notifications">
                No hay notificaciones
              </div>
            </div>
            
            <div class="notification-footer">
              <router-link to="/notifications" class="view-all-link">
                Ver todas las notificaciones
              </router-link>
            </div>
          </div>
        </BaseDropdown>
      </div>
      
      <!-- User Menu -->
      <div class="user-menu">
        <BaseDropdown placement="bottom-end">
          <template #trigger>
            <button class="user-button">
              <div class="user-avatar">
                <img 
                  v-if="user?.avatar" 
                  :src="user.avatar" 
                  :alt="user.name"
                  class="avatar-image"
                >
                <div v-else class="avatar-placeholder">
                  {{ getUserInitials(user?.name) }}
                </div>
              </div>
              <div class="user-info">
                <div class="user-name">{{ user?.name }}</div>
                <div class="user-role">{{ user?.role || 'Admin' }}</div>
              </div>
              <svg class="chevron-icon" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </template>
          
          <div class="user-dropdown">
            <div class="user-dropdown-header">
              <div class="user-dropdown-info">
                <div class="user-dropdown-name">{{ user?.name }}</div>
                <div class="user-dropdown-email">{{ user?.email }}</div>
              </div>
            </div>
            
            <div class="user-dropdown-menu">
              <button class="dropdown-item" @click="$emit('profile')">
                <svg class="dropdown-icon" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
                Mi Perfil
              </button>
              
              <button class="dropdown-item" @click="$router.push('/settings')">
                <svg class="dropdown-icon" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                </svg>
                Configuración
              </button>
              
              <button class="dropdown-item" @click="toggleTheme">
                <svg class="dropdown-icon" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                </svg>
                Tema
              </button>
              
              <div class="dropdown-divider"></div>
              
              <button class="dropdown-item danger" @click="$emit('logout')">
                <svg class="dropdown-icon" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd" />
                </svg>
                Cerrar Sesión
              </button>
            </div>
          </div>
        </BaseDropdown>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { BaseSearchInput, BaseButton, BaseDropdown } from '@/components/common'

// Props
const props = defineProps({
  user: {
    type: Object,
    default: null
  },
  sidebarCollapsed: {
    type: Boolean,
    default: false
  },
  breadcrumbs: {
    type: Array,
    default: () => []
  }
})

// Emits
const emit = defineEmits(['toggle-sidebar', 'logout', 'profile', 'quick-action'])

// Data
const searchQuery = ref('')
const notifications = ref([
  {
    id: 1,
    title: 'Nuevo lavado completado',
    message: 'El lavado #001234 ha sido completado',
    createdAt: new Date(),
    read: false
  },
  {
    id: 2,
    title: 'Pago recibido',
    message: 'Se ha recibido un pago de $50.000',
    createdAt: new Date(Date.now() - 30 * 60 * 1000),
    read: false
  },
  {
    id: 3,
    title: 'Nuevo cliente registrado',
    message: 'Juan Pérez se ha registrado como nuevo cliente',
    createdAt: new Date(Date.now() - 60 * 60 * 1000),
    read: true
  }
])

// Computed
const unreadCount = computed(() => {
  return notifications.value.filter(n => !n.read).length
})

const searchSuggestions = computed(() => {
  if (!searchQuery.value) return []
  
  // Mock search suggestions
  return [
    'Cliente: Juan Pérez',
    'Lavado #001234',
    'Producto: Shampoo Premium'
  ].filter(suggestion => 
    suggestion.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

// Methods
const getUserInitials = (name) => {
  if (!name) return '??'
  return name
    .split(' ')
    .map(part => part.charAt(0))
    .slice(0, 2)
    .join('')
    .toUpperCase()
}

const handleGlobalSearch = (query) => {
  console.log('Global search:', query)
  // Implement global search logic
}

const markAsRead = (notificationId) => {
  const notification = notifications.value.find(n => n.id === notificationId)
  if (notification) {
    notification.read = true
  }
}

const markAllAsRead = () => {
  notifications.value.forEach(notification => {
    notification.read = true
  })
}

const formatTime = (date) => {
  const now = new Date()
  const diff = now - date
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)
  
  if (days > 0) return `Hace ${days} día${days > 1 ? 's' : ''}`
  if (hours > 0) return `Hace ${hours} hora${hours > 1 ? 's' : ''}`
  if (minutes > 0) return `Hace ${minutes} minuto${minutes > 1 ? 's' : ''}`
  return 'Hace un momento'
}

const toggleTheme = () => {
  // Implement theme toggle
  console.log('Toggle theme')
}

onMounted(() => {
  // Initialize header functionality
})
</script>

<style scoped>
.app-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 4rem;
  padding: 0 1.5rem;
  background-color: white;
  border-bottom: 1px solid var(--color-border-200);
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 30;
  gap: 1rem;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
  min-width: 0;
}

.mobile-menu-toggle {
  display: none;
  width: 2.5rem;
  height: 2.5rem;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  color: var(--color-text-600);
  cursor: pointer;
  border-radius: 0.375rem;
  transition: all 0.2s ease;
}

.mobile-menu-toggle:hover {
  background-color: var(--color-bg-100);
  color: var(--color-text-900);
}

.menu-icon {
  width: 1.5rem;
  height: 1.5rem;
}

/* Breadcrumbs */
.breadcrumbs {
  flex: 1;
  min-width: 0;
}

.breadcrumb-list {
  display: flex;
  align-items: center;
  list-style: none;
  margin: 0;
  padding: 0;
  gap: 0.5rem;
}

.breadcrumb-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.breadcrumb-link {
  color: var(--color-text-500);
  text-decoration: none;
  font-size: 0.875rem;
  transition: color 0.2s ease;
}

.breadcrumb-link:hover {
  color: var(--color-primary-600);
}

.breadcrumb-text {
  color: var(--color-text-900);
  font-size: 0.875rem;
  font-weight: 500;
}

.breadcrumb-separator {
  width: 1rem;
  height: 1rem;
  color: var(--color-text-400);
}

.header-center {
  flex: 0 0 auto;
  max-width: 24rem;
  width: 100%;
}

.global-search {
  width: 100%;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
  justify-content: flex-end;
}

/* Quick Actions */
.quick-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.action-icon {
  width: 1rem;
  height: 1rem;
}

.action-text {
  font-size: 0.875rem;
  font-weight: 500;
}

/* Notifications */
.notification-button {
  position: relative;
  width: 2.5rem;
  height: 2.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  color: var(--color-text-600);
  cursor: pointer;
  border-radius: 0.375rem;
  transition: all 0.2s ease;
}

.notification-button:hover {
  background-color: var(--color-bg-100);
  color: var(--color-text-900);
}

.notification-button.has-notifications {
  color: var(--color-primary-600);
}

.notification-icon {
  width: 1.5rem;
  height: 1.5rem;
}

.notification-badge {
  position: absolute;
  top: 0.25rem;
  right: 0.25rem;
  min-width: 1.25rem;
  height: 1.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--color-error-500);
  color: white;
  font-size: 0.75rem;
  font-weight: 600;
  border-radius: 0.625rem;
  padding: 0 0.25rem;
}

.notification-dropdown {
  width: 20rem;
  max-height: 24rem;
  background: white;
  border: 1px solid var(--color-border-200);
  border-radius: 0.5rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.notification-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  border-bottom: 1px solid var(--color-border-200);
}

.notification-header h3 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
  color: var(--color-text-900);
}

.mark-all-read {
  font-size: 0.75rem;
  color: var(--color-primary-600);
  background: none;
  border: none;
  cursor: pointer;
  text-decoration: underline;
}

.notification-list {
  max-height: 16rem;
  overflow-y: auto;
}

.notification-item {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid var(--color-border-100);
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.notification-item:hover {
  background-color: var(--color-bg-50);
}

.notification-item.unread {
  background-color: var(--color-primary-50);
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-content {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.notification-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-text-900);
}

.notification-message {
  font-size: 0.75rem;
  color: var(--color-text-600);
  line-height: 1.4;
}

.notification-time {
  font-size: 0.75rem;
  color: var(--color-text-400);
}

.no-notifications {
  padding: 2rem 1rem;
  text-align: center;
  color: var(--color-text-500);
  font-size: 0.875rem;
}

.notification-footer {
  padding: 0.75rem 1rem;
  border-top: 1px solid var(--color-border-200);
  text-align: center;
}

.view-all-link {
  font-size: 0.875rem;
  color: var(--color-primary-600);
  text-decoration: none;
  font-weight: 500;
}

.view-all-link:hover {
  text-decoration: underline;
}

/* User Menu */
.user-button {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem;
  background: none;
  border: none;
  cursor: pointer;
  border-radius: 0.5rem;
  transition: background-color 0.2s ease;
}

.user-button:hover {
  background-color: var(--color-bg-100);
}

.user-avatar {
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
}

.avatar-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--color-primary-600);
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
}

.user-info {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  min-width: 0;
}

.user-name {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--color-text-900);
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 8rem;
}

.user-role {
  font-size: 0.75rem;
  color: var(--color-text-500);
  line-height: 1.2;
}

.chevron-icon {
  width: 1rem;
  height: 1rem;
  color: var(--color-text-400);
  flex-shrink: 0;
}

.user-dropdown {
  width: 16rem;
  background: white;
  border: 1px solid var(--color-border-200);
  border-radius: 0.5rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.user-dropdown-header {
  padding: 1rem;
  border-bottom: 1px solid var(--color-border-200);
  background-color: var(--color-bg-50);
}

.user-dropdown-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-text-900);
  margin-bottom: 0.25rem;
}

.user-dropdown-email {
  font-size: 0.75rem;
  color: var(--color-text-500);
}

.user-dropdown-menu {
  padding: 0.5rem 0;
}

.dropdown-item {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  background: none;
  border: none;
  color: var(--color-text-700);
  text-align: left;
  cursor: pointer;
  transition: background-color 0.2s ease;
  font-size: 0.875rem;
}

.dropdown-item:hover {
  background-color: var(--color-bg-50);
}

.dropdown-item.danger {
  color: var(--color-error-600);
}

.dropdown-item.danger:hover {
  background-color: var(--color-error-50);
}

.dropdown-icon {
  width: 1rem;
  height: 1rem;
  flex-shrink: 0;
}

.dropdown-divider {
  height: 1px;
  background-color: var(--color-border-200);
  margin: 0.5rem 0;
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .app-header {
    padding: 0 1rem;
  }
  
  .mobile-menu-toggle {
    display: flex;
  }
  
  .breadcrumbs {
    display: none;
  }
  
  .header-center {
    display: none;
  }
  
  .quick-actions {
    display: none;
  }
  
  .user-info {
    display: none;
  }
}

@media (max-width: 480px) {
  .app-header {
    padding: 0 0.75rem;
    gap: 0.5rem;
  }
  
  .header-right {
    gap: 0.5rem;
  }
}
</style>
