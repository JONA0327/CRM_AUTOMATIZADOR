<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mapeo de instancias de Evolution API → tenant
        Schema::create('tenant_instances', function (Blueprint $table) {
            $table->id();
            $table->char('tenant_id', 36)->index();
            $table->string('instance_name')->unique(); // nombre exacto de la instancia en Evolution API
            $table->string('descripcion')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // Agregar tenant_id a users (tabla landlord)
        Schema::table('users', function (Blueprint $table) {
            $table->char('tenant_id', 36)->nullable()->after('id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
        Schema::dropIfExists('tenant_instances');
    }
};
