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
        Schema::table('diseases', function (Blueprint $table) {
            $table->text('suggested')->nullable()->change();
            $table->text('symptoms')->nullable()->change();
            $table->text('treatment')->nullable()->change();
            $table->text('prevention')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('diseases', function (Blueprint $table) {
            $table->string('suggested')->nullable()->change();
            $table->string('symptoms')->nullable()->change();
            $table->string('treatment')->nullable()->change();
            $table->string('prevention')->nullable()->change();
        });
    }
};
