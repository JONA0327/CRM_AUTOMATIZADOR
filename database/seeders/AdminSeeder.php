<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Crear tenant del admin (si no existe) ──────────────────────────
        $tenant = Tenant::where('slug', 'jona')->first();

        if (! $tenant) {
            $tenant = Tenant::create([
                'id'        => (string) Str::uuid(),
                'nombre'    => 'Jona Admin',
                'slug'      => 'jona',
                'db_driver' => 'mysql',
                'db_name'   => 'tenant_jona',
            ]);

            $this->command->info("  Tenant creado: {$tenant->id}");

            // Ejecutar migraciones en la BD del tenant
            Artisan::call('tenants:migrate', [
                '--tenants' => [$tenant->id],
                '--force'   => true,
            ]);

            $this->command->info("  Migraciones del tenant completadas.");
        } else {
            $this->command->info("  Tenant ya existe: {$tenant->id}");
        }

        // ── 2. Crear o actualizar el usuario admin ────────────────────────────
        User::updateOrCreate(
            ['email' => 'jona03278@gmail.com'],
            [
                'name'              => 'Jona Admin',
                'username'          => 'jona03278',
                'email'             => 'jona03278@gmail.com',
                'password'          => Hash::make('Jona@03278'),
                'role'              => 'admin',
                'email_verified_at' => now(),
                'tenant_id'         => $tenant->id,
            ]
        );

        $this->command->info("  Usuario admin listo: jona03278@gmail.com (tenant: {$tenant->id})");
    }
}
