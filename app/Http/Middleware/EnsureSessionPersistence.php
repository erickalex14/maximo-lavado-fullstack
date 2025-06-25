<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionPersistence
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('EnsureSessionPersistence middleware triggered', [
            'url' => $request->url(),
            'session_id' => session()->getId(),
            'auth_check' => Auth::check(),
            'user_id' => Auth::id(),
            'session_data' => session()->all()
        ]);

        // Si ya está autenticado, continuar
        if (Auth::check()) {
            // Asegurar que los datos persistan en la sesión
            session(['user_id' => Auth::id(), 'logged_in' => true]);
            return $next($request);
        }

        // Intentar recuperar autenticación desde la sesión
        if (session('logged_in') && session('user_id')) {
            $user = \App\Models\User::find(session('user_id'));
            if ($user) {
                Auth::login($user, true);
                Log::info('Authentication recovered in middleware', [
                    'user_id' => $user->id,
                    'session_id' => session()->getId()
                ]);
                return $next($request);
            }
        }

        // Si no se puede recuperar la autenticación, limpiar la sesión
        session()->forget(['user_id', 'logged_in']);
        
        return $next($request);
    }
}
