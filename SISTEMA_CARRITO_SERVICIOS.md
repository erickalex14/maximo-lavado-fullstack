# Sistema de Carrito de Servicios - Lavado de Autos

## Descripción General

El Sistema de Carrito de Servicios es una evolución del sistema actual de lavado de autos que permite:

- **Servicios dinámicos y configurables** desde panel de administración
- **Carrito tipo e-commerce** para combinar múltiples servicios y productos
- **Precios diferenciados** por tipo de vehículo
- **Gestión completa del flujo** desde orden hasta facturación
- **Compatibilidad total** con el sistema actual durante la transición

## Características Principales

### 🛒 **Sistema de Carrito**
- Combinar múltiples servicios en una sola orden
- Agregar productos automotrices y de despensa
- Precios automáticos según tipo de vehículo
- Precios editables en tiempo real
- Cálculo automático de totales y descuentos

### ⚙️ **Gestión Dinámica de Servicios**
- Crear, editar y eliminar servicios desde admin
- Categorización de servicios (Lavado, Detailing, Adicionales)
- Configuración de precios por tipo de vehículo
- Control de disponibilidad (activar/desactivar)
- Códigos únicos para cada servicio

### 🚗 **Tipos de Vehículo**
- **Motos**: Lavado $3
- **Auto Pequeño**: Lavado $8, Solo exterior $4-5
- **Auto Mediano**: Lavado $10, Solo exterior $5
- **Camioneta**: Lavado $10, Solo exterior $7-8
- **Pulverizado**: +$2 (aplicable a todos)

### 📋 **Flujo de Órdenes**
1. **Pendiente**: Orden creada, esperando procesamiento
2. **En Proceso**: Empleado trabajando en los servicios
3. **Completado**: Servicios terminados, listo para facturar
4. **Facturado**: Orden facturada y cerrada
5. **Cancelado**: Orden cancelada

## Arquitectura del Sistema

### Nuevas Tablas

#### **tipos_vehiculos**
- Catálogo de tipos de vehículo (moto, auto pequeño, etc.)
- Configuración de precios base por tipo

#### **categorias_servicios**
- Organización de servicios por categorías
- Control visual (colores, iconos)

#### **tipos_servicios**
- Catálogo dinámico de servicios disponibles
- Configuración de comportamiento (base, adicional, fijo)
- Gestión de disponibilidad y orden de visualización

#### **precios_servicios**
- Matriz de precios: servicio x tipo de vehículo
- Soporte para rangos de precio y vigencias

#### **ordenes_servicio**
- Carrito principal con información de la orden
- Control de estados y flujo de trabajo
- Totales, descuentos y observaciones

#### **orden_servicio_detalles**
- Items individuales del carrito
- Servicios y productos combinados
- Precios originales vs editados
- Control de ejecución por item

### Arquitectura de Código (Patrón Repository)

```
app/
├── Contracts/              # Interfaces de repositorios
│   ├── TipoVehiculoRepositoryInterface.php
│   ├── CategoriaServicioRepositoryInterface.php
│   ├── TipoServicioRepositoryInterface.php
│   ├── PrecioServicioRepositoryInterface.php
│   ├── OrdenServicioRepositoryInterface.php
│   └── OrdenServicioDetalleRepositoryInterface.php
│
├── Http/
│   ├── Controllers/        # Controladores REST
│   │   ├── TipoVehiculoController.php
│   │   ├── CategoriaServicioController.php
│   │   ├── TipoServicioController.php
│   │   ├── PrecioServicioController.php
│   │   ├── OrdenServicioController.php
│   │   └── CarritoController.php
│   │
│   └── Requests/          # Validación de requests (organizados por módulo)
│       ├── TipoVehiculo/
│       │   ├── StoreTipoVehiculoRequest.php
│       │   └── UpdateTipoVehiculoRequest.php
│       ├── CategoriaServicio/
│       │   ├── StoreCategoriaServicioRequest.php
│       │   └── UpdateCategoriaServicioRequest.php
│       ├── TipoServicio/
│       │   ├── StoreTipoServicioRequest.php
│       │   └── UpdateTipoServicioRequest.php
│       ├── PrecioServicio/
│       │   ├── StorePrecioServicioRequest.php
│       │   └── UpdatePrecioServicioRequest.php
│       ├── OrdenServicio/
│       │   ├── StoreOrdenServicioRequest.php
│       │   ├── UpdateOrdenServicioRequest.php
│       │   ├── AgregarServicioRequest.php
│       │   ├── AgregarProductoRequest.php
│       │   └── UpdateDetalleRequest.php
│       └── Carrito/
│           ├── CrearOrdenRequest.php
│           └── ProcesarOrdenRequest.php
│
├── Models/                # Modelos Eloquent
│   ├── TipoVehiculo.php
│   ├── CategoriaServicio.php
│   ├── TipoServicio.php
│   ├── PrecioServicio.php
│   ├── OrdenServicio.php
│   └── OrdenServicioDetalle.php
│
├── Repositories/          # Implementación de repositorios
│   ├── TipoVehiculoRepository.php
│   ├── CategoriaServicioRepository.php
│   ├── TipoServicioRepository.php
│   ├── PrecioServicioRepository.php
│   ├── OrdenServicioRepository.php
│   └── OrdenServicioDetalleRepository.php
│
└── Services/             # Lógica de negocio
    ├── TipoVehiculoService.php
    ├── CategoriaServicioService.php
    ├── TipoServicioService.php
    ├── PrecioServicioService.php
    ├── OrdenServicioService.php
    └── CarritoService.php
```

## API Endpoints

### Administración de Servicios

```http
GET    /api/tipos-servicios           # Listar servicios
POST   /api/tipos-servicios           # Crear servicio
PUT    /api/tipos-servicios/{id}      # Actualizar servicio
DELETE /api/tipos-servicios/{id}      # Eliminar servicio
PUT    /api/tipos-servicios/{id}/toggle # Activar/Desactivar

GET    /api/precios-servicios         # Gestión de precios
POST   /api/precios-servicios         # Crear precio
PUT    /api/precios-servicios/{id}    # Actualizar precio
```

### Sistema de Carrito

```http
GET    /api/carrito/servicios-disponibles/{tipoVehiculoId}  # Servicios por vehículo
GET    /api/carrito/productos-disponibles                   # Productos disponibles

POST   /api/ordenes-servicio                               # Crear orden
GET    /api/ordenes-servicio/{id}                          # Ver orden
PUT    /api/ordenes-servicio/{id}                          # Actualizar orden

POST   /api/ordenes-servicio/{id}/agregar-servicio         # Agregar servicio
POST   /api/ordenes-servicio/{id}/agregar-producto         # Agregar producto
PUT    /api/ordenes-servicio/{ordenId}/detalle/{detalleId} # Editar item
DELETE /api/ordenes-servicio/{ordenId}/detalle/{detalleId} # Quitar item
```

### Flujo de Trabajo

```http
PUT    /api/ordenes-servicio/{id}/procesar   # Iniciar procesamiento
PUT    /api/ordenes-servicio/{id}/completar  # Marcar completado  
POST   /api/ordenes-servicio/{id}/facturar   # Generar factura
```

## Flujo de Uso

### 1. Configuración Inicial (Admin)

1. **Configurar tipos de vehículo** (ya incluidos por defecto)
2. **Crear categorías de servicios** (Lavado, Detailing, etc.)
3. **Agregar servicios** con sus precios por tipo de vehículo
4. **Activar servicios** para que aparezcan en punto de venta

### 2. Punto de Venta

1. **Seleccionar cliente y vehículo**
2. **Agregar servicios al carrito**:
   - Sistema detecta tipo de vehículo automáticamente
   - Muestra precios correspondientes
   - Permite editar precios si es necesario
3. **Agregar productos** (automotrices/despensa)
4. **Revisar total** y aplicar descuentos si aplica
5. **Crear orden** y asignar empleado

### 3. Ejecución de Servicios

1. **Empleado ve órdenes pendientes**
2. **Marca orden como "en proceso"**
3. **Ejecuta servicios** marcando items completados
4. **Marca orden como "completada"**

### 4. Facturación

1. **Desde orden completada**
2. **Generar factura** (consolida todos los items)
3. **Orden pasa a estado "facturado"**

## Compatibilidad con Sistema Actual

### Durante la Transición

- ✅ **Sistema actual sigue funcionando** sin interrupciones
- ✅ **APIs actuales mantienen compatibilidad** total
- ✅ **Datos históricos preservados** en tablas originales
- ✅ **Migración gradual** frontend puede usar ambos sistemas

### Estrategia de Migración

1. **Fase 1**: Implementar nuevo sistema en paralelo
2. **Fase 2**: Probar funcionalidades con datos de prueba
3. **Fase 3**: Migrar gradualmente módulos del frontend
4. **Fase 4**: Deprecar endpoints antiguos (opcional)

## Beneficios del Nuevo Sistema

### Para el Negocio
- **Mayor flexibilidad** en servicios ofrecidos
- **Upselling automático** al mostrar servicios adicionales
- **Control de precios** centralizado y actualizable
- **Reportes detallados** por tipo de servicio

### Para Empleados
- **Interfaz intuitiva** tipo carrito conocido
- **Proceso claro** de órdenes de trabajo
- **Seguimiento detallado** de servicios por ejecutar

### Para Clientes
- **Transparencia total** en servicios y precios
- **Personalización** de servicios según necesidades
- **Facturación detallada** de todos los servicios

## Configuración Técnica

### Requisitos
- Laravel 10+
- PHP 8.1+
- Base de datos MySQL/PostgreSQL
- Laravel Sanctum (autenticación)

### Instalación

1. **Ejecutar migraciones**:
```bash
php artisan migrate
```

2. **Ejecutar seeders**:
```bash
php artisan db:seed --class=TipoVehiculoSeeder
php artisan db:seed --class=CategoriaServicioSeeder
php artisan db:seed --class=TipoServicioSeeder
php artisan db:seed --class=PrecioServicioSeeder
```

3. **Limpiar cachés**:
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### Testing

```bash
# Ejecutar tests unitarios
php artisan test --filter=CarritoTest

# Ejecutar tests de integración
php artisan test --filter=OrdenServicioTest

# Tests completos del módulo
php artisan test tests/Feature/CarritoServiciosTest.php
```

## Roadmap Futuro

### Versión 2.0
- 🎁 **Paquetes y promociones** (combos con descuento)
- 📱 **App móvil** para empleados
- 📅 **Sistema de citas** integrado
- 🏷️ **Programa de lealtad** para clientes

### Versión 2.1
- 📊 **Dashboard avanzado** con analytics
- 🔄 **Servicios recurrentes** (suscripciones)
- 💳 **Múltiples formas de pago**
- 📧 **Notificaciones automáticas**

## Soporte y Documentación

### Archivos Relacionados
- `routes/api.php` - Definición de endpoints
- `database/migrations/` - Estructura de base de datos
- `database/seeders/` - Datos iniciales
- `app/Http/Controllers/` - Lógica de controladores
- `app/Services/` - Lógica de negocio

### Contacto Técnico
Para soporte técnico o consultas sobre implementación, revisar:
1. Documentación de API en `/api/documentation`
2. Tests unitarios en `/tests`
3. Logs de aplicación en `/storage/logs`

---

**Versión**: 1.0.0  
**Fecha**: Julio 2025  
**Desarrollado**: Sistema de Lavado de Autos con Arquitectura Repository Pattern

---

## Plan de Implementación Detallado

### **Estructura Completa a Implementar**

#### **1. Migraciones (7 nuevas)**
```
database/migrations/
├── 2025_07_12_000001_create_tipos_vehiculos_table.php
├── 2025_07_12_000002_create_categorias_servicios_table.php
├── 2025_07_12_000003_create_tipos_servicios_table.php
├── 2025_07_12_000004_create_precios_servicios_table.php
├── 2025_07_12_000005_create_ordenes_servicio_table.php
├── 2025_07_12_000006_create_orden_servicio_detalles_table.php
└── 2025_07_12_000007_add_tipo_vehiculo_to_vehiculos_table.php
```

#### **2. Seeders (4 nuevos)**
```
database/seeders/
├── TipoVehiculoSeeder.php
├── CategoriaServicioSeeder.php
├── TipoServicioSeeder.php
└── PrecioServicioSeeder.php
```

#### **3. Archivos de Código (34 nuevos archivos)**
- **6 Interfaces** (Contracts/)
- **6 Modelos** (Models/)
- **6 Repositorios** (Repositories/)
- **6 Servicios** (Services/)
- **6 Controladores** (Controllers/)
- **11 Requests** (organizados en subcarpetas)

### **Orden de Implementación Sugerido**

1. **Migraciones y Seeders** (Base de datos)
2. **Modelos y Interfaces** (Estructura base)
3. **Repositorios** (Acceso a datos)
4. **Servicios** (Lógica de negocio)
5. **Requests** (Validaciones)
6. **Controladores** (API endpoints)
7. **Rutas** (Configuración de endpoints)
8. **Testing** (Pruebas unitarias e integración)
