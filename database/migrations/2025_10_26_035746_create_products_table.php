<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('name');
            $table->json('key_points')->nullable(); // Puntos clave
            $table->longText('information')->nullable(); // Información del producto
            $table->longText('image')->nullable(); // Imagen en base64
            $table->string('image_name')->nullable(); // Nombre de la imagen
            $table->longText('video')->nullable(); // Video en base64 (opcional)
            $table->string('video_name')->nullable(); // Nombre del video
            $table->string('disease')->nullable(); // Enfermedad asociada (opcional)
            $table->string('country'); // País
            $table->json('dosage'); // Dosis: preventivo, correctivo, crónico
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
