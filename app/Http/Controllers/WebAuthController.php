<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class WebAuthController extends Controller
{
    // Mostrar formulario de login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Procesar login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
              return response()->json([
                'success' => true,
                'message' => 'Login exitoso',
                'redirect' => '/dashboard'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Las credenciales no coinciden con nuestros registros.'
        ], 401);
    }

    // Procesar logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }

    // Crear usuario por defecto (solo para desarrollo)
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
