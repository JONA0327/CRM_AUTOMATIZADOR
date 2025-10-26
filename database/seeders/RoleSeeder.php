<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'name' => 'admin',
            'description' => 'Administrador del sistema con acceso completo'
        ]);

        Role::create([
            'name' => 'user',
            'description' => 'Usuario regular del CRM'
        ]);

        Role::create([
            'name' => 'manager',
            'description' => 'Gerente con acceso a reportes y gestión de equipos'
        ]);

        Role::create([
            'name' => 'sales',
            'description' => 'Vendedor con acceso al módulo de ventas'
        ]);
    }
}
