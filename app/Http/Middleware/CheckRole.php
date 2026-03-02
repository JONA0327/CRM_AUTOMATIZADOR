<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifica que el usuario autenticado tenga al menos uno de los roles requeridos.
 *
 * Uso en rutas:
 *   ->middleware('role:super_admin')
 *   ->middleware('role:super_admin,anfitrion')
 *
 * El super_admin siempre pasa, independientemente de qué roles se listen.
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // super_admin tiene acceso irrestricto a todo
        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        // Verificar si el usuario tiene alguno de los roles permitidos
        foreach ($roles as $role) {
            if ($user->hasRole(trim($role))) {
                return $next($request);
            }
        }

        abort(403, 'No tienes permisos para acceder a esta sección.');
    }
}
