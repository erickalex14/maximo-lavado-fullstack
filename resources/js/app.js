import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';

// Importar estilos globales
import '../css/app.css';

// Importar Material Web Components
import '@material/web/all.js';

// Crear la aplicación Vue
const app = createApp(App);

// Configurar Pinia para el manejo de estado
const pinia = createPinia();
app.use(pinia);

// Configurar el router
app.use(router);

// Montar la aplicación
app.mount('#app');
