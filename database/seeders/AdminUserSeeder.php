<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar el rol de administrador
        $adminRole = Role::where('name', 'admin')->first();

        if ($adminRole) {
            // Crear usuario administrador
            User::create([
                'name' => 'Jonathan Administrador',
                'email' => 'jona03278@gmail.com',
                'password' => Hash::make('Jona@03278'),
                'role_id' => $adminRole->id,
                'email_verified_at' => now(),
            ]);

            $this->command->info('Usuario administrador creado exitosamente!');
            $this->command->info('Email: jona03278@gmail.com');
            $this->command->info('Contraseña: Jona@03278');
        } else {
            $this->command->error('No se encontró el rol de administrador. Ejecuta primero el RoleSeeder.');
        }
    }
}
