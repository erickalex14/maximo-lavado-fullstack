<template>
  <div class="datepicker-container">
    <label
      v-if="label"
      :for="computedId"
      :class="labelClasses"
    >
      {{ label }}
      <span v-if="required" class="required-indicator">*</span>
    </label>
    
    <div class="datepicker-wrapper" :class="wrapperClasses">
      <div class="input-container" :class="inputContainerClasses">
        <input
          :id="computedId"
          ref="dateInput"
          type="date"
          :value="formattedValue"
          :disabled="disabled"
          :readonly="readonly"
          :required="required"
          :min="min"
          :max="max"
          :name="name"
          :placeholder="placeholder"
          :class="inputClasses"
          v-bind="$attrs"
          @input="handleInput"
          @change="handleChange"
          @focus="handleFocus"
          @blur="handleBlur"
        >
        
        <div class="input-icons">
          <slot name="prepend-icon">
            <svg
              v-if="showCalendarIcon"
              class="calendar-icon"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                clip-rule="evenodd"
              />
            </svg>
          </slot>
          
          <button
            v-if="clearable && modelValue"
            type="button"
            class="clear-button"
            :disabled="disabled"
            @click="clear"
            @mousedown.prevent
          >
            <svg class="clear-icon" viewBox="0 0 20 20" fill="currentColor">
              <path
                fill-rule="evenodd"
                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                clip-rule="evenodd"
              />
            </svg>
          </button>
        </div>
      </div>
      
      <div v-if="showCustomPicker && isOpen" class="custom-picker" :class="pickerClasses">
        <div class="picker-header">
          <button
            type="button"
            class="picker-nav-button"
            @click="previousMonth"
          >
            <svg viewBox="0 0 20 20" fill="currentColor">
              <path
                fill-rule="evenodd"
                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                clip-rule="evenodd"
              />
            </svg>
          </button>
          
          <div class="picker-title">
            <select v-model="currentMonth" class="month-select">
              <option v-for="(month, index) in monthNames" :key="index" :value="index">
                {{ month }}
              </option>
            </select>
            <select v-model="currentYear" class="year-select">
              <option v-for="year in availableYears" :key="year" :value="year">
                {{ year }}
              </option>
            </select>
          </div>
          
          <button
            type="button"
            class="picker-nav-button"
            @click="nextMonth"
          >
            <svg viewBox="0 0 20 20" fill="currentColor">
              <path
                fill-rule="evenodd"
                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                clip-rule="evenodd"
              />
            </svg>
          </button>
        </div>
        
        <div class="picker-calendar">
          <div class="weekdays">
            <div v-for="day in weekDays" :key="day" class="weekday">
              {{ day }}
            </div>
          </div>
          
          <div class="days-grid">
            <button
              v-for="day in calendarDays"
              :key="`${day.date}-${day.isCurrentMonth}`"
              type="button"
              class="day-button"
              :class="getDayClasses(day)"
              :disabled="isDayDisabled(day)"
              @click="selectDate(day)"
            >
              {{ day.day }}
            </button>
          </div>
        </div>
        
        <div v-if="showTimeSelector" class="time-selector">
          <div class="time-inputs">
            <input
              v-model="selectedHour"
              type="number"
              min="0"
              max="23"
              class="time-input"
              placeholder="HH"
            >
            <span class="time-separator">:</span>
            <input
              v-model="selectedMinute"
              type="number"
              min="0"
              max="59"
              class="time-input"
              placeholder="MM"
            >
          </div>
        </div>
        
        <div class="picker-footer">
          <button
            type="button"
            class="picker-today-button"
            @click="selectToday"
          >
            Hoy
          </button>
          <button
            type="button"
            class="picker-close-button"
            @click="closePicker"
          >
            Cerrar
          </button>
        </div>
      </div>
    </div>
    
    <div v-if="hint" class="datepicker-hint">
      {{ hint }}
    </div>
    
    <div v-if="error" class="datepicker-error">
      {{ error }}
    </div>
  </div>
</template>

<script setup>
import { computed, ref, useAttrs, watch, onMounted, onUnmounted } from 'vue'

// Props
const props = defineProps({
  modelValue: {
    type: [String, Date],
    default: null
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Seleccionar fecha'
  },
  hint: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  readonly: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  clearable: {
    type: Boolean,
    default: true
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'filled', 'outlined'].includes(value)
  },
  format: {
    type: String,
    default: 'YYYY-MM-DD'
  },
  min: {
    type: String,
    default: ''
  },
  max: {
    type: String,
    default: ''
  },
  name: {
    type: String,
    default: ''
  },
  id: {
    type: String,
    default: ''
  },
  showCalendarIcon: {
    type: Boolean,
    default: true
  },
  showCustomPicker: {
    type: Boolean,
    default: false
  },
  showTimeSelector: {
    type: Boolean,
    default: false
  },
  locale: {
    type: String,
    default: 'es'
  }
})

// Emits
const emit = defineEmits(['update:modelValue', 'change', 'focus', 'blur'])

// Reactive data
const focused = ref(false)
const isOpen = ref(false)
const dateInput = ref(null)
const currentMonth = ref(new Date().getMonth())
const currentYear = ref(new Date().getFullYear())
const selectedHour = ref('00')
const selectedMinute = ref('00')

// Attrs
const attrs = useAttrs()

// Computed
const computedId = computed(() => {
  return props.id || `datepicker-${Math.random().toString(36).substr(2, 9)}`
})

const formattedValue = computed(() => {
  if (!props.modelValue) return ''
  
  const date = new Date(props.modelValue)
  if (isNaN(date.getTime())) return ''
  
  return date.toISOString().split('T')[0]
})

const monthNames = computed(() => {
  const names = []
  for (let i = 0; i < 12; i++) {
    const date = new Date(2000, i, 1)
    names.push(date.toLocaleDateString(props.locale, { month: 'long' }))
  }
  return names
})

const weekDays = computed(() => {
  const days = []
  for (let i = 0; i < 7; i++) {
    const date = new Date(2000, 0, i + 1) // Start from Sunday
    days.push(date.toLocaleDateString(props.locale, { weekday: 'short' }))
  }
  return days
})

const availableYears = computed(() => {
  const currentYear = new Date().getFullYear()
  const years = []
  for (let i = currentYear - 50; i <= currentYear + 10; i++) {
    years.push(i)
  }
  return years
})

const calendarDays = computed(() => {
  const days = []
  const firstDay = new Date(currentYear.value, currentMonth.value, 1)
  const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0)
  const startDate = new Date(firstDay)
  startDate.setDate(startDate.getDate() - firstDay.getDay())
  
  for (let i = 0; i < 42; i++) {
    const date = new Date(startDate)
    date.setDate(startDate.getDate() + i)
    
    days.push({
      date: date.toISOString().split('T')[0],
      day: date.getDate(),
      isCurrentMonth: date.getMonth() === currentMonth.value,
      isToday: isToday(date),
      isSelected: isSelected(date)
    })
  }
  
  return days
})

const wrapperClasses = computed(() => {
  const classes = ['datepicker-base']
  
  // Size
  classes.push(`datepicker-${props.size}`)
  
  // Variant
  classes.push(`datepicker-${props.variant}`)
  
  // States
  if (props.disabled) classes.push('datepicker-disabled')
  if (focused.value) classes.push('datepicker-focused')
  if (props.error) classes.push('datepicker-error')
  
  return classes
})

const inputContainerClasses = computed(() => {
  const classes = ['input-container-base']
  
  return classes
})

const inputClasses = computed(() => {
  const classes = ['datepicker-input']
  
  return classes
})

const labelClasses = computed(() => {
  const classes = ['datepicker-label']
  
  if (props.disabled) classes.push('datepicker-label-disabled')
  
  return classes
})

const pickerClasses = computed(() => {
  const classes = ['picker-base']
  
  return classes
})

// Methods
const handleInput = (event) => {
  const value = event.target.value
  emit('update:modelValue', value || null)
}

const handleChange = (event) => {
  const value = event.target.value
  emit('change', value || null)
}

const handleFocus = (event) => {
  focused.value = true
  emit('focus', event)
  
  if (props.showCustomPicker) {
    isOpen.value = true
  }
}

const handleBlur = (event) => {
  focused.value = false
  emit('blur', event)
}

const clear = () => {
  emit('update:modelValue', null)
  emit('change', null)
}

const focus = () => {
  dateInput.value?.focus()
}

const blur = () => {
  dateInput.value?.blur()
}

const isToday = (date) => {
  const today = new Date()
  return date.toDateString() === today.toDateString()
}

const isSelected = (date) => {
  if (!props.modelValue) return false
  const selectedDate = new Date(props.modelValue)
  return date.toDateString() === selectedDate.toDateString()
}

const isDayDisabled = (day) => {
  if (props.min && day.date < props.min) return true
  if (props.max && day.date > props.max) return true
  return false
}

const getDayClasses = (day) => {
  const classes = []
  
  if (!day.isCurrentMonth) classes.push('day-other-month')
  if (day.isToday) classes.push('day-today')
  if (day.isSelected) classes.push('day-selected')
  if (isDayDisabled(day)) classes.push('day-disabled')
  
  return classes
}

const selectDate = (day) => {
  if (isDayDisabled(day)) return
  
  let dateValue = day.date
  
  if (props.showTimeSelector) {
    dateValue += `T${selectedHour.value.padStart(2, '0')}:${selectedMinute.value.padStart(2, '0')}:00`
  }
  
  emit('update:modelValue', dateValue)
  emit('change', dateValue)
  
  if (!props.showTimeSelector) {
    closePicker()
  }
}

const selectToday = () => {
  const today = new Date()
  const dateValue = today.toISOString().split('T')[0]
  
  emit('update:modelValue', dateValue)
  emit('change', dateValue)
  closePicker()
}

const previousMonth = () => {
  if (currentMonth.value === 0) {
    currentMonth.value = 11
    currentYear.value--
  } else {
    currentMonth.value--
  }
}

const nextMonth = () => {
  if (currentMonth.value === 11) {
    currentMonth.value = 0
    currentYear.value++
  } else {
    currentMonth.value++
  }
}

const closePicker = () => {
  isOpen.value = false
}

const handleClickOutside = (event) => {
  if (!event.target.closest('.datepicker-container')) {
    closePicker()
  }
}

// Lifecycle
onMounted(() => {
  if (props.showCustomPicker) {
    document.addEventListener('click', handleClickOutside)
  }
})

onUnmounted(() => {
  if (props.showCustomPicker) {
    document.removeEventListener('click', handleClickOutside)
  }
})

// Watch for model value changes to update current month/year
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    const date = new Date(newValue)
    if (!isNaN(date.getTime())) {
      currentMonth.value = date.getMonth()
      currentYear.value = date.getFullYear()
      
      if (props.showTimeSelector) {
        selectedHour.value = date.getHours().toString().padStart(2, '0')
        selectedMinute.value = date.getMinutes().toString().padStart(2, '0')
      }
    }
  }
})

// Expose methods
defineExpose({
  focus,
  blur,
  clear
})
</script>

<style scoped>
/* Base datepicker styles */
.datepicker-container {
  width: 100%;
  position: relative;
}

.datepicker-wrapper {
  position: relative;
}

.datepicker-base {
  position: relative;
}

.datepicker-label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--color-text-700);
}

.datepicker-label-disabled {
  color: var(--color-text-400);
}

.required-indicator {
  color: var(--color-error-500);
  margin-left: 0.25rem;
}

/* Input container */
.input-container {
  position: relative;
  display: flex;
  align-items: center;
}

.input-container-base {
  border: 1px solid var(--color-border-300);
  border-radius: 0.375rem;
  background-color: white;
  transition: all 0.2s ease;
}

.datepicker-input {
  width: 100%;
  padding: 0.625rem 2.5rem 0.625rem 0.75rem;
  border: none;
  outline: none;
  background: transparent;
  font-size: 0.875rem;
  color: var(--color-text-900);
}

.datepicker-input::placeholder {
  color: var(--color-text-400);
}

.input-icons {
  position: absolute;
  right: 0.75rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  pointer-events: none;
}

.calendar-icon {
  width: 1rem;
  height: 1rem;
  color: var(--color-text-400);
}

.clear-button {
  width: 1rem;
  height: 1rem;
  color: var(--color-text-400);
  background: none;
  border: none;
  cursor: pointer;
  pointer-events: auto;
  transition: color 0.2s ease;
}

.clear-button:hover {
  color: var(--color-text-600);
}

.clear-icon {
  width: 100%;
  height: 100%;
}

/* Size variants */
.datepicker-sm .datepicker-input {
  padding: 0.5rem 2rem 0.5rem 0.625rem;
  font-size: 0.75rem;
}

.datepicker-sm .input-icons {
  right: 0.625rem;
}

.datepicker-sm .calendar-icon,
.datepicker-sm .clear-button {
  width: 0.875rem;
  height: 0.875rem;
}

.datepicker-lg .datepicker-input {
  padding: 0.75rem 3rem 0.75rem 1rem;
  font-size: 1rem;
}

.datepicker-lg .input-icons {
  right: 1rem;
}

.datepicker-lg .calendar-icon,
.datepicker-lg .clear-button {
  width: 1.25rem;
  height: 1.25rem;
}

/* Variant styles */
.datepicker-filled .input-container-base {
  background-color: var(--color-bg-50);
  border-color: var(--color-bg-50);
}

.datepicker-outlined .input-container-base {
  border-width: 2px;
}

/* State styles */
.datepicker-focused .input-container-base {
  border-color: var(--color-primary-500);
  box-shadow: 0 0 0 1px var(--color-primary-500);
}

.datepicker-error .input-container-base {
  border-color: var(--color-error-500);
}

.datepicker-disabled .input-container-base {
  background-color: var(--color-bg-50);
  border-color: var(--color-border-200);
}

.datepicker-disabled .datepicker-input {
  color: var(--color-text-400);
  cursor: not-allowed;
}

/* Hint and error text */
.datepicker-hint {
  margin-top: 0.25rem;
  font-size: 0.75rem;
  color: var(--color-text-500);
}

.datepicker-error {
  margin-top: 0.25rem;
  font-size: 0.75rem;
  color: var(--color-error-600);
}

/* Custom picker */
.custom-picker {
  position: absolute;
  top: 100%;
  left: 0;
  z-index: 50;
  margin-top: 0.25rem;
  min-width: 20rem;
  background: white;
  border: 1px solid var(--color-border-200);
  border-radius: 0.5rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.picker-base {
  padding: 1rem;
}

/* Picker header */
.picker-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
}

.picker-nav-button {
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
  color: var(--color-text-600);
  transition: all 0.2s ease;
}

.picker-nav-button:hover {
  background-color: var(--color-bg-100);
  color: var(--color-text-900);
}

.picker-nav-button svg {
  width: 1rem;
  height: 1rem;
}

.picker-title {
  display: flex;
  gap: 0.5rem;
}

.month-select,
.year-select {
  padding: 0.25rem 0.5rem;
  border: 1px solid var(--color-border-300);
  border-radius: 0.25rem;
  background: white;
  font-size: 0.875rem;
}

/* Picker calendar */
.picker-calendar {
  margin-bottom: 1rem;
}

.weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 0.25rem;
  margin-bottom: 0.5rem;
}

.weekday {
  padding: 0.5rem;
  text-align: center;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--color-text-500);
}

.days-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 0.25rem;
}

.day-button {
  width: 2rem;
  height: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
  font-size: 0.875rem;
  color: var(--color-text-700);
  transition: all 0.2s ease;
}

.day-button:hover {
  background-color: var(--color-bg-100);
}

.day-other-month {
  color: var(--color-text-300);
}

.day-today {
  background-color: var(--color-primary-100);
  color: var(--color-primary-700);
  font-weight: 600;
}

.day-selected {
  background-color: var(--color-primary-600);
  color: white;
}

.day-disabled {
  color: var(--color-text-200);
  cursor: not-allowed;
}

.day-disabled:hover {
  background: none;
}

/* Time selector */
.time-selector {
  padding: 1rem 0;
  border-top: 1px solid var(--color-border-200);
  margin-bottom: 1rem;
}

.time-inputs {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.time-input {
  width: 3rem;
  padding: 0.5rem;
  text-align: center;
  border: 1px solid var(--color-border-300);
  border-radius: 0.25rem;
  font-size: 0.875rem;
}

.time-separator {
  font-weight: 600;
  color: var(--color-text-600);
}

/* Picker footer */
.picker-footer {
  display: flex;
  justify-content: space-between;
}

.picker-today-button,
.picker-close-button {
  padding: 0.5rem 1rem;
  border: 1px solid var(--color-border-300);
  border-radius: 0.375rem;
  background: white;
  cursor: pointer;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.picker-today-button:hover,
.picker-close-button:hover {
  background-color: var(--color-bg-50);
}

.picker-today-button {
  color: var(--color-primary-600);
  border-color: var(--color-primary-300);
}

.picker-close-button {
  color: var(--color-text-600);
}
</style>
