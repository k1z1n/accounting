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
        Schema::table('users', function (Blueprint $table) {
            // Изменяем enum для роли, добавляя новые роли
            $table->enum('role', ['user', 'admin', 'accountant', 'statistician'])
                  ->default('user')
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Возвращаем к исходному состоянию
            $table->enum('role', ['user', 'admin'])
                  ->default('user')
                  ->change();
        });
    }
};
