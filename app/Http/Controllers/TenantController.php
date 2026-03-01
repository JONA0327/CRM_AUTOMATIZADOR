<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    /**
     * Lista todos los tenants.
     * GET /admin/tenants
     */
    public function index(): JsonResponse
    {
        $tenants = Tenant::with('instances', 'users')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($t) => [
                'id'         => $t->id,
                'nombre'     => $t->nombre,
                'slug'       => $t->slug,
                'db_driver'  => $t->db_driver ?? 'mysql',
                'db_name'    => $t->getDatabaseName(),
                'instancias' => $t->instances->pluck('instance_name'),
                'usuarios'   => $t->users->count(),
                'created_at' => $t->created_at?->format('d/m/Y'),
            ]);

        return response()->json($tenants);
    }

    /**
     * Crea un nuevo tenant con su BD y usuario admin.
     * POST /admin/tenants
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'    => 'required|string|max:100',
            'slug'      => 'required|string|max:50|regex:/^[a-z0-9\-]+$/|unique:tenants',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8',
            'db_driver' => 'nullable|in:mysql,pgsql',
            'db_name'   => 'nullable|string|max:64',
        ]);

        // 1. Crear el tenant (stancl auto-crea la BD vía CreatesDatabase)
        $tenant = Tenant::create([
            'id'        => Str::uuid(),
            'nombre'    => $data['nombre'],
            'slug'      => $data['slug'],
            'db_driver' => $data['db_driver'] ?? 'mysql',
            'db_name'   => $data['db_name'] ?? null,
        ]);

        // 2. Ejecutar migraciones en la BD del nuevo tenant
        Artisan::call('tenants:migrate', [
            '--tenants' => [$tenant->id],
            '--force'   => true,
        ]);

        // 3. Crear el usuario admin en la BD central con el tenant_id
        $user = User::create([
            'name'      => $data['nombre'] . ' Admin',
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'tenant_id' => $tenant->id,
        ]);

        return response()->json([
            'tenant' => [
                'id'      => $tenant->id,
                'nombre'  => $tenant->nombre,
                'slug'    => $tenant->slug,
                'db_name' => $tenant->getDatabaseName(),
            ],
            'usuario' => [
                'id'    => $user->id,
                'email' => $user->email,
            ],
        ], 201);
    }

    /**
     * Elimina un tenant y su BD.
     * DELETE /admin/tenants/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $tenant = Tenant::findOrFail($id);

        // Eliminar usuarios del tenant en BD central
        User::where('tenant_id', $tenant->id)->delete();

        // Eliminar instancias de Evolution API registradas
        $tenant->instances()->delete();

        // Eliminar el tenant — stancl borrará la BD automáticamente
        $tenant->delete();

        return response()->json(['ok' => true]);
    }
}
