<template>
  <div class="chart-container">
    <Doughnut
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
  ArcElement
} from 'chart.js'
import { Doughnut } from 'vue-chartjs'

ChartJS.register(Title, Tooltip, Legend, ArcElement)

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
      data: props.data.values || [],
      backgroundColor: props.data.backgroundColor || [
        'rgba(79, 70, 229, 0.8)',
        'rgba(16, 185, 129, 0.8)',
        'rgba(245, 158, 11, 0.8)',
        'rgba(239, 68, 68, 0.8)',
        'rgba(139, 92, 246, 0.8)'
      ],
      borderColor: props.data.borderColor || [
        'rgba(79, 70, 229, 1)',
        'rgba(16, 185, 129, 1)',
        'rgba(245, 158, 11, 1)',
        'rgba(239, 68, 68, 1)',
        'rgba(139, 92, 246, 1)'
      ],
      borderWidth: 2
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
      position: 'bottom'
    },
    tooltip: {
      callbacks: {
        label: function(context) {
          const label = context.label || ''
          const value = new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN'
          }).format(context.parsed)
          return `${label}: ${value}`
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
