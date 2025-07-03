import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';

// Importar estilos
import '../css/app.css';

// Crear la aplicación Vue
const app = createApp(App);

// Configurar Pinia (state management)
const pinia = createPinia();

// Usar plugins
app.use(pinia);
app.use(router);

// Montar la aplicación
app.mount('#app');
