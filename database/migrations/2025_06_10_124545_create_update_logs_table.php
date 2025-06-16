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
        Schema::create('update_logs', function (Blueprint $table) {
            $table->id();
            // Ссылка на пользователя, который совершил изменение
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            // Полиморфная связь: ID и тип сущности, которую обновляли
            $table->unsignedBigInteger('sourceable_id')->nullable();
            $table->string('sourceable_type')->nullable();
            // JSON или текст описания изменений
            $table->longText('update')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('update_logs');
    }
};
