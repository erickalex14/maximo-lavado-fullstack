# Sistema de Lavado de Autos - Frontend

## 🚀 Arquitectura y Tecnologías

Este frontend está construido con las siguientes tecnologías:

- **Vue.js 3** con Composition API
- **Vue Router 4** para navegación
- **Pinia** para manejo de estado
- **Tailwind CSS** para estilos
- **Material Design 3** (Material Web Components)
- **Heroicons** para iconografía
- **Chart.js** para gráficos
- **Axios** para peticiones HTTP
- **Vite** como bundler

## 📁 Estructura del Proyecto

```
resources/js/
├── App.vue                 # Componente raíz
├── app.js                  # Entrada de la aplicación
├── components/             # Componentes reutilizables
│   ├── common/            # Componentes comunes
│   ├── dashboard/         # Componentes del dashboard
│   ├── charts/            # Componentes de gráficos
│   └── layout/            # Componentes de layout
├── layouts/               # Layouts principales
│   ├── AuthLayout.vue     # Layout para autenticación
│   └── DashboardLayout.vue # Layout del dashboard
├── router/                # Configuración de rutas
│   ├── index.js           # Router principal
│   └── modules/           # Rutas modulares
├── services/              # Servicios de API
├── stores/                # Stores de Pinia
└── views/                 # Páginas/Vistas
    ├── auth/              # Vistas de autenticación
    ├── dashboard/         # Vista del dashboard
    ├── empleados/         # Vistas de empleados
    ├── clientes/          # Vistas de clientes
    └── ...
```

## 🎯 Principios de Arquitectura Implementados

### SOLID Principles

1. **Single Responsibility**: Cada componente tiene una responsabilidad específica
2. **Open/Closed**: Los servicios son extensibles sin modificar código existente
3. **Liskov Substitution**: Los componentes son intercambiables
4. **Interface Segregation**: Interfaces específicas en lugar de genéricas
5. **Dependency Inversion**: Dependencias a través de inyección

### DRY (Don't Repeat Yourself)

- Componentes reutilizables (MetricCard, ChartComponent, etc.)
- Servicios base para operaciones CRUD
- Stores modulares para manejo de estado
- Estilos CSS reutilizables con Tailwind

### Patrones de Diseño

- **Repository Pattern**: En los servicios de API
- **Observer Pattern**: Con Pinia stores
- **Factory Pattern**: En la creación de componentes
- **Module Pattern**: En la organización de rutas

## 🔧 Configuración y Desarrollo

### Requisitos Previos

- Node.js >= 18
- npm >= 8
- PHP >= 8.1
- Laravel >= 10

### Instalación

1. Instalar dependencias:
```bash
npm install
```

2. Configurar variables de entorno:
```bash
# En el archivo .env de Laravel
VITE_API_URL=http://localhost:8000
```

3. Ejecutar en desarrollo:
```bash
# Terminal 1: Laravel
php artisan serve

# Terminal 2: Vite
npm run dev
```

### Comandos Disponibles

```bash
npm run dev         # Modo desarrollo
npm run build       # Construir para producción
npm run preview     # Previsualizar build de producción
```

## 📦 Componentes Principales

### 1. Sistema de Autenticación

- Login con Sanctum
- Guard de rutas
- Manejo de tokens
- Persistencia de sesión

### 2. Dashboard

- Métricas en tiempo real
- Gráficos interactivos
- Actividad reciente
- Alertas del sistema

### 3. Gestión de Datos

- CRUD completo para todas las entidades
- Filtrado y paginación
- Búsqueda en tiempo real
- Validaciones del lado cliente

### 4. Sistema de Notificaciones

- Toast notifications
- Diferentes tipos (success, error, warning, info)
- Auto-dismiss configurable
- Acciones personalizadas

## 🔄 Flujo de Datos

```
API ← → Service ← → Store ← → Component
```

1. **API**: Endpoints del backend Laravel
2. **Service**: Capa de abstracción para HTTP requests
3. **Store**: Manejo de estado global con Pinia
4. **Component**: Componentes Vue que consumen el estado

## 🎨 Sistema de Diseño

### Colores

- **Primary**: Verde (#4caf50) - Acción principal
- **Secondary**: Rosa (#e91e63) - Acciones secundarias
- **Surface**: Grises - Fondos y superficies
- **Status**: Verde/Rojo/Amarillo/Azul - Estados

### Tipografía

- **Font**: Inter (Google Fonts)
- **Tamaños**: Sistema modular (text-sm, text-base, text-lg, etc.)

### Espaciado

- Sistema de espaciado basado en Tailwind (4px base)
- Grid responsive (12 columnas)

## 🔐 Seguridad

- Autenticación basada en tokens
- Guards de rutas
- Validación en cliente y servidor
- Sanitización de inputs
- CSRF protection

## 📱 Responsive Design

- Mobile-first approach
- Breakpoints: sm (640px), md (768px), lg (1024px), xl (1280px)
- Sidebar colapsable en móvil
- Tablas con scroll horizontal

## 🚀 Mejores Prácticas Implementadas

### Vue.js

- Composition API para mejor reutilización
- Props y emits tipados
- Lazy loading de componentes
- Directivas personalizadas

### Estado

- Estado modular con Pinia
- Acciones asíncronas manejadas correctamente
- Estado persistente donde es necesario

### Performance

- Code splitting por rutas
- Lazy loading de imágenes
- Debounced search
- Virtual scrolling (futuro)

## 📊 Funcionalidades Implementadas

### ✅ Completadas

- [x] Sistema de autenticación
- [x] Dashboard con métricas
- [x] Gestión de empleados (vista principal)
- [x] Gestión de clientes (vista principal)
- [x] Sistema de notificaciones
- [x] Componentes base reutilizables
- [x] Router con guards
- [x] Layouts responsivos

### 🔄 En Desarrollo

- [ ] Formularios de creación/edición
- [ ] Gestión de vehículos
- [ ] Registro de lavados
- [ ] Gestión de productos
- [ ] Sistema de facturación
- [ ] Reportes y gráficos
- [ ] Configuraciones

### 🎯 Próximas Funcionalidades

- [ ] Notificaciones push
- [ ] Modo offline
- [ ] Export/Import de datos
- [ ] Configuración de temas
- [ ] Multi-idioma

## 🐛 Debugging

### Vue DevTools

Instalar la extensión de Vue DevTools para Chrome/Firefox para debugging.

### Console Logging

Los servicios incluyen logging de errores. Verificar la consola del navegador.

### Network Tab

Verificar las peticiones HTTP en la pestaña Network del navegador.

## 🔧 Extensibilidad

### Agregar Nuevas Vistas

1. Crear el archivo en `views/modulo/`
2. Agregar la ruta en `router/modules/`
3. Crear el servicio correspondiente
4. Agregar al store si es necesario

### Agregar Nuevos Servicios

1. Crear clase en `services/`
2. Extender el cliente base HTTP
3. Implementar métodos CRUD
4. Manejar errores apropiadamente

### Personalizar Estilos

1. Modificar `tailwind.config.cjs`
2. Agregar clases en `app.css`
3. Usar variables CSS para temas

## 📚 Recursos Adicionales

- [Vue.js 3 Documentation](https://vuejs.org/)
- [Pinia Documentation](https://pinia.vuejs.org/)
- [Tailwind CSS Documentation](https://tailwindcss.com/)
- [Material Design 3](https://m3.material.io/)
- [Chart.js Documentation](https://www.chartjs.org/)

## 🤝 Contribución

1. Seguir las convenciones de naming
2. Escribir código autodocumentado
3. Usar TypeScript donde sea posible (futuro)
4. Mantener componentes pequeños y reutilizables
5. Seguir los principios SOLID y DRY

---

**¡Feliz coding! 🚀**
