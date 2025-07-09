<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Login para API (con tokens Sanctum)
     */
    public function loginApi(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }

    /**
     * Logout para API
     */
    public function logoutApi(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada correctamente'
        ]);
    }

    /**
     * Obtener usuario autenticado
     */
    public function user(Request $request)
    {
        \Log::info('AuthController::user method called');
        \Log::info('Request headers:', $request->headers->all());
        \Log::info('Auth header:', [$request->header('Authorization')]);
        \Log::info('Request user:', [$request->user()]);
        \Log::info('Auth check:', [auth('sanctum')->check()]);
        \Log::info('Auth user:', [auth('sanctum')->user()]);
        
        $user = $request->user();
        
        if (!$user) {
            \Log::error('No user found in request');
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado'
            ], 401);
        }
        
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Crear usuario por defecto (solo para desarrollo)
     */
    public function createDefaultUser()
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@lavado.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Usuario creado',
            'user' => $user,
            'credentials' => [
                'email' => 'admin@lavado.com',
                'password' => 'password123'
            ]
        ]);
    }
}
