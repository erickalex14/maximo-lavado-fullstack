# Dashboard de Máximo Lavado

## Descripción

El Dashboard es la vista principal del sistema de gestión de lavado de autos "Máximo Lavado". Proporciona una vista general de las métricas clave del negocio, actividad reciente, servicios populares y acciones rápidas.

## Características Principales

### 📊 Métricas en Tiempo Real
- **Ingresos del día**: Muestra los ingresos generados en el día actual
- **Lavados realizados**: Cantidad de servicios completados
- **Clientes activos**: Número de clientes que han utilizado el servicio
- **Tiempo promedio**: Duración promedio de los servicios

### 📈 Gráfico de Ventas
- Visualización de ventas de los últimos 7 días (preparado para integración con Chart.js)
- Selector de período (7 días, 30 días, 3 meses, 1 año)
- Placeholder para futura integración de gráficos

### 🔔 Actividad Reciente
- Lista de actividades más recientes del sistema
- Categorización por tipo (ventas, servicios, clientes)
- Timestamps formatados de manera amigable
- Iconos diferenciados por tipo de actividad

### 🏆 Servicios Más Populares
- Ranking de servicios por popularidad
- Métricas de ingresos por servicio
- Indicadores visuales con iconos y colores

### ⚡ Acciones Rápidas
- Botones de acceso directo a funciones principales
- Nueva venta, registro de cliente, inventario, configuración
- Navegación inteligente según disponibilidad de rutas

### 📋 Generación de Reportes
- Modal para generar reportes personalizados
- Selección de tipo de reporte y rango de fechas
- Validación de formularios
- Feedback al usuario mediante toast notifications

## Arquitectura Técnica

### Componentes Utilizados
- **AppLayout**: Layout principal de la aplicación
- **DashboardStatCard**: Componente personalizado para tarjetas de estadísticas
- **BaseCard, BaseButton, BaseSelect, BaseModal**: Componentes base reutilizables
- **Icon**: Sistema de iconos SVG dinámico

### Gestión de Estado
- **Vue 3 Composition API**: Uso de `ref`, `reactive`, `computed`
- **Stores**: Integración con `useToast` para notificaciones

### Iconografía
Iconos utilizados en el dashboard:
- `currency-dollar`: Ingresos y ventas
- `sparkles`: Servicios de lavado
- `users`: Clientes
- `clock`: Tiempo promedio
- `chart-bar`: Gráficos
- `refresh`: Actualización de datos
- `document-text`: Reportes
- `arrow-right`: Navegación
- `plus`: Crear nuevo
- `user-plus`: Agregar cliente
- `cube`: Inventario
- `cog-6-tooth`: Configuración
- `sun`: Servicios exteriores
- `star`: Servicios premium
- `shield-check`: Servicios de protección

### Estilos y Diseño
- **Tailwind CSS**: Framework de utilidades CSS
- **Dark Mode**: Soporte completo para tema oscuro
- **Responsive Design**: Adaptación a diferentes tamaños de pantalla
- **Grid System**: Layout responsivo con CSS Grid
- **Hover Effects**: Interacciones visuales suaves

## Funcionalidades Implementadas

### ✅ Completado
1. **Vista de estadísticas**: Tarjetas con métricas principales
2. **Actividad reciente**: Lista de actividades con timestamps
3. **Servicios populares**: Ranking con métricas de ingresos
4. **Acciones rápidas**: Botones de navegación directa
5. **Modal de reportes**: Generación de reportes con validación
6. **Actualización de datos**: Función de refresh con loading states
7. **Manejo de errores**: Toast notifications para feedback
8. **Navegación inteligente**: Redirección a rutas del sistema

### 🔄 Preparado para Integración
1. **Gráficos**: Placeholder preparado para Chart.js
2. **APIs**: Simulación de llamadas a API con datos mock
3. **Filtros**: Sistema de filtrado por períodos
4. **Exportación**: Base para exportación de reportes

## Datos Simulados (Mock Data)

### Estadísticas
```javascript
stats = [
  { icon: 'currency-dollar', title: 'Ingresos Hoy', value: '$2,350', change: '+12.5%', trend: 'up' },
  { icon: 'sparkles', title: 'Lavados Hoy', value: '24', change: '+8.2%', trend: 'up' },
  { icon: 'users', title: 'Clientes Activos', value: '186', change: '+15.1%', trend: 'up' },
  { icon: 'clock', title: 'Tiempo Promedio', value: '45 min', change: '-5.3%', trend: 'down' }
]
```

### Actividades Recientes
- Nuevas ventas registradas
- Servicios completados
- Clientes registrados
- Con timestamps relativos (hace X minutos/horas)

### Servicios Populares
- Lavado Completo: 45 servicios, $13,500
- Lavado Exterior: 32 servicios, $6,400
- Lavado Premium: 18 servicios, $9,000
- Encerado: 12 servicios, $4,800

## Integración con Backend

### Endpoints Esperados (Futura Implementación)
```javascript
// Estadísticas del dashboard
GET /api/dashboard/stats

// Actividad reciente
GET /api/dashboard/activities

// Servicios populares
GET /api/dashboard/top-services

// Generar reporte
POST /api/reports/generate
```

### Estructura de Respuesta Esperada
```javascript
// Stats endpoint
{
  "daily_revenue": 2350,
  "daily_washes": 24,
  "active_customers": 186,
  "average_time": 45,
  "changes": {
    "revenue": 12.5,
    "washes": 8.2,
    "customers": 15.1,
    "time": -5.3
  }
}
```

## Uso y Navegación

### Desde el Dashboard se puede acceder a:
- **Nueva Venta**: `/ventas/nueva`
- **Registrar Cliente**: `/clientes/nuevo`
- **Ver Inventario**: `/inventario`
- **Configuración**: `/configuracion`

### Funciones Disponibles:
1. **Actualizar datos**: Botón refresh con loading state
2. **Generar reportes**: Modal con formulario de configuración
3. **Ver actividades**: Acceso a lista completa de actividades
4. **Navegación rápida**: Botones de acceso directo

## Mantenimiento y Extensibilidad

### Para agregar nuevas métricas:
1. Actualizar el array `stats` en el componente
2. Agregar nuevos iconos si es necesario
3. Implementar lógica de cálculo en el backend

### Para nuevas acciones rápidas:
1. Agregar elemento al array `quickActions`
2. Implementar handler en `handleQuickAction`
3. Asegurar que la ruta existe

### Para nuevos tipos de actividad:
1. Agregar tipo al array `recentActivities`
2. Definir icono y color correspondiente
3. Actualizar lógica de colores en template

## Consideraciones de Rendimiento

- **Lazy Loading**: Los gráficos se cargan bajo demanda
- **Mock Data**: Simulación de delays de API realistas
- **Loading States**: Indicadores visuales durante operaciones
- **Error Handling**: Manejo graceful de errores con toast notifications
- **Memory Management**: Limpieza automática de referencias en unmount

## Testing

### Casos de Prueba Recomendados
1. **Carga inicial**: Verificar que todas las métricas se muestran
2. **Actualización**: Probar función refresh
3. **Modal de reportes**: Validación de formularios
4. **Navegación**: Verificar que las acciones rápidas funcionan
5. **Responsive**: Verificar adaptación a diferentes pantallas
6. **Dark mode**: Verificar funcionamiento en modo oscuro

Esta documentación debe actualizarse conforme se implementen nuevas funcionalidades y se integre con el backend real.
