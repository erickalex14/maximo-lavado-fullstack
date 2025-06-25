import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import { createPinia } from 'pinia'
import axios from 'axios'

// Import components
import App from './App.vue'
import Login from './pages/Login.vue'
import Dashboard from './pages/Dashboard.vue'
import Clientes from './pages/Clientes.vue'
import Lavados from './pages/Lavados.vue'

// Configure axios
axios.defaults.withCredentials = true
axios.defaults.baseURL = window.location.origin
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

// Configure CSRF token
const token = document.head.querySelector('meta[name="csrf-token"]')
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content
}

// Configure routes
const routes = [
    { path: '/', redirect: '/dashboard' },
    { path: '/login', name: 'login', component: Login },
    { path: '/dashboard', name: 'dashboard', component: Dashboard, meta: { requiresAuth: true } },
    { path: '/clientes', name: 'clientes', component: Clientes, meta: { requiresAuth: true } },
    { path: '/lavados', name: 'lavados', component: Lavados, meta: { requiresAuth: true } },
    { path: '/lavados/nuevo', name: 'lavados-nuevo', component: Lavados, meta: { requiresAuth: true } },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

// Navigation guard
router.beforeEach(async (to, from, next) => {
    console.log('Navigating to:', to.path)
    
    // Check if route requires authentication
    if (to.matched.some(record => record.meta.requiresAuth)) {
        try {
            // Check authentication status
            const response = await axios.get('/test-auth')
            console.log('Auth check:', response.data)
            
            if (response.data.authenticated) {
                next()
            } else {
                console.log('Not authenticated, redirecting to login')
                next('/login')
            }
        } catch (error) {
            console.error('Auth check failed:', error)
            next('/login')
        }
    } else {
        next()
    }
})

// Create app
const app = createApp(App)

app.use(createPinia())
app.use(router)

// Global properties
app.config.globalProperties.$http = axios

app.mount('#app')
