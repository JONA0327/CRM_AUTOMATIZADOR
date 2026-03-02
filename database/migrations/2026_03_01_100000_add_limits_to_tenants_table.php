<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega límites operacionales a cada negocio (tenant):
 *  - max_instances    : cuántas instancias de WhatsApp puede tener (default 1)
 *  - max_collaborators: cuántos colaboradores puede invitar (default 3)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->unsignedSmallInteger('max_instances')
                  ->default(1)
                  ->after('db_name')
                  ->comment('Máx. instancias WhatsApp permitidas');

            $table->unsignedSmallInteger('max_collaborators')
                  ->default(3)
                  ->after('max_instances')
                  ->comment('Máx. colaboradores que puede invitar el anfitrión');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['max_instances', 'max_collaborators']);
        });
    }
};
