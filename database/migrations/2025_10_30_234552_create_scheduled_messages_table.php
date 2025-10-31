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
        Schema::create('scheduled_messages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message_text')->nullable();
            $table->longText('audio_data')->nullable(); // Base64 del audio
            $table->enum('category', [
                'bienvenida',
                'seguimiento',
                'contestar_preguntas',
                'informacion_productos'
            ]);
            $table->string('associated_question')->nullable(); // Para contestar_preguntas
            $table->time('start_time')->nullable(); // Hora inicio (México)
            $table->time('end_time')->nullable(); // Hora fin (México)
            $table->enum('time_period', ['mañana', 'tarde', 'noche'])->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable(); // Información adicional
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['category', 'is_active']);
            $table->index(['time_period', 'is_active']);
            $table->index(['start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_messages');
    }
};
