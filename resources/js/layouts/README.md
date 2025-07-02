# Layouts del Sistema de Lavado de Autos

Esta documentación describe la estructura y uso de los layouts principales de la aplicación.

## 🏗️ Estructura de Layouts

```
resources/js/layouts/
├── AppLayout.vue              # Layout principal tipo dashboard
├── components/                # Componentes específicos de layouts
│   ├── AppHeader.vue         # Header/barra superior
│   ├── AppSidebar.vue        # Sidebar/menú lateral
│   ├── AppFooter.vue         # Footer/pie de página
│   └── AppToastContainer.vue # Contenedor de notificaciones toast
└── README.md                 # Esta documentación
```

## 📋 Componentes de Layout

### AppLayout.vue
**Layout principal tipo dashboard** que envuelve toda la aplicación.

**Props:**
- `pageTitle`: Título de la página
- `pageDescription`: Descripción de la página
- `showPageHeader`: Mostrar header de página (default: true)
- `showFooter`: Mostrar footer (default: true)
- `maxWidth`: Ancho máximo del contenido (default: '1200px')
- `padding`: Tipo de padding ('none', 'sm', 'default', 'lg')

**Slots:**
- `default`: Contenido principal de la página
- `actions`: Acciones de la página (botones, etc.)

**Ejemplo de uso:**
```vue
<template>
  <AppLayout
    page-title="Gestión de Clientes"
    page-description="Administra la información de tus clientes"
    :show-footer="true"
  >
    <template #actions>
      <BaseButton variant="primary">
        Nuevo Cliente
      </BaseButton>
    </template>

    <!-- Contenido principal -->
    <div class="space-y-6">
      <ClientesTable />
    </div>
  </AppLayout>
</template>
```

### AppHeader.vue
**Barra superior** con información del usuario y controles de navegación.

**Props:**
- `user`: Objeto del usuario actual
- `sidebarCollapsed`: Estado del sidebar
- `breadcrumbs`: Array de breadcrumbs

**Eventos:**
- `@toggle-sidebar`: Alternar estado del sidebar
- `@logout`: Cerrar sesión
- `@profile`: Ir al perfil

### AppSidebar.vue
**Menú lateral** con navegación principal de la aplicación.

**Props:**
- `collapsed`: Estado colapsado del sidebar
- `user`: Objeto del usuario actual
- `navigation`: Array de elementos de navegación

**Eventos:**
- `@navigate`: Navegación a una ruta
- `@update:collapsed`: Actualizar estado colapsado

### AppFooter.vue
**Pie de página** con información de copyright y enlaces.

**Características:**
- Copyright dinámico con año actual
- Enlaces de ayuda y privacidad
- Información de versión
- Responsive

### AppToastContainer.vue
**Contenedor de notificaciones toast** que maneja las notificaciones globales.

**Características:**
- Integración con `useToastStore`
- Animaciones de entrada/salida
- Posicionamiento fijo en esquina superior derecha
- Auto-dismiss configurable

## 🎨 Características de Diseño

### Responsividad
- **Desktop (≥768px)**: Sidebar expandido lateral
- **Mobile (<768px)**: Sidebar colapsado como overlay
- **Print**: Layout optimizado para impresión

### Tema y Accesibilidad
- Soporte para modo claro/oscuro
- Navegación por teclado
- ARIA labels apropiados
- Contraste de colores accesible

### Animaciones
- Transiciones suaves para sidebar
- Animaciones de toast
- Efectos hover consistentes

## 🔧 Integración con Stores

### useAppStore
Maneja el estado global de la aplicación:
```javascript
const appStore = useAppStore()

// Loading global
appStore.setLoading(true)

// Alertas
appStore.showSuccess('Operación exitosa')
appStore.showError('Error en la operación')

// Sidebar
appStore.toggleSidebar()
```

### useToastStore
Maneja las notificaciones toast:
```javascript
const toastStore = useToastStore()

// Notificaciones
toastStore.success('Cliente guardado exitosamente')
toastStore.error('Error al guardar cliente')
toastStore.warning('Campos requeridos')
toastStore.info('Información actualizada')
```

### useAuthStore
Maneja autenticación y usuario:
```javascript
const authStore = useAuthStore()

// Usuario actual
const user = authStore.user

// Logout
await authStore.logout()
```

## 📱 Estructura de Navegación

El sidebar incluye las siguientes secciones principales:

1. **Dashboard** - Resumen general
2. **Lavados** - Gestión de servicios de lavado
3. **Clientes** - Administración de clientes
4. **Vehículos** - Gestión de vehículos
5. **Productos** - Inventario automotriz y despensa
6. **Ventas** - Gestión de ventas
7. **Finanzas** - Ingresos, egresos, balance
8. **Empleados** - Gestión de personal
9. **Proveedores** - Administración de proveedores
10. **Reportes** - Análisis y reportes
11. **Configuración** - Ajustes del sistema

## 🎯 Mejores Prácticas

### Uso del Layout
```vue
<script setup>
import AppLayout from '@/layouts/AppLayout.vue'
import { useAppStore } from '@/stores/app'

const appStore = useAppStore()

// Mostrar loading durante operaciones
const handleSave = async () => {
  appStore.setLoading(true)
  try {
    // ... operación
    appStore.showSuccess('Guardado exitosamente')
  } catch (error) {
    appStore.showError('Error al guardar')
  } finally {
    appStore.setLoading(false)
  }
}
</script>
```

### Navegación Programática
```javascript
import { useRouter } from 'vue-router'

const router = useRouter()

// Navegación con breadcrumbs automáticos
router.push('/clientes/nuevo')
```

### Gestión de Estado
```javascript
// Layout context disponible en componentes hijos
import { inject } from 'vue'

const layout = inject('layout')
layout.addAlert({ variant: 'success', message: 'Operación exitosa' })
```

## 🔄 Estados de la Aplicación

### Loading States
- Global loading overlay
- Component-level loading
- Button loading states

### Alert States
- Page-level alerts (persistent)
- Toast notifications (temporary)
- Inline validation messages

### Navigation States
- Active route highlighting
- Breadcrumb generation
- Mobile overlay handling

## 🚀 Próximos Pasos

1. **AuthLayout** - Layout para páginas de autenticación
2. **PublicLayout** - Layout para páginas públicas
3. **PrintLayout** - Layout optimizado para impresión
4. **ModalLayout** - Layout para modales complejos
5. **Mejoras de accesibilidad** - ARIA, navegación por teclado
6. **Temas adicionales** - Más opciones de personalización
