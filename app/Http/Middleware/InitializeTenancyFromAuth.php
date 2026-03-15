<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InitializeTenancyFromAuth
{
    /**
     * Inicializa la tenancy a partir de:
     *  1. La sesión de impersonación del super_admin (prioridad)
     *  2. El tenant_id del usuario autenticado
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        // Super admin impersonando un tenant específico
        if ($user->hasRole('super_admin') && session('tenancy_impersonate_id')) {
            $tenant = Tenant::find(session('tenancy_impersonate_id'));
            if ($tenant) {
                tenancy()->initialize($tenant);
                return $next($request);
            }
            // ID inválido — limpiar sesión
            session()->forget('tenancy_impersonate_id');
        }

        if (! $user->tenant_id) {
            // Super admin sin impersonación activa — sin tenant
            return $next($request);
        }

        $tenant = Tenant::find($user->tenant_id);

        if (! $tenant) {
            abort(403, 'Tenant no encontrado.');
        }

        tenancy()->initialize($tenant);

        return $next($request);
    }
}
