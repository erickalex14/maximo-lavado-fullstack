<template>
  <div class="card">
    <div class="card-body">
      <div class="flex items-center">
        <div class="flex-1">
          <p class="text-sm font-medium text-surface-600">{{ title }}</p>
          <p class="text-2xl font-bold" :class="textColorClass">{{ value }}</p>
        </div>
        <div
          class="w-12 h-12 rounded-lg flex items-center justify-center"
          :class="backgroundColorClass"
        >
          <component :is="iconComponent" class="w-6 h-6" :class="iconColorClass" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import LavadoIcon from '@/components/icons/LavadoIcon.vue';
import DineroIcon from '@/components/icons/DineroIcon.vue';
import ClientesIcon from '@/components/icons/ClientesIcon.vue';
import EmpleadosIcon from '@/components/icons/EmpleadosIcon.vue';

// Props
interface Props {
  title: string;
  value: string | number;
  icon: string;
  color?: 'primary' | 'green' | 'blue' | 'purple' | 'red' | 'yellow';
}

const props = withDefaults(defineProps<Props>(), {
  color: 'primary',
});

// Icon components
const iconComponents = {
  lavado: LavadoIcon,
  dinero: DineroIcon,
  clientes: ClientesIcon,
  empleados: EmpleadosIcon,
};

// Computed
const iconComponent = computed(() => {
  return iconComponents[props.icon as keyof typeof iconComponents] || iconComponents.lavado;
});

const backgroundColorClass = computed(() => {
  const classes = {
    primary: 'bg-primary-100',
    green: 'bg-green-100',
    blue: 'bg-blue-100',
    purple: 'bg-purple-100',
    red: 'bg-red-100',
    yellow: 'bg-yellow-100',
  };
  return classes[props.color];
});

const iconColorClass = computed(() => {
  const classes = {
    primary: 'text-primary-600',
    green: 'text-green-600',
    blue: 'text-blue-600',
    purple: 'text-purple-600',
    red: 'text-red-600',
    yellow: 'text-yellow-600',
  };
  return classes[props.color];
});

const textColorClass = computed(() => {
  const classes = {
    primary: 'text-primary-700',
    green: 'text-green-700',
    blue: 'text-blue-700',
    purple: 'text-purple-700',
    red: 'text-red-700',
    yellow: 'text-yellow-700',
  };
  return classes[props.color];
});
</script>
