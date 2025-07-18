# 📋 ROADMAP SISTEMA MAXIMO LAVADO V2.0

**Fecha de creación**: 18 de Julio, 2025  
**Versión**: 2.0  
**Base de datos**: PostgreSQL (Nueva)  
**Estado**: En desarrollo  

---

## 🎯 OBJETIVOS PRINCIPALES

### **TRANSFORMACIÓN ARQUITECTÓNICA**
- ✅ **Lavados → Servicios**: Conceptualizar lavados como servicios vendibles
- ✅ **Sistema Unificado**: Productos + Servicios en una sola venta
- ✅ **Facturación Electrónica**: Cumplimiento normativo SRI Ecuador
- ✅ **Tipos Dinámicos**: Vehículos configurables por administrador

### **MEJORAS FUNCIONALES**
- ✅ **Grilla de Ventas**: Interfaz moderna para agregar productos/servicios
- ✅ **Precios Modificables**: Valores por defecto pero editables
- ✅ **Generación Automática**: Venta → Factura → Ingreso
- ✅ **Trazabilidad Completa**: Mantener empleado/vehículo/cliente en servicios

---

## 💰 PRECIOS POR DEFECTO - SERVICIOS

| Tipo Vehículo | Lavado Completo | Solo Exterior | Pulverizado |
|----------------|-----------------|---------------|-------------|
| Moto           | $3.00          | N/A           | $2.00       |
| Auto Pequeño   | $8.00          | $4.50         | $2.00       |
| Auto Mediano   | $10.00         | $5.00         | $2.00       |
| Camioneta      | $10.00         | $7.50         | $2.00       |

---

## 🗄️ ESTRATEGIA BASE DE DATOS

### **ENFOQUE: MIGRACIONES OPTIMIZADAS**
- **Nueva BD PostgreSQL** - Sin datos en producción
- **Soft Deletes incluidos** en migraciones principales
- **Sin migraciones de parches** - Estructura completa desde inicio

### **TABLAS A MANTENER (CON OPTIMIZACIONES)**
- `clientes` - Agregar soft deletes
- `vehiculos` - Modificar para tipos dinámicos + soft deletes  
- `empleados` - Agregar soft deletes
- `productos_automotrices` - Agregar soft deletes
- `productos_despensa` - Agregar soft deletes
- `proveedores` - Agregar soft deletes
- `ingresos` - Agregar soft deletes
- `egresos` - Agregar soft deletes
- `gastos_generales` - Agregar soft deletes
- `pagos_proveedores` - Agregar soft deletes
- `users` - Agregar soft deletes

### **TABLAS A ELIMINAR (LEGACY)** ❌
- `factura_detalles` - Reemplazada por `venta_detalles`
- `ventas_productos_automotrices` - Reemplazada por `ventas` + `venta_detalles`
- `ventas_productos_despensa` - Reemplazada por `ventas` + `venta_detalles`

### **NUEVAS TABLAS**
- `tipos_vehiculos` - Gestión dinámica de tipos
- `servicios` - Catálogo de servicios (reemplazo conceptual lavados)
- `ventas` - Cabecera de ventas unificadas
- `venta_detalles` - Líneas de venta (productos/servicios)
- `facturas_electronicas` - Facturación SRI

### **TABLAS A MODIFICAR**
- `vehiculos` - FK a tipos_vehiculos
- `facturas` - Campos facturación electrónica
- `lavados` - FK a servicios + mantener trazabilidad

---

## 📋 FASES DE IMPLEMENTACIÓN

### **FASE 1: MIGRACIONES OPTIMIZADAS** 🗄️
**Duración**: 1-2 días

#### Optimizar Existentes
- [x] `create_clientes_table.php` + soft deletes
- [x] `create_vehiculos_table.php` + tipos dinámicos + soft deletes
- [x] `create_lavados_table.php` + FK servicios + soft deletes
- [x] `create_facturas_table.php` + campos SRI + soft deletes
- [x] `create_empleados_table.php` + soft deletes
- [x] `create_productos_*.php` + soft deletes
- [x] `create_proveedores_table.php` + soft deletes
- [x] `create_ingresos_table.php` + soft deletes
- [x] `create_egresos_table.php` + soft deletes
- [x] `create_gastos_generales_table.php` + soft deletes
- [x] `create_pagos_proveedores_table.php` + soft deletes
- [x] `create_users_table.php` + soft deletes

#### Crear Nuevas
- [x] `create_tipos_vehiculos_table.php`
- [x] `create_servicios_table.php`
- [x] `create_ventas_table.php`
- [x] `create_venta_detalles_table.php`
- [x] `create_facturas_electronicas_table.php`

#### Eliminar Legacy
- [x] `create_factura_detalles_table.php` - ELIMINADA
- [x] `create_ventas_productos_automotrices_table.php` - ELIMINADA  
- [x] `create_ventas_productos_despensa_table.php` - ELIMINADA

#### Seeders
- [x] `TiposVehiculosSeeder.php`
- [x] `ServiciosSeeder.php`

---

### **FASE 2: MODELOS ELOQUENT** 🏗️
**Duración**: 1-2 días

#### Nuevos Modelos
- [ ] `TipoVehiculo.php`
- [ ] `Servicio.php`
- [ ] `Venta.php`
- [ ] `VentaDetalle.php`
- [ ] `FacturaElectronica.php`

#### Actualizar Existentes
- [ ] Agregar `use SoftDeletes` a todos
- [ ] `Vehiculo.php` - relación tipos
- [ ] `Lavado.php` - relación servicios
- [ ] `Factura.php` - campos SRI

---

### **FASE 3: REPOSITORIES** 📦
**Duración**: 3-4 días

#### Contracts
- [ ] `TipoVehiculoRepositoryInterface.php`
- [ ] `ServicioRepositoryInterface.php`
- [ ] `VentaRepositoryInterface.php`
- [ ] `FacturaElectronicaRepositoryInterface.php`

#### Implementaciones
- [ ] `TipoVehiculoRepository.php`
- [ ] `ServicioRepository.php`
- [ ] `VentaRepository.php`
- [ ] `FacturaElectronicaRepository.php`

#### Actualizar Existentes
- [ ] Integrar soft deletes en todos
- [ ] Optimizar consultas PostgreSQL

---

### **FASE 4: SERVICES (LÓGICA NEGOCIO)** 🧠
**Duración**: 4-5 días

#### Nuevos Services
- [ ] `TipoVehiculoService.php`
- [ ] `ServicioService.php`
- [ ] `VentaService.php` ⭐ **CORE DEL SISTEMA**
- [ ] `FacturaElectronicaService.php`

#### Lógica VentaService
- [ ] Cálculo automático totales
- [ ] Generación automática factura
- [ ] Creación automática ingreso
- [ ] Manejo stock productos
- [ ] Trazabilidad servicios
- [ ] Validaciones negocio

---

### **FASE 5: API CONTROLLERS** 🌐
**Duración**: 3-4 días

#### Request Validation
- [ ] `CreateVentaRequest.php`
- [ ] `UpdateVentaRequest.php`
- [ ] `CreateServicioRequest.php`
- [ ] `CreateTipoVehiculoRequest.php`

#### Controllers
- [ ] `VentaController.php` ⭐ **PRINCIPAL**
- [ ] `ServicioController.php`
- [ ] `TipoVehiculoController.php`
- [ ] `FacturaElectronicaController.php`

#### Routes API
- [ ] CRUD nuevas entidades
- [ ] Endpoints especializados ventas
- [ ] Integración rutas existentes

---

### **FASE 6: FRONTEND VUE** 🎨
**Duración**: 5-6 días

#### Types TypeScript
- [ ] Interfaces nuevas entidades
- [ ] Types ventas/facturación

#### Services Frontend
- [ ] `venta.service.ts` ⭐ **PRINCIPAL**
- [ ] `servicio.service.ts`
- [ ] `tipo-vehiculo.service.ts`

#### Stores Pinia
- [ ] `venta.ts` - Estado ventas
- [ ] `servicio.ts` - Catálogo servicios
- [ ] Actualizar stores existentes

#### Componentes Vue
- [ ] `VentaForm.vue` ⭐ **COMPONENTE PRINCIPAL**
- [ ] `GrillaVenta.vue` - Grilla productos/servicios
- [ ] `ServicioManager.vue` - Gestión servicios
- [ ] `TipoVehiculoManager.vue` - Gestión tipos

---

### **FASE 7: INTEGRACIÓN** 🔄
**Duración**: 1-2 días

#### Setup BD Nueva
- [ ] Configuración PostgreSQL
- [ ] Ejecutar migraciones optimizadas
- [ ] Ejecutar seeders

#### Providers
- [ ] `AppServiceProvider.php` - Nuevos bindings
- [ ] Registro dependencias

---

### **FASE 8: TESTING** 🧪
**Duración**: 2-3 días

#### Tests
- [ ] Unit tests services
- [ ] Feature tests API
- [ ] Integration tests ventas

#### Documentación
- [ ] README.md actualizado
- [ ] Documentar APIs
- [ ] Guía usuario

---

## 🏢 FACTURACIÓN ELECTRÓNICA SRI

### **Campos Obligatorios**
- RUC/RISE emisor
- Tipo documento (01: Factura)
- Secuencial (001-001-000000001)
- Fecha emisión
- Identificación comprador
- Razón social comprador
- Subtotal sin impuestos
- Subtotal 0% / 12%
- IVA
- Total

### **Estados SRI**
- `BORRADOR` - En construcción
- `AUTORIZADA` - Válida por SRI
- `RECHAZADA` - Error en SRI

---

## ⭐ COMPONENTES CORE DEL SISTEMA

### **1. VentaService.php**
**Responsabilidades principales:**
- Manejar grilla de productos/servicios
- Calcular totales dinámicamente
- Generar factura automáticamente
- Crear ingreso correspondiente
- Validar stock productos
- Mantener trazabilidad servicios

### **2. VentaForm.vue**
**Características principales:**
- Grilla interactiva productos/servicios
- Cálculo tiempo real totales
- Búsqueda productos/servicios
- Modificación precios servicios
- Selección empleado para servicios
- Selección vehículo/cliente

### **3. Servicio Model**
**Relaciones importantes:**
- `belongsTo(TipoVehiculo)` - Precios por tipo
- `hasMany(VentaDetalle)` - Ventas del servicio
- `hasMany(Lavado)` - Trazabilidad histórica

---

## 📊 MÉTRICAS DE ÉXITO

### **Funcionales**
- ✅ Ventas unificadas productos + servicios
- ✅ Facturación electrónica válida SRI
- ✅ Trazabilidad completa servicios
- ✅ Tipos vehículos dinámicos

### **Técnicas**
- ✅ 0% pérdida datos migración
- ✅ Tiempo respuesta < 500ms ventas
- ✅ Cobertura tests > 80%
- ✅ PostgreSQL optimizado

---

## 📅 CRONOGRAMA

**Total**: 19-25 días laborables (3.8-5 semanas)

```
Semana 1: Fases 1-2 (Migraciones + Modelos)
Semana 2: Fases 3-4 (Repositories + Services)  
Semana 3: Fase 5 (API Controllers)
Semana 4: Fase 6 (Frontend Vue)
Semana 5: Fases 7-8 (Integración + Testing)
```

---

## 🚨 NOTAS IMPORTANTES

### **Compatibilidad**
- Mantener funcionalidad actual lavados para historial
- APIs legacy durante transición
- Migración sin pérdida datos

### **Performance**
- Índices optimizados PostgreSQL
- Consultas eficientes grilla ventas
- Cache catálogo servicios

### **Seguridad**
- Validaciones robustas ventas
- Integridad referencial estricta
- Auditoría cambios precios

---

## 🎯 ESTADO ACTUAL

**Última actualización**: 18 de Julio, 2025  
**Fase actual**: FASE 1 - COMPLETADA ✅  
**Siguiente paso**: Fase 2 - Modelos Eloquent  
**Responsable**: Programador Senior + GitHub Copilot  

---

**📝 Nota**: Este documento es la guía principal del proyecto. Consultar en caso de dudas sobre arquitectura, cronograma o especificaciones técnicas.
