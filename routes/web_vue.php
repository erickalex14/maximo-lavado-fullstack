<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;

// Rutas de autenticación (API)
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

// API Routes (protected)
Route::middleware(['auth'])->prefix('api')->group(function () {
    // Dashboard API
    Route::get('/dashboard/data', [DashboardController::class, 'getData']);
    
    // Add other API routes here as needed
});

// Catch all route - serve Vue app
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
