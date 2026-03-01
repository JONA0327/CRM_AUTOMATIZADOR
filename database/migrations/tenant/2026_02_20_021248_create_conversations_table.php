<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->nullable()->index();
            $table->string('instancia')->nullable()->index();
            $table->string('contact_name')->nullable();
            $table->text('user_message');
            $table->text('bot_response');
            $table->string('status')->default('ok');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
