<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\LavadoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\EgresoController;
use App\Http\Controllers\GastoGeneralController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\UserController;

// ========================================================
// 🔥 SISTEMA UNIFICADO V2 - CONTROLADORES EXISTENTES
// ========================================================
use App\Http\Controllers\TipoVehiculoController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\FacturaElectronicaController;

// Rutas de autenticación Sanctum
Route::post('/login', [AuthController::class, 'loginApi']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logoutApi']);

// Endpoint para obtener usuario autenticado
Route::middleware('auth:sanctum')->get('/usuario', [AuthController::class, 'user']);


Route::middleware('auth:sanctum')->group(function () {
    // Dashboard
    Route::get('/dashboard/datos', [DashboardController::class, 'getData']);
    Route::get('/dashboard/metricas', [DashboardController::class, 'getMetricas']);
    Route::get('/dashboard/actividad', [DashboardController::class, 'getActividadReciente']);
    Route::get('/dashboard/citas', [DashboardController::class, 'getProximasCitas']);
    Route::get('/dashboard/charts', [DashboardController::class, 'getChartData']);
    Route::get('/dashboard/alertas', [DashboardController::class, 'getAlertas']);
    Route::get('/dashboard/estadisticas', [DashboardController::class, 'getEstadisticas']);
    Route::get('/dashboard/analisis-financiero', [DashboardController::class, 'getAnalisisFinanciero']);
    Route::get('/dashboard/rendimiento-operativo', [DashboardController::class, 'getRendimientoOperativo']);
    Route::get('/dashboard/resumen-completo', [DashboardController::class, 'getResumenCompleto']);

    // ========================================================
    // 🔥 SISTEMA V2 - RUTAS PRINCIPALES
    // ========================================================

    // 🚗 TIPOS DE VEHÍCULOS - Gestión dinámica
    Route::prefix('tipos-vehiculos')->group(function () {
        Route::get('/', [TipoVehiculoController::class, 'index']);
        Route::get('/all', [TipoVehiculoController::class, 'all']);
        Route::get('/stats', [TipoVehiculoController::class, 'stats']);
        Route::post('/', [TipoVehiculoController::class, 'store']);
        Route::get('/{id}', [TipoVehiculoController::class, 'show']);
        Route::put('/{id}', [TipoVehiculoController::class, 'update']);
        Route::patch('/{id}/toggle-activo', [TipoVehiculoController::class, 'toggleActivo']);
        Route::delete('/{id}', [TipoVehiculoController::class, 'destroy']);
        Route::put('/{id}/restore', [TipoVehiculoController::class, 'restore']);
        Route::get('/trashed/list', [TipoVehiculoController::class, 'trashed']);
    });

    // 🛠️ SERVICIOS - Catálogo unificado
    Route::prefix('servicios')->group(function () {
        Route::get('/', [ServicioController::class, 'index']);
        Route::get('/all', [ServicioController::class, 'all']);
        Route::get('/activos', [ServicioController::class, 'activos']);
        Route::get('/stats', [ServicioController::class, 'stats']);
        Route::post('/', [ServicioController::class, 'store']);
        Route::get('/{id}', [ServicioController::class, 'show']);
        Route::put('/{id}', [ServicioController::class, 'update']);
        Route::patch('/{id}/toggle-activo', [ServicioController::class, 'toggleActivo']);
        Route::delete('/{id}', [ServicioController::class, 'destroy']);
        Route::put('/{id}/restore', [ServicioController::class, 'restore']);
        Route::get('/trashed/list', [ServicioController::class, 'trashed']);
        
        // Precios por tipo de vehículo
        Route::get('/{id}/precios', [ServicioController::class, 'getPrecios']);
        Route::put('/{id}/precios/{tipoVehiculoId}', [ServicioController::class, 'updatePrecio']);
        Route::delete('/{id}/precios/{tipoVehiculoId}', [ServicioController::class, 'deletePrecio']);
    });

    // 🛒 VENTAS UNIFICADAS - Sistema principal de ventas
    Route::prefix('ventas')->group(function () {
        Route::get('/', [VentaController::class, 'index']);
        Route::get('/all', [VentaController::class, 'all']);
        Route::get('/stats', [VentaController::class, 'stats']);
        Route::get('/del-dia', [VentaController::class, 'ventasDelDia']);
        Route::post('/', [VentaController::class, 'store']);
        Route::post('/debug-lavados', [VentaController::class, 'debugLavados']);
        Route::get('/{id}', [VentaController::class, 'show']);
        Route::put('/{id}', [VentaController::class, 'update']);
        Route::delete('/{id}', [VentaController::class, 'destroy']);
        Route::put('/{id}/restore', [VentaController::class, 'restore']);
        Route::get('/trashed/list', [VentaController::class, 'trashed']);
        
        // Consultas específicas
        Route::get('/cliente/{clienteId}', [VentaController::class, 'porCliente']);
        Route::get('/empleado/{empleadoId}', [VentaController::class, 'porEmpleado']);
        Route::get('/fecha/{fecha}', [VentaController::class, 'porFecha']);
        Route::get('/rango-fechas', [VentaController::class, 'porRangoFechas']);
        
        // Productos y servicios más vendidos
        Route::get('/productos-mas-vendidos', [VentaController::class, 'productosMasVendidos']);
        Route::get('/servicios-mas-vendidos', [VentaController::class, 'serviciosMasVendidos']);
    });

    // 🧾 LAVADOS - Sistema de auditoría simple
    Route::prefix('lavados')->group(function () {
        Route::get('/', [LavadoController::class, 'index']);
        // Ajustar nombres a métodos reales del controlador
        Route::get('/stats', [LavadoController::class, 'getStats']);
        // Recientes (si se implementa en el controlador más tarde)
        // Route::get('/recientes', [LavadoController::class, 'getRecientes']);
        Route::post('/', [LavadoController::class, 'store']);
        Route::get('/{id}', [LavadoController::class, 'show']);
        Route::put('/{id}', [LavadoController::class, 'update']);
        Route::delete('/{id}', [LavadoController::class, 'destroy']);
        // Route::put('/{id}/restore', [LavadoController::class, 'restore']); // no existe método restore actualmente
        Route::get('/trashed/list', [LavadoController::class, 'trashed']);

        // Consultas por entidad (ajustar a métodos existentes si se implementan)
        // Route::get('/cliente/{clienteId}', [LavadoController::class, 'getByCliente']);
        Route::get('/empleado/{empleadoId}', [LavadoController::class, 'getByEmpleado']);
        Route::get('/vehiculo/{vehiculoId}', [LavadoController::class, 'getByVehiculo']);
        // Route::get('/servicio/{servicioId}', [LavadoController::class, 'getByServicio']);

        // Consultas por fecha
        // Reemplazamos segment variable por query param en getByDay; mantener rutas legacy desactivadas si no existen
        // Route::get('/dia/{fecha}', [LavadoController::class, 'getByDay']);
    });

    // 🧾 FACTURAS ELECTRÓNICAS - Sistema SRI Ecuador
    Route::prefix('facturas-electronicas')->group(function () {
        Route::get('/', [FacturaElectronicaController::class, 'index']);
        Route::post('/', [FacturaElectronicaController::class, 'store']);
        Route::get('/{id}', [FacturaElectronicaController::class, 'show']);
        Route::put('/{id}', [FacturaElectronicaController::class, 'update']);
        
        // Procesamiento SRI
        Route::post('/{id}/procesar-sri', [FacturaElectronicaController::class, 'procesarConSRI']);
        Route::post('/{id}/reenviar', [FacturaElectronicaController::class, 'reenviarAlSRI']);
        Route::post('/{id}/anular', [FacturaElectronicaController::class, 'anular']);
        
        // Documentos
        Route::get('/{id}/xml', [FacturaElectronicaController::class, 'getXML']);
        Route::get('/{id}/pdf', [FacturaElectronicaController::class, 'downloadPDF']);
        
        // Consultas específicas
        Route::get('/venta/{ventaId}', [FacturaElectronicaController::class, 'getByVenta']);
        Route::get('/pendientes-sri', [FacturaElectronicaController::class, 'getPendientesSRI']);
        Route::get('/estadisticas', [FacturaElectronicaController::class, 'getEstadisticas']);
        
        // Operaciones en lote
        Route::post('/procesar-lote', [FacturaElectronicaController::class, 'procesarLote']);
        Route::get('/validar-sri', [FacturaElectronicaController::class, 'validarConexionSRI']);
    });

    // ========================================================
    // 📋 SISTEMA EXISTENTE - CONTROLADORES LEGACY
    // ========================================================
    
    // CRUD de empleados
    Route::get('/empleados', [EmpleadoController::class, 'index']);
    Route::get('/empleados/trashed', [EmpleadoController::class, 'trashed']);
    Route::post('/empleados', [EmpleadoController::class, 'store']);
    Route::get('/empleados/{empleado}', [EmpleadoController::class, 'show']);
    Route::put('/empleados/{empleado}', [EmpleadoController::class, 'update']);
    Route::put('/empleados/{empleado}/restore', [EmpleadoController::class, 'restore']);
    Route::delete('/empleados/{empleado}', [EmpleadoController::class, 'destroy']);
    
    // Consultas de empleados con lavados
    Route::get('/empleados/{empleado}/lavados/dia/{fecha}', [EmpleadoController::class, 'lavadosPorDia']);
    Route::get('/empleados/{empleado}/lavados/semana/{fecha}', [EmpleadoController::class, 'lavadosPorSemana']);
    Route::get('/empleados/{empleado}/lavados/mes/{anio}/{mes}', [EmpleadoController::class, 'lavadosPorMes']);

    // CRUD de usuarios
    Route::get('/usuarios', [UserController::class, 'index']);
    Route::post('/usuarios', [UserController::class, 'store']);
    Route::get('/usuarios/activos', [UserController::class, 'getActiveUsers']);
    Route::get('/usuarios/estadisticas', [UserController::class, 'getStats']);
    Route::get('/usuarios/{id}', [UserController::class, 'show']);
    Route::put('/usuarios/{id}', [UserController::class, 'update']);
    Route::delete('/usuarios/{id}', [UserController::class, 'destroy']);
    Route::put('/usuarios/{id}/restore', [UserController::class, 'restore']);
    Route::get('/usuarios/trashed', [UserController::class, 'trashed']);
    Route::put('/usuarios/{id}/password', [UserController::class, 'updatePassword']);
    Route::put('/usuarios/{id}/reset-password', [UserController::class, 'resetPassword']);
    Route::put('/usuarios/{id}/verify-email', [UserController::class, 'verifyEmail']);

    // CRUD de clientes
    Route::get('/clientes', [ClienteController::class, 'index']);
    Route::get('/clientes/all', [ClienteController::class, 'all']);
    Route::get('/clientes/search', [ClienteController::class, 'search']);
    Route::get('/clientes/stats', [ClienteController::class, 'stats']);
    Route::get('/clientes/trashed', [ClienteController::class, 'trashed']);
    Route::post('/clientes', [ClienteController::class, 'store']);
    Route::get('/clientes/{id}', [ClienteController::class, 'show']);
    Route::put('/clientes/{id}', [ClienteController::class, 'update']);
    Route::patch('/clientes/{id}/toggle-activo', [ClienteController::class, 'toggleActivo']);
    Route::delete('/clientes/{id}', [ClienteController::class, 'destroy']);
    Route::put('/clientes/{id}/restore', [ClienteController::class, 'restore']);

    // CRUD de vehículos
    Route::get('/vehiculos', [VehiculoController::class, 'index']);
    Route::get('/vehiculos/all', [VehiculoController::class, 'all']);
    Route::get('/vehiculos/stats', [VehiculoController::class, 'stats']);
    Route::get('/vehiculos/trashed', [VehiculoController::class, 'trashed']);
    Route::get('/vehiculos/cliente/{clienteId}', [VehiculoController::class, 'byCliente']);
    Route::post('/vehiculos', [VehiculoController::class, 'store']);
    Route::get('/vehiculos/{id}', [VehiculoController::class, 'show']);
    Route::put('/vehiculos/{id}', [VehiculoController::class, 'update']);
    Route::delete('/vehiculos/{id}', [VehiculoController::class, 'destroy']);
    Route::put('/vehiculos/{id}/restore', [VehiculoController::class, 'restore']);

    // CRUD unificado de productos
    Route::get('/productos', [ProductoController::class, 'index']);
    Route::get('/productos/metricas', [ProductoController::class, 'getMetricas']);
    
    // Productos automotrices
    Route::get('/productos/automotrices', [ProductoController::class, 'getProductosAutomotrices']);
    Route::get('/productos/automotrices/trashed', [ProductoController::class, 'trashedAutomotriz']);
    Route::post('/productos/automotrices', [ProductoController::class, 'storeAutomotriz']);
    Route::get('/productos/automotrices/{id}', [ProductoController::class, 'showAutomotriz']);
    Route::put('/productos/automotrices/{id}', [ProductoController::class, 'updateAutomotriz']);
    Route::put('/productos/automotrices/{id}/restore', [ProductoController::class, 'restoreAutomotriz']);
    Route::put('/productos/automotrices/{id}/stock', [ProductoController::class, 'updateStockAutomotriz']);
    Route::delete('/productos/automotrices/{id}', [ProductoController::class, 'destroyAutomotriz']);
    
    // Productos de despensa
    Route::get('/productos/despensa', [ProductoController::class, 'getProductosDespensa']);
    Route::get('/productos/despensa/trashed', [ProductoController::class, 'trashedDespensa']);
    Route::post('/productos/despensa', [ProductoController::class, 'storeDespensa']);
    Route::get('/productos/despensa/{id}', [ProductoController::class, 'showDespensa']);
    Route::put('/productos/despensa/{id}', [ProductoController::class, 'updateDespensa']);
    Route::put('/productos/despensa/{id}/restore', [ProductoController::class, 'restoreDespensa']);
    Route::put('/productos/despensa/{id}/stock', [ProductoController::class, 'updateStockDespensa']);
    Route::delete('/productos/despensa/{id}', [ProductoController::class, 'destroyDespensa']);

    // CRUD de proveedores
    Route::get('/proveedores', [ProveedorController::class, 'index']);
    Route::get('/proveedores/trashed', [ProveedorController::class, 'trashed']);
    Route::get('/proveedores/pagos', [ProveedorController::class, 'getAllPagos']);
    Route::get('/proveedores/pagos/metricas', [ProveedorController::class, 'getMetricasPagos']);
    Route::post('/proveedores', [ProveedorController::class, 'store']);
    Route::post('/proveedores/pagos', [ProveedorController::class, 'createPago']);
    Route::get('/proveedores/{id}', [ProveedorController::class, 'show']);
    Route::put('/proveedores/{id}', [ProveedorController::class, 'update']);
    Route::delete('/proveedores/{id}', [ProveedorController::class, 'destroy']);
    Route::put('/proveedores/{id}/restore', [ProveedorController::class, 'restore']);
    
    // Gestión de deudas y pagos a proveedores
    Route::get('/proveedores/{id}/deuda', [ProveedorController::class, 'verDeuda']);
    Route::get('/proveedores/{id}/pagos', [ProveedorController::class, 'pagos']);
    Route::get('/proveedores/pagos/{pagoId}', [ProveedorController::class, 'getPago']);
    Route::put('/proveedores/pagos/{pagoId}', [ProveedorController::class, 'updatePago']);
    Route::delete('/proveedores/pagos/{pagoId}', [ProveedorController::class, 'deletePago']);

    // CRUD de ingresos
    Route::get('/ingresos', [IngresoController::class, 'index']);
    Route::post('/ingresos', [IngresoController::class, 'store']);
    Route::get('/ingresos/trashed', [IngresoController::class, 'trashed']);
    Route::get('/ingresos/{id}', [IngresoController::class, 'show']);
    Route::put('/ingresos/{id}', [IngresoController::class, 'update']);
    Route::put('/ingresos/{id}/restore', [IngresoController::class, 'restore']);
    Route::delete('/ingresos/{id}', [IngresoController::class, 'destroy']);
    Route::get('/ingresos/metricas', [IngresoController::class, 'getMetricas']);

    // CRUD de egresos
    Route::get('/egresos', [EgresoController::class, 'index']);
    Route::post('/egresos', [EgresoController::class, 'store']);
    Route::get('/egresos/trashed', [EgresoController::class, 'trashed']);
    Route::get('/egresos/{id}', [EgresoController::class, 'show']);
    Route::put('/egresos/{id}', [EgresoController::class, 'update']);
    Route::put('/egresos/{id}/restore', [EgresoController::class, 'restore']);
    Route::delete('/egresos/{id}', [EgresoController::class, 'destroy']);
    Route::get('/egresos/metricas', [EgresoController::class, 'getMetricas']);

    // CRUD de gastos generales
    Route::get('/gastos-generales', [GastoGeneralController::class, 'index']);
    Route::post('/gastos-generales', [GastoGeneralController::class, 'store']);
    Route::get('/gastos-generales/trashed', [GastoGeneralController::class, 'trashed']);
    Route::get('/gastos-generales/{id}', [GastoGeneralController::class, 'show']);
    Route::put('/gastos-generales/{id}', [GastoGeneralController::class, 'update']);
    Route::put('/gastos-generales/{id}/restore', [GastoGeneralController::class, 'restore']);
    Route::delete('/gastos-generales/{id}', [GastoGeneralController::class, 'destroy']);
    Route::get('/gastos-generales/metricas', [GastoGeneralController::class, 'getMetricas']);

    // Reportes
    Route::get('/reportes', [ReporteController::class, 'index']);
    Route::get('/reportes/ventas', [ReporteController::class, 'reporteVentas']);
    Route::get('/reportes/lavados', [ReporteController::class, 'reporteLavados']);
    Route::get('/reportes/ingresos', [ReporteController::class, 'reporteIngresos']);
    Route::get('/reportes/egresos', [ReporteController::class, 'reporteEgresos']);
    Route::get('/reportes/facturas', [ReporteController::class, 'reporteFacturas']);
    Route::get('/reportes/empleados', [ReporteController::class, 'reporteEmpleados']);
    Route::get('/reportes/productos', [ReporteController::class, 'reporteProductos']);
    Route::get('/reportes/clientes', [ReporteController::class, 'reporteClientes']);
    Route::get('/reportes/financiero', [ReporteController::class, 'reporteFinanciero']);
    Route::get('/reportes/balance', [ReporteController::class, 'reporteBalance']);
    Route::get('/reportes/completo', [ReporteController::class, 'reporteCompleto']);

    // Balance y Análisis Financiero
    Route::get('/balance/general', [BalanceController::class, 'balanceGeneral']);
    Route::get('/balance/categorias', [BalanceController::class, 'balancePorCategoria']);
    Route::get('/balance/mensual', [BalanceController::class, 'balancePorMes']);
    Route::get('/balance/trimestral', [BalanceController::class, 'balancePorTrimestre']);
    Route::get('/balance/anual', [BalanceController::class, 'balanceAnual']);
    Route::get('/balance/flujo-caja', [BalanceController::class, 'flujoCaja']);
    Route::get('/balance/indicadores', [BalanceController::class, 'indicadoresFinancieros']);
    Route::get('/balance/comparativo', [BalanceController::class, 'comparativoMensual']);
    Route::get('/balance/proyeccion', [BalanceController::class, 'proyeccion']);
    Route::get('/balance/resumen', [BalanceController::class, 'resumenCompleto']);
});
