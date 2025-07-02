<template>
  <AppLayout>
    <!-- Header Section -->
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            Dashboard
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">
            Bienvenido al panel de control de Máximo Lavado
          </p>
        </div>
        <div class="flex items-center gap-3">
          <BaseButton 
            variant="outline" 
            size="sm"
            @click="refreshData"
            :loading="loading"
          >
            <Icon name="refresh" class="w-4 h-4 mr-2" />
            Actualizar
          </BaseButton>
          <BaseButton 
            variant="primary" 
            size="sm"
            @click="openReportModal"
          >
            <Icon name="document-text" class="w-4 h-4 mr-2" />
            Generar Reporte
          </BaseButton>
        </div>
      </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <DashboardStatCard
        v-for="stat in stats"
        :key="stat.id"
        :icon="stat.icon"
        :title="stat.title"
        :value="stat.value"
        :change="stat.change"
        :trend="stat.trend"
        :color="stat.color"
      />
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
      <!-- Sales Chart -->
      <div class="lg:col-span-2">
        <BaseCard>
          <template #header>
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Ventas de los Últimos 7 Días
              </h3>
              <BaseSelect
                v-model="chartPeriod"
                :options="chartPeriodOptions"
                size="sm"
                class="w-32"
              />
            </div>
          </template>
          <div class="h-80 flex items-center justify-center text-gray-500 dark:text-gray-400">
            <div class="text-center">
              <Icon name="chart-bar" class="w-16 h-16 mx-auto mb-4 opacity-50" />
              <p>Gráfico de ventas</p>
              <p class="text-sm">(Se integrará con Chart.js)</p>
            </div>
          </div>
        </BaseCard>
      </div>

      <!-- Recent Activity -->
      <div>
        <BaseCard>
          <template #header>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              Actividad Reciente
            </h3>
          </template>
          <div class="space-y-4">
            <div 
              v-for="activity in recentActivities"
              :key="activity.id"
              class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50"
            >
              <div :class="[
                'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center',
                activity.type === 'sale' ? 'bg-green-100 text-green-600 dark:bg-green-900/20 dark:text-green-400' :
                activity.type === 'wash' ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400' :
                'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/20 dark:text-yellow-400'
              ]">
                <Icon :name="activity.icon" class="w-4 h-4" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ activity.title }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ activity.description }}
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                  {{ formatTimeAgo(activity.timestamp) }}
                </p>
              </div>
            </div>
          </div>
          <template #footer>
            <BaseButton variant="ghost" size="sm" class="w-full">
              Ver todas las actividades
              <Icon name="arrow-right" class="w-4 h-4 ml-2" />
            </BaseButton>
          </template>
        </BaseCard>
      </div>
    </div>

    <!-- Additional Information Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Top Services -->
      <BaseCard>
        <template #header>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Servicios Más Populares
          </h3>
        </template>
        <div class="space-y-4">
          <div 
            v-for="service in topServices"
            :key="service.id"
            class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50"
          >
            <div class="flex items-center gap-3">
              <div :class="[
                'flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center',
                service.color
              ]">
                <Icon :name="service.icon" class="w-5 h-5" />
              </div>
              <div>
                <h4 class="font-medium text-gray-900 dark:text-white">
                  {{ service.name }}
                </h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  {{ service.count }} servicios este mes
                </p>
              </div>
            </div>
            <div class="text-right">
              <p class="font-semibold text-gray-900 dark:text-white">
                ${{ service.revenue.toLocaleString() }}
              </p>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                ingresos
              </p>
            </div>
          </div>
        </div>
      </BaseCard>

      <!-- Quick Actions -->
      <BaseCard>
        <template #header>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Acciones Rápidas
          </h3>
        </template>
        <div class="grid grid-cols-2 gap-4">
          <BaseButton
            v-for="action in quickActions"
            :key="action.id"
            :variant="action.variant"
            size="lg"
            class="h-20 flex-col"
            @click="handleQuickAction(action.action)"
          >
            <Icon :name="action.icon" class="w-6 h-6 mb-2" />
            <span class="text-sm">{{ action.label }}</span>
          </BaseButton>
        </div>
      </BaseCard>
    </div>

    <!-- Report Modal -->
    <BaseModal
      v-model="showReportModal"
      title="Generar Reporte"
      size="md"
    >
      <div class="space-y-4">
        <div>
          <BaseLabel for="reportType">Tipo de Reporte</BaseLabel>
          <BaseSelect
            id="reportType"
            v-model="reportForm.type"
            :options="reportTypeOptions"
            placeholder="Selecciona el tipo de reporte"
          />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <BaseLabel for="startDate">Fecha Inicio</BaseLabel>
            <BaseInput
              id="startDate"
              v-model="reportForm.startDate"
              type="date"
            />
          </div>
          <div>
            <BaseLabel for="endDate">Fecha Fin</BaseLabel>
            <BaseInput
              id="endDate"
              v-model="reportForm.endDate"
              type="date"
            />
          </div>
        </div>
      </div>
      <template #footer>
        <div class="flex justify-end gap-3">
          <BaseButton variant="outline" @click="showReportModal = false">
            Cancelar
          </BaseButton>
          <BaseButton 
            variant="primary" 
            @click="generateReport"
            :loading="generatingReport"
          >
            Generar Reporte
          </BaseButton>
        </div>
      </template>
    </BaseModal>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import AppLayout from '@/layouts/AppLayout.vue'
import Icon from '@/components/icons/index.js'
import {
  BaseButton,
  BaseCard,
  BaseSelect,
  BaseModal,
  BaseLabel,
  BaseInput
} from '@/components/common'
import DashboardStatCard from './components/DashboardStatCard.vue'
import { useToast } from '@/stores/toast'

const router = useRouter()
const toast = useToast()

// Reactive data
const loading = ref(false)
const showReportModal = ref(false)
const generatingReport = ref(false)
const chartPeriod = ref('7d')

// Form data
const reportForm = reactive({
  type: '',
  startDate: '',
  endDate: ''
})

// Options
const chartPeriodOptions = [
  { value: '7d', label: 'Últimos 7 días' },
  { value: '30d', label: 'Últimos 30 días' },
  { value: '90d', label: 'Últimos 3 meses' },
  { value: '1y', label: 'Último año' }
]

const reportTypeOptions = [
  { value: 'sales', label: 'Reporte de Ventas' },
  { value: 'services', label: 'Reporte de Servicios' },
  { value: 'customers', label: 'Reporte de Clientes' },
  { value: 'financial', label: 'Reporte Financiero' }
]

// Mock data
const stats = ref([
  {
    id: 1,
    icon: 'currency-dollar',
    title: 'Ingresos Hoy',
    value: '$2,350',
    change: '+12.5%',
    trend: 'up',
    color: 'green'
  },
  {
    id: 2,
    icon: 'sparkles',
    title: 'Lavados Hoy',
    value: '24',
    change: '+8.2%',
    trend: 'up',
    color: 'blue'
  },
  {
    id: 3,
    icon: 'users',
    title: 'Clientes Activos',
    value: '186',
    change: '+15.1%',
    trend: 'up',
    color: 'purple'
  },
  {
    id: 4,
    icon: 'clock',
    title: 'Tiempo Promedio',
    value: '45 min',
    change: '-5.3%',
    trend: 'down',
    color: 'orange'
  }
])

const recentActivities = ref([
  {
    id: 1,
    type: 'sale',
    icon: 'currency-dollar',
    title: 'Nueva venta registrada',
    description: 'Lavado completo - Toyota Camry',
    timestamp: new Date(Date.now() - 10 * 60 * 1000) // 10 minutes ago
  },
  {
    id: 2,
    type: 'wash',
    icon: 'sparkles',
    title: 'Servicio completado',
    description: 'Lavado exterior - Honda Civic',
    timestamp: new Date(Date.now() - 25 * 60 * 1000) // 25 minutes ago
  },
  {
    id: 3,
    type: 'customer',
    icon: 'user-plus',
    title: 'Nuevo cliente registrado',
    description: 'Juan Pérez - Tel: 555-0123',
    timestamp: new Date(Date.now() - 45 * 60 * 1000) // 45 minutes ago
  },
  {
    id: 4,
    type: 'sale',
    icon: 'currency-dollar',
    title: 'Venta completada',
    description: 'Lavado premium - Mercedes Benz',
    timestamp: new Date(Date.now() - 1 * 60 * 60 * 1000) // 1 hour ago
  }
])

const topServices = ref([
  {
    id: 1,
    name: 'Lavado Completo',
    icon: 'sparkles',
    color: 'bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400',
    count: 45,
    revenue: 13500
  },
  {
    id: 2,
    name: 'Lavado Exterior',
    icon: 'sun',
    color: 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/20 dark:text-yellow-400',
    count: 32,
    revenue: 6400
  },
  {
    id: 3,
    name: 'Lavado Premium',
    icon: 'star',
    color: 'bg-purple-100 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400',
    count: 18,
    revenue: 9000
  },
  {
    id: 4,
    name: 'Encerado',
    icon: 'shield-check',
    color: 'bg-green-100 text-green-600 dark:bg-green-900/20 dark:text-green-400',
    count: 12,
    revenue: 4800
  }
])

const quickActions = ref([
  {
    id: 1,
    label: 'Nueva Venta',
    icon: 'plus',
    variant: 'primary',
    action: 'new-sale'
  },
  {
    id: 2,
    label: 'Registrar Cliente',
    icon: 'user-plus',
    variant: 'outline',
    action: 'new-customer'
  },
  {
    id: 3,
    label: 'Ver Inventario',
    icon: 'cube',
    variant: 'outline',
    action: 'inventory'
  },
  {
    id: 4,
    label: 'Configuración',
    icon: 'cog-6-tooth',
    variant: 'ghost',
    action: 'settings'
  }
])

// Methods
const refreshData = async () => {
  loading.value = true
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    toast.success('Datos actualizados correctamente')
  } catch (error) {
    toast.error('Error al actualizar los datos')
  } finally {
    loading.value = false
  }
}

const openReportModal = () => {
  showReportModal.value = true
  // Set default dates
  const today = new Date()
  const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate())
  
  reportForm.startDate = lastMonth.toISOString().split('T')[0]
  reportForm.endDate = today.toISOString().split('T')[0]
}

const generateReport = async () => {
  if (!reportForm.type || !reportForm.startDate || !reportForm.endDate) {
    toast.error('Por favor completa todos los campos')
    return
  }

  generatingReport.value = true
  try {
    // Simulate report generation
    await new Promise(resolve => setTimeout(resolve, 2000))
    toast.success('Reporte generado exitosamente')
    showReportModal.value = false
    
    // Reset form
    Object.assign(reportForm, {
      type: '',
      startDate: '',
      endDate: ''
    })
  } catch (error) {
    toast.error('Error al generar el reporte')
  } finally {
    generatingReport.value = false
  }
}

const handleQuickAction = (action) => {
  switch (action) {
    case 'new-sale':
      router.push('/ventas/nueva')
      break
    case 'new-customer':
      router.push('/clientes/nuevo')
      break
    case 'inventory':
      router.push('/inventario')
      break
    case 'settings':
      router.push('/configuracion')
      break
    default:
      toast.info(`Funcionalidad "${action}" en desarrollo`)
  }
}

const formatTimeAgo = (date) => {
  const now = new Date()
  const diff = now - date
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)

  if (minutes < 1) return 'Hace un momento'
  if (minutes < 60) return `Hace ${minutes} min`
  if (hours < 24) return `Hace ${hours}h`
  return `Hace ${days}d`
}

// Lifecycle
onMounted(() => {
  // Load initial data
  refreshData()
})
</script>
