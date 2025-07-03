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
  lavado: () => `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
  </svg>`,
  dinero: () => `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
  </svg>`,
  clientes: () => `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
  </svg>`,
  empleados: () => `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
  </svg>`,
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
