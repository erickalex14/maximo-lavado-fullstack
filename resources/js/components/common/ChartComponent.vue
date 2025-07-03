<template>
  <div class="chart-container" style="position: relative; height: 300px;">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
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
  Filler,
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
  Filler
);

// Props
interface Props {
  type: 'line' | 'bar' | 'doughnut' | 'pie';
  data: any;
  options?: any;
}

const props = defineProps<Props>();

// Refs
const chartCanvas = ref<HTMLCanvasElement>();
let chartInstance: ChartJS | null = null;

// Methods
const createChart = async () => {
  if (!chartCanvas.value || !props.data) return;

  await nextTick();

  // Destruir instancia anterior si existe
  if (chartInstance) {
    chartInstance.destroy();
  }

  const ctx = chartCanvas.value.getContext('2d');
  if (!ctx) return;

  chartInstance = new ChartJS(ctx, {
    type: props.type,
    data: props.data,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      ...props.options,
    },
  });
};

const updateChart = () => {
  if (chartInstance && props.data) {
    chartInstance.data = props.data;
    chartInstance.update();
  }
};

// Watchers
watch(() => props.data, updateChart, { deep: true });
watch(() => props.type, createChart);

// Lifecycle
onMounted(() => {
  createChart();
});

onUnmounted(() => {
  if (chartInstance) {
    chartInstance.destroy();
  }
});
</script>
