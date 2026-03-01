<?php

namespace App\Http\Middleware;

use App\Models\TenantInstance;
use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedException;

class InitializeTenancyFromInstance
{
    /**
     * Inicializa la tenancy a partir del nombre de la instancia de Evolution API.
     * Si la instancia no está registrada se ignora silenciosamente (Evolution
     * no necesita recibir un error 4xx para dejar de reintentar).
     */
    public function handle(Request $request, Closure $next)
    {
        $instanceName = $request->route('instancia');

        if (! $instanceName) {
            return response()->json(['status' => 'ignored'], 200);
        }

        $tenantInstance = TenantInstance::with('tenant')
            ->where('instance_name', $instanceName)
            ->first();

        if (! $tenantInstance || ! $tenantInstance->tenant) {
            \Illuminate\Support\Facades\Log::warning(
                "[Webhook] Instancia '{$instanceName}' no tiene tenant registrado — ignorando."
            );
            return response()->json(['status' => 'ignored'], 200);
        }

        tenancy()->initialize($tenantInstance->tenant);

        return $next($request);
    }
}
