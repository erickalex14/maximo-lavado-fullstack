<template>
  <div id="app">
    <router-view />
    <NotificationContainer />
    <LoadingOverlay />
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import NotificationContainer from '@/components/NotificationContainer.vue'
import LoadingOverlay from '@/components/LoadingOverlay.vue'

onMounted(() => {
  console.log('Vue App mounted successfully')
})
</script>

<style>
body {
  margin: 0;
  font-family: 'Inter', system-ui, sans-serif;
}

#app {
  min-height: 100vh;
}
</style>
