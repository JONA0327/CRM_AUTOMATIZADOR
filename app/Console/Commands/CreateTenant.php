<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateTenant extends Command
{
    protected $signature = 'tenant:create
                            {nombre    : Nombre del negocio}
                            {slug      : Slug único (solo letras minúsculas, números y guiones)}
                            {email     : Email del usuario administrador}
                            {password  : Contraseña del administrador}
                            {--driver=mysql : Driver de BD del tenant (mysql|pgsql)}
                            {--db-name=     : Nombre de la BD (opcional, default: tenant_{slug})}';

    protected $description = 'Crea un nuevo tenant con su base de datos y usuario administrador.';

    public function handle(): int
    {
        $nombre   = $this->argument('nombre');
        $slug     = $this->argument('slug');
        $email    = $this->argument('email');
        $password = $this->argument('password');
        $driver   = $this->option('driver');
        $dbName   = $this->option('db-name') ?: null;

        // Validaciones básicas
        if (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
            $this->error("El slug solo puede contener letras minúsculas, números y guiones.");
            return self::FAILURE;
        }

        if (Tenant::where('slug', $slug)->exists()) {
            $this->error("Ya existe un tenant con el slug '{$slug}'.");
            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error("Ya existe un usuario con el email '{$email}'.");
            return self::FAILURE;
        }

        $this->info("Creando tenant '{$nombre}'...");

        // 1. Crear tenant (stancl auto-provisiona la BD)
        $tenant = Tenant::create([
            'id'        => (string) Str::uuid(),
            'nombre'    => $nombre,
            'slug'      => $slug,
            'db_driver' => $driver,
            'db_name'   => $dbName,
        ]);

        $this->info("  ✓ Tenant creado: {$tenant->id}");
        $this->info("  ✓ Base de datos: {$tenant->getDatabaseName()} ({$driver})");

        // 2. Ejecutar migraciones del tenant
        $this->info("  Ejecutando migraciones...");
        $exitCode = \Illuminate\Support\Facades\Artisan::call('tenants:migrate', [
            '--tenants' => [$tenant->id],
            '--force'   => true,
        ]);

        if ($exitCode !== 0) {
            $this->error("  ✗ Error al ejecutar migraciones. Revisa el log.");
            return self::FAILURE;
        }

        $this->info("  ✓ Migraciones completadas.");

        // 3. Crear usuario admin en BD central
        $user = User::create([
            'name'              => $nombre . ' Admin',
            'username'          => $slug . '-admin',
            'email'             => $email,
            'email_verified_at' => now(),
            'password'          => Hash::make($password),
            'tenant_id'         => $tenant->id,
        ]);

        $this->info("  ✓ Usuario administrador creado: {$user->email}");

        $this->newLine();
        $this->table(
            ['Campo', 'Valor'],
            [
                ['Tenant ID',    $tenant->id],
                ['Nombre',       $tenant->nombre],
                ['Slug',         $tenant->slug],
                ['Base de datos', $tenant->getDatabaseName()],
                ['Driver',       $driver],
                ['Admin email',  $user->email],
            ]
        );

        $this->newLine();
        $this->info("¡Tenant '{$nombre}' listo! Accede con: {$email}");

        return self::SUCCESS;
    }
}
