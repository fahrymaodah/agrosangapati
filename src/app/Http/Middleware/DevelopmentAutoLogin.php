<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DevelopmentAutoLogin
{
    /**
     * Handle an incoming request.
     *
     * DEVELOPMENT ONLY: Automatically login as first superadmin
     * 
     * TODO: Remove this middleware before production!
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if already authenticated
        if (Auth::check()) {
            return $next($request);
        }

        // Auto login as first superadmin
        $user = User::where('role', 'superadmin')->first();
        
        if ($user) {
            Auth::login($user);
        }

        return $next($request);
    }
}
