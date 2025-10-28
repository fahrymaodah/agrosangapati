<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * Check if the authenticated user has the required permission gate.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission  The permission gate name to check
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            // API request - return JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please login first.'
                ], 401);
            }
            
            // Web request - redirect to login
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Check permission using Gate
        if (Gate::denies($permission)) {
            // API request - return JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden. You do not have the required permission.',
                    'required_permission' => $permission,
                    'your_role' => auth()->user()->role
                ], 403);
            }
            
            // Web request - abort with 403
            abort(403, 'Anda tidak memiliki izin untuk mengakses resource ini.');
        }

        return $next($request);
    }
}
