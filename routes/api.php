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
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\TestController;

// Rutas de autenticación Sanctum
Route::post('/login', [AuthController::class, 'loginApi']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logoutApi']);
Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'user']);

Route::middleware('auth:sanctum')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'apiData']); // Legacy endpoint
    Route::get('/dashboard/data', [DashboardController::class, 'getData']);
    Route::get('/dashboard/metricas', [DashboardController::class, 'getMetricas']);
    Route::get('/dashboard/actividad', [DashboardController::class, 'getActividadReciente']);
    Route::get('/dashboard/citas', [DashboardController::class, 'getProximasCitas']);
    Route::get('/dashboard/charts', [DashboardController::class, 'getChartData']);
    Route::get('/dashboard/alertas', [DashboardController::class, 'getAlertas']);
    Route::get('/dashboard/estadisticas', [DashboardController::class, 'getEstadisticas']);
    Route::get('/dashboard/analisis-financiero', [DashboardController::class, 'getAnalisisFinanciero']);
    Route::get('/dashboard/rendimiento-operativo', [DashboardController::class, 'getRendimientoOperativo']);
    Route::get('/dashboard/resumen-completo', [DashboardController::class, 'getResumenCompleto']);
    
    // Ruta de prueba
    Route::get('/test/data', [TestController::class, 'testData']);
    
    // CRUD de empleados
    Route::get('/empleados', [EmpleadoController::class, 'index']);
    Route::post('/empleados', [EmpleadoController::class, 'store']);
    Route::get('/empleados/{empleado}', [EmpleadoController::class, 'show']);
    Route::put('/empleados/{empleado}', [EmpleadoController::class, 'update']);
    Route::delete('/empleados/{empleado}', [EmpleadoController::class, 'destroy']);
    
    // Filtros de lavados por empleado
    Route::get('/empleados/{empleado}/lavados/dia/{fecha}', [EmpleadoController::class, 'lavadosPorDia']);
    Route::get('/empleados/{empleado}/lavados/semana/{fecha}', [EmpleadoController::class, 'lavadosPorSemana']);
    Route::get('/empleados/{empleado}/lavados/mes/{anio}/{mes}', [EmpleadoController::class, 'lavadosPorMes']);

    // CRUD de usuarios
    Route::get('/usuarios', [UserController::class, 'index']);
    Route::post('/usuarios', [UserController::class, 'store']);
    Route::get('/usuarios/{id}', [UserController::class, 'show']);
    Route::put('/usuarios/{id}', [UserController::class, 'update']);
    Route::delete('/usuarios/{id}', [UserController::class, 'destroy']);

    // CRUD de clientes
    Route::get('/clientes', [ClienteController::class, 'index']);
    Route::get('/clientes/all', [ClienteController::class, 'all']); // Para selects
    Route::get('/clientes/search', [ClienteController::class, 'search']); // Búsqueda
    Route::get('/clientes/stats', [ClienteController::class, 'stats']); // Estadísticas
    Route::post('/clientes', [ClienteController::class, 'store']);
    Route::get('/clientes/{id}', [ClienteController::class, 'show']);
    Route::put('/clientes/{id}', [ClienteController::class, 'update']);
    Route::patch('/clientes/{id}/toggle-activo', [ClienteController::class, 'toggleActivo']);
    Route::delete('/clientes/{id}', [ClienteController::class, 'destroy']);

    // CRUD de vehículos
    Route::get('/vehiculos', [VehiculoController::class, 'index']);
    Route::get('/vehiculos/all', [VehiculoController::class, 'all']); // Para selects
    Route::get('/vehiculos/stats', [VehiculoController::class, 'stats']); // Estadísticas
    Route::get('/vehiculos/cliente/{clienteId}', [VehiculoController::class, 'byCliente']); // Por cliente
    Route::post('/vehiculos', [VehiculoController::class, 'store']);
    Route::get('/vehiculos/{id}', [VehiculoController::class, 'show']);
    Route::put('/vehiculos/{id}', [VehiculoController::class, 'update']);
    Route::delete('/vehiculos/{id}', [VehiculoController::class, 'destroy']);

    // CRUD de lavados
    Route::get('/lavados', [LavadoController::class, 'index']);
    Route::post('/lavados', [LavadoController::class, 'store']);
    Route::get('/lavados/{id}', [LavadoController::class, 'show']);
    Route::put('/lavados/{id}', [LavadoController::class, 'update']);
    Route::delete('/lavados/{id}', [LavadoController::class, 'destroy']);

    // CRUD unificado de productos
    Route::get('/productos', [ProductoController::class, 'index']); // Todos los productos
    Route::get('/productos/metricas', [ProductoController::class, 'getMetricas']); // Métricas
    
    // Productos automotrices
    Route::get('/productos/automotrices', [ProductoController::class, 'getProductosAutomotrices']);
    Route::post('/productos/automotrices', [ProductoController::class, 'storeAutomotriz']);
    Route::get('/productos/automotrices/{id}', [ProductoController::class, 'showAutomotriz']);
    Route::put('/productos/automotrices/{id}', [ProductoController::class, 'updateAutomotriz']);
    Route::delete('/productos/automotrices/{id}', [ProductoController::class, 'destroyAutomotriz']);
    Route::put('/productos/automotrices/{id}/stock', [ProductoController::class, 'updateStockAutomotriz']);
    
    // Productos de despensa
    Route::get('/productos/despensa', [ProductoController::class, 'getProductosDespensa']);
    Route::post('/productos/despensa', [ProductoController::class, 'storeDespensa']);
    Route::get('/productos/despensa/{id}', [ProductoController::class, 'showDespensa']);
    Route::put('/productos/despensa/{id}', [ProductoController::class, 'updateDespensa']);
    Route::delete('/productos/despensa/{id}', [ProductoController::class, 'destroyDespensa']);
    Route::put('/productos/despensa/{id}/stock', [ProductoController::class, 'updateStockDespensa']);

    // CRUD de proveedores
    Route::get('/proveedores', [ProveedorController::class, 'index']);
    Route::post('/proveedores', [ProveedorController::class, 'store']);
    Route::get('/proveedores/{id}', [ProveedorController::class, 'show']);
    Route::put('/proveedores/{id}', [ProveedorController::class, 'update']);
    Route::delete('/proveedores/{id}', [ProveedorController::class, 'destroy']);
    
    // Gestión de deudas y pagos a proveedores específicos
    Route::get('/proveedores/{id}/deuda', [ProveedorController::class, 'verDeuda']);
    Route::get('/proveedores/{id}/pagos', [ProveedorController::class, 'pagos']);
    
    // Gestión consolidada de pagos (todos los proveedores)
    Route::get('/proveedores/pagos', [ProveedorController::class, 'getAllPagos']);
    Route::post('/proveedores/pagos', [ProveedorController::class, 'createPago']);
    Route::get('/proveedores/pagos/metricas', [ProveedorController::class, 'getMetricasPagos']);
    Route::get('/proveedores/pagos/{pagoId}', [ProveedorController::class, 'getPago']);
    Route::put('/proveedores/pagos/{pagoId}', [ProveedorController::class, 'updatePago']);
    Route::delete('/proveedores/pagos/{pagoId}', [ProveedorController::class, 'deletePago']);

    // CRUD de ingresos
    Route::get('/ingresos', [IngresoController::class, 'index']);
    Route::post('/ingresos', [IngresoController::class, 'store']);
    Route::get('/ingresos/{id}', [IngresoController::class, 'show']);
    Route::put('/ingresos/{id}', [IngresoController::class, 'update']);
    Route::delete('/ingresos/{id}', [IngresoController::class, 'destroy']);
    Route::get('/ingresos/metricas', [IngresoController::class, 'getMetricas']);

    // CRUD de egresos
    Route::get('/egresos', [EgresoController::class, 'index']);
    Route::post('/egresos', [EgresoController::class, 'store']);
    Route::get('/egresos/{id}', [EgresoController::class, 'show']);
    Route::put('/egresos/{id}', [EgresoController::class, 'update']);
    Route::delete('/egresos/{id}', [EgresoController::class, 'destroy']);
    Route::get('/egresos/metricas', [EgresoController::class, 'getMetricas']);

    // CRUD de gastos generales
    Route::get('/gastos-generales', [GastoGeneralController::class, 'index']);
    Route::post('/gastos-generales', [GastoGeneralController::class, 'store']);
    Route::get('/gastos-generales/{id}', [GastoGeneralController::class, 'show']);
    Route::put('/gastos-generales/{id}', [GastoGeneralController::class, 'update']);
    Route::delete('/gastos-generales/{id}', [GastoGeneralController::class, 'destroy']);
    Route::get('/gastos-generales/metricas', [GastoGeneralController::class, 'getMetricas']);

    // Facturación
    Route::get('/facturas', [FacturaController::class, 'index']);
    Route::post('/facturas', [FacturaController::class, 'store']);
    Route::get('/facturas/{id}', [FacturaController::class, 'show']);
    Route::put('/facturas/{id}', [FacturaController::class, 'update']);
    Route::delete('/facturas/{id}', [FacturaController::class, 'destroy']);
    Route::get('/facturas/numero/{numeroFactura}', [FacturaController::class, 'findByNumero']);
    Route::get('/facturas/metricas', [FacturaController::class, 'getMetricas']);

    // Ventas
    Route::get('/ventas', [VentaController::class, 'index']);
    Route::post('/ventas', [VentaController::class, 'store']);
    Route::get('/ventas/metricas', [VentaController::class, 'getMetricas']);
    Route::get('/ventas/productos-disponibles', [VentaController::class, 'getProductosDisponibles']);
    Route::get('/ventas/clientes', [VentaController::class, 'getClientes']);

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

    // CRUD de usuarios
    Route::get('/usuarios', [UserController::class, 'index']);
    Route::post('/usuarios', [UserController::class, 'store']);
    Route::get('/usuarios/activos', [UserController::class, 'getActiveUsers']);
    Route::get('/usuarios/estadisticas', [UserController::class, 'getStats']);
    Route::get('/usuarios/{id}', [UserController::class, 'show']);
    Route::put('/usuarios/{id}', [UserController::class, 'update']);
    Route::delete('/usuarios/{id}', [UserController::class, 'destroy']);
    Route::put('/usuarios/{id}/password', [UserController::class, 'updatePassword']);
    Route::put('/usuarios/{id}/reset-password', [UserController::class, 'resetPassword']);
    Route::put('/usuarios/{id}/verify-email', [UserController::class, 'verifyEmail']);

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

    // Ruta de prueba para TestController
    Route::get('/test', [TestController::class, 'index']);
});
