<template>
  <svg
    :width="size"
    :height="size"
    :class="className"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
  >
    <circle cx="9" cy="9" r="2" />
    <path d="m21 15-3.086-3.086a2 2 0 0 0-1.414-.586H13l-2.9-2.9A2 2 0 0 0 8.686 8H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h4.001" />
    <path d="M15 18h6" />
    <path d="M18 15v6" />
  </svg>
</template>

<script setup>
defineProps({
  size: {
    type: [Number, String],
    default: 24
  },
  className: {
    type: String,
    default: ''
  }
})
</script>
