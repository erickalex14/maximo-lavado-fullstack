<template>
  <div class="w-full h-64">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement
} from 'chart.js';

// Registrar componentes de Chart.js
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  ArcElement
);

const props = defineProps({
  data: {
    type: Object,
    required: true
  },
  type: {
    type: String,
    default: 'line',
    validator: (value) => ['line', 'bar', 'doughnut', 'pie'].includes(value)
  },
  options: {
    type: Object,
    default: () => ({})
  }
});

const chartCanvas = ref(null);
let chartInstance = null;

const defaultOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top'
    },
    tooltip: {
      mode: 'index',
      intersect: false
    }
  },
  scales: {
    x: {
      display: true,
      grid: {
        display: false
      }
    },
    y: {
      display: true,
      beginAtZero: true,
      grid: {
        color: 'rgba(0, 0, 0, 0.1)'
      }
    }
  },
  interaction: {
    mode: 'nearest',
    axis: 'x',
    intersect: false
  }
};

const createChart = () => {
  if (!chartCanvas.value || !props.data) return;

  // Destruir gráfico existente
  if (chartInstance) {
    chartInstance.destroy();
  }

  const ctx = chartCanvas.value.getContext('2d');
  
  // Configurar opciones según el tipo de gráfico
  const options = { ...defaultOptions, ...props.options };
  
  // Para gráficos circulares, no necesitamos escalas
  if (props.type === 'doughnut' || props.type === 'pie') {
    delete options.scales;
  }

  chartInstance = new ChartJS(ctx, {
    type: props.type,
    data: props.data,
    options
  });
};

// Observar cambios en los datos
watch(() => props.data, () => {
  nextTick(() => {
    createChart();
  });
}, { deep: true });

watch(() => props.type, () => {
  nextTick(() => {
    createChart();
  });
});

onMounted(() => {
  nextTick(() => {
    createChart();
  });
});
</script>
