<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saved_prompts', function (Blueprint $table) {
            $table->string('tipo', 30)->default('prompt')->after('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('saved_prompts', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
