<template>
  <div class="table-container" :class="containerClasses">
    <!-- TABLE HEADER (ACTIONS Y FILTROS) -->
    <div v-if="$slots.header || showHeader" class="table-header">
      <slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <!-- TITLE Y DESCRIPTION -->
          <div>
            <h3 v-if="title" class="text-heading-4">{{ title }}</h3>
            <p v-if="description" class="text-caption mt-1">{{ description }}</p>
          </div>
          
          <!-- ACTIONS -->
          <div v-if="$slots.actions" class="flex items-center space-x-2">
            <slot name="actions"></slot>
          </div>
        </div>
      </slot>
    </div>

    <!-- LOADING OVERLAY -->
    <div v-if="loading" class="table-loading">
      <div class="loading-spinner">
        <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="ml-2 text-gray-600">{{ loadingText }}</span>
      </div>
    </div>

    <!-- TABLE WRAPPER (RESPONSIVE SCROLL) -->
    <div class="table-wrapper" :class="{ 'opacity-50': loading }">
      <table class="table" :class="tableClasses">
        <!-- TABLE HEAD -->
        <thead class="table-head">
          <tr>
            <!-- SELECT ALL CHECKBOX -->
            <th v-if="selectable" class="table-cell-checkbox">
              <input
                type="checkbox"
                :checked="allSelected"
                :indeterminate="someSelected"
                @change="toggleAllSelection"
                class="checkbox"
                :aria-label="selectAllLabel"
              />
            </th>
            
            <!-- COLUMN HEADERS -->
            <th
              v-for="column in columns"
              :key="column.key"
              :class="getHeaderClasses(column)"
              :style="getColumnStyle(column)"
              @click="handleSort(column)"
            >
              <div class="header-content">
                <!-- COLUMN LABEL -->
                <span>{{ column.label }}</span>
                
                <!-- SORT INDICATOR -->
                <div v-if="column.sortable" class="sort-indicator">
                  <svg
                    v-if="sortKey === column.key"
                    class="sort-icon"
                    :class="{ 'rotate-180': sortOrder === 'desc' }"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                  </svg>
                  <svg
                    v-else
                    class="sort-icon opacity-0 group-hover:opacity-50"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                  </svg>
                </div>
              </div>
            </th>
            
            <!-- ACTIONS COLUMN -->
            <th v-if="$slots.actions || showActionsColumn" class="table-cell-actions">
              {{ actionsLabel }}
            </th>
          </tr>
        </thead>

        <!-- TABLE BODY -->
        <tbody class="table-body">
          <!-- EMPTY STATE -->
          <tr v-if="!loading && (!data || data.length === 0)">
            <td :colspan="totalColumns" class="table-cell-empty">
              <slot name="empty">
                <div class="empty-state">
                  <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                  </svg>
                  <h3 class="empty-title">{{ emptyTitle }}</h3>
                  <p class="empty-description">{{ emptyDescription }}</p>
                </div>
              </slot>
            </td>
          </tr>
          
          <!-- DATA ROWS -->
          <tr
            v-for="(item, index) in data"
            :key="getRowKey(item, index)"
            :class="getRowClasses(item, index)"
            @click="handleRowClick(item, index)"
          >
            <!-- SELECT CHECKBOX -->
            <td v-if="selectable" class="table-cell-checkbox">
              <input
                type="checkbox"
                :checked="isSelected(item)"
                @change="toggleSelection(item)"
                @click.stop
                class="checkbox"
                :aria-label="`Seleccionar fila ${index + 1}`"
              />
            </td>
            
            <!-- DATA CELLS -->
            <td
              v-for="column in columns"
              :key="column.key"
              :class="getCellClasses(column, item)"
              :style="getColumnStyle(column)"
            >
              <slot
                :name="`cell-${column.key}`"
                :item="item"
                :value="getCellValue(item, column.key)"
                :index="index"
                :column="column"
              >
                <div class="cell-content">
                  {{ formatCellValue(getCellValue(item, column.key), column) }}
                </div>
              </slot>
            </td>
            
            <!-- ACTIONS CELL -->
            <td v-if="$slots.actions || showActionsColumn" class="table-cell-actions">
              <slot name="actions" :item="item" :index="index">
                <div class="actions-menu">
                  <!-- DEFAULT ACTIONS -->
                  <button
                    v-if="!hideEditAction"
                    class="action-button"
                    @click.stop="handleEdit(item, index)"
                    :aria-label="`Editar fila ${index + 1}`"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  
                  <button
                    v-if="!hideDeleteAction"
                    class="action-button text-red-600 hover:text-red-800"
                    @click.stop="handleDelete(item, index)"
                    :aria-label="`Eliminar fila ${index + 1}`"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- TABLE FOOTER (PAGINATION, ETC.) -->
    <div v-if="$slots.footer || showFooter" class="table-footer">
      <slot name="footer">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <!-- SELECTION INFO -->
          <div v-if="selectable && selectedItems.length > 0" class="text-caption">
            {{ selectedItems.length }} de {{ data?.length || 0 }} elementos seleccionados
          </div>
          
          <!-- ROWS PER PAGE -->
          <div v-if="pagination" class="text-caption">
            Mostrando {{ data?.length || 0 }} resultados
          </div>
        </div>
      </slot>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'

// PROPS
const props = defineProps({
  // DATA
  data: {
    type: Array,
    default: () => []
  },
  
  columns: {
    type: Array,
    required: true
  },
  
  // HEADER
  title: {
    type: String,
    default: null
  },
  
  description: {
    type: String,
    default: null
  },
  
  showHeader: {
    type: Boolean,
    default: true
  },
  
  // VISUAL
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'striped', 'bordered', 'compact'].includes(value)
  },
  
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  
  // STATES
  loading: {
    type: Boolean,
    default: false
  },
  
  loadingText: {
    type: String,
    default: 'Cargando datos...'
  },
  
  // SELECTION
  selectable: {
    type: Boolean,
    default: false
  },
  
  selectedItems: {
    type: Array,
    default: () => []
  },
  
  selectAllLabel: {
    type: String,
    default: 'Seleccionar todos'
  },
  
  // SORTING
  sortable: {
    type: Boolean,
    default: true
  },
  
  sortKey: {
    type: String,
    default: null
  },
  
  sortOrder: {
    type: String,
    default: 'asc',
    validator: (value) => ['asc', 'desc'].includes(value)
  },
  
  // ACTIONS
  showActionsColumn: {
    type: Boolean,
    default: false
  },
  
  actionsLabel: {
    type: String,
    default: 'Acciones'
  },
  
  hideEditAction: {
    type: Boolean,
    default: false
  },
  
  hideDeleteAction: {
    type: Boolean,
    default: false
  },
  
  // ROW INTERACTION
  clickableRows: {
    type: Boolean,
    default: false
  },
  
  rowKey: {
    type: String,
    default: 'id'
  },
  
  // EMPTY STATE
  emptyTitle: {
    type: String,
    default: 'No hay datos'
  },
  
  emptyDescription: {
    type: String,
    default: 'No se encontraron elementos para mostrar'
  },
  
  // FOOTER
  showFooter: {
    type: Boolean,
    default: false
  },
  
  pagination: {
    type: Boolean,
    default: false
  }
})

// EVENTS
const emit = defineEmits([
  'update:selectedItems',
  'sort',
  'row-click',
  'edit',
  'delete',
  'select',
  'select-all'
])

// COMPUTED
const containerClasses = computed(() => {
  const classes = ['relative']
  
  if (props.variant === 'bordered') {
    classes.push('border', 'border-gray-200', 'rounded-lg', 'overflow-hidden')
  }
  
  return classes.join(' ')
})

const tableClasses = computed(() => {
  const classes = ['min-w-full', 'divide-y', 'divide-gray-200']
  
  // VARIANTES
  if (props.variant === 'striped') {
    classes.push('table-striped')
  }
  
  if (props.variant === 'compact') {
    classes.push('table-compact')
  }
  
  // TAMAÑOS
  const sizeClasses = {
    sm: 'text-sm',
    md: 'text-base',
    lg: 'text-lg'
  }
  classes.push(sizeClasses[props.size])
  
  return classes.join(' ')
})

const totalColumns = computed(() => {
  let count = props.columns.length
  if (props.selectable) count++
  if (props.$slots.actions || props.showActionsColumn) count++
  return count
})

const allSelected = computed(() => {
  return props.data && props.data.length > 0 && props.selectedItems.length === props.data.length
})

const someSelected = computed(() => {
  return props.selectedItems.length > 0 && props.selectedItems.length < (props.data?.length || 0)
})

// METHODS
const getRowKey = (item, index) => {
  return item[props.rowKey] || index
}

const getRowClasses = (item, index) => {
  const classes = ['table-row']
  
  if (props.clickableRows) {
    classes.push('cursor-pointer', 'hover:bg-gray-50')
  }
  
  if (isSelected(item)) {
    classes.push('bg-blue-50')
  }
  
  return classes.join(' ')
}

const getHeaderClasses = (column) => {
  const classes = ['table-header-cell']
  
  if (column.sortable && props.sortable) {
    classes.push('sortable-header', 'cursor-pointer', 'group')
  }
  
  if (column.align) {
    classes.push(`text-${column.align}`)
  }
  
  return classes.join(' ')
}

const getCellClasses = (column, item) => {
  const classes = ['table-cell']
  
  if (column.align) {
    classes.push(`text-${column.align}`)
  }
  
  if (column.cellClass) {
    classes.push(column.cellClass)
  }
  
  return classes.join(' ')
}

const getColumnStyle = (column) => {
  const styles = {}
  
  if (column.width) {
    styles.width = column.width
    styles.minWidth = column.width
  }
  
  if (column.maxWidth) {
    styles.maxWidth = column.maxWidth
  }
  
  return styles
}

const getCellValue = (item, key) => {
  return key.split('.').reduce((obj, k) => obj?.[k], item)
}

const formatCellValue = (value, column) => {
  if (column.formatter) {
    return column.formatter(value)
  }
  
  if (value === null || value === undefined) {
    return '-'
  }
  
  // Formateo básico según tipo
  if (column.type === 'currency') {
    return new Intl.NumberFormat('es-ES', {
      style: 'currency',
      currency: 'EUR'
    }).format(value)
  }
  
  if (column.type === 'date') {
    return new Date(value).toLocaleDateString('es-ES')
  }
  
  if (column.type === 'datetime') {
    return new Date(value).toLocaleString('es-ES')
  }
  
  return value
}

const isSelected = (item) => {
  return props.selectedItems.some(selected => selected[props.rowKey] === item[props.rowKey])
}

const toggleSelection = (item) => {
  const newSelection = [...props.selectedItems]
  const index = newSelection.findIndex(selected => selected[props.rowKey] === item[props.rowKey])
  
  if (index > -1) {
    newSelection.splice(index, 1)
  } else {
    newSelection.push(item)
  }
  
  emit('update:selectedItems', newSelection)
  emit('select', { item, selected: index === -1 })
}

const toggleAllSelection = () => {
  if (allSelected.value) {
    emit('update:selectedItems', [])
  } else {
    emit('update:selectedItems', [...props.data])
  }
  
  emit('select-all', { selected: !allSelected.value })
}

const handleSort = (column) => {
  if (!column.sortable || !props.sortable) return
  
  let newOrder = 'asc'
  if (props.sortKey === column.key && props.sortOrder === 'asc') {
    newOrder = 'desc'
  }
  
  emit('sort', { key: column.key, order: newOrder })
}

const handleRowClick = (item, index) => {
  if (props.clickableRows) {
    emit('row-click', { item, index })
  }
}

const handleEdit = (item, index) => {
  emit('edit', { item, index })
}

const handleDelete = (item, index) => {
  emit('delete', { item, index })
}
</script>

<style scoped>
/* TABLE CONTAINER */
.table-container {
  background-color: white;
  border-radius: 0.5rem;
  box-shadow: var(--shadow-sm);
}

/* TABLE HEADER */
.table-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

/* TABLE WRAPPER */
.table-wrapper {
  overflow-x: auto;
  position: relative;
}

/* TABLE STYLES */
.table {
  background-color: white;
}

.table-head {
  background-color: #f9fafb;
}

.table-header-cell {
  padding: 0.75rem 1.5rem;
  text-align: left;
  font-size: 0.75rem;
  line-height: 1rem;
  font-weight: 600;
  color: #374151;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 1px solid #e5e7eb;
}

.table-cell {
  padding: 1rem 1.5rem;
  color: #111827;
  border-bottom: 1px solid #e5e7eb;
}

.table-cell-checkbox {
  padding: 1rem 1.5rem;
  width: 3rem;
  border-bottom: 1px solid #e5e7eb;
}

.table-cell-actions {
  padding: 1rem 1.5rem;
  width: 8rem;
  text-align: right;
  border-bottom: 1px solid #e5e7eb;
}

.table-cell-empty {
  padding: 3rem 1.5rem;
  text-align: center;
  border-bottom: 1px solid #e5e7eb;
}

/* SORTABLE HEADERS */
.sortable-header:hover {
  background-color: #f3f4f6;
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.sort-indicator {
  margin-left: 0.5rem;
}

.sort-icon {
  width: 1rem;
  height: 1rem;
  transition: transform 0.2s ease, opacity 0.2s ease;
}

/* CHECKBOX STYLES */
.checkbox {
  width: 1rem;
  height: 1rem;
  color: #3b82f6;
  border-radius: 0.25rem;
  border: 1px solid #d1d5db;
  background-color: white;
}

.checkbox:checked {
  background-color: #3b82f6;
  border-color: #3b82f6;
}

/* ACTIONS */
.actions-menu {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.5rem;
}

.action-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  color: #6b7280;
  background-color: transparent;
  border: none;
  border-radius: 0.375rem;
  transition: all 0.2s ease;
}

.action-button:hover {
  background-color: #f3f4f6;
  color: #374151;
}

/* EMPTY STATE */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 2rem;
}

.empty-icon {
  width: 3rem;
  height: 3rem;
  color: #9ca3af;
  margin-bottom: 1rem;
}

.empty-title {
  font-size: 1.125rem;
  line-height: 1.75rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 0.5rem;
}

.empty-description {
  color: #6b7280;
  text-align: center;
  max-width: 20rem;
}

/* LOADING OVERLAY */
.table-loading {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255, 255, 255, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
}

.loading-spinner {
  display: flex;
  align-items: center;
  font-weight: 500;
  color: #374151;
}

/* TABLE FOOTER */
.table-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #e5e7eb;
  background-color: #f9fafb;
}

/* STRIPED VARIANT */
.table-striped .table-row:nth-child(even) {
  background-color: #f9fafb;
}

/* COMPACT VARIANT */
.table-compact .table-header-cell,
.table-compact .table-cell,
.table-compact .table-cell-checkbox,
.table-compact .table-cell-actions {
  padding: 0.5rem 1rem;
}

/* RESPONSIVE */
@media (max-width: 640px) {
  .table-header,
  .table-footer {
    padding: 1rem;
  }
  
  .table-header-cell,
  .table-cell,
  .table-cell-checkbox,
  .table-cell-actions {
    padding: 0.75rem 1rem;
  }
  
  .table-cell-empty {
    padding: 2rem 1rem;
  }
}
</style>
