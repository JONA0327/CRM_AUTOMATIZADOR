<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_instances', function (Blueprint $table) {
            // Permite apagar una instancia individual sin eliminarla
            $table->boolean('activo')->default(true)->after('descripcion');
            // Marca la instancia predeterminada para envíos del bot
            $table->boolean('is_default')->default(false)->after('activo');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_instances', function (Blueprint $table) {
            $table->dropColumn(['activo', 'is_default']);
        });
    }
};
