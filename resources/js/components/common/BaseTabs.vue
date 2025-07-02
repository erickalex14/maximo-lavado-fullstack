<template>
  <div class="tabs-container" :class="containerClasses">
    <div class="tabs-header" :class="headerClasses">
      <div class="tabs-nav" :class="navClasses" role="tablist">
        <button
          v-for="(tab, index) in tabs"
          :key="tab.key || index"
          type="button"
          class="tab-button"
          :class="getTabClasses(tab, index)"
          :disabled="tab.disabled"
          :aria-selected="activeTab === (tab.key || index)"
          :aria-controls="`tabpanel-${tab.key || index}`"
          :id="`tab-${tab.key || index}`"
          role="tab"
          @click="selectTab(tab.key || index)"
          @keydown.enter.prevent="selectTab(tab.key || index)"
          @keydown.space.prevent="selectTab(tab.key || index)"
          @keydown.arrow-left.prevent="selectPreviousTab"
          @keydown.arrow-right.prevent="selectNextTab"
          @keydown.home.prevent="selectFirstTab"
          @keydown.end.prevent="selectLastTab"
        >
          <div v-if="tab.icon" class="tab-icon">
            <component :is="tab.icon" v-if="typeof tab.icon === 'object'" class="icon" />
            <i v-else :class="tab.icon" class="icon"></i>
          </div>
          
          <span class="tab-label">{{ tab.label }}</span>
          
          <div v-if="tab.badge" class="tab-badge">
            <BaseBadge
              :variant="tab.badge.variant || 'secondary'"
              :size="tab.badge.size || 'sm'"
            >
              {{ tab.badge.text }}
            </BaseBadge>
          </div>
          
          <button
            v-if="tab.closable"
            type="button"
            class="tab-close"
            :aria-label="`Cerrar ${tab.label}`"
            @click.stop="closeTab(tab.key || index)"
          >
            <svg class="close-icon" viewBox="0 0 20 20" fill="currentColor">
              <path
                fill-rule="evenodd"
                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                clip-rule="evenodd"
              />
            </svg>
          </button>
        </button>
        
        <div v-if="addable" class="tab-add">
          <button
            type="button"
            class="tab-add-button"
            :disabled="disabled"
            @click="addTab"
            aria-label="Agregar nueva pestaña"
          >
            <svg class="add-icon" viewBox="0 0 20 20" fill="currentColor">
              <path
                fill-rule="evenodd"
                d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                clip-rule="evenodd"
              />
            </svg>
          </button>
        </div>
        
        <div v-if="showOverflow" class="tab-overflow">
          <BaseDropdown
            :items="overflowItems"
            trigger-variant="ghost"
            :size="size"
            placement="bottom-end"
            @item-click="handleOverflowItemClick"
          >
            <template #trigger>
              <button type="button" class="tab-overflow-button">
                <svg class="overflow-icon" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                </svg>
              </button>
            </template>
          </BaseDropdown>
        </div>
      </div>
      
      <div v-if="$slots.actions" class="tabs-actions">
        <slot name="actions" :activeTab="activeTab" :tabs="tabs" />
      </div>
    </div>
    
    <div class="tabs-content" :class="contentClasses">
      <div
        v-for="(tab, index) in tabs"
        :key="tab.key || index"
        v-show="activeTab === (tab.key || index)"
        class="tab-panel"
        :class="getPanelClasses(tab, index)"
        :id="`tabpanel-${tab.key || index}`"
        :aria-labelledby="`tab-${tab.key || index}`"
        role="tabpanel"
        :tabindex="activeTab === (tab.key || index) ? 0 : -1"
      >
        <div v-if="tab.loading" class="tab-loading">
          <BaseLoading :size="size" text="Cargando contenido..." />
        </div>
        
        <div v-else-if="tab.error" class="tab-error">
          <BaseAlert variant="danger" :title="tab.error.title || 'Error'">
            {{ tab.error.message }}
            
            <template v-if="tab.error.retry" #actions>
              <BaseButton
                size="sm"
                variant="outline"
                @click="retryTab(tab.key || index)"
              >
                Reintentar
              </BaseButton>
            </template>
          </BaseAlert>
        </div>
        
        <template v-else>
          <slot
            :name="tab.slot || 'default'"
            :tab="tab"
            :index="index"
            :isActive="activeTab === (tab.key || index)"
          >
            <div v-if="tab.content" v-html="tab.content"></div>
          </slot>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue'
import BaseBadge from './BaseBadge.vue'
import BaseDropdown from './BaseDropdown.vue'
import BaseLoading from './BaseLoading.vue'
import BaseAlert from './BaseAlert.vue'
import BaseButton from './BaseButton.vue'

// Props
const props = defineProps({
  tabs: {
    type: Array,
    required: true,
    validator: (tabs) => {
      return tabs.every(tab => 
        typeof tab === 'object' && tab.label
      )
    }
  },
  modelValue: {
    type: [String, Number],
    default: null
  },
  defaultTab: {
    type: [String, Number],
    default: 0
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'pills', 'underline', 'cards'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  alignment: {
    type: String,
    default: 'start',
    validator: (value) => ['start', 'center', 'end', 'stretch'].includes(value)
  },
  orientation: {
    type: String,
    default: 'horizontal',
    validator: (value) => ['horizontal', 'vertical'].includes(value)
  },
  disabled: {
    type: Boolean,
    default: false
  },
  addable: {
    type: Boolean,
    default: false
  },
  lazy: {
    type: Boolean,
    default: false
  },
  keepAlive: {
    type: Boolean,
    default: false
  },
  destroyOnHide: {
    type: Boolean,
    default: false
  },
  showOverflow: {
    type: Boolean,
    default: true
  }
})

// Emits
const emit = defineEmits([
  'update:modelValue',
  'tab-change',
  'tab-click',
  'tab-close',
  'tab-add',
  'tab-retry'
])

// Reactive data
const activeTab = ref(props.modelValue || props.defaultTab)
const loadedTabs = ref(new Set())

// Computed
const containerClasses = computed(() => {
  const classes = ['tabs-base']
  
  // Variant
  classes.push(`tabs-${props.variant}`)
  
  // Size
  classes.push(`tabs-${props.size}`)
  
  // Orientation
  classes.push(`tabs-${props.orientation}`)
  
  // States
  if (props.disabled) classes.push('tabs-disabled')
  
  return classes
})

const headerClasses = computed(() => {
  const classes = ['tabs-header-base']
  
  return classes
})

const navClasses = computed(() => {
  const classes = ['tabs-nav-base']
  
  // Alignment
  classes.push(`tabs-nav-${props.alignment}`)
  
  return classes
})

const contentClasses = computed(() => {
  const classes = ['tabs-content-base']
  
  return classes
})

const enabledTabs = computed(() => {
  return props.tabs.filter(tab => !tab.disabled)
})

const overflowItems = computed(() => {
  // This would be populated based on which tabs don't fit in the visible area
  return []
})

// Methods
const selectTab = (tabKey) => {
  if (props.disabled) return
  
  const tab = props.tabs.find((t, i) => (t.key || i) === tabKey)
  if (!tab || tab.disabled) return
  
  const previousTab = activeTab.value
  activeTab.value = tabKey
  
  // Load tab content if lazy loading
  if (props.lazy && !loadedTabs.value.has(tabKey)) {
    loadedTabs.value.add(tabKey)
  }
  
  emit('update:modelValue', tabKey)
  emit('tab-change', {
    activeTab: tabKey,
    previousTab,
    tab
  })
  emit('tab-click', { tab, tabKey })
}

const selectNextTab = () => {
  const currentIndex = enabledTabs.value.findIndex(tab => 
    (tab.key || props.tabs.indexOf(tab)) === activeTab.value
  )
  
  if (currentIndex !== -1) {
    const nextIndex = (currentIndex + 1) % enabledTabs.value.length
    const nextTab = enabledTabs.value[nextIndex]
    selectTab(nextTab.key || props.tabs.indexOf(nextTab))
  }
}

const selectPreviousTab = () => {
  const currentIndex = enabledTabs.value.findIndex(tab => 
    (tab.key || props.tabs.indexOf(tab)) === activeTab.value
  )
  
  if (currentIndex !== -1) {
    const prevIndex = currentIndex === 0 ? enabledTabs.value.length - 1 : currentIndex - 1
    const prevTab = enabledTabs.value[prevIndex]
    selectTab(prevTab.key || props.tabs.indexOf(prevTab))
  }
}

const selectFirstTab = () => {
  if (enabledTabs.value.length > 0) {
    const firstTab = enabledTabs.value[0]
    selectTab(firstTab.key || props.tabs.indexOf(firstTab))
  }
}

const selectLastTab = () => {
  if (enabledTabs.value.length > 0) {
    const lastTab = enabledTabs.value[enabledTabs.value.length - 1]
    selectTab(lastTab.key || props.tabs.indexOf(lastTab))
  }
}

const closeTab = (tabKey) => {
  const tab = props.tabs.find((t, i) => (t.key || i) === tabKey)
  if (!tab) return
  
  emit('tab-close', { tab, tabKey })
  
  // If closing active tab, select another one
  if (activeTab.value === tabKey) {
    const currentIndex = props.tabs.findIndex((t, i) => (t.key || i) === tabKey)
    const nextTab = props.tabs[currentIndex + 1] || props.tabs[currentIndex - 1]
    
    if (nextTab && !nextTab.disabled) {
      selectTab(nextTab.key || props.tabs.indexOf(nextTab))
    }
  }
}

const addTab = () => {
  emit('tab-add')
}

const retryTab = (tabKey) => {
  const tab = props.tabs.find((t, i) => (t.key || i) === tabKey)
  if (!tab) return
  
  emit('tab-retry', { tab, tabKey })
}

const getTabClasses = (tab, index) => {
  const classes = ['tab-button-base']
  
  const tabKey = tab.key || index
  if (activeTab.value === tabKey) classes.push('tab-active')
  if (tab.disabled) classes.push('tab-disabled')
  if (tab.loading) classes.push('tab-loading')
  if (tab.error) classes.push('tab-error')
  
  return classes
}

const getPanelClasses = (tab, index) => {
  const classes = ['tab-panel-base']
  
  const tabKey = tab.key || index
  if (activeTab.value === tabKey) classes.push('tab-panel-active')
  
  return classes
}

const handleOverflowItemClick = ({ item }) => {
  selectTab(item.tabKey)
}

// Watch for external model value changes
watch(() => props.modelValue, (newValue) => {
  if (newValue !== null && newValue !== activeTab.value) {
    selectTab(newValue)
  }
})

// Initialize loaded tabs for lazy loading
onMounted(() => {
  if (!props.lazy) {
    // Load all tabs initially if not lazy
    props.tabs.forEach((tab, index) => {
      loadedTabs.value.add(tab.key || index)
    })
  } else {
    // Load only the active tab
    loadedTabs.value.add(activeTab.value)
  }
})

// Expose methods
defineExpose({
  selectTab,
  selectNextTab,
  selectPreviousTab,
  selectFirstTab,
  selectLastTab,
  closeTab,
  addTab,
  activeTab
})
</script>

<style scoped>
/* Base tabs styles */
.tabs-container {
  width: 100%;
}

.tabs-base {
  display: flex;
  flex-direction: column;
}

.tabs-vertical {
  flex-direction: row;
}

.tabs-disabled {
  opacity: 0.6;
  pointer-events: none;
}

/* Header */
.tabs-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--color-border-200);
}

.tabs-header-base {
  background-color: white;
}

.tabs-vertical .tabs-header {
  flex-direction: column;
  border-right: 1px solid var(--color-border-200);
  border-bottom: none;
  width: 200px;
  min-height: 400px;
}

/* Navigation */
.tabs-nav {
  display: flex;
  overflow-x: auto;
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.tabs-nav::-webkit-scrollbar {
  display: none;
}

.tabs-nav-base {
  align-items: center;
  gap: 0.25rem;
  padding: 0.25rem;
}

.tabs-nav-start {
  justify-content: flex-start;
}

.tabs-nav-center {
  justify-content: center;
}

.tabs-nav-end {
  justify-content: flex-end;
}

.tabs-nav-stretch .tab-button {
  flex: 1;
}

.tabs-vertical .tabs-nav {
  flex-direction: column;
  overflow-y: auto;
  overflow-x: hidden;
}

/* Tab buttons */
.tab-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border: none;
  background: none;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--color-text-600);
  border-radius: 0.375rem;
  outline: none;
  position: relative;
}

.tab-button-base {
  min-height: 2.5rem;
}

.tab-button:hover:not(.tab-disabled) {
  color: var(--color-text-900);
  background-color: var(--color-bg-100);
}

.tab-button:focus {
  color: var(--color-primary-700);
  box-shadow: 0 0 0 2px var(--color-primary-500);
}

.tab-active {
  color: var(--color-primary-700);
  background-color: var(--color-primary-50);
}

.tab-disabled {
  color: var(--color-text-400);
  cursor: not-allowed;
}

.tab-loading {
  opacity: 0.7;
}

.tab-error {
  color: var(--color-error-600);
}

/* Tab components */
.tab-icon {
  flex-shrink: 0;
}

.tab-icon .icon {
  width: 1rem;
  height: 1rem;
}

.tab-label {
  flex: 1;
  text-align: left;
}

.tab-badge {
  flex-shrink: 0;
}

.tab-close {
  flex-shrink: 0;
  width: 1rem;
  height: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  background: none;
  cursor: pointer;
  border-radius: 0.125rem;
  color: var(--color-text-400);
  transition: all 0.15s ease;
}

.tab-close:hover {
  color: var(--color-text-600);
  background-color: var(--color-bg-200);
}

.close-icon {
  width: 0.75rem;
  height: 0.75rem;
}

/* Variant styles */
.tabs-pills .tab-button {
  border-radius: 9999px;
}

.tabs-underline .tabs-header {
  border-bottom: 2px solid var(--color-border-200);
}

.tabs-underline .tab-button {
  border-radius: 0;
  border-bottom: 2px solid transparent;
  background: none;
}

.tabs-underline .tab-button:hover:not(.tab-disabled) {
  background: none;
  border-bottom-color: var(--color-border-400);
}

.tabs-underline .tab-active {
  background: none;
  border-bottom-color: var(--color-primary-600);
}

.tabs-cards .tab-button {
  border: 1px solid var(--color-border-200);
  border-bottom: none;
  border-radius: 0.375rem 0.375rem 0 0;
  background-color: var(--color-bg-50);
  margin-bottom: -1px;
}

.tabs-cards .tab-active {
  background-color: white;
  border-bottom: 1px solid white;
  z-index: 1;
  position: relative;
}

/* Size variants */
.tabs-sm .tab-button {
  padding: 0.375rem 0.75rem;
  font-size: 0.75rem;
  min-height: 2rem;
}

.tabs-sm .tab-icon .icon {
  width: 0.875rem;
  height: 0.875rem;
}

.tabs-lg .tab-button {
  padding: 0.75rem 1.25rem;
  font-size: 1rem;
  min-height: 3rem;
}

.tabs-lg .tab-icon .icon {
  width: 1.25rem;
  height: 1.25rem;
}

/* Add tab button */
.tab-add {
  flex-shrink: 0;
  margin-left: 0.5rem;
}

.tab-add-button {
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px dashed var(--color-border-300);
  background: none;
  cursor: pointer;
  border-radius: 0.375rem;
  color: var(--color-text-500);
  transition: all 0.2s ease;
}

.tab-add-button:hover {
  border-color: var(--color-primary-400);
  color: var(--color-primary-600);
  background-color: var(--color-primary-50);
}

.add-icon {
  width: 1rem;
  height: 1rem;
}

/* Overflow */
.tab-overflow {
  flex-shrink: 0;
  margin-left: 0.5rem;
}

.tab-overflow-button {
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  background: none;
  cursor: pointer;
  border-radius: 0.375rem;
  color: var(--color-text-500);
  transition: all 0.2s ease;
}

.tab-overflow-button:hover {
  color: var(--color-text-700);
  background-color: var(--color-bg-100);
}

.overflow-icon {
  width: 1rem;
  height: 1rem;
}

/* Actions */
.tabs-actions {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-left: 1rem;
}

/* Content */
.tabs-content {
  flex: 1;
  min-height: 0;
}

.tabs-content-base {
  padding: 1rem 0;
}

.tabs-vertical .tabs-content-base {
  padding: 0 1rem;
}

/* Tab panels */
.tab-panel {
  outline: none;
}

.tab-panel-base {
  min-height: 0;
}

.tab-loading,
.tab-error {
  padding: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Responsive */
@media (max-width: 768px) {
  .tabs-vertical {
    flex-direction: column;
  }
  
  .tabs-vertical .tabs-header {
    width: 100%;
    min-height: auto;
    border-right: none;
    border-bottom: 1px solid var(--color-border-200);
  }
  
  .tabs-vertical .tabs-nav {
    flex-direction: row;
    overflow-x: auto;
    overflow-y: hidden;
  }
  
  .tabs-actions {
    margin-left: 0;
    margin-top: 0.5rem;
  }
  
  .tab-button {
    min-width: max-content;
  }
}

@media (max-width: 480px) {
  .tabs-nav-base {
    padding: 0.125rem;
  }
  
  .tab-button {
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
  }
  
  .tabs-content-base {
    padding: 0.75rem 0;
  }
}
</style>
