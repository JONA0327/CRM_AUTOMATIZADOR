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
     * Panel de gestión de negocios (Super Admin).
     * GET /admin/negocios
     */
    public function index()
    {
        $negocios = Tenant::with(['instances', 'users' => fn($q) => $q->whereHas('roles', fn($r) => $r->whereIn('name', ['anfitrion', 'colaborador']))])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($t) {
                $admin = $t->users->firstWhere(fn($u) => $u->hasRole('anfitrion'));
                $colabs = $t->users->filter(fn($u) => $u->hasRole('colaborador'));

                return [
                    'id'                  => $t->id,
                    'nombre'              => $t->nombre,
                    'slug'                => $t->slug,
                    'db_driver'           => $t->db_driver ?? 'mysql',
                    'db_name'             => $t->getDatabaseName(),
                    'max_instances'       => $t->max_instances ?? 1,
                    'max_collaborators'   => $t->max_collaborators ?? 3,
                    'instancias_count'    => $t->instances->count(),
                    'colaboradores_count' => $colabs->count(),
                    'admin'               => $admin ? ['name' => $admin->name, 'email' => $admin->email] : null,
                    'created_at'          => $t->created_at?->format('d/m/Y'),
                ];
            });

        if (request()->wantsJson()) {
            return response()->json($negocios);
        }

        return view('admin.negocios.index', compact('negocios'));
    }

    /**
     * Crea un nuevo negocio con su BD, usuario anfitrion y rol.
     * POST /admin/negocios
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre'            => 'required|string|max:100',
            'slug'              => 'required|string|max:50|regex:/^[a-z0-9\-]+$/|unique:tenants',
            'admin_email'       => 'required|email',
            'admin_password'    => 'nullable|string|min:8',
            'db_driver'         => 'nullable|in:mysql,pgsql',
            'db_name'           => 'nullable|string|max:64',
            'max_instances'     => 'nullable|integer|min:1|max:50',
            'max_collaborators' => 'nullable|integer|min:0|max:100',
        ]);

        // Si el email no existe en la BD se necesita contraseña para crear el usuario
        $usuarioExistente = User::where('email', $data['admin_email'])->first();
        if (! $usuarioExistente && empty($data['admin_password'])) {
            return response()->json([
                'message' => 'La contraseña es requerida para crear un nuevo usuario administrador.',
                'errors'  => ['admin_password' => ['La contraseña es requerida para usuarios nuevos.']],
            ], 422);
        }

        $tenant = Tenant::create([
            'id'                => Str::uuid(),
            'nombre'            => $data['nombre'],
            'slug'              => $data['slug'],
            'db_driver'         => $data['db_driver'] ?? 'mysql',
            'db_name'           => $data['db_name'] ?? null,
            'max_instances'     => $data['max_instances'] ?? 1,
            'max_collaborators' => $data['max_collaborators'] ?? 3,
        ]);

        Artisan::call('tenants:migrate', [
            '--tenants' => [$tenant->id],
            '--force'   => true,
        ]);

        if ($usuarioExistente) {
            // Reutilizar usuario existente: asignar tenant si aún no tiene uno
            if (is_null($usuarioExistente->tenant_id)) {
                $usuarioExistente->tenant_id = $tenant->id;
                $usuarioExistente->save();
            }
            if (! $usuarioExistente->hasRole('anfitrion')) {
                $usuarioExistente->assignRole('anfitrion');
            }
            $user = $usuarioExistente;
        } else {
            $user = User::create([
                'name'              => $data['nombre'] . ' Admin',
                'username'          => $data['slug'] . '-admin',
                'email'             => $data['admin_email'],
                'password'          => Hash::make($data['admin_password']),
                'tenant_id'         => $tenant->id,
                'email_verified_at' => now(),
            ]);
            $user->assignRole('anfitrion');
        }

        return response()->json([
            'id'                  => $tenant->id,
            'nombre'              => $tenant->nombre,
            'slug'                => $tenant->slug,
            'db_name'             => $tenant->getDatabaseName(),
            'max_instances'       => $tenant->max_instances,
            'max_collaborators'   => $tenant->max_collaborators,
            'instancias_count'    => 0,
            'colaboradores_count' => 0,
            'admin'               => ['name' => $user->name, 'email' => $user->email],
            'created_at'          => $tenant->created_at->format('d/m/Y'),
        ], 201);
    }

    /**
     * Actualiza limites y nombre de un negocio.
     * PUT /admin/negocios/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $tenant = Tenant::findOrFail($id);

        $data = $request->validate([
            'nombre'            => 'sometimes|string|max:100',
            'max_instances'     => 'sometimes|integer|min:1|max:50',
            'max_collaborators' => 'sometimes|integer|min:0|max:100',
        ]);

        foreach (['nombre', 'max_instances', 'max_collaborators'] as $campo) {
            if (array_key_exists($campo, $data)) {
                $tenant->$campo = $data[$campo];
            }
        }

        $tenant->save();

        return response()->json([
            'id'                => $tenant->id,
            'nombre'            => $tenant->nombre,
            'slug'              => $tenant->slug,
            'db_name'           => $tenant->getDatabaseName(),
            'max_instances'     => $tenant->max_instances,
            'max_collaborators' => $tenant->max_collaborators,
        ]);
    }

    /**
     * Elimina un negocio, sus usuarios e instancias.
     * DELETE /admin/negocios/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $tenant = Tenant::findOrFail($id);

        User::where('tenant_id', $tenant->id)->each(fn($u) => $u->syncRoles([]));
        User::where('tenant_id', $tenant->id)->delete();
        $tenant->instances()->delete();
        $tenant->delete();

        return response()->json(['ok' => true]);
    }
}
