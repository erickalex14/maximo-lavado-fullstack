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

// Rutas de autenticación Sanctum
Route::post('/login', [AuthController::class, 'loginApi']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logoutApi']);

// Endpoint para obtener usuario autenticado
// NOTA IMPORTANTE: No usar '/user' como ruta - conflicto con rutas internas de Laravel/Sanctum
Route::middleware('auth:sanctum')->get('/usuario', [AuthController::class, 'user']);

Route::middleware('auth:sanctum')->group(function () {
    // Dashboard
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
    
    
    // CRUD de empleados
    Route::get('/empleados', [EmpleadoController::class, 'index']);
    Route::get('/empleados/trashed', [EmpleadoController::class, 'trashed']); // Empleados eliminados
    Route::post('/empleados', [EmpleadoController::class, 'store']);
    Route::get('/empleados/{empleado}', [EmpleadoController::class, 'show']);
    Route::put('/empleados/{empleado}', [EmpleadoController::class, 'update']);
    Route::put('/empleados/{empleado}/restore', [EmpleadoController::class, 'restore']); // Restaurar empleado
    Route::delete('/empleados/{empleado}', [EmpleadoController::class, 'destroy']);
    
    // Filtros de lavados por empleado
    Route::get('/empleados/{empleado}/lavados/dia/{fecha}', [EmpleadoController::class, 'lavadosPorDia']);
    Route::get('/empleados/{empleado}/lavados/semana/{fecha}', [EmpleadoController::class, 'lavadosPorSemana']);
    Route::get('/empleados/{empleado}/lavados/mes/{anio}/{mes}', [EmpleadoController::class, 'lavadosPorMes']);

    // CRUD de usuarios - Gestión completa
    Route::get('/usuarios', [UserController::class, 'index']);
    Route::post('/usuarios', [UserController::class, 'store']);
    Route::get('/usuarios/activos', [UserController::class, 'getActiveUsers']);
    Route::get('/usuarios/estadisticas', [UserController::class, 'getStats']);
    Route::get('/usuarios/{id}', [UserController::class, 'show']);
    Route::put('/usuarios/{id}', [UserController::class, 'update']);
    Route::delete('/usuarios/{id}', [UserController::class, 'destroy']);
    Route::put('/usuarios/{id}/restore', [UserController::class, 'restore']); // Restaurar usuario
    Route::get('/usuarios/trashed', [UserController::class, 'trashed']); // Usuarios eliminados
    Route::put('/usuarios/{id}/password', [UserController::class, 'updatePassword']);
    Route::put('/usuarios/{id}/reset-password', [UserController::class, 'resetPassword']);
    Route::put('/usuarios/{id}/verify-email', [UserController::class, 'verifyEmail']);

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
    Route::put('/clientes/{id}/restore', [ClienteController::class, 'restore']); // Restaurar cliente
    Route::get('/clientes/trashed', [ClienteController::class, 'trashed']); // Clientes eliminados

    // CRUD de vehículos
    Route::get('/vehiculos', [VehiculoController::class, 'index']);
    Route::get('/vehiculos/all', [VehiculoController::class, 'all']); // Para selects
    Route::get('/vehiculos/stats', [VehiculoController::class, 'stats']); // Estadísticas
    Route::get('/vehiculos/cliente/{clienteId}', [VehiculoController::class, 'byCliente']); // Por cliente
    Route::post('/vehiculos', [VehiculoController::class, 'store']);
    Route::get('/vehiculos/{id}', [VehiculoController::class, 'show']);
    Route::put('/vehiculos/{id}', [VehiculoController::class, 'update']);
    Route::delete('/vehiculos/{id}', [VehiculoController::class, 'destroy']);
    Route::put('/vehiculos/{id}/restore', [VehiculoController::class, 'restore']); // Restaurar vehículo
    Route::get('/vehiculos/trashed', [VehiculoController::class, 'trashed']); // Vehículos eliminados

    // CRUD de lavados
    Route::get('/lavados', [LavadoController::class, 'index']);
    Route::post('/lavados', [LavadoController::class, 'store']);
    Route::get('/lavados/stats', [LavadoController::class, 'getStats']);
    Route::get('/lavados/trashed', [LavadoController::class, 'trashed']); // Lavados eliminados
    Route::get('/lavados/empleado/{empleadoId}', [LavadoController::class, 'getByEmpleado']);
    Route::get('/lavados/vehiculo/{vehiculoId}', [LavadoController::class, 'getByVehiculo']);
    Route::get('/lavados/dia', [LavadoController::class, 'getByDay']);
    Route::get('/lavados/semana', [LavadoController::class, 'getByWeek']);
    Route::get('/lavados/mes', [LavadoController::class, 'getByMonth']);
    Route::get('/lavados/anio', [LavadoController::class, 'getByYear']);
    Route::get('/lavados/{id}', [LavadoController::class, 'show']);
    Route::put('/lavados/{id}', [LavadoController::class, 'update']);
    Route::put('/lavados/{id}/restore', [LavadoController::class, 'restore']); // Restaurar lavado
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
    Route::put('/productos/automotrices/{id}/restore', [ProductoController::class, 'restoreAutomotriz']); // Restaurar producto automotriz
    Route::get('/productos/automotrices/trashed', [ProductoController::class, 'trashedAutomotriz']); // Productos automotrices eliminados
    Route::put('/productos/automotrices/{id}/stock', [ProductoController::class, 'updateStockAutomotriz']);
    
    // Productos de despensa
    Route::get('/productos/despensa', [ProductoController::class, 'getProductosDespensa']);
    Route::post('/productos/despensa', [ProductoController::class, 'storeDespensa']);
    Route::get('/productos/despensa/{id}', [ProductoController::class, 'showDespensa']);
    Route::put('/productos/despensa/{id}', [ProductoController::class, 'updateDespensa']);
    Route::delete('/productos/despensa/{id}', [ProductoController::class, 'destroyDespensa']);
    Route::put('/productos/despensa/{id}/restore', [ProductoController::class, 'restoreDespensa']); // Restaurar producto despensa
    Route::get('/productos/despensa/trashed', [ProductoController::class, 'trashedDespensa']); // Productos despensa eliminados
    Route::put('/productos/despensa/{id}/stock', [ProductoController::class, 'updateStockDespensa']);

    // CRUD de proveedores
    Route::get('/proveedores', [ProveedorController::class, 'index']);
    Route::post('/proveedores', [ProveedorController::class, 'store']);
    Route::get('/proveedores/{id}', [ProveedorController::class, 'show']);
    Route::put('/proveedores/{id}', [ProveedorController::class, 'update']);
    Route::delete('/proveedores/{id}', [ProveedorController::class, 'destroy']);
    Route::put('/proveedores/{id}/restore', [ProveedorController::class, 'restore']); // Restaurar proveedor
    Route::get('/proveedores/trashed', [ProveedorController::class, 'trashed']); // Proveedores eliminados
    
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
    Route::get('/ingresos/trashed', [IngresoController::class, 'trashed']); // Ingresos eliminados
    Route::get('/ingresos/{id}', [IngresoController::class, 'show']);
    Route::put('/ingresos/{id}', [IngresoController::class, 'update']);
    Route::put('/ingresos/{id}/restore', [IngresoController::class, 'restore']); // Restaurar ingreso
    Route::delete('/ingresos/{id}', [IngresoController::class, 'destroy']);
    Route::get('/ingresos/metricas', [IngresoController::class, 'getMetricas']);

    // CRUD de egresos
    Route::get('/egresos', [EgresoController::class, 'index']);
    Route::post('/egresos', [EgresoController::class, 'store']);
    Route::get('/egresos/trashed', [EgresoController::class, 'trashed']); // Egresos eliminados
    Route::get('/egresos/{id}', [EgresoController::class, 'show']);
    Route::put('/egresos/{id}', [EgresoController::class, 'update']);
    Route::put('/egresos/{id}/restore', [EgresoController::class, 'restore']); // Restaurar egreso
    Route::delete('/egresos/{id}', [EgresoController::class, 'destroy']);
    Route::get('/egresos/metricas', [EgresoController::class, 'getMetricas']);

    // CRUD de gastos generales
    Route::get('/gastos-generales', [GastoGeneralController::class, 'index']);
    Route::post('/gastos-generales', [GastoGeneralController::class, 'store']);
    Route::get('/gastos-generales/trashed', [GastoGeneralController::class, 'trashed']); // Gastos eliminados
    Route::get('/gastos-generales/{id}', [GastoGeneralController::class, 'show']);
    Route::put('/gastos-generales/{id}', [GastoGeneralController::class, 'update']);
    Route::put('/gastos-generales/{id}/restore', [GastoGeneralController::class, 'restore']); // Restaurar gasto
    Route::delete('/gastos-generales/{id}', [GastoGeneralController::class, 'destroy']);
    Route::get('/gastos-generales/metricas', [GastoGeneralController::class, 'getMetricas']);

    // Facturación
    Route::get('/facturas', [FacturaController::class, 'index']);
    Route::post('/facturas', [FacturaController::class, 'store']);
    Route::get('/facturas/trashed', [FacturaController::class, 'trashed']); // Facturas eliminadas
    Route::get('/facturas/{id}', [FacturaController::class, 'show']);
    Route::put('/facturas/{id}', [FacturaController::class, 'update']);
    Route::put('/facturas/{id}/restore', [FacturaController::class, 'restore']); // Restaurar factura
    Route::delete('/facturas/{id}', [FacturaController::class, 'destroy']);
    Route::get('/facturas/numero/{numeroFactura}', [FacturaController::class, 'findByNumero']);
    Route::get('/facturas/metricas', [FacturaController::class, 'getMetricas']);

    // Ventas - Gestión consolidada
    Route::get('/ventas', [VentaController::class, 'index']);
    Route::get('/ventas/trashed', [VentaController::class, 'trashed']); // Todas las ventas eliminadas
    Route::get('/ventas/metricas', [VentaController::class, 'getMetricas']);
    Route::get('/ventas/productos-disponibles', [VentaController::class, 'getProductosDisponibles']);
    Route::get('/ventas/clientes', [VentaController::class, 'getClientes']);

    // Ventas de productos automotrices
    Route::get('/ventas/automotrices', [VentaController::class, 'getVentasAutomotrices']);
    Route::get('/ventas/automotrices/trashed', [VentaController::class, 'getTrashedVentasAutomotrices']); // Eliminadas
    Route::post('/ventas/automotrices', [VentaController::class, 'createVentaAutomotriz']);
    Route::get('/ventas/automotrices/metricas', [VentaController::class, 'getMetricasAutomotrices']);
    Route::get('/ventas/automotrices/{id}', [VentaController::class, 'getVentaAutomotriz']);
    Route::put('/ventas/automotrices/{id}', [VentaController::class, 'updateVentaAutomotriz']);
    Route::put('/ventas/automotrices/{id}/restore', [VentaController::class, 'restoreVentaAutomotriz']); // Restaurar
    Route::delete('/ventas/automotrices/{id}', [VentaController::class, 'deleteVentaAutomotriz']);

    // Ventas de productos de despensa
    Route::get('/ventas/despensa', [VentaController::class, 'getVentasDespensa']);
    Route::get('/ventas/despensa/trashed', [VentaController::class, 'getTrashedVentasDespensa']); // Eliminadas
    Route::post('/ventas/despensa', [VentaController::class, 'createVentaDespensa']);
    Route::get('/ventas/despensa/metricas', [VentaController::class, 'getMetricasDespensa']);
    Route::get('/ventas/despensa/{id}', [VentaController::class, 'getVentaDespensa']);
    Route::put('/ventas/despensa/{id}', [VentaController::class, 'updateVentaDespensa']);
    Route::put('/ventas/despensa/{id}/restore', [VentaController::class, 'restoreVentaDespensa']); // Restaurar
    Route::delete('/ventas/despensa/{id}', [VentaController::class, 'deleteVentaDespensa']);

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
