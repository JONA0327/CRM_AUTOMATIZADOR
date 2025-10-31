<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Especificar la conexión PostgreSQL para esta migración
    protected $connection = 'pgsql_secondary';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('pgsql_secondary')->create('secondary_data', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('data')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['name', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('pgsql_secondary')->dropIfExists('secondary_data');
    }
};
