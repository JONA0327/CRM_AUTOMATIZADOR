<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_fields', function (Blueprint $table) {
            // Configuración extra por tipo:
            // file → {"accept":"image|video|all", "max_mb":10}
            // url  → null
            $table->json('meta')->nullable()->after('modulo_relacion');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_fields', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
    }
};
