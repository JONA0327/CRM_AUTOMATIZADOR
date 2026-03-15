<?php

namespace App\Http\Controllers;

use App\Mail\BienvenidaUsuario;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

/**
 * Gestión de colaboradores por parte del Anfitrión.
 * El anfitrión puede invitar/eliminar usuarios dentro de su negocio,
 * gestionar sus permisos y respetar el límite max_collaborators.
 */
class CollaboratorController extends Controller
{
    /**
     * Permisos que el anfitrión puede asignar a sus colaboradores.
     */
    const PERMISOS_ASIGNABLES = [
        'ver.conversaciones',
        'catalogos.ver',
        'catalogos.editar',
        'instancias.ver',
        'instancias.crear',
        'instancias.eliminar',
        'instancias.pausar',
    ];

    /**
     * Lista los colaboradores del negocio del anfitrión.
     * GET /colaboradores
     */
    public function index()
    {
        $user   = auth()->user();
        $tenant = Tenant::findOrFail($user->tenant_id);

        $colaboradores = User::where('tenant_id', $tenant->id)
            ->where('id', '!=', $user->id)
            ->whereHas('roles', fn($q) => $q->where('name', 'colaborador'))
            ->get(['id', 'name', 'username', 'email', 'created_at'])
            ->map(fn($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'username'   => $u->username,
                'email'      => $u->email,
                'created_at' => $u->created_at?->format('d/m/Y'),
                'permisos'   => $u->getDirectPermissions()->pluck('name')->values()->all(),
            ]);

        $data = [
            'colaboradores'      => $colaboradores,
            'max_collaborators'  => $tenant->max_collaborators ?? 3,
            'total'              => $colaboradores->count(),
            'permisos_opciones'  => self::PERMISOS_ASIGNABLES,
        ];

        if (request()->wantsJson()) {
            return response()->json($data);
        }

        return view('colaboradores.index', $data);
    }

    /**
     * Invita (crea) un nuevo colaborador al negocio.
     * POST /colaboradores
     */
    public function store(Request $request): JsonResponse
    {
        $anfitrion = auth()->user();
        $tenant    = Tenant::findOrFail($anfitrion->tenant_id);

        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'username' => 'required|string|max:60|unique:users,username',
            'email'    => 'required|email|unique:users,email',
        ]);

        $actualColabs = User::where('tenant_id', $tenant->id)
            ->whereHas('roles', fn($q) => $q->where('name', 'colaborador'))
            ->count();

        $limite = $tenant->max_collaborators ?? 3;

        if ($actualColabs >= $limite) {
            return response()->json([
                'message' => "Has alcanzado el límite de {$limite} colaborador(es) para tu negocio.",
            ], 422);
        }

        // Generar contraseña temporal y notificar por correo
        $plainPassword = Str::password(12, true, true, false);

        $colaborador = User::create([
            'name'                 => $data['name'],
            'username'             => $data['username'],
            'email'                => $data['email'],
            'password'             => Hash::make($plainPassword),
            'tenant_id'            => $tenant->id,
            'email_verified_at'    => now(),
            'must_change_password' => true,
        ]);

        $colaborador->assignRole('colaborador');

        try {
            Mail::to($colaborador->email)
                ->send(new BienvenidaUsuario($colaborador, $plainPassword, 'colaborador'));
        } catch (\Throwable) {}

        return response()->json([
            'id'       => $colaborador->id,
            'name'     => $colaborador->name,
            'username' => $colaborador->username,
            'email'    => $colaborador->email,
            'permisos' => [],
            'rol'      => 'colaborador',
        ], 201);
    }

    /**
     * Actualiza los permisos directos de un colaborador.
     * PUT /colaboradores/{id}/permisos
     *
     * Body: { "permisos": ["ver.conversaciones", "catalogos.editar", ...] }
     */
    public function updatePermisos(int $id, Request $request): JsonResponse
    {
        $anfitrion   = auth()->user();
        $colaborador = User::where('id', $id)
            ->where('tenant_id', $anfitrion->tenant_id)
            ->whereHas('roles', fn($q) => $q->where('name', 'colaborador'))
            ->firstOrFail();

        $data = $request->validate([
            'permisos'   => 'present|array',
            'permisos.*' => 'string|in:' . implode(',', self::PERMISOS_ASIGNABLES),
        ]);

        // Sync solo los permisos del conjunto asignable (no toca permisos de roles)
        $nuevos = collect($data['permisos'])
            ->intersect(self::PERMISOS_ASIGNABLES)
            ->values()
            ->all();

        // Revocar primero los permisos directos asignables, luego dar los nuevos
        foreach (self::PERMISOS_ASIGNABLES as $permiso) {
            $colaborador->revokePermissionTo($permiso);
        }

        if (! empty($nuevos)) {
            $colaborador->givePermissionTo($nuevos);
        }

        return response()->json([
            'permisos' => $colaborador->fresh()->getDirectPermissions()->pluck('name')->values()->all(),
        ]);
    }

    /**
     * Elimina un colaborador del negocio.
     * DELETE /colaboradores/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $anfitrion   = auth()->user();
        $colaborador = User::where('id', $id)
            ->where('tenant_id', $anfitrion->tenant_id)
            ->whereHas('roles', fn($q) => $q->where('name', 'colaborador'))
            ->firstOrFail();

        $colaborador->syncRoles([]);
        $colaborador->revokePermissionTo(Permission::all());
        $colaborador->delete();

        return response()->json(['ok' => true]);
    }
}
