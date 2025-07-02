<template>
  <aside class="app-sidebar" :class="sidebarClasses">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
      <div class="logo-section">
        <div class="logo-container">
          <svg class="logo-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M19 7h-3V6a4 4 0 0 0-8 0v1H5a1 1 0 0 0-1 1v11a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V8a1 1 0 0 0-1-1zM10 6a2 2 0 0 1 4 0v1h-4V6zm8 13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V9h2v1a1 1 0 0 0 2 0V9h4v1a1 1 0 0 0 2 0V9h2v10z"/>
          </svg>
          <div v-if="!collapsed" class="logo-text">
            <h2 class="app-name">AutoWash</h2>
            <span class="app-tagline">Pro</span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- User Profile Section -->
    <div v-if="user" class="user-section">
      <div class="user-profile" :class="{ collapsed }">
        <div class="user-avatar">
          <img 
            v-if="user.avatar" 
            :src="user.avatar" 
            :alt="user.name"
            class="avatar-image"
          >
          <div v-else class="avatar-placeholder">
            {{ getUserInitials(user.name) }}
          </div>
        </div>
        
        <div v-if="!collapsed" class="user-info">
          <div class="user-name">{{ user.name }}</div>
          <div class="user-role">{{ user.role || 'Administrador' }}</div>
        </div>
      </div>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="sidebar-nav">
      <ul class="nav-list">
        <li
          v-for="item in navigation"
          :key="item.route"
          class="nav-item"
          :class="{ 
            active: item.active,
            'has-children': item.children && item.children.length > 0
          }"
        >
          <button
            class="nav-link"
            :class="{ active: item.active }"
            @click="handleNavClick(item)"
          >
            <div class="nav-icon">
              <component :is="getIcon(item.icon)" />
            </div>
            <span v-if="!collapsed" class="nav-text">{{ item.title }}</span>
            <div v-if="!collapsed && item.children" class="nav-arrow">
              <svg
                class="arrow-icon"
                :class="{ expanded: expandedItems.includes(item.route) }"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path
                  fill-rule="evenodd"
                  d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
          </button>
          
          <!-- Submenu -->
          <div
            v-if="item.children && !collapsed"
            class="submenu"
            :class="{ expanded: expandedItems.includes(item.route) }"
          >
            <ul class="submenu-list">
              <li
                v-for="child in item.children"
                :key="child.route"
                class="submenu-item"
              >
                <button
                  class="submenu-link"
                  :class="{ active: $route.path === child.route }"
                  @click="$emit('navigate', child)"
                >
                  {{ child.title }}
                </button>
              </li>
            </ul>
          </div>
        </li>
      </ul>
    </nav>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
      <div class="footer-content">
        <button
          class="collapse-button"
          @click="$emit('update:collapsed', !collapsed)"
          :title="collapsed ? 'Expandir menú' : 'Contraer menú'"
        >
          <svg class="collapse-icon" viewBox="0 0 20 20" fill="currentColor">
            <path
              fill-rule="evenodd"
              :d="collapsed 
                ? 'M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z'
                : 'M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z'"
              clip-rule="evenodd"
            />
          </svg>
        </button>
        
        <div v-if="!collapsed" class="app-version">
          v1.0.0
        </div>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { iconMap, DashboardIcon } from '@/components/icons'

// Props
const props = defineProps({
  collapsed: {
    type: Boolean,
    default: false
  },
  user: {
    type: Object,
    default: null
  },
  navigation: {
    type: Array,
    default: () => []
  }
})

// Emits
const emit = defineEmits(['update:collapsed', 'navigate'])

// Data
const expandedItems = ref([])
const route = useRoute()

// Computed
const sidebarClasses = computed(() => ({
  collapsed: props.collapsed
}))

// Methods
const handleNavClick = (item) => {
  if (item.children && item.children.length > 0) {
    // Toggle submenu
    const index = expandedItems.value.indexOf(item.route)
    if (index > -1) {
      expandedItems.value.splice(index, 1)
    } else {
      expandedItems.value.push(item.route)
    }
  } else {
    // Navigate to route
    emit('navigate', item)
  }
}

const getUserInitials = (name) => {
  if (!name) return '??'
  return name
    .split(' ')
    .map(part => part.charAt(0))
    .slice(0, 2)
    .join('')
    .toUpperCase()
}

const getIcon = (iconName) => {
  // Use our icon mapping from components/icons
  return iconMap[iconName] || DashboardIcon
}

// Auto-expand current menu item
const autoExpandCurrentItem = () => {
  const currentPath = route.path
  
  props.navigation.forEach(item => {
    if (item.children) {
      const hasActiveChild = item.children.some(child => 
        currentPath.startsWith(child.route)
      )
      
      if (hasActiveChild && !expandedItems.value.includes(item.route)) {
        expandedItems.value.push(item.route)
      }
    }
  })
}

// Initialize expanded items
autoExpandCurrentItem()
</script>

<style scoped>
.app-sidebar {
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  width: 16rem;
  background-color: var(--color-bg-900);
  color: white;
  transform: translateX(0);
  transition: all 0.3s ease;
  z-index: 50;
  display: flex;
  flex-direction: column;
  box-shadow: 2px 0 8px rgba(0, 0, 0, 0.1);
}

.app-sidebar.collapsed {
  width: 4rem;
}

/* Sidebar Header */
.sidebar-header {
  padding: 1.5rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.logo-container {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.logo-icon {
  width: 2rem;
  height: 2rem;
  color: var(--color-primary-400);
  flex-shrink: 0;
}

.logo-text {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.app-name {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
  color: white;
  line-height: 1;
}

.app-tagline {
  font-size: 0.75rem;
  color: var(--color-primary-400);
  font-weight: 500;
}

/* User Section */
.user-section {
  padding: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.user-profile {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  border-radius: 0.5rem;
  background-color: rgba(255, 255, 255, 0.05);
  transition: background-color 0.2s ease;
}

.user-profile:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.user-profile.collapsed {
  justify-content: center;
  padding: 0.5rem;
}

.user-avatar {
  width: 2.5rem;
  height: 2.5rem;
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
  font-size: 0.875rem;
  font-weight: 600;
  color: white;
}

.user-info {
  flex: 1;
  min-width: 0;
}

.user-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: white;
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-role {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.7);
  line-height: 1.2;
}

/* Navigation */
.sidebar-nav {
  flex: 1;
  padding: 1rem 0;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
}

.sidebar-nav::-webkit-scrollbar {
  width: 4px;
}

.sidebar-nav::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar-nav::-webkit-scrollbar-thumb {
  background-color: rgba(255, 255, 255, 0.2);
  border-radius: 2px;
}

.nav-list {
  list-style: none;
  margin: 0;
  padding: 0;
}

.nav-item {
  margin-bottom: 0.25rem;
}

.nav-link {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.8);
  text-align: left;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
}

.nav-link:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
}

.nav-link.active {
  background-color: var(--color-primary-600);
  color: white;
}

.nav-link.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background-color: var(--color-primary-300);
}

.nav-icon {
  width: 1.5rem;
  height: 1.5rem;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.nav-text {
  flex: 1;
  font-size: 0.875rem;
  font-weight: 500;
  line-height: 1.2;
  min-width: 0;
}

.nav-arrow {
  width: 1rem;
  height: 1rem;
  flex-shrink: 0;
}

.arrow-icon {
  width: 100%;
  height: 100%;
  transition: transform 0.2s ease;
}

.arrow-icon.expanded {
  transform: rotate(90deg);
}

/* Submenu */
.submenu {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease;
}

.submenu.expanded {
  max-height: 500px;
}

.submenu-list {
  list-style: none;
  margin: 0;
  padding: 0;
  background-color: rgba(0, 0, 0, 0.2);
}

.submenu-item {
  margin: 0;
}

.submenu-link {
  width: 100%;
  display: block;
  padding: 0.625rem 1rem 0.625rem 3.5rem;
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.7);
  text-align: left;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 0.8125rem;
  line-height: 1.2;
}

.submenu-link:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
}

.submenu-link.active {
  background-color: var(--color-primary-700);
  color: white;
}

/* Sidebar Footer */
.sidebar-footer {
  padding: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.footer-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
}

.collapse-button {
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.7);
  cursor: pointer;
  border-radius: 0.375rem;
  transition: all 0.2s ease;
}

.collapse-button:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
}

.collapse-icon {
  width: 1rem;
  height: 1rem;
}

.app-version {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.5);
  font-weight: 500;
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .app-sidebar {
    transform: translateX(-100%);
  }
  
  .app-sidebar:not(.collapsed) {
    transform: translateX(0);
  }
}

/* Print styles */
@media print {
  .app-sidebar {
    display: none;
  }
}
</style>
