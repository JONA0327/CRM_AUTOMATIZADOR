<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client', function (Blueprint $table) {
            $table->string('folio')->nullable()->unique()->after('id');
        });

        // Remover columna observation (ahora es una tabla relacionada)
        Schema::table('client', function (Blueprint $table) {
            $table->dropColumn('observation');
        });
    }

    public function down(): void
    {
        Schema::table('client', function (Blueprint $table) {
            $table->dropColumn('folio');
            $table->string('observation')->nullable();
        });
    }
};
