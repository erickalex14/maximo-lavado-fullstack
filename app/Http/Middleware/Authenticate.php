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
        Log::info('Authenticate middleware - redirectTo called', [
            'expects_json' => $request->expectsJson(),
            'session_id' => session()->getId(),
            'auth_check' => auth()->check(),
            'user_id' => auth()->id(),
            'url' => $request->url()
        ]);
        
        return $request->expectsJson() ? null : route('login');
    }
}
