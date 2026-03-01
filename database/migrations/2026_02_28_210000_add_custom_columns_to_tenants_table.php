<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('nombre')->nullable()->after('id');
            $table->string('slug')->nullable()->unique()->after('nombre');
            $table->string('db_driver', 10)->default('mysql')->after('slug');
            $table->string('db_name')->nullable()->after('db_driver');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['nombre', 'slug', 'db_driver', 'db_name']);
        });
    }
};
