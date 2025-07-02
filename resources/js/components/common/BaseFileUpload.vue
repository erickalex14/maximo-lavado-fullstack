<template>
  <div class="file-upload-container">
    <label
      v-if="label"
      :for="computedId"
      :class="labelClasses"
    >
      {{ label }}
      <span v-if="required" class="required-indicator">*</span>
    </label>
    
    <div class="file-upload-wrapper" :class="wrapperClasses">
      <input
        :id="computedId"
        ref="fileInput"
        type="file"
        :multiple="multiple"
        :accept="accept"
        :disabled="disabled || loading"
        :required="required"
        :name="name"
        class="file-input"
        v-bind="$attrs"
        @change="handleFileChange"
        @dragover.prevent="handleDragOver"
        @dragleave.prevent="handleDragLeave"
        @drop.prevent="handleDrop"
      >
      
      <div class="upload-area" :class="uploadAreaClasses" @click="triggerFileInput">
        <div v-if="loading" class="upload-loading">
          <svg class="loading-spinner" viewBox="0 0 24 24">
            <circle
              class="loading-circle"
              cx="12"
              cy="12"
              r="10"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            />
          </svg>
          <span class="loading-text">{{ loadingText }}</span>
        </div>
        
        <div v-else-if="files.length === 0" class="upload-placeholder">
          <slot name="placeholder">
            <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="7,10 12,15 17,10"/>
              <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            <div class="upload-text">
              <span class="upload-primary-text">
                {{ dragActive ? dropText : clickText }}
              </span>
              <span class="upload-secondary-text">{{ supportText }}</span>
            </div>
          </slot>
        </div>
        
        <div v-else class="files-preview">
          <div v-for="(file, index) in files" :key="index" class="file-item" :class="getFileItemClasses(file)">
            <div class="file-info">
              <div class="file-icon">
                <slot name="file-icon" :file="file">
                  <svg v-if="isImage(file)" class="icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                  </svg>
                  <svg v-else class="icon" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/>
                  </svg>
                </slot>
              </div>
              
              <div class="file-details">
                <div class="file-name" :title="file.name">{{ file.name }}</div>
                <div class="file-meta">
                  <span class="file-size">{{ formatFileSize(file.size) }}</span>
                  <span v-if="file.status" class="file-status" :class="`status-${file.status}`">
                    {{ getStatusText(file.status) }}
                  </span>
                </div>
                
                <div v-if="file.progress !== undefined" class="file-progress">
                  <div class="progress-bar">
                    <div
                      class="progress-fill"
                      :style="{ width: `${file.progress}%` }"
                    ></div>
                  </div>
                  <span class="progress-text">{{ file.progress }}%</span>
                </div>
              </div>
            </div>
            
            <button
              v-if="!disabled && !loading"
              type="button"
              class="remove-button"
              @click.stop="removeFile(index)"
            >
              <svg class="remove-icon" viewBox="0 0 20 20" fill="currentColor">
                <path
                  fill-rule="evenodd"
                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>
          </div>
        </div>
      </div>
      
      <div v-if="showFileList && files.length > 0" class="file-list">
        <div class="file-list-header">
          <span class="file-count">{{ files.length }} archivo(s) seleccionado(s)</span>
          <button
            v-if="!disabled && !loading && files.length > 1"
            type="button"
            class="clear-all-button"
            @click="clearAll"
          >
            Limpiar todo
          </button>
        </div>
      </div>
    </div>
    
    <div v-if="hint" class="file-upload-hint">
      {{ hint }}
    </div>
    
    <div v-if="error" class="file-upload-error">
      {{ error }}
    </div>
  </div>
</template>

<script setup>
import { computed, ref, useAttrs, watch } from 'vue'

// Props
const props = defineProps({
  modelValue: {
    type: [File, Array],
    default: null
  },
  label: {
    type: String,
    default: ''
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
  loading: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  multiple: {
    type: Boolean,
    default: false
  },
  accept: {
    type: String,
    default: ''
  },
  maxSize: {
    type: Number,
    default: 10 * 1024 * 1024 // 10MB
  },
  maxFiles: {
    type: Number,
    default: 10
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'dashed', 'minimal'].includes(value)
  },
  name: {
    type: String,
    default: ''
  },
  id: {
    type: String,
    default: ''
  },
  clickText: {
    type: String,
    default: 'Haz clic para seleccionar archivos'
  },
  dropText: {
    type: String,
    default: 'Suelta los archivos aquí'
  },
  supportText: {
    type: String,
    default: 'o arrastra y suelta'
  },
  loadingText: {
    type: String,
    default: 'Subiendo archivos...'
  },
  showFileList: {
    type: Boolean,
    default: true
  },
  autoUpload: {
    type: Boolean,
    default: false
  }
})

// Emits
const emit = defineEmits([
  'update:modelValue',
  'change',
  'file-added',
  'file-removed',
  'files-exceeded',
  'size-exceeded',
  'type-rejected',
  'upload-start',
  'upload-progress',
  'upload-success',
  'upload-error'
])

// Reactive data
const files = ref([])
const dragActive = ref(false)
const fileInput = ref(null)

// Attrs
const attrs = useAttrs()

// Computed
const computedId = computed(() => {
  return props.id || `file-upload-${Math.random().toString(36).substr(2, 9)}`
})

const wrapperClasses = computed(() => {
  const classes = ['file-upload-base']
  
  // Size
  classes.push(`file-upload-${props.size}`)
  
  // Variant
  classes.push(`file-upload-${props.variant}`)
  
  // States
  if (props.disabled) classes.push('file-upload-disabled')
  if (props.loading) classes.push('file-upload-loading')
  if (props.error) classes.push('file-upload-error')
  if (dragActive.value) classes.push('file-upload-drag-active')
  
  return classes
})

const uploadAreaClasses = computed(() => {
  const classes = ['upload-area-base']
  
  if (files.value.length === 0) classes.push('upload-area-empty')
  
  return classes
})

const labelClasses = computed(() => {
  const classes = ['file-upload-label']
  
  if (props.disabled) classes.push('file-upload-label-disabled')
  
  return classes
})

// Methods
const triggerFileInput = () => {
  if (!props.disabled && !props.loading) {
    fileInput.value?.click()
  }
}

const handleFileChange = (event) => {
  const selectedFiles = Array.from(event.target.files || [])
  processFiles(selectedFiles)
}

const handleDragOver = (event) => {
  if (!props.disabled && !props.loading) {
    dragActive.value = true
  }
}

const handleDragLeave = (event) => {
  dragActive.value = false
}

const handleDrop = (event) => {
  dragActive.value = false
  
  if (props.disabled || props.loading) return
  
  const droppedFiles = Array.from(event.dataTransfer?.files || [])
  processFiles(droppedFiles)
}

const processFiles = (newFiles) => {
  const validFiles = []
  
  for (const file of newFiles) {
    // Check file size
    if (file.size > props.maxSize) {
      emit('size-exceeded', { file, maxSize: props.maxSize })
      continue
    }
    
    // Check file type
    if (props.accept && !isFileTypeAccepted(file)) {
      emit('type-rejected', { file, accept: props.accept })
      continue
    }
    
    // Check max files limit
    if (!props.multiple && files.value.length >= 1) {
      emit('files-exceeded', { maxFiles: 1 })
      break
    }
    
    if (props.multiple && (files.value.length + validFiles.length) >= props.maxFiles) {
      emit('files-exceeded', { maxFiles: props.maxFiles })
      break
    }
    
    validFiles.push({
      ...file,
      id: Math.random().toString(36).substr(2, 9),
      status: 'pending',
      progress: 0
    })
  }
  
  if (validFiles.length > 0) {
    if (props.multiple) {
      files.value = [...files.value, ...validFiles]
    } else {
      files.value = [validFiles[0]]
    }
    
    updateModelValue()
    
    validFiles.forEach(file => {
      emit('file-added', file)
      
      if (props.autoUpload) {
        uploadFile(file)
      }
    })
  }
  
  // Clear input
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const removeFile = (index) => {
  const removedFile = files.value[index]
  files.value.splice(index, 1)
  updateModelValue()
  emit('file-removed', removedFile)
}

const clearAll = () => {
  files.value = []
  updateModelValue()
  emit('change', null)
}

const updateModelValue = () => {
  if (props.multiple) {
    emit('update:modelValue', files.value)
  } else {
    emit('update:modelValue', files.value[0] || null)
  }
  
  emit('change', props.multiple ? files.value : (files.value[0] || null))
}

const isFileTypeAccepted = (file) => {
  if (!props.accept) return true
  
  const acceptTypes = props.accept.split(',').map(type => type.trim())
  
  return acceptTypes.some(type => {
    if (type.startsWith('.')) {
      return file.name.toLowerCase().endsWith(type.toLowerCase())
    }
    
    if (type.includes('*')) {
      const baseType = type.split('/')[0]
      return file.type.startsWith(baseType)
    }
    
    return file.type === type
  })
}

const isImage = (file) => {
  return file.type?.startsWith('image/')
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const getStatusText = (status) => {
  const statusMap = {
    pending: 'Pendiente',
    uploading: 'Subiendo...',
    success: 'Completado',
    error: 'Error'
  }
  
  return statusMap[status] || status
}

const getFileItemClasses = (file) => {
  const classes = []
  
  if (file.status) {
    classes.push(`file-${file.status}`)
  }
  
  return classes
}

const uploadFile = async (file) => {
  file.status = 'uploading'
  emit('upload-start', file)
  
  try {
    // Simulate upload progress
    for (let progress = 0; progress <= 100; progress += 10) {
      file.progress = progress
      emit('upload-progress', { file, progress })
      await new Promise(resolve => setTimeout(resolve, 100))
    }
    
    file.status = 'success'
    emit('upload-success', file)
  } catch (error) {
    file.status = 'error'
    emit('upload-error', { file, error })
  }
}

// Watch for external model value changes
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    if (props.multiple && Array.isArray(newValue)) {
      files.value = newValue
    } else if (!props.multiple && newValue) {
      files.value = [newValue]
    }
  } else {
    files.value = []
  }
}, { immediate: true })

// Expose methods
defineExpose({
  triggerFileInput,
  clearAll,
  uploadFile
})
</script>

<style scoped>
/* Base file upload styles */
.file-upload-container {
  width: 100%;
}

.file-upload-wrapper {
  position: relative;
}

.file-upload-base {
  position: relative;
}

.file-upload-label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--color-text-700);
}

.file-upload-label-disabled {
  color: var(--color-text-400);
}

.required-indicator {
  color: var(--color-error-500);
  margin-left: 0.25rem;
}

.file-input {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

/* Upload area */
.upload-area {
  min-height: 8rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border: 2px solid var(--color-border-300);
  border-radius: 0.5rem;
  background-color: var(--color-bg-50);
  cursor: pointer;
  transition: all 0.2s ease;
}

.upload-area-base {
  border-style: solid;
}

.upload-area:hover:not(.file-upload-disabled) {
  border-color: var(--color-primary-400);
  background-color: var(--color-primary-50);
}

/* Variant styles */
.file-upload-dashed .upload-area {
  border-style: dashed;
}

.file-upload-minimal .upload-area {
  border: 1px solid var(--color-border-200);
  background-color: white;
}

/* Size variants */
.file-upload-sm .upload-area {
  min-height: 6rem;
}

.file-upload-lg .upload-area {
  min-height: 10rem;
}

/* State styles */
.file-upload-drag-active .upload-area {
  border-color: var(--color-primary-500);
  background-color: var(--color-primary-100);
}

.file-upload-disabled .upload-area {
  border-color: var(--color-border-200);
  background-color: var(--color-bg-100);
  cursor: not-allowed;
}

.file-upload-error .upload-area {
  border-color: var(--color-error-400);
}

/* Upload placeholder */
.upload-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  text-align: center;
}

.upload-icon {
  width: 3rem;
  height: 3rem;
  color: var(--color-text-400);
}

.upload-text {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.upload-primary-text {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--color-text-700);
}

.upload-secondary-text {
  font-size: 0.75rem;
  color: var(--color-text-500);
}

/* Loading state */
.upload-loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.loading-spinner {
  width: 2rem;
  height: 2rem;
  color: var(--color-primary-600);
  animation: spin 1s linear infinite;
}

.loading-circle {
  stroke-dasharray: 31.416;
  stroke-dashoffset: 31.416;
  animation: loading 2s ease-in-out infinite;
}

.loading-text {
  font-size: 0.875rem;
  color: var(--color-text-600);
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@keyframes loading {
  0% { stroke-dasharray: 0 31.416; }
  50% { stroke-dasharray: 15.708 15.708; }
  100% { stroke-dasharray: 31.416 0; }
}

/* Files preview */
.files-preview {
  width: 100%;
  padding: 1rem;
}

.file-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem;
  border: 1px solid var(--color-border-200);
  border-radius: 0.375rem;
  margin-bottom: 0.5rem;
  background-color: white;
  transition: all 0.2s ease;
}

.file-item:last-child {
  margin-bottom: 0;
}

.file-item:hover {
  border-color: var(--color-border-300);
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.file-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex: 1;
  min-width: 0;
}

.file-icon {
  flex-shrink: 0;
}

.file-icon .icon {
  width: 2rem;
  height: 2rem;
  color: var(--color-text-400);
}

.file-details {
  flex: 1;
  min-width: 0;
}

.file-name {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--color-text-900);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.file-meta {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.25rem;
}

.file-size {
  font-size: 0.75rem;
  color: var(--color-text-500);
}

.file-status {
  font-size: 0.75rem;
  font-weight: 500;
}

.status-pending {
  color: var(--color-text-500);
}

.status-uploading {
  color: var(--color-primary-600);
}

.status-success {
  color: var(--color-success-600);
}

.status-error {
  color: var(--color-error-600);
}

.file-progress {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.progress-bar {
  flex: 1;
  height: 0.25rem;
  background-color: var(--color-bg-200);
  border-radius: 0.125rem;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background-color: var(--color-primary-600);
  transition: width 0.3s ease;
}

.progress-text {
  font-size: 0.75rem;
  color: var(--color-text-500);
  min-width: 3rem;
  text-align: right;
}

.remove-button {
  flex-shrink: 0;
  width: 1.5rem;
  height: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
  color: var(--color-text-400);
  transition: all 0.2s ease;
}

.remove-button:hover {
  background-color: var(--color-error-100);
  color: var(--color-error-600);
}

.remove-icon {
  width: 1rem;
  height: 1rem;
}

/* File list */
.file-list {
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid var(--color-border-200);
}

.file-list-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.file-count {
  font-size: 0.875rem;
  color: var(--color-text-600);
}

.clear-all-button {
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
  color: var(--color-error-600);
  background: none;
  border: none;
  border-radius: 0.25rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.clear-all-button:hover {
  background-color: var(--color-error-50);
}

/* Hint and error text */
.file-upload-hint {
  margin-top: 0.25rem;
  font-size: 0.75rem;
  color: var(--color-text-500);
}

.file-upload-error {
  margin-top: 0.25rem;
  font-size: 0.75rem;
  color: var(--color-error-600);
}

/* File status styles */
.file-pending {
  border-color: var(--color-border-200);
}

.file-uploading {
  border-color: var(--color-primary-300);
  background-color: var(--color-primary-50);
}

.file-success {
  border-color: var(--color-success-300);
  background-color: var(--color-success-50);
}

.file-error {
  border-color: var(--color-error-300);
  background-color: var(--color-error-50);
}
</style>
