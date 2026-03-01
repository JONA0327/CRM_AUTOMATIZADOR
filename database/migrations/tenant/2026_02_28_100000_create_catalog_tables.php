<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Módulos (ej: Clientes, Agenda, Inventario...)
        Schema::create('catalog_modules', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->string('icono')->default('📋');
            $table->string('color', 7)->default('#6366f1');
            $table->boolean('activo')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });

        // Campos de cada módulo
        Schema::create('catalog_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('catalog_modules')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('slug');
            // text | number | date | select | relation | email | phone | textarea
            $table->string('tipo')->default('text');
            $table->boolean('obligatorio')->default(false);
            $table->json('opciones')->nullable();          // para tipo=select
            $table->string('modulo_relacion')->nullable(); // slug del módulo referenciado (tipo=relation)
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();

            $table->unique(['module_id', 'slug']);
        });

        // Registros genéricos (datos en JSON)
        Schema::create('catalog_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('catalog_modules')->cascadeOnDelete();
            $table->json('datos');
            $table->timestamps();

            $table->index(['module_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_records');
        Schema::dropIfExists('catalog_fields');
        Schema::dropIfExists('catalog_modules');
    }
};
