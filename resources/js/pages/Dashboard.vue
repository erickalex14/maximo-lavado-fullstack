<template>
  <AppLayout>
    <template #header>
      <PageHeader
        title="Dashboard Principal"
        description="Resumen general del sistema de lavado de autos"
        :icon="HomeIcon"
        :stats="dashboardStats"
        :breadcrumbs="breadcrumbs"
      >
        <template #actions>
          <MaterialButton
            text="Nuevo Lavado"
            :prefix-icon="PlusIcon"
            @click="$router.push('/lavados/nuevo')"
          />
          <MaterialButton
            text="Exportar Reporte"
            variant="outlined"
            :prefix-icon="DocumentArrowDownIcon"
            @click="exportReport"
          />
          <MaterialButton
            v-if="loading"
            text="Actualizando..."
            variant="outlined"
            disabled
            :prefix-icon="ClockIcon"
            @click="loadDashboardData"
          />
          <MaterialButton
            v-else
            text="Actualizar"
            variant="outlined"
            :prefix-icon="ClockIcon"
            @click="loadDashboardData"
          />
        </template>
      </PageHeader>
    </template>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Cargando datos del dashboard...</p>
      </div>
    </div>

    <div v-else>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <MaterialCard
        v-for="action in quickActions"
        :key="action.name"
        class="hover:shadow-material-2 transition-all duration-200 cursor-pointer transform hover:-translate-y-1"
        @click="$router.push(action.href)"
      >
        <div class="p-6 text-center">
          <div class="mx-auto h-12 w-12 rounded-lg flex items-center justify-center mb-4" :class="action.iconBg">
            <component :is="action.icon" class="h-6 w-6" :class="action.iconColor" />
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ action.name }}</h3>
          <p class="text-sm text-gray-600">{{ action.description }}</p>
        </div>
      </MaterialCard>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- Revenue Chart -->
      <MaterialCard elevated>
        <div class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Ingresos Mensuales</h3>
            <div class="flex items-center space-x-2">
              <select class="text-sm border-gray-300 rounded-md">
                <option>Últimos 6 meses</option>
                <option>Año actual</option>
                <option>Año anterior</option>
              </select>
            </div>
          </div>
          <div class="h-64 bg-gradient-to-br from-primary-50 to-blue-50 rounded-lg flex items-center justify-center">
            <div class="text-center">
              <ChartBarIcon class="h-16 w-16 text-primary-300 mx-auto mb-4" />
              <p class="text-gray-500">Gráfico de ingresos</p>
              <p class="text-sm text-gray-400">Próximamente con Chart.js</p>
            </div>
          </div>
        </div>
      </MaterialCard>

      <!-- Services Chart -->
      <MaterialCard elevated>
        <div class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Servicios Más Populares</h3>
            <button class="text-sm text-primary-600 hover:text-primary-700">Ver detalles</button>
          </div>
          <div class="space-y-4">
            <div
              v-for="service in popularServices"
              :key="service.name"
              class="flex items-center justify-between"
            >
              <div class="flex items-center space-x-3">
                <div class="h-3 w-3 rounded-full" :style="{ backgroundColor: service.color }"></div>
                <span class="text-sm font-medium text-gray-700">{{ service.name }}</span>
              </div>
              <div class="flex items-center space-x-3">
                <div class="w-24 bg-gray-200 rounded-full h-2">
                  <div
                    class="h-2 rounded-full"
                    :style="{ width: service.percentage + '%', backgroundColor: service.color }"
                  ></div>
                </div>
                <span class="text-sm text-gray-600 w-8 text-right">{{ service.percentage }}%</span>
              </div>
            </div>
          </div>
        </div>
      </MaterialCard>
    </div>

    <!-- Recent Activity and Notifications -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Recent Transactions -->
      <div class="lg:col-span-2">
        <MaterialCard elevated>
          <div class="p-6">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-lg font-semibold text-gray-900">Actividad Reciente</h3>
              <router-link
                to="/transacciones"
                class="text-sm text-primary-600 hover:text-primary-700 font-medium"
              >
                Ver todas
              </router-link>
            </div>
            <div class="space-y-4">
              <div
                v-for="transaction in recentTransactions"
                :key="transaction.id"
                class="flex items-center space-x-4 p-3 rounded-lg hover:bg-gray-50 transition-colors"
              >
                <div class="h-10 w-10 rounded-full flex items-center justify-center" :class="transaction.iconBg">
                  <component :is="transaction.icon" class="h-5 w-5" :class="transaction.iconColor" />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate">
                    {{ transaction.description }}
                  </p>
                  <p class="text-sm text-gray-500">{{ transaction.time }}</p>
                </div>
                <div class="text-right">
                  <p class="text-sm font-medium" :class="transaction.amount > 0 ? 'text-green-600' : 'text-red-600'">
                    {{ transaction.amount > 0 ? '+' : '' }}${{ Math.abs(transaction.amount).toLocaleString() }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </MaterialCard>
      </div>

      <!-- Notifications and Alerts -->
      <MaterialCard elevated>
        <div class="p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-6">Notificaciones</h3>
          <div class="space-y-4">
            <div
              v-for="notification in notifications"
              :key="notification.id"
              class="flex items-start space-x-3 p-3 rounded-lg"
              :class="notification.bgColor"
            >
              <component :is="notification.icon" class="h-5 w-5 mt-0.5" :class="notification.iconColor" />
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium" :class="notification.textColor">
                  {{ notification.title }}
                </p>
                <p class="text-xs mt-1" :class="notification.subTextColor">
                  {{ notification.message }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </MaterialCard>    </div>

    <!-- Debug Info (Development Only) -->
    <div v-if="showDebugInfo && authInfo" class="mt-8">
      <MaterialCard>
        <div class="p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Información de Sesión (Debug)</h3>
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div><strong>Autenticado:</strong> {{ authInfo.authenticated ? 'Sí' : 'No' }}</div>
            <div><strong>ID de Usuario:</strong> {{ authInfo.user_id || 'N/A' }}</div>
            <div><strong>ID de Sesión:</strong> {{ authInfo.session_id || 'N/A' }}</div>
            <div><strong>Logged In:</strong> {{ authInfo.session_logged_in ? 'Sí' : 'No' }}</div>
          </div>
        </div>
      </MaterialCard>
    </div>
    </div> <!-- Cierre del div v-else -->
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import AppLayout from '../components/AppLayout.vue'
import PageHeader from '../components/PageHeader.vue'
import MaterialCard from '../components/MaterialCard.vue'
import MaterialButton from '../components/MaterialButton.vue'
import { useApi } from '@/composables/useApi'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import {
  HomeIcon,
  PlusIcon,
  DocumentArrowDownIcon,
  ClockIcon,
  CurrencyDollarIcon,
  TruckIcon,
  ShoppingBagIcon,
  UserGroupIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  ChartBarIcon,
  ExclamationTriangleIcon,
  XCircleIcon,
  CheckCircleIcon,
  InformationCircleIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const api = useApi()
const authStore = useAuthStore()
const notificationStore = useNotificationStore()

const breadcrumbs = [
  { name: 'Dashboard', href: '/dashboard' }
]

const loading = ref(false)
const dashboardData = ref({
  stats: {
    ingresos_hoy: 0,
    lavados_hoy: 0,
    productos_vendidos_hoy: 0,
    clientes_nuevos: 0
  },
  lavados_recientes: [],
  ingresos_semanales: [],
  alertas: []
})

const dashboardStats = computed(() => [
  {
    nombre: 'Ingresos Hoy',
    valor: formatCurrency(dashboardData.value.stats.ingresos_hoy),
    icono: CurrencyDollarIcon,
    color: 'green'
  },
  {
    nombre: 'Lavados Hoy',
    valor: dashboardData.value.stats.lavados_hoy,
    icono: TruckIcon,
    color: 'blue'
  },
  {
    nombre: 'Productos Vendidos',
    valor: dashboardData.value.stats.productos_vendidos_hoy,
    icono: ShoppingBagIcon,
    color: 'purple'
  },
  {
    nombre: 'Clientes Nuevos',
    valor: dashboardData.value.stats.clientes_nuevos,
    icono: UserGroupIcon,
    color: 'yellow'
  }
])

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN'
  }).format(amount)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('es-MX', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const loadDashboardData = async () => {
  try {
    loading.value = true
    
    const response = await api.get('/api/dashboard', {
      loadingMessage: 'Cargando dashboard...',
      showError: false
    })
    
    if (response.success && response.data) {
      dashboardData.value = response.data
    } else {
      // Si no hay datos, usar estructura vacía
      dashboardData.value = {
        stats: {
          ingresos_hoy: 0,
          lavados_hoy: 0,
          productos_vendidos_hoy: 0,
          clientes_nuevos: 0
        },
        lavados_recientes: [],
        ingresos_semanales: [],
        servicios_populares: [],
        alertas: [
          {
            tipo: 'info',
            titulo: 'Sistema Nuevo',
            mensaje: 'No hay datos disponibles. Comienza agregando información.',
            fecha: new Date().toISOString()
          }
        ]
      }
    }
  } catch (error) {
    console.error('Error cargando dashboard:', error)
    notificationStore.error('Error al cargar los datos del dashboard')
    
    // Usar estructura vacía en caso de error
    dashboardData.value = {
      stats: {
        ingresos_hoy: 0,
        lavados_hoy: 0,
        productos_vendidos_hoy: 0,
        clientes_nuevos: 0
      },
      lavados_recientes: [],
      ingresos_semanales: [],
      servicios_populares: [],
      alertas: [
        {
          tipo: 'error',
          titulo: 'Error de Conexión',
          mensaje: 'No se pudieron cargar los datos. Verifica tu conexión.',
          fecha: new Date().toISOString()
        }
      ]
    }
  } finally {
    loading.value = false
  }
}

const verLavado = (id) => {
  router.push(`/lavados/${id}`)
}

const editarLavado = (id) => {
  router.push(`/lavados/${id}/editar`)
}

const eliminarLavado = async (id) => {
  if (confirm('¿Estás seguro de que deseas eliminar este lavado?')) {
    try {
      await api.delete(`/api/lavados/${id}`, {
        successMessage: 'Lavado eliminado correctamente'
      })
      await loadDashboardData()
    } catch (error) {
      console.error('Error al eliminar lavado:', error)
    }
  }
}

const exportReport = async () => {
  try {
    notificationStore.info('Generando reporte...', 'Exportación')
    // Simulación de exportación
    setTimeout(() => {
      notificationStore.success('Reporte exportado correctamente', 'Exportación')
    }, 2000)
  } catch (error) {
    console.error('Error al exportar reporte:', error)
  }
}

const getEstadoClass = (estado) => {
  const classes = {
    'Completado': 'bg-green-100 text-green-800',
    'En proceso': 'bg-yellow-100 text-yellow-800',
    'Pendiente': 'bg-gray-100 text-gray-800',
    'Cancelado': 'bg-red-100 text-red-800'
  }
  return classes[estado] || 'bg-gray-100 text-gray-800'
}

// Variables computadas para mostrar datos en el dashboard
const quickActions = computed(() => [
  {
    name: 'Nuevo Lavado',
    description: 'Registrar un nuevo servicio',
    href: '/lavados/nuevo',
    icon: TruckIcon,
    iconBg: 'bg-blue-100',
    iconColor: 'text-blue-600'
  },
  {
    name: 'Gestionar Clientes',
    description: 'Ver y editar clientes',
    href: '/clientes',
    icon: UserGroupIcon,
    iconBg: 'bg-green-100',
    iconColor: 'text-green-600'
  },
  {
    name: 'Productos',
    description: 'Inventario y ventas',
    href: '/productos',
    icon: ShoppingBagIcon,
    iconBg: 'bg-purple-100',
    iconColor: 'text-purple-600'
  },
  {
    name: 'Reportes',
    description: 'Análisis y estadísticas',
    href: '/reportes',
    icon: DocumentArrowDownIcon,
    iconBg: 'bg-yellow-100',
    iconColor: 'text-yellow-600'
  }
])

const popularServices = computed(() => {
  const servicios = dashboardData.value.servicios_populares || []
  return servicios.length > 0 ? servicios : [
    { name: 'Lavado Completo', percentage: 0, color: '#4F46E5' },
    { name: 'Lavado Básico', percentage: 0, color: '#10B981' },
    { name: 'Encerado', percentage: 0, color: '#F59E0B' },
    { name: 'Aspirado', percentage: 0, color: '#EF4444' }
  ]
})

const recentTransactions = computed(() => {
  const lavados = dashboardData.value.lavados_recientes || []
  return lavados.map(lavado => ({
    id: lavado.id,
    description: `Lavado ${lavado.tipo_lavado} - ${lavado.cliente?.nombre || 'Cliente N/A'}`,
    time: formatDate(lavado.created_at),
    amount: lavado.total || 0,
    icon: TruckIcon,
    iconBg: 'bg-blue-100',
    iconColor: 'text-blue-600'
  }))
})

const notifications = computed(() => {
  const alertas = dashboardData.value.alertas || []
  
  if (alertas.length === 0) {
    return [{
      id: 1,
      title: 'Sistema Listo',
      message: 'No hay notificaciones pendientes',
      icon: HomeIcon,
      iconColor: 'text-blue-500',
      textColor: 'text-blue-800',
      subTextColor: 'text-blue-600',
      bgColor: 'bg-blue-50'
    }]
  }
  
  return alertas.map((alerta, index) => ({
    id: index + 1,
    title: alerta.titulo || getAlertTitle(alerta.tipo),
    message: alerta.mensaje,
    icon: getAlertIcon(alerta.tipo),
    iconColor: getAlertIconColor(alerta.tipo),
    textColor: getAlertTextColor(alerta.tipo),
    subTextColor: getAlertSubTextColor(alerta.tipo),
    bgColor: getAlertBgColor(alerta.tipo)
  }))
})

const getAlertTitle = (tipo) => {
  const titles = {
    'warning': 'Advertencia',
    'error': 'Error',
    'info': 'Información',
    'success': 'Éxito'
  }
  return titles[tipo] || 'Notificación'
}

const getAlertIcon = (tipo) => {
  const icons = {
    'warning': ClockIcon,
    'error': ClockIcon,
    'info': HomeIcon,
    'success': HomeIcon
  }
  return icons[tipo] || HomeIcon
}

const getAlertIconColor = (tipo) => {
  const colors = {
    'warning': 'text-yellow-500',
    'error': 'text-red-500',
    'info': 'text-blue-500',
    'success': 'text-green-500'
  }
  return colors[tipo] || 'text-blue-500'
}

const getAlertTextColor = (tipo) => {
  const colors = {
    'warning': 'text-yellow-800',
    'error': 'text-red-800',
    'info': 'text-blue-800',
    'success': 'text-green-800'
  }
  return colors[tipo] || 'text-blue-800'
}

const getAlertSubTextColor = (tipo) => {
  const colors = {
    'warning': 'text-yellow-600',
    'error': 'text-red-600',
    'info': 'text-blue-600',
    'success': 'text-green-600'
  }
  return colors[tipo] || 'text-blue-600'
}

const getAlertBgColor = (tipo) => {
  const colors = {
    'warning': 'bg-yellow-50',
    'error': 'bg-red-50',
    'info': 'bg-blue-50',
    'success': 'bg-green-50'
  }
  return colors[tipo] || 'bg-blue-50'
}

// Debug info (solo en desarrollo)
const showDebugInfo = ref(false)
const authInfo = computed(() => authStore.user)

onMounted(() => {
  loadDashboardData()
})
</script>
