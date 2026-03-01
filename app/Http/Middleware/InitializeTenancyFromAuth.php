<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InitializeTenancyFromAuth
{
    /**
     * Inicializa la tenancy a partir del tenant_id del usuario autenticado.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user || ! $user->tenant_id) {
            // Superadmin sin tenant — permitir rutas globales sin tenant
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
