<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\LavadoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

// Ruta principal - redirigir al dashboard
Route::get('/', function () {
    return redirect('/dashboard');
});

// Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'loginWeb']);
Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');

// Ruta para crear usuario por defecto (solo desarrollo)
Route::get('/create-default-user', [AuthController::class, 'createDefaultUser']);

// Ruta de test de autenticación
Route::get('/test-auth', function () {
    return response()->json([
        'authenticated' => Auth::check(),
        'user_id' => Auth::id(),
        'session_id' => session()->getId(),
        'session_user_id' => session('user_id'),
        'session_logged_in' => session('logged_in'),
        'session_data' => session()->all()
    ]);
});

// Rutas protegidas por autenticación web
Route::middleware(['auth', 'auth.session'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Usuarios y Perfil
    Route::get('/usuarios', [UserController::class, 'indexView'])->name('usuarios.index');
    Route::get('/api/usuarios', [UserController::class, 'index'])->name('usuarios.api.index');
    Route::post('/api/usuarios', [UserController::class, 'store'])->name('usuarios.api.store');
    Route::get('/api/usuarios/{id}', [UserController::class, 'show'])->name('usuarios.api.show');
    Route::put('/api/usuarios/{id}', [UserController::class, 'update'])->name('usuarios.api.update');
    Route::delete('/api/usuarios/{id}', [UserController::class, 'destroy'])->name('usuarios.api.destroy');
    Route::get('/usuarios/create', [UserController::class, 'create'])->name('usuarios.create');
    Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store');
    Route::get('/usuarios/{id}', [UserController::class, 'show'])->name('usuarios.show');
    Route::get('/usuarios/{id}/edit', [UserController::class, 'edit'])->name('usuarios.edit');
    Route::put('/usuarios/{id}', [UserController::class, 'update'])->name('usuarios.update');
    Route::delete('/usuarios/{id}', [UserController::class, 'destroy'])->name('usuarios.destroy');
    Route::get('/perfil', [UserController::class, 'profile'])->name('usuarios.profile');
    Route::put('/perfil', [UserController::class, 'updateProfile'])->name('usuarios.profile.update');
    Route::put('/perfil/password', [UserController::class, 'updatePassword'])->name('usuarios.password.update');

    // API del dashboard (para AJAX desde las vistas autenticadas)
    Route::get('/api/dashboard/data', [DashboardController::class, 'getData'])->name('api.dashboard.data');
    Route::get('/api/dashboard/charts', [DashboardController::class, 'getChartData'])->name('api.dashboard.charts');

    // Clientes
    Route::get('/clientes', [ClienteController::class, 'indexView'])->name('clientes.index');
    Route::get('/api/clientes', [ClienteController::class, 'index'])->name('api.clientes.index');
    Route::post('/api/clientes', [ClienteController::class, 'store'])->name('api.clientes.store');
    Route::get('/api/clientes/{id}', [ClienteController::class, 'show'])->name('api.clientes.show');
    Route::put('/api/clientes/{id}', [ClienteController::class, 'update'])->name('api.clientes.update');
    Route::delete('/api/clientes/{id}', [ClienteController::class, 'destroy'])->name('api.clientes.destroy');
    Route::get('/api/clientes/buscar/{cedula}', [ClienteController::class, 'buscarPorCedula'])->name('api.clientes.buscar');

    // Vehículos
    Route::get('/vehiculos', [VehiculoController::class, 'indexView'])->name('vehiculos.index');
    Route::get('/api/vehiculos', [VehiculoController::class, 'index'])->name('api.vehiculos.index');
    Route::post('/api/vehiculos', [VehiculoController::class, 'store'])->name('api.vehiculos.store');
    Route::get('/api/vehiculos/{id}', [VehiculoController::class, 'show'])->name('api.vehiculos.show');
    Route::put('/api/vehiculos/{id}', [VehiculoController::class, 'update'])->name('api.vehiculos.update');
    Route::delete('/api/vehiculos/{id}', [VehiculoController::class, 'destroy'])->name('api.vehiculos.destroy');
    Route::get('/api/vehiculos/cliente/{cliente_id}', [VehiculoController::class, 'getByCliente'])->name('api.vehiculos.cliente');

    // Lavados
    Route::get('/lavados', [LavadoController::class, 'indexView'])->name('lavados.index');
    Route::get('/lavados/create', [LavadoController::class, 'indexView'])->name('lavados.create');
    Route::get('/api/lavados', [LavadoController::class, 'index'])->name('api.lavados.index');
    Route::post('/api/lavados', [LavadoController::class, 'store'])->name('api.lavados.store');
    Route::get('/api/lavados/{id}', [LavadoController::class, 'show'])->name('api.lavados.show');
    Route::put('/api/lavados/{id}', [LavadoController::class, 'update'])->name('api.lavados.update');
    Route::delete('/api/lavados/{id}', [LavadoController::class, 'destroy'])->name('api.lavados.destroy');

    // Productos
    Route::get('/productos', [ProductoController::class, 'indexView'])->name('productos.index');
    Route::get('/api/productos/metricas', [ProductoController::class, 'getMetricas'])->name('api.productos.metricas');
    Route::get('/api/productos/automotrices', [ProductoController::class, 'getProductosAutomotrices'])->name('api.productos.automotrices');
    Route::get('/api/productos/despensa', [ProductoController::class, 'getProductosDespensa'])->name('api.productos.despensa');
    Route::post('/api/productos/automotrices', [ProductoController::class, 'storeAutomotriz'])->name('api.productos.automotrices.store');
    Route::post('/api/productos/despensa', [ProductoController::class, 'storeDespensa'])->name('api.productos.despensa.store');
    Route::put('/api/productos/automotrices/{id}', [ProductoController::class, 'updateAutomotriz'])->name('api.productos.automotrices.update');
    Route::put('/api/productos/despensa/{id}', [ProductoController::class, 'updateDespensa'])->name('api.productos.despensa.update');
    Route::delete('/api/productos/automotrices/{id}', [ProductoController::class, 'destroyAutomotriz'])->name('api.productos.automotrices.destroy');
    Route::delete('/api/productos/despensa/{id}', [ProductoController::class, 'destroyDespensa'])->name('api.productos.despensa.destroy');

    // Ventas
    Route::get('/ventas', [VentaController::class, 'indexView'])->name('ventas.index');
    Route::get('/api/ventas/metricas', [VentaController::class, 'getMetricas'])->name('api.ventas.metricas');
    Route::get('/api/ventas', [VentaController::class, 'getVentas'])->name('api.ventas.index');
    Route::get('/api/ventas/productos', [VentaController::class, 'getProductosDisponibles'])->name('api.ventas.productos');
    Route::get('/api/ventas/clientes', [VentaController::class, 'getClientes'])->name('api.ventas.clientes');
    Route::post('/api/ventas', [VentaController::class, 'store'])->name('api.ventas.store');

    // Facturas
    Route::get('/facturas', [FacturaController::class, 'indexView'])->name('facturas.index');
    Route::get('/api/facturas', [FacturaController::class, 'index'])->name('api.facturas.index');
    Route::post('/api/facturas', [FacturaController::class, 'store'])->name('api.facturas.store');
    Route::get('/api/facturas/{id}', [FacturaController::class, 'show'])->name('api.facturas.show');
    Route::put('/api/facturas/{id}', [FacturaController::class, 'update'])->name('api.facturas.update');
    Route::delete('/api/facturas/{id}', [FacturaController::class, 'destroy'])->name('api.facturas.destroy');

    // Reportes
    Route::get('/reportes', [ReporteController::class, 'indexView'])->name('reportes.index');
    Route::get('/api/reportes/resumen-financiero', [ReporteController::class, 'getResumenFinanciero'])->name('api.reportes.resumen');
    Route::get('/api/reportes/ventas', [ReporteController::class, 'getVentasData'])->name('api.reportes.ventas');
    Route::get('/api/reportes/servicios', [ReporteController::class, 'getServiciosData'])->name('api.reportes.servicios');
    Route::get('/api/reportes/clientes', [ReporteController::class, 'getClientesData'])->name('api.reportes.clientes');
    Route::get('/api/reportes/empleados', [ReporteController::class, 'getEmpleadosData'])->name('api.reportes.empleados');
    Route::get('/reportes/ingresos/pdf', [ReporteController::class, 'ingresosPDF'])->name('reportes.ingresos.pdf');
    Route::get('/reportes/egresos/pdf', [ReporteController::class, 'egresosPDF'])->name('reportes.egresos.pdf');
    Route::get('/reportes/inventario/pdf', [ReporteController::class, 'inventarioPDF'])->name('reportes.inventario.pdf');
    Route::get('/reportes/pagos/pdf', [ReporteController::class, 'pagosPDF'])->name('reportes.pagos.pdf');
    Route::get('/reportes/deudas/pdf', [ReporteController::class, 'deudasPDF'])->name('reportes.deudas.pdf');
    Route::get('/reportes/factura/{id}/pdf', [ReporteController::class, 'facturaPDF'])->name('reportes.factura.pdf');

    // Empleados
    Route::get('/empleados', [EmpleadoController::class, 'indexView'])->name('empleados.index');
    Route::get('/api/empleados', [EmpleadoController::class, 'index'])->name('api.empleados.index');
    Route::post('/api/empleados', [EmpleadoController::class, 'store'])->name('api.empleados.store');
    Route::get('/api/empleados/{id}', [EmpleadoController::class, 'show'])->name('api.empleados.show');
    Route::put('/api/empleados/{id}', [EmpleadoController::class, 'update'])->name('api.empleados.update');
    Route::delete('/api/empleados/{id}', [EmpleadoController::class, 'destroy'])->name('api.empleados.destroy');

    // Proveedores
    Route::get('/proveedores', [ProveedorController::class, 'indexView'])->name('proveedores.index');
    Route::get('/api/proveedores', [ProveedorController::class, 'index'])->name('api.proveedores.index');
    Route::post('/api/proveedores', [ProveedorController::class, 'store'])->name('api.proveedores.store');
    Route::get('/api/proveedores/{id}', [ProveedorController::class, 'show'])->name('api.proveedores.show');
    Route::put('/api/proveedores/{id}', [ProveedorController::class, 'update'])->name('api.proveedores.update');
    Route::delete('/api/proveedores/{id}', [ProveedorController::class, 'destroy'])->name('api.proveedores.destroy');
});
