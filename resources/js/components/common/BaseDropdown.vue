<template>
  <div class="dropdown-container" :class="containerClasses" ref="dropdownRef">
    <div
      class="dropdown-trigger"
      :class="triggerClasses"
      @click="toggle"
      @keydown.enter.prevent="toggle"
      @keydown.space.prevent="toggle"
      @keydown.escape="close"
      @keydown.arrow-down.prevent="openAndFocusFirst"
      :tabindex="disabled ? -1 : 0"
      :aria-expanded="isOpen"
      :aria-haspopup="true"
      :aria-disabled="disabled"
    >
      <slot name="trigger" :isOpen="isOpen" :toggle="toggle">
        <BaseButton
          :variant="triggerVariant"
          :size="size"
          :disabled="disabled"
          :class="['dropdown-button', { 'dropdown-button-open': isOpen }]"
        >
          <span class="dropdown-button-content">
            <slot name="button-content">{{ triggerText }}</slot>
          </span>
          <svg
            class="dropdown-chevron"
            :class="{ 'dropdown-chevron-open': isOpen }"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
              clip-rule="evenodd"
            />
          </svg>
        </BaseButton>
      </slot>
    </div>
    
    <Teleport to="body">
      <div
        v-if="isOpen"
        class="dropdown-overlay"
        @click="close"
        :style="overlayStyles"
      ></div>
    </Teleport>
    
    <Transition name="dropdown" @after-leave="onAfterLeave">
      <div
        v-if="isOpen"
        ref="dropdownMenu"
        class="dropdown-menu"
        :class="menuClasses"
        :style="menuStyles"
        @keydown.escape="close"
        @keydown.arrow-up.prevent="focusPrevious"
        @keydown.arrow-down.prevent="focusNext"
        @keydown.home.prevent="focusFirst"
        @keydown.end.prevent="focusLast"
        role="menu"
        :aria-labelledby="triggerId"
      >
        <div v-if="title" class="dropdown-header">
          <h3 class="dropdown-title">{{ title }}</h3>
          <p v-if="description" class="dropdown-description">{{ description }}</p>
        </div>
        
        <div class="dropdown-content" :class="contentClasses">
          <template v-if="items && items.length > 0">
            <template v-for="(item, index) in items" :key="item.key || index">
              <hr v-if="item.type === 'divider'" class="dropdown-divider" />
              
              <div v-else-if="item.type === 'header'" class="dropdown-section-header">
                {{ item.text }}
              </div>
              
              <button
                v-else
                type="button"
                class="dropdown-item"
                :class="getItemClasses(item)"
                :disabled="item.disabled"
                :tabindex="item.disabled ? -1 : 0"
                role="menuitem"
                @click="handleItemClick(item, index)"
                @keydown.enter.prevent="handleItemClick(item, index)"
                @keydown.space.prevent="handleItemClick(item, index)"
              >
                <div v-if="item.icon" class="dropdown-item-icon">
                  <component :is="item.icon" v-if="typeof item.icon === 'object'" class="icon" />
                  <i v-else :class="item.icon" class="icon"></i>
                </div>
                
                <div class="dropdown-item-content">
                  <div class="dropdown-item-text">{{ item.text }}</div>
                  <div v-if="item.description" class="dropdown-item-description">
                    {{ item.description }}
                  </div>
                </div>
                
                <div v-if="item.shortcut" class="dropdown-item-shortcut">
                  {{ item.shortcut }}
                </div>
                
                <div v-if="item.badge" class="dropdown-item-badge">
                  <BaseBadge :variant="item.badge.variant" :size="item.badge.size">
                    {{ item.badge.text }}
                  </BaseBadge>
                </div>
              </button>
            </template>
          </template>
          
          <slot v-else :close="close" />
        </div>
        
        <div v-if="footer || $slots.footer" class="dropdown-footer">
          <slot name="footer" :close="close">
            <div v-if="footer" v-html="footer"></div>
          </slot>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted, watch } from 'vue'
import BaseButton from './BaseButton.vue'
import BaseBadge from './BaseBadge.vue'

// Props
const props = defineProps({
  items: {
    type: Array,
    default: () => []
  },
  triggerText: {
    type: String,
    default: 'Menu'
  },
  triggerVariant: {
    type: String,
    default: 'outline'
  },
  title: {
    type: String,
    default: ''
  },
  description: {
    type: String,
    default: ''
  },
  footer: {
    type: String,
    default: ''
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  placement: {
    type: String,
    default: 'bottom-start',
    validator: (value) => [
      'top-start', 'top', 'top-end',
      'bottom-start', 'bottom', 'bottom-end',
      'left-start', 'left', 'left-end',
      'right-start', 'right', 'right-end'
    ].includes(value)
  },
  offset: {
    type: Number,
    default: 8
  },
  disabled: {
    type: Boolean,
    default: false
  },
  closeOnClick: {
    type: Boolean,
    default: true
  },
  closeOnClickOutside: {
    type: Boolean,
    default: true
  },
  maxHeight: {
    type: String,
    default: '300px'
  },
  minWidth: {
    type: String,
    default: '200px'
  },
  fullWidth: {
    type: Boolean,
    default: false
  }
})

// Emits
const emit = defineEmits(['item-click', 'open', 'close', 'toggle'])

// Reactive data
const isOpen = ref(false)
const dropdownRef = ref(null)
const dropdownMenu = ref(null)
const triggerId = ref(`dropdown-trigger-${Math.random().toString(36).substr(2, 9)}`)
const focusedIndex = ref(-1)

// Computed
const containerClasses = computed(() => {
  const classes = ['dropdown-base']
  
  // Size
  classes.push(`dropdown-${props.size}`)
  
  // States
  if (props.disabled) classes.push('dropdown-disabled')
  if (isOpen.value) classes.push('dropdown-open')
  
  return classes
})

const triggerClasses = computed(() => {
  const classes = ['dropdown-trigger-base']
  
  if (props.disabled) classes.push('dropdown-trigger-disabled')
  
  return classes
})

const menuClasses = computed(() => {
  const classes = ['dropdown-menu-base']
  
  // Size
  classes.push(`dropdown-menu-${props.size}`)
  
  // Placement
  classes.push(`dropdown-menu-${props.placement}`)
  
  return classes
})

const contentClasses = computed(() => {
  const classes = ['dropdown-content-base']
  
  return classes
})

const menuStyles = computed(() => {
  const styles = {
    maxHeight: props.maxHeight,
    minWidth: props.minWidth
  }
  
  if (props.fullWidth && dropdownRef.value) {
    styles.width = `${dropdownRef.value.offsetWidth}px`
  }
  
  return styles
})

const overlayStyles = computed(() => {
  return {
    zIndex: '999'
  }
})

const menuItems = computed(() => {
  return props.items.filter(item => item.type !== 'divider' && item.type !== 'header')
})

// Methods
const toggle = () => {
  if (props.disabled) return
  
  if (isOpen.value) {
    close()
  } else {
    open()
  }
}

const open = async () => {
  if (props.disabled) return
  
  isOpen.value = true
  emit('open')
  emit('toggle', true)
  
  await nextTick()
  positionMenu()
  
  // Focus first item if using keyboard
  if (document.activeElement === dropdownRef.value?.querySelector('.dropdown-trigger')) {
    focusFirst()
  }
}

const close = () => {
  if (!isOpen.value) return
  
  isOpen.value = false
  focusedIndex.value = -1
  emit('close')
  emit('toggle', false)
  
  // Return focus to trigger
  const trigger = dropdownRef.value?.querySelector('.dropdown-trigger')
  if (trigger) {
    trigger.focus()
  }
}

const openAndFocusFirst = () => {
  if (!isOpen.value) {
    open().then(() => {
      focusFirst()
    })
  } else {
    focusFirst()
  }
}

const positionMenu = () => {
  if (!dropdownRef.value || !dropdownMenu.value) return
  
  const trigger = dropdownRef.value
  const menu = dropdownMenu.value
  const triggerRect = trigger.getBoundingClientRect()
  const menuRect = menu.getBoundingClientRect()
  const viewport = {
    width: window.innerWidth,
    height: window.innerHeight
  }
  
  let top = 0
  let left = 0
  
  // Calculate position based on placement
  switch (props.placement) {
    case 'top-start':
      top = triggerRect.top - menuRect.height - props.offset
      left = triggerRect.left
      break
    case 'top':
      top = triggerRect.top - menuRect.height - props.offset
      left = triggerRect.left + (triggerRect.width / 2) - (menuRect.width / 2)
      break
    case 'top-end':
      top = triggerRect.top - menuRect.height - props.offset
      left = triggerRect.right - menuRect.width
      break
    case 'bottom-start':
      top = triggerRect.bottom + props.offset
      left = triggerRect.left
      break
    case 'bottom':
      top = triggerRect.bottom + props.offset
      left = triggerRect.left + (triggerRect.width / 2) - (menuRect.width / 2)
      break
    case 'bottom-end':
      top = triggerRect.bottom + props.offset
      left = triggerRect.right - menuRect.width
      break
    case 'left-start':
      top = triggerRect.top
      left = triggerRect.left - menuRect.width - props.offset
      break
    case 'left':
      top = triggerRect.top + (triggerRect.height / 2) - (menuRect.height / 2)
      left = triggerRect.left - menuRect.width - props.offset
      break
    case 'left-end':
      top = triggerRect.bottom - menuRect.height
      left = triggerRect.left - menuRect.width - props.offset
      break
    case 'right-start':
      top = triggerRect.top
      left = triggerRect.right + props.offset
      break
    case 'right':
      top = triggerRect.top + (triggerRect.height / 2) - (menuRect.height / 2)
      left = triggerRect.right + props.offset
      break
    case 'right-end':
      top = triggerRect.bottom - menuRect.height
      left = triggerRect.right + props.offset
      break
  }
  
  // Adjust for viewport boundaries
  if (left < 0) left = 8
  if (left + menuRect.width > viewport.width) left = viewport.width - menuRect.width - 8
  if (top < 0) top = 8
  if (top + menuRect.height > viewport.height) top = viewport.height - menuRect.height - 8
  
  menu.style.position = 'fixed'
  menu.style.top = `${top}px`
  menu.style.left = `${left}px`
  menu.style.zIndex = '1000'
}

const handleItemClick = (item, index) => {
  if (item.disabled) return
  
  emit('item-click', { item, index })
  
  if (item.handler) {
    item.handler(item, index)
  }
  
  if (props.closeOnClick && !item.keepOpen) {
    close()
  }
}

const getItemClasses = (item) => {
  const classes = ['dropdown-item-base']
  
  if (item.variant) classes.push(`dropdown-item-${item.variant}`)
  if (item.disabled) classes.push('dropdown-item-disabled')
  if (item.active) classes.push('dropdown-item-active')
  
  return classes
}

const focusFirst = () => {
  const items = dropdownMenu.value?.querySelectorAll('.dropdown-item:not([disabled])')
  if (items && items.length > 0) {
    items[0].focus()
    focusedIndex.value = 0
  }
}

const focusLast = () => {
  const items = dropdownMenu.value?.querySelectorAll('.dropdown-item:not([disabled])')
  if (items && items.length > 0) {
    items[items.length - 1].focus()
    focusedIndex.value = items.length - 1
  }
}

const focusNext = () => {
  const items = dropdownMenu.value?.querySelectorAll('.dropdown-item:not([disabled])')
  if (items && items.length > 0) {
    const nextIndex = (focusedIndex.value + 1) % items.length
    items[nextIndex].focus()
    focusedIndex.value = nextIndex
  }
}

const focusPrevious = () => {
  const items = dropdownMenu.value?.querySelectorAll('.dropdown-item:not([disabled])')
  if (items && items.length > 0) {
    const prevIndex = focusedIndex.value <= 0 ? items.length - 1 : focusedIndex.value - 1
    items[prevIndex].focus()
    focusedIndex.value = prevIndex
  }
}

const onAfterLeave = () => {
  // Clean up any positioning styles
  if (dropdownMenu.value) {
    dropdownMenu.value.style.position = ''
    dropdownMenu.value.style.top = ''
    dropdownMenu.value.style.left = ''
    dropdownMenu.value.style.zIndex = ''
  }
}

const handleClickOutside = (event) => {
  if (!props.closeOnClickOutside || !isOpen.value) return
  
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    close()
  }
}

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  window.addEventListener('resize', positionMenu)
  window.addEventListener('scroll', positionMenu)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  window.removeEventListener('resize', positionMenu)
  window.removeEventListener('scroll', positionMenu)
})

// Watch for position updates
watch(isOpen, (newValue) => {
  if (newValue) {
    nextTick(() => {
      positionMenu()
    })
  }
})

// Expose methods
defineExpose({
  open,
  close,
  toggle,
  isOpen
})
</script>

<style scoped>
/* Base dropdown styles */
.dropdown-container {
  position: relative;
  display: inline-block;
}

.dropdown-base {
  position: relative;
}

.dropdown-trigger {
  cursor: pointer;
}

.dropdown-trigger-base {
  outline: none;
}

.dropdown-trigger-disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.dropdown-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.dropdown-button-content {
  flex: 1;
  display: flex;
  align-items: center;
}

.dropdown-chevron {
  width: 1rem;
  height: 1rem;
  transition: transform 0.2s ease;
  flex-shrink: 0;
}

.dropdown-chevron-open {
  transform: rotate(180deg);
}

/* Overlay */
.dropdown-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: transparent;
}

/* Menu */
.dropdown-menu {
  position: absolute;
  z-index: 1000;
  background: white;
  border: 1px solid var(--color-border-200);
  border-radius: 0.5rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  outline: none;
}

.dropdown-menu-base {
  min-width: 200px;
  max-height: 300px;
  overflow-y: auto;
}

/* Size variants */
.dropdown-menu-sm {
  min-width: 150px;
  max-height: 200px;
}

.dropdown-menu-lg {
  min-width: 250px;
  max-height: 400px;
}

/* Menu header */
.dropdown-header {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid var(--color-border-200);
  background-color: var(--color-bg-50);
}

.dropdown-title {
  margin: 0 0 0.25rem 0;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-text-900);
}

.dropdown-description {
  margin: 0;
  font-size: 0.75rem;
  color: var(--color-text-600);
  line-height: 1.4;
}

/* Menu content */
.dropdown-content {
  padding: 0.5rem 0;
}

.dropdown-content-base {
  display: flex;
  flex-direction: column;
}

/* Menu items */
.dropdown-item {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 1rem;
  border: none;
  background: none;
  text-align: left;
  cursor: pointer;
  transition: all 0.15s ease;
  outline: none;
}

.dropdown-item-base {
  color: var(--color-text-700);
}

.dropdown-item:hover:not(.dropdown-item-disabled) {
  background-color: var(--color-bg-100);
  color: var(--color-text-900);
}

.dropdown-item:focus {
  background-color: var(--color-primary-50);
  color: var(--color-primary-900);
  outline: none;
}

.dropdown-item-disabled {
  color: var(--color-text-400);
  cursor: not-allowed;
}

.dropdown-item-active {
  background-color: var(--color-primary-100);
  color: var(--color-primary-900);
}

/* Item variants */
.dropdown-item-danger {
  color: var(--color-error-600);
}

.dropdown-item-danger:hover:not(.dropdown-item-disabled) {
  background-color: var(--color-error-50);
  color: var(--color-error-700);
}

.dropdown-item-success {
  color: var(--color-success-600);
}

.dropdown-item-success:hover:not(.dropdown-item-disabled) {
  background-color: var(--color-success-50);
  color: var(--color-success-700);
}

/* Item components */
.dropdown-item-icon {
  flex-shrink: 0;
  display: flex;
  align-items: center;
}

.dropdown-item-icon .icon {
  width: 1rem;
  height: 1rem;
}

.dropdown-item-content {
  flex: 1;
  min-width: 0;
}

.dropdown-item-text {
  font-size: 0.875rem;
  font-weight: 500;
  line-height: 1.4;
}

.dropdown-item-description {
  font-size: 0.75rem;
  color: var(--color-text-500);
  line-height: 1.3;
  margin-top: 0.125rem;
}

.dropdown-item-shortcut {
  flex-shrink: 0;
  font-size: 0.75rem;
  color: var(--color-text-400);
  font-weight: 500;
}

.dropdown-item-badge {
  flex-shrink: 0;
}

/* Divider */
.dropdown-divider {
  margin: 0.5rem 0;
  border: none;
  border-top: 1px solid var(--color-border-200);
}

/* Section header */
.dropdown-section-header {
  padding: 0.5rem 1rem 0.25rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-text-500);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

/* Menu footer */
.dropdown-footer {
  padding: 0.75rem 1rem;
  border-top: 1px solid var(--color-border-200);
  background-color: var(--color-bg-50);
  font-size: 0.75rem;
  color: var(--color-text-600);
}

/* Transitions */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.15s ease;
}

.dropdown-enter-from {
  opacity: 0;
  transform: translateY(-8px) scale(0.95);
}

.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.95);
}

/* Size variants for items */
.dropdown-sm .dropdown-item {
  padding: 0.375rem 0.75rem;
}

.dropdown-sm .dropdown-item-text {
  font-size: 0.75rem;
}

.dropdown-sm .dropdown-item-icon .icon {
  width: 0.875rem;
  height: 0.875rem;
}

.dropdown-lg .dropdown-item {
  padding: 0.625rem 1.25rem;
}

.dropdown-lg .dropdown-item-text {
  font-size: 1rem;
}

.dropdown-lg .dropdown-item-icon .icon {
  width: 1.25rem;
  height: 1.25rem;
}

/* Responsive */
@media (max-width: 640px) {
  .dropdown-menu-base {
    min-width: 180px;
    max-width: calc(100vw - 2rem);
  }
  
  .dropdown-item {
    padding: 0.625rem 1rem;
  }
}
</style>
