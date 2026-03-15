<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Permite al super_admin "entrar" al contexto de un tenant específico.
 *
 * Cuando hay un 'tenancy_impersonate_id' en sesión, this middleware
 * inicializa el tenant a partir de ese ID en lugar del tenant del usuario.
 *
 * Flujo:
 *   POST /admin/negocios/{id}/impersonate  → guarda id en sesión
 *   DELETE /admin/negocios/impersonate     → limpia sesión
 */
class ImpersonateTenant
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        // Solo super_admin puede impersonar un tenant
        if (! $user->hasRole('super_admin')) {
            return $next($request);
        }

        $tenantId = session('tenancy_impersonate_id');

        if (! $tenantId) {
            return $next($request);
        }

        $tenant = Tenant::find($tenantId);

        if (! $tenant) {
            // ID de sesión inválido — limpiar y continuar sin tenant
            session()->forget('tenancy_impersonate_id');
            return $next($request);
        }

        tenancy()->initialize($tenant);

        return $next($request);
    }
}
