import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from '@/router'
import axios from 'axios'

// Import main app component
import App from './App.vue'

// Configure axios
axios.defaults.withCredentials = true
axios.defaults.baseURL = window.location.origin
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// Configure CSRF token
const token = document.head.querySelector('meta[name="csrf-token"]')
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content
}

// Create Pinia store
const pinia = createPinia()

// Create Vue app
const app = createApp(App)

// Use plugins
app.use(pinia)
app.use(router)

// Mount app
app.mount('#app')

console.log('Vue app initialized successfully')
