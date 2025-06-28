// PROPUESTA DE RUTAS CONSOLIDADAS PARA PROVEEDORES
// Reemplazar las rutas existentes de proveedores y pagos-proveedores

// CRUD básico de proveedores
Route::get('/proveedores', [ProveedorController::class, 'index']);
Route::post('/proveedores', [ProveedorController::class, 'store']);
Route::get('/proveedores/{id}', [ProveedorController::class, 'show']);
Route::put('/proveedores/{id}', [ProveedorController::class, 'update']);
Route::delete('/proveedores/{id}', [ProveedorController::class, 'destroy']);

// Gestión de deudas (por proveedor específico)
Route::get('/proveedores/{id}/deuda', [ProveedorController::class, 'verDeuda']);
Route::post('/proveedores/{id}/pago', [ProveedorController::class, 'registrarPago']);
Route::get('/proveedores/{id}/pagos', [ProveedorController::class, 'pagos']);

// Gestión consolidada de pagos (todos los proveedores)
Route::get('/proveedores/pagos', [ProveedorController::class, 'getAllPagos']);
Route::post('/proveedores/pagos', [ProveedorController::class, 'createPago']);
Route::get('/proveedores/pagos/metricas', [ProveedorController::class, 'getMetricasPagos']);
Route::get('/proveedores/pagos/{pagoId}', [ProveedorController::class, 'getPago']);
Route::put('/proveedores/pagos/{pagoId}', [ProveedorController::class, 'updatePago']);
Route::delete('/proveedores/pagos/{pagoId}', [ProveedorController::class, 'deletePago']);

// ELIMINAR ESTAS RUTAS (mover funcionalidad al ProveedorController):
/*
Route::get('/pagos-proveedores', [PagoProveedorController::class, 'index']);
Route::post('/pagos-proveedores', [PagoProveedorController::class, 'store']);
Route::get('/pagos-proveedores/{id}', [PagoProveedorController::class, 'show']);
Route::put('/pagos-proveedores/{id}', [PagoProveedorController::class, 'update']);
Route::delete('/pagos-proveedores/{id}', [PagoProveedorController::class, 'destroy']);
Route::get('/pagos-proveedores/metricas', [PagoProveedorController::class, 'getMetricas']);
*/
