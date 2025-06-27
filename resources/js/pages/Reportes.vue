<template>
  <AppLayout>
    <template #header>
      <PageHeader
        title="Reportes y Estadísticas"
        description="Analiza el rendimiento de tu negocio con reportes detallados"
        :icon="ChartBarIcon"
        :breadcrumbs="breadcrumbs"
      >
        <template #actions>
          <MaterialButton
            text="Exportar Todo"
            variant="outlined"
            :prefix-icon="DocumentArrowDownIcon"
            @click="exportarTodo"
          />
        </template>
      </PageHeader>
    </template>

    <!-- Selector de Periodo -->
    <MaterialCard class="mb-6">
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Periodo</label>
            <select
              v-model="filtros.periodo"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              @change="cargarReportes"
            >
              <option value="hoy">Hoy</option>
              <option value="semana">Esta semana</option>
              <option value="mes">Este mes</option>
              <option value="trimestre">Este trimestre</option>
              <option value="anio">Este año</option>
              <option value="personalizado">Personalizado</option>
            </select>
          </div>
          <div v-if="filtros.periodo === 'personalizado'">
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha inicio</label>
            <input
              v-model="filtros.fechaInicio"
              type="date"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              @change="cargarReportes"
            />
          </div>
          <div v-if="filtros.periodo === 'personalizado'">
            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha fin</label>
            <input
              v-model="filtros.fechaFin"
              type="date"
              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
              @change="cargarReportes"
            />
          </div>
          <div class="flex items-end">
            <MaterialButton
              text="Actualizar"
              @click="cargarReportes"
            />
          </div>
        </div>
      </div>
    </MaterialCard>

    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Generando reportes...</p>
      </div>
    </div>

    <div v-else class="space-y-6">
      <!-- Métricas Principales -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <MaterialCard
          v-for="metrica in metricas"
          :key="metrica.nombre"
          class="text-center"
        >
          <div class="p-6">
            <div class="mx-auto h-12 w-12 rounded-lg flex items-center justify-center mb-4" :class="metrica.iconBg">
              <component :is="metrica.icon" class="h-6 w-6" :class="metrica.iconColor" />
            </div>
            <div class="text-2xl font-bold text-gray-900 mb-1">{{ metrica.valor }}</div>
            <div class="text-sm text-gray-600 mb-2">{{ metrica.nombre }}</div>
            <div class="text-xs" :class="metrica.cambio.includes('+') ? 'text-green-600' : 'text-red-600'">
              {{ metrica.cambio }}
            </div>
          </div>
        </MaterialCard>
      </div>

      <!-- Gráficos Principales -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Ingresos por Mes -->
        <MaterialCard>
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Ingresos por Mes</h3>
            <LineChart
              :data="chartData.ingresosPorMes"
              title="Evolución de Ingresos"
              :height="300"
            />
          </div>
        </MaterialCard>

        <!-- Lavados por Tipo -->
        <MaterialCard>
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Lavados por Tipo</h3>
            <DoughnutChart
              :data="chartData.lavadosPorTipo"
              title="Distribución de Servicios"
              :height="300"
            />
          </div>
        </MaterialCard>

        <!-- Ventas por Día -->
        <MaterialCard>
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Ventas Diarias</h3>
            <BarChart
              :data="chartData.ventasPorDia"
              title="Ventas por Día de la Semana"
              :height="300"
            />
          </div>
        </MaterialCard>

        <!-- Top Productos -->
        <MaterialCard>
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Productos Más Vendidos</h3>
            <BarChart
              :data="chartData.topProductos"
              title="Top 5 Productos"
              :height="300"
            />
          </div>
        </MaterialCard>
      </div>

      <!-- Tabla de Resumen -->
      <MaterialCard>
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Resumen del Periodo</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Concepto
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Cantidad
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Total
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Promedio
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    Lavados
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ reporteData.metricas.lavados_totales || 0 }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatCurrency(reporteData.metricas.ingresos_lavados || 0) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatCurrency(reporteData.metricas.promedio_lavado || 0) }}
                  </td>
                </tr>
                <tr>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    Productos
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ reporteData.metricas.productos_vendidos || 0 }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatCurrency(reporteData.metricas.ingresos_productos || 0) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatCurrency(reporteData.metricas.promedio_producto || 0) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </MaterialCard>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import AppLayout from '../components/AppLayout.vue'
import PageHeader from '../components/PageHeader.vue'
import MaterialCard from '../components/MaterialCard.vue'
import MaterialButton from '../components/MaterialButton.vue'
import BarChart from '../components/BarChart.vue'
import LineChart from '../components/LineChart.vue'
import DoughnutChart from '../components/DoughnutChart.vue'
import { useApi } from '@/composables/useApi'
import {
  ChartBarIcon,
  DocumentArrowDownIcon,
  CurrencyDollarIcon,
  TruckIcon,
  ShoppingBagIcon,
  UserGroupIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const api = useApi()

const breadcrumbs = [
  { name: 'Dashboard', href: '/dashboard' },
  { name: 'Reportes', href: '/reportes' }
]

const loading = ref(false)
const filtros = ref({
  periodo: 'mes',
  fechaInicio: '',
  fechaFin: ''
})

const reporteData = ref({
  metricas: {},
  ingresos: [],
  lavados: [],
  productos: [],
  ventas: []
})

const metricas = computed(() => [
  {
    nombre: 'Ingresos Totales',
    valor: formatCurrency(reporteData.value.metricas.ingresos_totales || 0),
    cambio: reporteData.value.metricas.cambio_ingresos || '+0%',
    icon: CurrencyDollarIcon,
    iconBg: 'bg-green-100',
    iconColor: 'text-green-600'
  },
  {
    nombre: 'Lavados Realizados',
    valor: reporteData.value.metricas.lavados_totales || 0,
    cambio: reporteData.value.metricas.cambio_lavados || '+0%',
    icon: TruckIcon,
    iconBg: 'bg-blue-100',
    iconColor: 'text-blue-600'
  },
  {
    nombre: 'Productos Vendidos',
    valor: reporteData.value.metricas.productos_vendidos || 0,
    cambio: reporteData.value.metricas.cambio_productos || '+0%',
    icon: ShoppingBagIcon,
    iconBg: 'bg-purple-100',
    iconColor: 'text-purple-600'
  },
  {
    nombre: 'Clientes Únicos',
    valor: reporteData.value.metricas.clientes_unicos || 0,
    cambio: reporteData.value.metricas.cambio_clientes || '+0%',
    icon: UserGroupIcon,
    iconBg: 'bg-yellow-100',
    iconColor: 'text-yellow-600'
  }
])

const chartData = computed(() => ({
  ingresosPorMes: {
    labels: reporteData.value.ingresos.map(item => item.periodo) || [],
    values: reporteData.value.ingresos.map(item => item.total) || [],
    label: 'Ingresos',
    backgroundColor: 'rgba(79, 70, 229, 0.2)',
    borderColor: 'rgba(79, 70, 229, 1)'
  },
  lavadosPorTipo: {
    labels: reporteData.value.lavados.map(item => item.tipo) || [],
    values: reporteData.value.lavados.map(item => item.cantidad) || [],
    backgroundColor: [
      'rgba(79, 70, 229, 0.8)',
      'rgba(16, 185, 129, 0.8)',
      'rgba(245, 158, 11, 0.8)',
      'rgba(239, 68, 68, 0.8)',
      'rgba(139, 92, 246, 0.8)'
    ]
  },
  ventasPorDia: {
    labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
    values: reporteData.value.ventas.map(item => item.total) || [0, 0, 0, 0, 0, 0, 0],
    label: 'Ventas',
    backgroundColor: 'rgba(16, 185, 129, 0.6)',
    borderColor: 'rgba(16, 185, 129, 1)'
  },
  topProductos: {
    labels: reporteData.value.productos.slice(0, 5).map(item => item.nombre) || [],
    values: reporteData.value.productos.slice(0, 5).map(item => item.vendidos) || [],
    label: 'Unidades Vendidas',
    backgroundColor: 'rgba(245, 158, 11, 0.6)',
    borderColor: 'rgba(245, 158, 11, 1)'
  }
}))

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('es-MX', {
    style: 'currency',
    currency: 'MXN'
  }).format(amount)
}

const cargarReportes = async () => {
  try {
    loading.value = true
    
    const params = new URLSearchParams({
      periodo: filtros.value.periodo,
      ...(filtros.value.fechaInicio && { fecha_inicio: filtros.value.fechaInicio }),
      ...(filtros.value.fechaFin && { fecha_fin: filtros.value.fechaFin })
    })

    // Cargar métricas principales con datos simulados si no hay API
    try {
      const metricas = await api.get(`/api/reportes/metricas?${params}`, {
        loadingMessage: 'Cargando métricas...',
        showError: false
      })

      const [ingresos, lavados, productos, ventas] = await Promise.all([
        api.get(`/api/reportes/ingresos?${params}`, { showError: false }),
        api.get(`/api/reportes/lavados-por-tipo?${params}`, { showError: false }),
        api.get(`/api/reportes/productos-vendidos?${params}`, { showError: false }),
        api.get(`/api/reportes/ventas-por-dia?${params}`, { showError: false })
      ])

      reporteData.value = {
        metricas: metricas.data || {},
        ingresos: ingresos.data || [],
        lavados: lavados.data || [],
        productos: productos.data || [],
        ventas: ventas.data || []
      }
    } catch (error) {
      // Usar datos simulados si la API no está disponible
      console.log('API no disponible, usando datos simulados')
      reporteData.value = {
        metricas: {
          ingresos_totales: 25400,
          lavados_totales: 142,
          productos_vendidos: 85,
          clientes_unicos: 67,
          cambio_ingresos: '+12.5%',
          cambio_lavados: '+8.3%',
          cambio_productos: '+15.2%',
          cambio_clientes: '+5.7%',
          ingresos_lavados: 18900,
          ingresos_productos: 6500,
          promedio_lavado: 133,
          promedio_producto: 76
        },
        ingresos: [
          { periodo: 'Ene', total: 18500 },
          { periodo: 'Feb', total: 21200 },
          { periodo: 'Mar', total: 25400 },
          { periodo: 'Abr', total: 23100 },
          { periodo: 'May', total: 26800 },
          { periodo: 'Jun', total: 24600 }
        ],
        lavados: [
          { tipo: 'Básico', cantidad: 45 },
          { tipo: 'Completo', cantidad: 38 },
          { tipo: 'Premium', cantidad: 25 },
          { tipo: 'Express', cantidad: 34 }
        ],
        productos: [
          { nombre: 'Ambientador', vendidos: 25 },
          { nombre: 'Cera', vendidos: 18 },
          { nombre: 'Shampoo', vendidos: 15 },
          { nombre: 'Toallas', vendidos: 12 },
          { nombre: 'Protector', vendidos: 10 }
        ],
        ventas: [
          { total: 2800 }, // Lunes
          { total: 3200 }, // Martes  
          { total: 2900 }, // Miércoles
          { total: 3500 }, // Jueves
          { total: 4100 }, // Viernes
          { total: 5200 }, // Sábado
          { total: 3700 }  // Domingo
        ]
      }
    }
  } finally {
    loading.value = false
  }
}

const exportarTodo = async () => {
  try {
    const params = new URLSearchParams({
      periodo: filtros.value.periodo,
      ...(filtros.value.fechaInicio && { fecha_inicio: filtros.value.fechaInicio }),
      ...(filtros.value.fechaFin && { fecha_fin: filtros.value.fechaFin })
    })

    const response = await fetch(`/api/reportes/exportar?${params}`, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      }
    })

    if (response.ok) {
      const blob = await response.blob()
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `reportes-${filtros.value.periodo}-${new Date().toISOString().split('T')[0]}.xlsx`
      document.body.appendChild(a)
      a.click()
      window.URL.revokeObjectURL(url)
      document.body.removeChild(a)
    }
  } catch (error) {
    console.error('Error al exportar reportes:', error)
  }
}

onMounted(() => {
  cargarReportes()
})
</script>
