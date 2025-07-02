# Componentes Base - Guía de Uso

Este documento describe todos los componentes base disponibles en la aplicación y cómo utilizarlos correctamente.

## Índice
- [Controles de Formulario](#controles-de-formulario)
- [Contenedores y Layout](#contenedores-y-layout) 
- [Visualización de Datos](#visualización-de-datos)
- [Feedback](#feedback)
- [Instalación y Uso](#instalación-y-uso)

---

## Controles de Formulario

### BaseButton
Botón versátil con múltiples variantes y estados.

```vue
<BaseButton 
  variant="primary" 
  size="md" 
  :loading="isLoading"
  @click="handleClick"
>
  Guardar
</BaseButton>
```

**Props principales:**
- `variant`: primary, secondary, success, warning, danger, outline
- `size`: sm, md, lg
- `loading`: boolean
- `disabled`: boolean

### BaseInput
Input de texto con validación y estados.

```vue
<BaseInput
  v-model="form.name"
  label="Nombre"
  placeholder="Ingresa tu nombre"
  :error="errors.name"
  required
/>
```

### BaseTextarea
Área de texto con auto-resize y contador de caracteres.

```vue
<BaseTextarea
  v-model="form.description"
  label="Descripción"
  :max-length="500"
  auto-resize
  show-counter
/>
```

### BaseSelect
Selector avanzado con búsqueda y opciones remotas.

```vue
<BaseSelect
  v-model="form.category"
  label="Categoría"
  :options="categories"
  searchable
  clearable
/>
```

### BaseSearchInput
Input de búsqueda con sugerencias y filtros.

```vue
<BaseSearchInput
  v-model="searchQuery"
  placeholder="Buscar productos..."
  :suggestions="searchSuggestions"
  @search="handleSearch"
/>
```

### BaseCheckbox
Checkbox con soporte para arrays y estado indeterminado.

```vue
<BaseCheckbox
  v-model="selectedItems"
  :value="item.id"
  label="Seleccionar item"
  variant="primary"
/>
```

### BaseRadio
Radio button para selección única.

```vue
<BaseRadio
  v-model="selectedOption"
  :value="option.value"
  :label="option.label"
  name="options"
/>
```

### BaseSwitch
Toggle/switch con iconos y etiquetas.

```vue
<BaseSwitch
  v-model="isActive"
  label="Activar notificaciones"
  show-icons
  on-label="ON"
  off-label="OFF"
/>
```

### BaseDatePicker
Selector de fecha con picker personalizable.

```vue
<BaseDatePicker
  v-model="form.date"
  label="Fecha"
  show-custom-picker
  show-time-selector
  clearable
/>
```

### BaseFileUpload
Subida de archivos con drag & drop y preview.

```vue
<BaseFileUpload
  v-model="uploadedFiles"
  label="Documentos"
  multiple
  accept="image/*,.pdf"
  :max-size="5242880"
  auto-upload
/>
```

---

## Contenedores y Layout

### BaseCard
Tarjeta flexible para contenido.

```vue
<BaseCard
  title="Información del Cliente"
  variant="elevated"
  padding="lg"
>
  <template #actions>
    <BaseButton size="sm">Editar</BaseButton>
  </template>
  
  <p>Contenido de la tarjeta...</p>
</BaseCard>
```

### BaseModal
Modal responsive y accesible.

```vue
<BaseModal
  v-model="showModal"
  title="Confirmar acción"
  size="md"
  :closable="true"
>
  <p>¿Estás seguro de que quieres eliminar este elemento?</p>
  
  <template #footer>
    <BaseButton variant="secondary" @click="showModal = false">
      Cancelar
    </BaseButton>
    <BaseButton variant="danger" @click="confirmDelete">
      Eliminar
    </BaseButton>
  </template>
</BaseModal>
```

### BaseForm
Wrapper de formulario con validación.

```vue
<BaseForm
  title="Nuevo Cliente"
  :loading="isSubmitting"
  @submit="handleSubmit"
  @cancel="handleCancel"
>
  <BaseInput v-model="form.name" label="Nombre" required />
  <BaseInput v-model="form.email" label="Email" type="email" />
  
  <template #actions>
    <BaseButton type="submit" variant="primary">Guardar</BaseButton>
  </template>
</BaseForm>
```

---

## Visualización de Datos

### BaseTable
Tabla avanzada con ordenamiento y paginación.

```vue
<BaseTable
  :columns="columns"
  :data="tableData"
  :loading="isLoading"
  sortable
  striped
  @sort="handleSort"
  @row-click="handleRowClick"
>
  <template #actions="{ row }">
    <BaseButton size="sm" @click="editItem(row)">Editar</BaseButton>
  </template>
</BaseTable>
```

### BaseBadge
Etiqueta para mostrar estados o contadores.

```vue
<BaseBadge
  variant="success"
  size="md"
  :dot="true"
>
  Activo
</BaseBadge>
```

### BasePagination
Paginación responsiva y accesible.

```vue
<BasePagination
  v-model="currentPage"
  :total-items="totalItems"
  :items-per-page="itemsPerPage"
  show-info
  @change="handlePageChange"
/>
```

---

## Feedback

### BaseAlert
Alertas informativas con acciones.

```vue
<BaseAlert
  variant="warning"
  title="Atención"
  :dismissible="true"
  :auto-dismiss="5000"
>
  Este es un mensaje de advertencia importante.
  
  <template #actions>
    <BaseButton size="sm" variant="outline">Más info</BaseButton>
  </template>
</BaseAlert>
```

### BaseToast
Notificaciones toast temporales.

```vue
<BaseToast
  v-model="showToast"
  variant="success"
  title="Operación exitosa"
  message="Los datos se guardaron correctamente"
  :duration="3000"
/>
```

### BaseLoading
Indicadores de carga flexibles.

```vue
<BaseLoading
  variant="spinner"
  size="lg"
  text="Cargando datos..."
  overlay
/>
```

---

## Instalación y Uso

### Importación Individual
```javascript
import { BaseButton, BaseInput, BaseCard } from '@/components/common'
```

### Registro Global
```javascript
// En main.js o app.js
import { registerBaseComponents } from '@/components/common'

const app = createApp(App)
registerBaseComponents(app)
```

### Uso en Componentes
```vue
<template>
  <BaseCard>
    <BaseForm @submit="handleSubmit">
      <BaseInput v-model="form.name" label="Nombre" />
      <BaseButton type="submit">Guardar</BaseButton>
    </BaseForm>
  </BaseCard>
</template>

<script setup>
// Si no están registrados globalmente
import { BaseCard, BaseForm, BaseInput, BaseButton } from '@/components/common'

const form = reactive({
  name: ''
})

const handleSubmit = (data) => {
  console.log('Form submitted:', data)
}
</script>
```

## Estructura CSS

Todos los componentes siguen la estructura CSS modular:

- **Variables CSS**: Definidas en `base.css`
- **Componentes**: Estilos específicos sin @apply
- **Utilidades**: Disponibles para casos especiales
- **Responsivo**: Breakpoints consistentes
- **Accesibilidad**: Estados de focus y screen readers

## Mejores Prácticas

1. **Consistencia**: Usar los mismos tamaños y variantes en toda la app
2. **Accesibilidad**: Siempre incluir labels y descripciones apropiadas
3. **Validación**: Implementar validación tanto en frontend como backend
4. **Performance**: Usar v-model con componentes de formulario
5. **Responsive**: Probar en diferentes tamaños de pantalla

## Personalización

### Temas
Los componentes respetan las variables CSS del tema:
```css
:root {
  --color-primary-600: #1d4ed8;
  --color-success-600: #059669;
  /* ... más variables */
}
```

### Extensión
Para crear variantes personalizadas:
```vue
<BaseButton class="my-custom-button" variant="primary">
  Botón personalizado
</BaseButton>

<style>
.my-custom-button {
  /* Estilos específicos */
}
</style>
```

---

## Soporte y Contribución

- Todos los componentes están tipados con PropTypes
- Incluyen slots para personalización
- Eventos documentados y consistentes
- Métodos expose para control programático

Para agregar nuevos componentes, seguir la misma estructura y patrones establecidos.
