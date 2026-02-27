<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('client')->cascadeOnDelete();
            $table->decimal('weight', 5, 2)->nullable()->comment('Peso en kg');
            $table->unsignedSmallInteger('age')->nullable()->comment('Edad en años');
            $table->text('observation')->nullable()->comment('Observaciones generales');
            $table->text('suggested_products')->nullable()->comment('Productos sugeridos');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_observations');
    }
};
