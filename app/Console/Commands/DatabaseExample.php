<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Product;

class DatabaseExample extends Command
{
    protected $signature = 'demo:database';
    protected $description = 'Demostrar el uso de múltiples bases de datos';

    public function handle()
    {
        $this->info('=== EJEMPLOS DE MÚLTIPLES BASES DE DATOS ===');

        // 1. Usando Query Builder con conexión específica
        $this->info("\n1. Query Builder con conexión específica:");

        // Base de datos principal
        $usersCount = DB::connection('mysql')->table('users')->count();
        $this->info("   Usuarios en BD principal: {$usersCount}");

        // Segunda base de datos PostgreSQL (si existe)
        try {
            $secondaryData = DB::connection('pgsql_secondary')->select('SELECT 1 as test');
            $this->info("   Conexión a BD secundaria PostgreSQL: ✓ Exitosa");
        } catch (\Exception $e) {
            $this->warn("   Conexión a BD secundaria PostgreSQL: ✗ No disponible");
        }

        // 2. Usando Eloquent con conexión específica
        $this->info("\n2. Eloquent con conexión específica:");

        // Consulta en la BD principal
        $products = Product::on('mysql')->count();
        $this->info("   Productos en BD principal: {$products}");

        // 3. Transacciones en múltiples bases de datos
        $this->info("\n3. Transacciones independientes:");

        DB::connection('mysql')->transaction(function () {
            $this->info("   Transacción en BD principal ejecutada");
        });

        // 4. Migraciones por conexión
        $this->info("\n4. Comandos útiles para múltiples BD:");
        $this->info("   php artisan migrate --database=mysql");
        $this->info("   php artisan migrate --database=pgsql_secondary");
        $this->info("   php artisan db:seed --database=pgsql_secondary");

        return 0;
    }
}
