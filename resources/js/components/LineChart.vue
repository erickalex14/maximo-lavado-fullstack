<template>
  <div class="chart-container">
    <Line
      :data="chartData"
      :options="chartOptions"
      :height="height"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  LinearScale,
  CategoryScale,
  PointElement
} from 'chart.js'
import { Line } from 'vue-chartjs'

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  LinearScale,
  CategoryScale,
  PointElement
)

const props = defineProps({
  data: {
    type: Object,
    required: true
  },
  title: {
    type: String,
    default: ''
  },
  height: {
    type: Number,
    default: 400
  }
})

const chartData = computed(() => {
  return {
    labels: props.data.labels || [],
    datasets: [{
      label: props.data.label || 'Datos',
      backgroundColor: props.data.backgroundColor || 'rgba(79, 70, 229, 0.2)',
      borderColor: props.data.borderColor || 'rgba(79, 70, 229, 1)',
      borderWidth: 2,
      fill: true,
      tension: 0.4,
      data: props.data.values || []
    }]
  }
})

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    title: {
      display: !!props.title,
      text: props.title
    },
    legend: {
      display: true,
      position: 'top'
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        callback: function(value) {
          return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN'
          }).format(value)
        }
      }
    }
  }
}))
</script>

<style scoped>
.chart-container {
  position: relative;
  width: 100%;
}
</style>
