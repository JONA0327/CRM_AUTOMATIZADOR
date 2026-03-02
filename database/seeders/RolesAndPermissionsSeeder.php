<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Crea los roles y permisos base del sistema.
 *
 * Ejecutar:
 *   php artisan db:seed --class=RolesAndPermissionsSeeder
 *
 * Roles:
 *  • super_admin   — Control total: tenants, APIs, BDs, prompts, configuración global
 *  • anfitrion     — Dueño del negocio: instancias, colaboradores, catálogos, bot básico
 *  • colaborador   — Acceso limitado: solo catálogos y conversaciones de su negocio
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles/permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permisos ────────────────────────────────────────────────────────
        $permisos = [
            // Global (Super Admin)
            'gestionar.negocios',       // CRUD de tenants/negocios
            'configurar.apis',          // Guardar API keys (OpenAI, Evolution, etc.)
            'configurar.prompts',       // Editar system_prompt
            'configurar.bd_externas',   // Conectar bases de datos externas

            // Negocio (Anfitrión)
            'gestionar.instancias',     // Crear/conectar instancias WhatsApp
            'gestionar.colaboradores',  // Invitar/eliminar colaboradores
            'configurar.bot_basico',    // Proveedor de IA, verificación WA (sin prompt/APIs)

            // Todos los usuarios del negocio
            'ver.dashboard',
            'gestionar.catalogos',      // CRUD de catálogos y módulos
            'ver.conversaciones',       // Ver historial del bot
            'ver.bot',                  // Ver estado del bot
        ];

        foreach ($permisos as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // ── Roles ────────────────────────────────────────────────────────────

        // Super Admin — todos los permisos
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // Anfitrión — gestión del propio negocio (sin APIs/prompts/BDs)
        $anfitrion = Role::firstOrCreate(['name' => 'anfitrion', 'guard_name' => 'web']);
        $anfitrion->syncPermissions([
            'ver.dashboard',
            'gestionar.instancias',
            'gestionar.colaboradores',
            'configurar.bot_basico',
            'gestionar.catalogos',
            'ver.conversaciones',
            'ver.bot',
        ]);

        // Colaborador — solo catálogos y conversaciones
        $colaborador = Role::firstOrCreate(['name' => 'colaborador', 'guard_name' => 'web']);
        $colaborador->syncPermissions([
            'ver.dashboard',
            'gestionar.catalogos',
            'ver.conversaciones',
            'ver.bot',
        ]);

        // ── Usuario Super Admin ──────────────────────────────────────────────
        $adminEmail    = env('SUPER_ADMIN_EMAIL',    'jona03278@gmail.com');
        $adminPassword = env('SUPER_ADMIN_PASSWORD', 'Jona@0327801');
        $adminName     = env('SUPER_ADMIN_NAME',     'Super Admin');

        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name'              => $adminName,
                'username'          => 'super-admin',
                'password'          => Hash::make($adminPassword),
                'tenant_id'         => null,
                'email_verified_at' => now(),
            ]
        );

        // Asignar rol (idempotente: no duplica si ya lo tiene)
        if (! $admin->hasRole('super_admin')) {
            $admin->assignRole('super_admin');
        }

        $this->command->info('✅ Roles y permisos creados: super_admin, anfitrion, colaborador');
        $this->command->info("✅ Super Admin: {$adminEmail} / {$adminPassword}");
    }
}
