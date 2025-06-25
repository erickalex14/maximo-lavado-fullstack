<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\LavadoController;
use App\Http\Controllers\ProductoAutomotrizController;
use App\Http\Controllers\ProductoDespensaController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\EgresoController;
use App\Http\Controllers\GastoGeneralController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TestController;

// Rutas de autenticación Sanctum
Route::post('/login', [AuthController::class, 'loginApi']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logoutApi']);
Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'user']);

Route::middleware('auth:sanctum')->group(function () {
    // Dashboard
    Route::get('/dashboard/data', [DashboardController::class, 'getData']);
    Route::get('/dashboard/charts', [DashboardController::class, 'getChartData']);
    
    // Ruta de prueba
    Route::get('/test/data', [TestController::class, 'testData']);
    
    // CRUD de empleados
    Route::get('/empleados', [EmpleadoController::class, 'index']);
    Route::post('/empleados', [EmpleadoController::class, 'store']);
    Route::get('/empleados/{id}', [EmpleadoController::class, 'show']);
    Route::put('/empleados/{id}', [EmpleadoController::class, 'update']);
    Route::delete('/empleados/{id}', [EmpleadoController::class, 'destroy']);    // Filtros de lavados por empleado
    Route::get('/empleados/{empleado_id}/lavados/dia/{fecha}', [EmpleadoController::class, 'lavadosPorDia']);
    Route::get('/empleados/{empleado_id}/lavados/semana/{fecha}', [EmpleadoController::class, 'lavadosPorSemana']);
    Route::get('/empleados/{empleado_id}/lavados/mes/{anio}/{mes}', [EmpleadoController::class, 'lavadosPorMes']);

    // CRUD de usuarios
    Route::get('/usuarios', [UserController::class, 'index']);
    Route::post('/usuarios', [UserController::class, 'store']);
    Route::get('/usuarios/{id}', [UserController::class, 'show']);
    Route::put('/usuarios/{id}', [UserController::class, 'update']);
    Route::delete('/usuarios/{id}', [UserController::class, 'destroy']);

    // CRUD de clientes
    Route::get('/clientes', [ClienteController::class, 'index']);
    Route::post('/clientes', [ClienteController::class, 'store']);
    Route::get('/clientes/{id}', [ClienteController::class, 'show']);
    Route::put('/clientes/{id}', [ClienteController::class, 'update']);
    Route::delete('/clientes/{id}', [ClienteController::class, 'destroy']);
    // Buscar cliente por cédula
    Route::get('/clientes/buscar/cedula/{cedula}', [ClienteController::class, 'buscarPorCedula']);

    // CRUD de vehículos
    Route::get('/vehiculos', [VehiculoController::class, 'index']);
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

    // CRUD de productos automotrices
    Route::get('/productos-automotrices', [ProductoAutomotrizController::class, 'index']);
    Route::post('/productos-automotrices', [ProductoAutomotrizController::class, 'store']);
    Route::get('/productos-automotrices/{id}', [ProductoAutomotrizController::class, 'show']);
    Route::put('/productos-automotrices/{id}', [ProductoAutomotrizController::class, 'update']);
    Route::delete('/productos-automotrices/{id}', [ProductoAutomotrizController::class, 'destroy']);
    // Actualizar stock
    Route::put('/productos-automotrices/{id}/stock', [ProductoAutomotrizController::class, 'actualizarStock']);

    // CRUD de productos de despensa
    Route::get('/productos-despensa', [ProductoDespensaController::class, 'index']);
    Route::post('/productos-despensa', [ProductoDespensaController::class, 'store']);
    Route::get('/productos-despensa/{id}', [ProductoDespensaController::class, 'show']);
    Route::put('/productos-despensa/{id}', [ProductoDespensaController::class, 'update']);
    Route::delete('/productos-despensa/{id}', [ProductoDespensaController::class, 'destroy']);
    // Actualizar stock
    Route::put('/productos-despensa/{id}/stock', [ProductoDespensaController::class, 'actualizarStock']);

    // CRUD de proveedores
    Route::get('/proveedores', [ProveedorController::class, 'index']);
    Route::post('/proveedores', [ProveedorController::class, 'store']);
    Route::get('/proveedores/{id}', [ProveedorController::class, 'show']);
    Route::put('/proveedores/{id}', [ProveedorController::class, 'update']);
    Route::delete('/proveedores/{id}', [ProveedorController::class, 'destroy']);
    // Gestión de deudas y pagos a proveedores
    Route::get('/proveedores/{id}/deuda', [ProveedorController::class, 'verDeuda']);
    Route::post('/proveedores/{id}/pago', [ProveedorController::class, 'registrarPago']);
    // Historial de pagos a proveedor
    Route::get('/proveedores/{id}/pagos', [ProveedorController::class, 'pagos']);

    // CRUD de ingresos
    Route::get('/ingresos', [IngresoController::class, 'index']);
    Route::post('/ingresos', [IngresoController::class, 'store']);
    Route::get('/ingresos/{id}', [IngresoController::class, 'show']);
    Route::put('/ingresos/{id}', [IngresoController::class, 'update']);
    Route::delete('/ingresos/{id}', [IngresoController::class, 'destroy']);

    // CRUD de egresos
    Route::get('/egresos', [EgresoController::class, 'index']);
    Route::post('/egresos', [EgresoController::class, 'store']);
    Route::get('/egresos/{id}', [EgresoController::class, 'show']);
    Route::put('/egresos/{id}', [EgresoController::class, 'update']);
    Route::delete('/egresos/{id}', [EgresoController::class, 'destroy']);

    // CRUD de gastos generales
    Route::get('/gastos-generales', [GastoGeneralController::class, 'index']);
    Route::post('/gastos-generales', [GastoGeneralController::class, 'store']);
    Route::get('/gastos-generales/{id}', [GastoGeneralController::class, 'show']);
    Route::put('/gastos-generales/{id}', [GastoGeneralController::class, 'update']);
    Route::delete('/gastos-generales/{id}', [GastoGeneralController::class, 'destroy']);

    // Balance y reportes
    Route::get('/balance', [BalanceController::class, 'resumen']);
    Route::get('/reportes/ingresos', [ReporteController::class, 'ingresosPDF']);
    Route::get('/reportes/egresos', [ReporteController::class, 'egresosPDF']);
    Route::get('/reportes/inventario', [ReporteController::class, 'inventarioPDF']);
    Route::get('/reportes/pagos', [ReporteController::class, 'pagosPDF']);
    Route::get('/reportes/deudas', [ReporteController::class, 'deudasPDF']);

    // Facturación
    Route::get('/facturas', [FacturaController::class, 'index']);
    Route::post('/facturas', [FacturaController::class, 'store']);
    Route::get('/facturas/{id}', [FacturaController::class, 'show']);
    Route::put('/facturas/{id}', [FacturaController::class, 'update']);
    Route::delete('/facturas/{id}', [FacturaController::class, 'destroy']);
    // Detalles de factura
    Route::get('/facturas/{id}/detalles', [FacturaController::class, 'detalles']);
    // Generar PDF de factura
    Route::get('/facturas/{id}/pdf', [FacturaController::class, 'generarPDF']);    // Ventas de productos automotrices
    Route::post('/ventas-productos-automotrices', [\App\Http\Controllers\VentaProductoAutomotrizController::class, 'store']);    // Ventas de productos de despensa    Route::post('/ventas-productos-despensa', [\App\Http\Controllers\VentaProductoDespensaController::class, 'store']);

    // Rutas de productos
    Route::get('/productos', [ProductoController::class, 'index']);
    Route::post('/productos', [ProductoController::class, 'store']);
    Route::get('/productos/{id}', [ProductoController::class, 'show']);
    Route::put('/productos/{id}', [ProductoController::class, 'update']);
    Route::delete('/productos/{id}', [ProductoController::class, 'destroy']);

    // CRUD de usuarios
    Route::get('/usuarios', [UserController::class, 'index']);
    Route::post('/usuarios', [UserController::class, 'store']);
    Route::get('/usuarios/{id}', [UserController::class, 'show']);
    Route::put('/usuarios/{id}', [UserController::class, 'update']);
    Route::delete('/usuarios/{id}', [UserController::class, 'destroy']);

    // Ruta de prueba para TestController
    Route::get('/test', [TestController::class, 'index']);
});
