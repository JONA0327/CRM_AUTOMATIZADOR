<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Gestión de colaboradores por parte del Anfitrión.
 * El anfitrión puede invitar/eliminar usuarios dentro de su negocio,
 * respetando el límite max_collaborators definido por el Super Admin.
 */
class CollaboratorController extends Controller
{
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
            ]);

        $data = [
            'colaboradores'     => $colaboradores,
            'max_collaborators' => $tenant->max_collaborators ?? 3,
            'total'             => $colaboradores->count(),
        ];

        if (request()->wantsJson()) {
            return response()->json($data);
        }

        return view('colaboradores.index', $data);
    }

    /**
     * Invita (crea) un nuevo colaborador al negocio.
     * POST /colaboradores
     *
     * Verifica el límite max_collaborators antes de crear.
     */
    public function store(Request $request): JsonResponse
    {
        $anfitrion = auth()->user();
        $tenant    = Tenant::findOrFail($anfitrion->tenant_id);

        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'username' => 'required|string|max:60|unique:users,username',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        // Verificar límite de colaboradores
        $actualColabs = User::where('tenant_id', $tenant->id)
            ->whereHas('roles', fn($q) => $q->where('name', 'colaborador'))
            ->count();

        $limite = $tenant->max_collaborators ?? 3;

        if ($actualColabs >= $limite) {
            return response()->json([
                'message' => "Has alcanzado el límite de {$limite} colaborador(es) para tu negocio.",
            ], 422);
        }

        $colaborador = User::create([
            'name'              => $data['name'],
            'username'          => $data['username'],
            'email'             => $data['email'],
            'password'          => Hash::make($data['password']),
            'tenant_id'         => $tenant->id,
            'email_verified_at' => now(),
        ]);

        $colaborador->assignRole('colaborador');

        return response()->json([
            'id'       => $colaborador->id,
            'name'     => $colaborador->name,
            'username' => $colaborador->username,
            'email'    => $colaborador->email,
            'rol'      => 'colaborador',
        ], 201);
    }

    /**
     * Elimina un colaborador del negocio.
     * DELETE /colaboradores/{id}
     *
     * Solo puede eliminar colaboradores de su propio negocio.
     */
    public function destroy(int $id): JsonResponse
    {
        $anfitrion   = auth()->user();
        $colaborador = User::where('id', $id)
            ->where('tenant_id', $anfitrion->tenant_id)
            ->whereHas('roles', fn($q) => $q->where('name', 'colaborador'))
            ->firstOrFail();

        $colaborador->syncRoles([]);
        $colaborador->delete();

        return response()->json(['ok' => true]);
    }
}
