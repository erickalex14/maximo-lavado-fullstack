<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;

// Rutas de autenticación (API)
Route::post('/login', [AuthController::class, 'loginWeb']);
Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');

// API Routes (protected)
Route::middleware(['auth'])->prefix('api')->group(function () {
    // User data
    Route::get('/user', [AuthController::class, 'user']);
    
    // Dashboard API
    Route::get('/dashboard/data', [DashboardController::class, 'getData']);
    
    // Add other API routes here as needed
});

// Catch all route - serve Vue app
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
