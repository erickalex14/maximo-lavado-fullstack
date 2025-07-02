<template>
  <div class="demo-container">
    <div class="demo-header">
      <h1>Demo de Componentes Base</h1>
      <p>Ejemplos de uso de todos los componentes base disponibles</p>
    </div>

    <!-- Form Demo -->
    <BaseCard title="Formulario de Ejemplo" class="demo-section">
      <BaseForm
        title="Registro de Cliente"
        description="Complete la información del cliente"
        :loading="isSubmitting"
        @submit="handleSubmit"
        @cancel="handleCancel"
      >
        <div class="form-grid">
          <BaseInput
            v-model="form.name"
            label="Nombre completo"
            placeholder="Ingrese el nombre"
            :error="errors.name"
            required
          />
          
          <BaseInput
            v-model="form.email"
            label="Correo electrónico"
            type="email"
            placeholder="ejemplo@correo.com"
            :error="errors.email"
            required
          />
          
          <BaseInput
            v-model="form.phone"
            label="Teléfono"
            type="tel"
            placeholder="+1234567890"
          />
          
          <BaseSelect
            v-model="form.category"
            label="Categoría"
            :options="categoryOptions"
            placeholder="Seleccione una categoría"
            searchable
            clearable
          />
          
          <BaseDatePicker
            v-model="form.birthDate"
            label="Fecha de nacimiento"
            placeholder="Seleccionar fecha"
            clearable
          />
          
          <BaseSwitch
            v-model="form.notifications"
            label="Recibir notificaciones"
            description="Acepto recibir notificaciones por email"
            variant="primary"
          />
        </div>
        
        <BaseTextarea
          v-model="form.notes"
          label="Notas adicionales"
          placeholder="Información adicional del cliente..."
          :max-length="500"
          show-counter
          auto-resize
        />
        
        <div class="checkbox-group">
          <h4>Servicios de interés:</h4>
          <BaseCheckbox
            v-for="service in services"
            :key="service.id"
            v-model="form.selectedServices"
            :value="service.id"
            :label="service.name"
            :description="service.description"
          />
        </div>
        
        <div class="radio-group">
          <h4>Preferencia de contacto:</h4>
          <BaseRadio
            v-for="option in contactOptions"
            :key="option.value"
            v-model="form.contactPreference"
            :value="option.value"
            :label="option.label"
            name="contact"
          />
        </div>
        
        <BaseFileUpload
          v-model="form.documents"
          label="Documentos"
          accept=".pdf,.doc,.docx,image/*"
          multiple
          :max-size="5242880"
          hint="Suba documentos relevantes (máximo 5MB cada uno)"
        />
      </BaseForm>
    </BaseCard>

    <!-- Table Demo -->
    <BaseCard title="Tabla de Clientes" class="demo-section">
      <template #actions>
        <BaseSearchInput
          v-model="searchQuery"
          placeholder="Buscar clientes..."
          :suggestions="searchSuggestions"
          @search="handleSearch"
        />
        <BaseButton variant="primary" @click="showAddModal = true">
          Agregar Cliente
        </BaseButton>
      </template>
      
      <BaseTable
        :columns="tableColumns"
        :data="filteredClients"
        :loading="isLoadingClients"
        sortable
        striped
        @sort="handleSort"
        @row-click="handleRowClick"
      >
        <template #status="{ row }">
          <BaseBadge
            :variant="getStatusVariant(row.status)"
            :text="row.status"
          />
        </template>
        
        <template #actions="{ row }">
          <BaseButton
            size="sm"
            variant="outline"
            @click="editClient(row)"
          >
            Editar
          </BaseButton>
          <BaseButton
            size="sm"
            variant="danger"
            @click="deleteClient(row)"
          >
            Eliminar
          </BaseButton>
        </template>
      </BaseTable>
      
      <BasePagination
        v-model="currentPage"
        :total-items="totalClients"
        :items-per-page="itemsPerPage"
        show-info
        @change="handlePageChange"
      />
    </BaseCard>

    <!-- Feedback Demo -->
    <BaseCard title="Componentes de Feedback" class="demo-section">
      <div class="feedback-demo">
        <div class="demo-row">
          <BaseButton @click="showSuccessToast">Mostrar Toast de Éxito</BaseButton>
          <BaseButton @click="showErrorAlert" variant="danger">Mostrar Alerta de Error</BaseButton>
          <BaseButton @click="toggleLoading" variant="secondary">Toggle Loading</BaseButton>
        </div>
        
        <BaseAlert
          v-if="showAlert"
          variant="warning"
          title="Atención"
          :dismissible="true"
          @dismiss="showAlert = false"
        >
          Este es un ejemplo de alerta que puede ser cerrada por el usuario.
          
          <template #actions>
            <BaseButton size="sm" variant="outline">Más información</BaseButton>
          </template>
        </BaseAlert>
        
        <BaseLoading
          v-if="isLoading"
          variant="spinner"
          text="Cargando datos del servidor..."
          overlay
        />
      </div>
    </BaseCard>

    <!-- Modals -->
    <BaseModal
      v-model="showAddModal"
      title="Agregar Nuevo Cliente"
      size="lg"
      :closable="true"
    >
      <p>Aquí iría el formulario para agregar un nuevo cliente...</p>
      
      <template #footer>
        <BaseButton variant="secondary" @click="showAddModal = false">
          Cancelar
        </BaseButton>
        <BaseButton variant="primary" @click="saveClient">
          Guardar Cliente
        </BaseButton>
      </template>
    </BaseModal>

    <!-- Toast Notifications -->
    <BaseToast
      v-model="showToast"
      :variant="toastVariant"
      :title="toastTitle"
      :message="toastMessage"
      :duration="3000"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import {
  BaseCard,
  BaseForm,
  BaseInput,
  BaseSelect,
  BaseDatePicker,
  BaseSwitch,
  BaseTextarea,
  BaseCheckbox,
  BaseRadio,
  BaseFileUpload,
  BaseButton,
  BaseTable,
  BaseBadge,
  BasePagination,
  BaseSearchInput,
  BaseAlert,
  BaseLoading,
  BaseModal,
  BaseToast
} from '@/components/common'

// Form data
const form = reactive({
  name: '',
  email: '',
  phone: '',
  category: null,
  birthDate: null,
  notifications: false,
  notes: '',
  selectedServices: [],
  contactPreference: 'email',
  documents: []
})

const errors = ref({})
const isSubmitting = ref(false)

// Form options
const categoryOptions = [
  { value: 'premium', label: 'Cliente Premium' },
  { value: 'regular', label: 'Cliente Regular' },
  { value: 'vip', label: 'Cliente VIP' }
]

const services = [
  { id: 1, name: 'Lavado Básico', description: 'Servicio de lavado estándar' },
  { id: 2, name: 'Lavado Premium', description: 'Servicio completo con encerado' },
  { id: 3, name: 'Detailing', description: 'Servicio profesional de detallado' }
]

const contactOptions = [
  { value: 'email', label: 'Correo electrónico' },
  { value: 'phone', label: 'Teléfono' },
  { value: 'sms', label: 'SMS' }
]

// Table data
const clients = ref([
  {
    id: 1,
    name: 'Juan Pérez',
    email: 'juan@email.com',
    phone: '+1234567890',
    status: 'activo',
    category: 'premium'
  },
  {
    id: 2,
    name: 'María García',
    email: 'maria@email.com',
    phone: '+0987654321',
    status: 'inactivo',
    category: 'regular'
  }
])

const tableColumns = [
  { key: 'name', label: 'Nombre', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'phone', label: 'Teléfono' },
  { key: 'status', label: 'Estado', slot: 'status' },
  { key: 'actions', label: 'Acciones', slot: 'actions' }
]

// Search and pagination
const searchQuery = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(10)
const isLoadingClients = ref(false)

const filteredClients = computed(() => {
  if (!searchQuery.value) return clients.value
  
  return clients.value.filter(client =>
    client.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    client.email.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const totalClients = computed(() => filteredClients.value.length)

const searchSuggestions = computed(() => {
  if (!searchQuery.value) return []
  
  return clients.value
    .filter(client => 
      client.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
    .map(client => client.name)
    .slice(0, 5)
})

// Feedback components
const showAlert = ref(true)
const isLoading = ref(false)
const showAddModal = ref(false)
const showToast = ref(false)
const toastVariant = ref('success')
const toastTitle = ref('')
const toastMessage = ref('')

// Methods
const handleSubmit = async (data) => {
  isSubmitting.value = true
  
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 2000))
    
    console.log('Form submitted:', data)
    
    showSuccessToast('Cliente guardado exitosamente')
    
    // Reset form
    Object.keys(form).forEach(key => {
      if (Array.isArray(form[key])) {
        form[key] = []
      } else if (typeof form[key] === 'boolean') {
        form[key] = false
      } else {
        form[key] = ''
      }
    })
    
  } catch (error) {
    console.error('Error submitting form:', error)
    showErrorToast('Error al guardar el cliente')
  } finally {
    isSubmitting.value = false
  }
}

const handleCancel = () => {
  console.log('Form cancelled')
}

const handleSearch = (query) => {
  console.log('Searching for:', query)
}

const handleSort = (sortData) => {
  console.log('Sorting:', sortData)
}

const handleRowClick = (row) => {
  console.log('Row clicked:', row)
}

const handlePageChange = (page) => {
  currentPage.value = page
  console.log('Page changed to:', page)
}

const editClient = (client) => {
  console.log('Editing client:', client)
}

const deleteClient = (client) => {
  console.log('Deleting client:', client)
}

const getStatusVariant = (status) => {
  const variants = {
    activo: 'success',
    inactivo: 'warning',
    bloqueado: 'danger'
  }
  return variants[status] || 'secondary'
}

const saveClient = () => {
  console.log('Saving new client...')
  showAddModal.value = false
  showSuccessToast('Cliente agregado exitosamente')
}

const showSuccessToast = (message = 'Operación exitosa') => {
  toastVariant.value = 'success'
  toastTitle.value = 'Éxito'
  toastMessage.value = message
  showToast.value = true
}

const showErrorToast = (message = 'Ha ocurrido un error') => {
  toastVariant.value = 'danger'
  toastTitle.value = 'Error'
  toastMessage.value = message
  showToast.value = true
}

const showErrorAlert = () => {
  showAlert.value = true
}

const toggleLoading = () => {
  isLoading.value = !isLoading.value
}

onMounted(() => {
  console.log('Demo component mounted')
})
</script>

<style scoped>
.demo-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.demo-header {
  text-align: center;
  margin-bottom: 3rem;
}

.demo-header h1 {
  font-size: 2.5rem;
  font-weight: 700;
  color: var(--color-text-900);
  margin-bottom: 0.5rem;
}

.demo-header p {
  font-size: 1.125rem;
  color: var(--color-text-600);
}

.demo-section {
  margin-bottom: 2rem;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.checkbox-group,
.radio-group {
  margin-bottom: 1.5rem;
}

.checkbox-group h4,
.radio-group h4 {
  margin: 0 0 1rem 0;
  font-size: 1rem;
  font-weight: 600;
  color: var(--color-text-700);
}

.checkbox-group > *:not(h4),
.radio-group > *:not(h4) {
  margin-bottom: 0.75rem;
}

.feedback-demo {
  padding: 1rem;
}

.demo-row {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

@media (max-width: 768px) {
  .demo-container {
    padding: 1rem;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .demo-row {
    flex-direction: column;
  }
}
</style>
