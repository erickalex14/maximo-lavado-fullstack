<template>
  <div id="app">
    <router-view />
  </div>
</template>

<script>
export default {
  name: 'App',
  mounted() {
    console.log('Vue App mounted successfully')
  }
}
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
