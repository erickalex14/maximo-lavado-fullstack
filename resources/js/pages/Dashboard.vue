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

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'
import AppLayout from '../components/AppLayout.vue'
import PageHeader from '../components/PageHeader.vue'
import MaterialCard from '../components/MaterialCard.vue'
import MaterialButton from '../components/MaterialButton.vue'
import {
  HomeIcon,
  PlusIcon,
  DocumentArrowDownIcon,
  ChartBarIcon,
  UserGroupIcon,
  TruckIcon,
  DocumentTextIcon,
  CurrencyDollarIcon,
  ShoppingCartIcon,
  CogIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  ClockIcon
} from '@heroicons/vue/24/outline'

export default {
  name: 'Dashboard',
  components: {
    AppLayout,
    PageHeader,
    MaterialCard,
    MaterialButton,
    HomeIcon,
    PlusIcon,
    DocumentArrowDownIcon,
    ChartBarIcon,
    UserGroupIcon,
    TruckIcon,
    DocumentTextIcon,
    CurrencyDollarIcon,
    ShoppingCartIcon,
    CogIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    InformationCircleIcon,
    ClockIcon
  },  setup() {
    const router = useRouter()
    const authStore = useAuthStore()
    const authInfo = ref(null)
    const showDebugInfo = ref(false) // Cambiar a true solo para debug
    const loading = ref(true)
    const dashboardData = ref(null)

    // Breadcrumbs
    const breadcrumbs = [
      { name: 'Inicio', href: '/dashboard' }
    ]

    // Dashboard Statistics (se llenarán con datos reales)
    const dashboardStats = ref([])

    // Quick Actions
    const quickActions = [
      {
        name: 'Nuevo Lavado',
        description: 'Registrar un nuevo servicio de lavado',
        href: '/lavados/nuevo',
        icon: PlusIcon,
        iconBg: 'bg-primary-100',
        iconColor: 'text-primary-600'
      },
      {
        name: 'Gestionar Clientes',
        description: 'Ver y administrar clientes',
        href: '/clientes',
        icon: UserGroupIcon,
        iconBg: 'bg-green-100',
        iconColor: 'text-green-600'
      },
      {
        name: 'Inventario',
        description: 'Gestionar productos y suministros',
        href: '/productos',
        icon: ShoppingCartIcon,
        iconBg: 'bg-blue-100',
        iconColor: 'text-blue-600'
      },
      {
        name: 'Configuración',
        description: 'Ajustes del sistema',
        href: '/configuracion',
        icon: CogIcon,
        iconBg: 'bg-gray-100',
        iconColor: 'text-gray-600'
      }
    ]    // Popular Services (se llenará con datos reales)
    const popularServices = ref([
      { name: 'Lavado Básico', percentage: 45, color: '#3B82F6' },
      { name: 'Lavado Premium', percentage: 30, color: '#10B981' },
      { name: 'Encerado', percentage: 15, color: '#F59E0B' },
      { name: 'Detailing Completo', percentage: 10, color: '#EF4444' }
    ])

    // Recent Transactions (se llenará con datos reales)
    const recentTransactions = ref([])

    // Notifications (se llenará con datos reales)
    const notifications = ref([])

    // Cargar datos del dashboard
    const loadDashboardData = async () => {
      try {
        loading.value = true
        const response = await axios.get('/api/dashboard/data')
        dashboardData.value = response.data
        
        // Actualizar estadísticas del dashboard
        const { metricas } = response.data
        dashboardStats.value = [
          {
            name: 'Lavados Hoy',
            value: metricas.lavadosHoy.toString(),
            change: `${metricas.lavadosHoy > 0 ? '+' : ''}${metricas.lavadosHoy}`,
            changeType: metricas.lavadosHoy > 0 ? 'increase' : 'neutral',
            icon: DocumentTextIcon,
            iconBg: 'bg-blue-100',
            iconColor: 'text-blue-600'
          },
          {
            name: 'Ingresos del Día',
            value: `$${metricas.ingresosHoy.toLocaleString()}`,
            change: metricas.variacionIngresos,
            changeType: metricas.variacionIngresos.includes('+') ? 'increase' : 'decrease',
            icon: CurrencyDollarIcon,
            iconBg: 'bg-green-100',
            iconColor: 'text-green-600'
          },
          {
            name: 'Total Clientes',
            value: metricas.clientesTotal.toString(),
            change: `${metricas.clientesNuevos} nuevos hoy`,
            changeType: metricas.clientesNuevos > 0 ? 'increase' : 'neutral',
            icon: UserGroupIcon,
            iconBg: 'bg-purple-100',
            iconColor: 'text-purple-600'
          },
          {
            name: 'Empleados',
            value: metricas.empleados.toString(),
            change: 'Personal activo',
            changeType: 'neutral',
            icon: TruckIcon,
            iconBg: 'bg-yellow-100',
            iconColor: 'text-yellow-600'
          }
        ]

        // Actualizar transacciones recientes
        recentTransactions.value = response.data.actividad_reciente.map((actividad, index) => ({
          id: actividad.id || index,
          description: actividad.descripcion.replace(/<[^>]*>/g, ''), // Remover HTML
          time: new Date(actividad.created_at).toLocaleString('es-ES', {
            hour: '2-digit',
            minute: '2-digit',
            day: '2-digit',
            month: '2-digit'
          }),
          amount: actividad.tipo === 'lavado' ? 50000 : 0, // Valor por defecto
          icon: actividad.tipo === 'lavado' ? DocumentTextIcon : UserGroupIcon,
          iconBg: actividad.tipo === 'lavado' ? 'bg-green-100' : 'bg-blue-100',
          iconColor: actividad.tipo === 'lavado' ? 'text-green-600' : 'text-blue-600'
        }))

        // Generar notificaciones basadas en datos
        const notificationsData = []
        
        if (metricas.lavadosEnProceso > 5) {
          notificationsData.push({
            id: 1,
            title: 'Cola de Lavados',
            message: `${metricas.lavadosEnProceso} vehículos en espera`,
            icon: ClockIcon,
            bgColor: 'bg-yellow-50',
            iconColor: 'text-yellow-500',
            textColor: 'text-yellow-800',
            subTextColor: 'text-yellow-600'
          })
        }

        if (metricas.clientesNuevos > 0) {
          notificationsData.push({
            id: 2,
            title: 'Nuevos Clientes',
            message: `${metricas.clientesNuevos} clientes se registraron hoy`,
            icon: InformationCircleIcon,
            bgColor: 'bg-blue-50',
            iconColor: 'text-blue-500',
            textColor: 'text-blue-800',
            subTextColor: 'text-blue-600'
          })
        }

        notificationsData.push({
          id: 3,
          title: 'Sistema Activo',
          message: 'Todos los servicios funcionando correctamente',
          icon: CheckCircleIcon,
          bgColor: 'bg-green-50',
          iconColor: 'text-green-500',
          textColor: 'text-green-800',
          subTextColor: 'text-green-600'
        })

        notifications.value = notificationsData

      } catch (error) {
        console.error('Error cargando datos del dashboard:', error)
        // Mantener valores por defecto en caso de error
      } finally {        loading.value = false
      }
    }

    const fetchAuthInfo = async () => {
      try {
        const response = await axios.get('/test-auth')
        authInfo.value = response.data
      } catch (error) {
        console.error('Error fetching auth info:', error)
      }
    }

    const exportReport = () => {
      // TODO: Implement export functionality      alert('Funcionalidad de exportación próximamente')
    }

    onMounted(() => {
      loadDashboardData()
      if (showDebugInfo.value) {
        fetchAuthInfo()
      }
    })

    return {
      breadcrumbs,
      dashboardStats,
      quickActions,
      popularServices,
      recentTransactions,
      notifications,
      authInfo,
      showDebugInfo,
      loading,
      dashboardData,
      loadDashboardData,
      exportReport
    }
  }
}
</script>
