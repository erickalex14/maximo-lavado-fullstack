<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckSessionHealth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Solo verificar en peticiones autenticadas
        if (Auth::check()) {
            $user = Auth::user();
            $sessionId = session()->getId();
            
            Log::info('Session health check', [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'user_name' => $user->name,
                'route' => $request->route()?->getName(),
                'session_data' => [
                    'has_user_id' => session()->has('user_id'),
                    'logged_in' => session()->get('logged_in'),
                    'login_time' => session()->get('login_time'),
                ]
            ]);
            
            // Verificar que la sesión tenga los datos necesarios
            if (!session()->has('user_id') || !session()->get('logged_in')) {
                Log::warning('Session missing required data, refreshing', [
                    'user_id' => $user->id,
                    'session_id' => $sessionId,
                ]);
                
                // Restaurar datos de sesión
                session()->put('user_id', $user->id);
                session()->put('logged_in', true);
                session()->put('login_time', now()->timestamp);
                session()->save();
            }
        }
        
        return $next($request);
    }
}
