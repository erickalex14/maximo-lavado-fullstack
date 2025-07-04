<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // Mostrar formulario de login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Login para API (con tokens)
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
    }    // Login para web (con sesiones)
    public function loginWeb(Request $request)
    {
        \Log::info('Login attempt started', [
            'email' => $request->email,
            'has_password' => !empty($request->password),
            'request_method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'expects_json' => $request->expectsJson(),
            'session_id_before' => session()->getId()
        ]);

        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:6',
            ]);
        } catch (\Exception $e) {
            \Log::error('Validation failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Datos de validación incorrectos'], 400);
        }

        $credentials = $request->only('email', 'password');
        \Log::info('Attempting authentication', ['email' => $credentials['email']]);

        if (Auth::attempt($credentials, true)) { // Remember me = true
            \Log::info('Authentication successful');
            
            // Obtener el usuario autenticado
            $user = Auth::user();
            
            // Regenerar la sesión para seguridad
            $request->session()->regenerate();
            
            // Guardar datos adicionales en la sesión
            $request->session()->put('auth.password_confirmed_at', time());
            $request->session()->put('user_id', $user->id);
            $request->session()->put('logged_in', true);
            $request->session()->put('login_time', now()->timestamp);
            
            // Forzar el guardado de la sesión
            $request->session()->save();
            
            \Log::info('Session data saved', [
                'session_id' => session()->getId(),
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
                'guard' => Auth::getDefaultDriver(),
                'session_has_user_id' => $request->session()->has('user_id'),
                'session_logged_in' => $request->session()->get('logged_in'),
                'login_time' => $request->session()->get('login_time')
            ]);
            
            // Verificar que el usuario sigue autenticado
            if (!Auth::check()) {
                \Log::error('User lost authentication after session save');
                return response()->json(['success' => false, 'message' => 'Error en la autenticación'], 500);
            }
            
            // Si es una petición AJAX, devolver JSON
            if ($request->expectsJson()) {
                \Log::info('Returning JSON response for AJAX login', [
                    'user_id' => Auth::id(),
                    'session_id' => session()->getId(),
                    'redirect_url' => '/dashboard'
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Login exitoso',
                    'data' => [
                        'user' => $user,
                        'session_id' => session()->getId()
                    ],
                    'redirect' => '/dashboard'
                ]);
            }
            
            // Si es una petición normal, redirigir directamente
            return redirect()->intended('/dashboard');
        }

        \Log::warning('Authentication failed', ['email' => $credentials['email']]);

        // Si es una petición AJAX, devolver JSON con error
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Las credenciales no coinciden con nuestros registros.'
            ], 401);
        }
        
        // Si es una petición normal, redirigir con error
        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    // Logout para API
    public function logoutApi(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada correctamente'
        ]);
    }

    // Logout para web
    public function logoutWeb(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }

    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
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
