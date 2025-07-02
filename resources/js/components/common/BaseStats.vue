<template>
  <div class="stats-container" :class="containerClasses">
    <div class="stats-grid" :class="gridClasses">
      <div
        v-for="(stat, index) in stats"
        :key="index"
        class="stat-item"
        :class="getStatItemClasses(stat)"
        @click="handleStatClick(stat, index)"
      >
        <div class="stat-content">
          <div v-if="stat.icon || $slots[`icon-${index}`]" class="stat-icon">
            <slot :name="`icon-${index}`" :stat="stat">
              <component :is="stat.icon" v-if="stat.icon" class="icon" />
              <svg v-else class="icon default-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
              </svg>
            </slot>
          </div>
          
          <div class="stat-details">
            <div class="stat-header">
              <h3 class="stat-label">{{ stat.label }}</h3>
              <div v-if="stat.trend !== undefined" class="stat-trend" :class="getTrendClasses(stat.trend)">
                <svg class="trend-icon" viewBox="0 0 20 20" fill="currentColor">
                  <path
                    v-if="stat.trend > 0"
                    fill-rule="evenodd"
                    d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z"
                    clip-rule="evenodd"
                  />
                  <path
                    v-else-if="stat.trend < 0"
                    fill-rule="evenodd"
                    d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z"
                    clip-rule="evenodd"
                  />
                  <path
                    v-else
                    fill-rule="evenodd"
                    d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                    clip-rule="evenodd"
                  />
                </svg>
                <span class="trend-value">{{ formatTrend(stat.trend) }}</span>
              </div>
            </div>
            
            <div class="stat-value-container">
              <div class="stat-value" :class="getValueClasses(stat)">
                <span v-if="stat.prefix" class="stat-prefix">{{ stat.prefix }}</span>
                <span class="stat-number">{{ formatValue(stat.value) }}</span>
                <span v-if="stat.suffix" class="stat-suffix">{{ stat.suffix }}</span>
              </div>
              
              <div v-if="stat.description" class="stat-description">
                {{ stat.description }}
              </div>
            </div>
            
            <div v-if="stat.progress !== undefined" class="stat-progress">
              <div class="progress-bar" :class="getProgressClasses(stat)">
                <div
                  class="progress-fill"
                  :style="{ width: `${Math.min(100, Math.max(0, stat.progress))}%` }"
                ></div>
              </div>
              <span class="progress-text">{{ stat.progress }}%</span>
            </div>
          </div>
        </div>
        
        <div v-if="stat.chart" class="stat-chart">
          <slot :name="`chart-${index}`" :stat="stat">
            <!-- Placeholder para gráfico -->
            <div class="chart-placeholder">
              <svg class="chart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <polyline points="22,6 13.5,15.5 8.5,10.5 2,17"/>
                <polyline points="16,6 22,6 22,12"/>
              </svg>
            </div>
          </slot>
        </div>
      </div>
    </div>
    
    <div v-if="showSummary && summary" class="stats-summary">
      <div class="summary-content">
        <h4 class="summary-title">{{ summary.title || 'Resumen' }}</h4>
        <p class="summary-text">{{ summary.text }}</p>
        
        <div v-if="summary.actions" class="summary-actions">
          <BaseButton
            v-for="(action, index) in summary.actions"
            :key="index"
            :variant="action.variant || 'outline'"
            :size="action.size || 'sm'"
            @click="handleSummaryAction(action, index)"
          >
            {{ action.text }}
          </BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, useSlots } from 'vue'
import BaseButton from './BaseButton.vue'

// Props
const props = defineProps({
  stats: {
    type: Array,
    required: true,
    validator: (stats) => {
      return stats.every(stat => 
        typeof stat === 'object' && 
        stat.label && 
        stat.value !== undefined
      )
    }
  },
  columns: {
    type: [Number, String],
    default: 'auto',
    validator: (value) => {
      return value === 'auto' || (typeof value === 'number' && value > 0 && value <= 12)
    }
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'bordered', 'elevated', 'minimal'].includes(value)
  },
  clickable: {
    type: Boolean,
    default: false
  },
  animated: {
    type: Boolean,
    default: true
  },
  responsive: {
    type: Boolean,
    default: true
  },
  showSummary: {
    type: Boolean,
    default: false
  },
  summary: {
    type: Object,
    default: () => ({})
  }
})

// Emits
const emit = defineEmits(['stat-click', 'summary-action'])

// Slots
const slots = useSlots()

// Computed
const containerClasses = computed(() => {
  const classes = ['stats-base']
  
  // Size
  classes.push(`stats-${props.size}`)
  
  // Variant
  classes.push(`stats-${props.variant}`)
  
  // States
  if (props.clickable) classes.push('stats-clickable')
  if (props.animated) classes.push('stats-animated')
  if (props.responsive) classes.push('stats-responsive')
  
  return classes
})

const gridClasses = computed(() => {
  const classes = ['stats-grid-base']
  
  if (props.columns !== 'auto') {
    classes.push(`stats-columns-${props.columns}`)
  }
  
  return classes
})

// Methods
const getStatItemClasses = (stat) => {
  const classes = ['stat-item-base']
  
  if (stat.variant) classes.push(`stat-${stat.variant}`)
  if (stat.highlighted) classes.push('stat-highlighted')
  if (props.clickable) classes.push('stat-clickable')
  
  return classes
}

const getValueClasses = (stat) => {
  const classes = []
  
  if (stat.valueColor) classes.push(`stat-value-${stat.valueColor}`)
  if (stat.size) classes.push(`stat-value-${stat.size}`)
  
  return classes
}

const getTrendClasses = (trend) => {
  const classes = ['trend-base']
  
  if (trend > 0) classes.push('trend-up')
  else if (trend < 0) classes.push('trend-down')
  else classes.push('trend-neutral')
  
  return classes
}

const getProgressClasses = (stat) => {
  const classes = ['progress-base']
  
  if (stat.progressVariant) classes.push(`progress-${stat.progressVariant}`)
  
  return classes
}

const formatValue = (value) => {
  if (typeof value === 'number') {
    if (value >= 1000000) {
      return (value / 1000000).toFixed(1) + 'M'
    } else if (value >= 1000) {
      return (value / 1000).toFixed(1) + 'K'
    }
    return value.toLocaleString()
  }
  return value
}

const formatTrend = (trend) => {
  if (typeof trend === 'number') {
    const abs = Math.abs(trend)
    return `${abs}%`
  }
  return trend
}

const handleStatClick = (stat, index) => {
  if (props.clickable) {
    emit('stat-click', { stat, index })
  }
}

const handleSummaryAction = (action, index) => {
  if (action.handler) {
    action.handler()
  }
  emit('summary-action', { action, index })
}
</script>

<style scoped>
/* Base stats styles */
.stats-container {
  width: 100%;
}

.stats-base {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.stats-grid {
  display: grid;
  gap: 1rem;
}

.stats-grid-base {
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
}

/* Column variants */
.stats-columns-1 {
  grid-template-columns: 1fr;
}

.stats-columns-2 {
  grid-template-columns: repeat(2, 1fr);
}

.stats-columns-3 {
  grid-template-columns: repeat(3, 1fr);
}

.stats-columns-4 {
  grid-template-columns: repeat(4, 1fr);
}

.stats-columns-5 {
  grid-template-columns: repeat(5, 1fr);
}

.stats-columns-6 {
  grid-template-columns: repeat(6, 1fr);
}

/* Stat item */
.stat-item {
  display: flex;
  flex-direction: column;
  border-radius: 0.5rem;
  transition: all 0.2s ease;
  overflow: hidden;
}

.stat-item-base {
  background-color: white;
  border: 1px solid var(--color-border-200);
}

.stat-content {
  padding: 1.5rem;
  flex: 1;
}

/* Variant styles */
.stats-bordered .stat-item-base {
  border: 2px solid var(--color-border-300);
}

.stats-elevated .stat-item-base {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  border: none;
}

.stats-minimal .stat-item-base {
  background-color: transparent;
  border: none;
  padding: 0;
}

/* Size variants */
.stats-sm .stat-content {
  padding: 1rem;
}

.stats-lg .stat-content {
  padding: 2rem;
}

/* Stat icon */
.stat-icon {
  margin-bottom: 1rem;
  display: flex;
  align-items: center;
  justify-content: flex-start;
}

.stat-icon .icon {
  width: 2.5rem;
  height: 2.5rem;
  color: var(--color-primary-600);
}

.stats-sm .stat-icon .icon {
  width: 2rem;
  height: 2rem;
}

.stats-lg .stat-icon .icon {
  width: 3rem;
  height: 3rem;
}

/* Stat header */
.stat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.5rem;
}

.stat-label {
  margin: 0;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--color-text-600);
  line-height: 1.4;
}

.stats-lg .stat-label {
  font-size: 1rem;
}

/* Stat trend */
.stat-trend {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  font-weight: 600;
}

.trend-base {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.trend-up {
  color: var(--color-success-600);
}

.trend-down {
  color: var(--color-error-600);
}

.trend-neutral {
  color: var(--color-text-500);
}

.trend-icon {
  width: 1rem;
  height: 1rem;
}

/* Stat value */
.stat-value-container {
  margin-bottom: 1rem;
}

.stat-value {
  display: flex;
  align-items: baseline;
  gap: 0.25rem;
  font-size: 2rem;
  font-weight: 700;
  color: var(--color-text-900);
  line-height: 1;
}

.stats-sm .stat-value {
  font-size: 1.5rem;
}

.stats-lg .stat-value {
  font-size: 2.5rem;
}

.stat-prefix,
.stat-suffix {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--color-text-600);
}

.stat-description {
  margin-top: 0.5rem;
  font-size: 0.75rem;
  color: var(--color-text-500);
  line-height: 1.4;
}

/* Value color variants */
.stat-value-primary {
  color: var(--color-primary-600);
}

.stat-value-success {
  color: var(--color-success-600);
}

.stat-value-warning {
  color: var(--color-warning-600);
}

.stat-value-danger {
  color: var(--color-error-600);
}

/* Progress bar */
.stat-progress {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.progress-bar {
  flex: 1;
  height: 0.5rem;
  background-color: var(--color-bg-200);
  border-radius: 0.25rem;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background-color: var(--color-primary-600);
  transition: width 0.3s ease;
}

.progress-base .progress-fill {
  background-color: var(--color-primary-600);
}

.progress-success .progress-fill {
  background-color: var(--color-success-600);
}

.progress-warning .progress-fill {
  background-color: var(--color-warning-600);
}

.progress-danger .progress-fill {
  background-color: var(--color-error-600);
}

.progress-text {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-text-600);
  min-width: 2.5rem;
  text-align: right;
}

/* Stat chart */
.stat-chart {
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--color-border-200);
  background-color: var(--color-bg-50);
}

.chart-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 60px;
}

.chart-icon {
  width: 2rem;
  height: 2rem;
  color: var(--color-text-400);
}

/* Stat variants */
.stat-primary {
  border-color: var(--color-primary-300);
}

.stat-primary .stat-icon .icon {
  color: var(--color-primary-600);
}

.stat-success {
  border-color: var(--color-success-300);
}

.stat-success .stat-icon .icon {
  color: var(--color-success-600);
}

.stat-warning {
  border-color: var(--color-warning-300);
}

.stat-warning .stat-icon .icon {
  color: var(--color-warning-600);
}

.stat-danger {
  border-color: var(--color-error-300);
}

.stat-danger .stat-icon .icon {
  color: var(--color-error-600);
}

/* Highlighted stat */
.stat-highlighted {
  background-color: var(--color-primary-50);
  border-color: var(--color-primary-400);
}

.stat-highlighted .stat-label {
  color: var(--color-primary-700);
}

.stat-highlighted .stat-value {
  color: var(--color-primary-900);
}

/* Clickable stats */
.stats-clickable .stat-clickable {
  cursor: pointer;
}

.stats-clickable .stat-clickable:hover {
  border-color: var(--color-primary-400);
  box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.1);
  transform: translateY(-1px);
}

/* Animation */
.stats-animated .stat-item {
  animation: statFadeIn 0.6s ease-out backwards;
}

.stats-animated .stat-item:nth-child(1) { animation-delay: 0.1s; }
.stats-animated .stat-item:nth-child(2) { animation-delay: 0.2s; }
.stats-animated .stat-item:nth-child(3) { animation-delay: 0.3s; }
.stats-animated .stat-item:nth-child(4) { animation-delay: 0.4s; }
.stats-animated .stat-item:nth-child(5) { animation-delay: 0.5s; }
.stats-animated .stat-item:nth-child(6) { animation-delay: 0.6s; }

@keyframes statFadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Stats summary */
.stats-summary {
  padding: 1.5rem;
  background-color: var(--color-bg-50);
  border: 1px solid var(--color-border-200);
  border-radius: 0.5rem;
}

.summary-title {
  margin: 0 0 0.5rem 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--color-text-900);
}

.summary-text {
  margin: 0 0 1rem 0;
  font-size: 0.875rem;
  color: var(--color-text-600);
  line-height: 1.5;
}

.summary-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

/* Responsive */
.stats-responsive .stats-grid-base {
  grid-template-columns: 1fr;
}

@media (min-width: 640px) {
  .stats-responsive .stats-grid-base {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  }
}

@media (min-width: 768px) {
  .stats-responsive .stats-grid-base {
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  }
}

@media (min-width: 1024px) {
  .stats-responsive .stats-columns-4 {
    grid-template-columns: repeat(4, 1fr);
  }
  
  .stats-responsive .stats-columns-5 {
    grid-template-columns: repeat(5, 1fr);
  }
  
  .stats-responsive .stats-columns-6 {
    grid-template-columns: repeat(6, 1fr);
  }
}

@media (max-width: 640px) {
  .stats-sm .stat-content {
    padding: 0.75rem;
  }
  
  .stat-chart {
    padding: 0.75rem 1rem;
  }
  
  .stats-summary {
    padding: 1rem;
  }
  
  .summary-actions {
    flex-direction: column;
  }
}
</style>
