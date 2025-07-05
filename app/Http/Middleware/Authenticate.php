<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Log;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo($request): ?string
    {
        // Para APIs, nunca redirigir, solo devolver null para que lance un 401
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }
        
        // Para web, redirigir a la página de login del frontend
        return '/login';
    }
}
