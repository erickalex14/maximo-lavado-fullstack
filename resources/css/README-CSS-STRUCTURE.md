# Estructura CSS Modular - Guía de Uso

## Descripción General

La estructura CSS ha sido refactorizada para separar los diferentes tipos de estilos y evitar conflictos entre CSS tradicional y directivas de Tailwind (`@apply`).

## Archivos de la Estructura

### 1. `app.css` - Archivo Principal
El punto de entrada que importa todos los demás archivos:
```css
@import './base.css';
@import './tailwind-utilities.css';
@import './components.css';
```

### 2. `base.css` - Estilos Base
**Contiene:** Variables CSS, reset, estilos globales y keyframes
**Estilo:** CSS puro sin directivas de Tailwind
**Incluye:**
- Variables CSS personalizadas (colores, espaciado, tipografía, etc.)
- Reset básico y estilos del body
- Configuración de accesibilidad
- Scrollbar personalizada
- Keyframes para animaciones (spin, pulse, bounce, etc.)

### 3. `tailwind-utilities.css` - Utilidades de Tailwind
**Contiene:** Utilidades con `@apply` y directivas de Tailwind
**Estilo:** Exclusivamente directivas `@apply`
**Incluye:**
- `@tailwind base`, `@tailwind components`, `@tailwind utilities`
- Utilidades de layout (flex-center, grid-auto-fit, etc.)
- Utilidades de botones (btn-base, btn-primary, etc.)
- Utilidades de formularios (input-base, form-group, etc.)
- Utilidades responsive y de hover

### 4. `components.css` - Componentes Específicos
**Contiene:** Estilos de componentes específicos
**Estilo:** CSS puro sin directivas de Tailwind
**Incluye:**
- Componentes de formulario (form-input, form-label, etc.)
- Componentes de card (card, card-header, etc.)
- Componentes de badge y botones
- Componentes de tabla y loading
- Utilidades responsive en CSS puro

## ¿Por Qué Esta Separación?

### Problema Anterior
Mezclar CSS tradicional con `@apply` en el mismo archivo puede generar:
- Conflictos de compilación
- Errores de procesamiento de Tailwind
- Inconsistencias en el orden de carga

### Solución Actual
- **CSS Puro**: Variables, componentes base, estilos complejos
- **@apply**: Utilidades simples de Tailwind
- **Separación clara**: Cada archivo tiene un propósito específico

## Mejores Prácticas

### Cuándo Usar Cada Archivo

#### `base.css`
✅ Variables CSS personalizadas
✅ Estilos globales (body, html, etc.)
✅ Keyframes y animaciones complejas
✅ Configuración de accesibilidad
❌ Utilidades de clases específicas

#### `tailwind-utilities.css`
✅ Utilidades con `@apply`
✅ Clases auxiliares simples
✅ Combinaciones frecuentes de utilidades de Tailwind
❌ CSS complejo o personalizado

#### `components.css`
✅ Estilos de componentes específicos
✅ CSS complejo que no se puede hacer con `@apply`
✅ Responsive personalizado
✅ Estados y variantes complejas
❌ Directivas `@apply`

### En Componentes Vue

#### Opción 1: Usar clases utilitarias
```vue
<template>
  <button class="btn-base btn-primary">
    Click me
  </button>
</template>
```

#### Opción 2: CSS local con @apply (SEGURO en archivos .vue)
```vue
<template>
  <button class="my-custom-button">
    Click me
  </button>
</template>

<style scoped>
.my-custom-button {
  @apply px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700;
}
</style>
```

#### Opción 3: CSS puro en componentes
```vue
<style scoped>
.my-custom-button {
  padding: 0.5rem 1rem;
  background-color: var(--color-primary-600);
  color: white;
  border-radius: var(--border-radius-md);
  transition: background-color var(--transition-fast);
}

.my-custom-button:hover {
  background-color: var(--color-primary-700);
}
</style>
```

## Variables CSS Disponibles

### Colores
```css
--color-primary-50 hasta --color-primary-900
--color-success-50 hasta --color-success-900
--color-warning-50 hasta --color-warning-900
--color-error-50 hasta --color-error-900
--color-gray-50 hasta --color-gray-900
```

### Espaciado
```css
--spacing-xs: 0.25rem
--spacing-sm: 0.5rem
--spacing-md: 0.75rem
--spacing-lg: 1rem
--spacing-xl: 1.5rem
--spacing-2xl: 2rem
--spacing-3xl: 3rem
--spacing-4xl: 4rem
```

### Sombras
```css
--shadow-sm, --shadow-md, --shadow-lg, --shadow-xl, --shadow-2xl
```

### Tipografía
```css
--font-size-xs hasta --font-size-4xl
--line-height-tight, --line-height-normal, --line-height-relaxed
```

### Bordes
```css
--border-radius-sm hasta --border-radius-2xl, --border-radius-full
```

### Z-index
```css
--z-dropdown: 1000
--z-modal: 1050
--z-toast: 1080
```

### Transiciones
```css
--transition-fast: 150ms ease-in-out
--transition-normal: 200ms ease-in-out
--transition-slow: 300ms ease-in-out
```

## Clases Utilitarias Disponibles

### Layout
- `.container-app`, `.container-fluid`
- `.flex-center`, `.flex-between`, `.flex-start`, `.flex-end`
- `.grid-auto-fit`, `.grid-auto-fill`

### Texto
- `.text-heading-1` hasta `.text-heading-4`
- `.text-body`, `.text-caption`, `.text-overline`

### Botones
- `.btn-base` + `.btn-primary`, `.btn-secondary`, etc.
- `.btn-outline`, `.btn-ghost`

### Formularios
- `.input-base`, `.input-error`, `.input-success`
- `.form-group`, `.form-label`, `.form-help`, `.form-error`

### Cards
- `.card-base`, `.card-elevated`, `.card-interactive`

### Badges
- `.badge-base` + `.badge-primary`, `.badge-success`, etc.

### Estados
- `.loading-spinner`, `.loading-skeleton`
- `.hover-lift`, `.hover-scale`, `.hover-glow`
- `.focus-ring`, `.focus-ring-inset`

## Beneficios de Esta Estructura

1. **Sin Conflictos**: CSS puro y `@apply` están separados
2. **Compilación Limpia**: Tailwind procesa solo lo que necesita
3. **Mantenibilidad**: Fácil encontrar y modificar estilos
4. **Escalabilidad**: Fácil añadir nuevos estilos sin rompimiento
5. **Performance**: Mejor optimización y carga
6. **Flexibilidad**: Múltiples enfoques para diferentes necesidades

## Migración

Para migrar código existente:

1. **Utilidades simples**: Mover a `tailwind-utilities.css` con `@apply`
2. **CSS complejo**: Mover a `components.css` sin `@apply`
3. **Variables**: Usar las del `base.css`
4. **Componentes Vue**: Usar `@apply` solo en archivos `.vue` individuales

Esta estructura asegura compatibilidad, performance y mantenibilidad a largo plazo.
