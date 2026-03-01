<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireTenant
{
    /**
     * Requiere que el usuario tenga un tenant activo.
     * Usa tenant.auth internamente y bloquea si no hay tenant.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user || ! $user->tenant_id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tu cuenta no tiene un tenant asignado. Contacta al administrador.',
                ], 403);
            }

            return redirect()->route('dashboard')
                ->with('error', 'Tu cuenta no tiene un tenant asignado. Contacta al administrador.');
        }

        // Delegar la inicialización al middleware tenant.auth
        $middleware = app(\App\Http\Middleware\InitializeTenancyFromAuth::class);
        return $middleware->handle($request, $next);
    }
}
