<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('histories', function (Blueprint $table) {
            $table->id();

            // Полиморфная связь – «к какой модели относится эта запись»:
            $table->unsignedBigInteger('sourceable_id')->nullable();
            $table->string('sourceable_type')->nullable();

            // Сумма операции (положительная → приход, отрицательная → расход)
            $table->decimal('amount', 30, 8)->nullable();

            // Валюта для этой суммы
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')
                ->references('id')->on('currencies')
                ->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('histories');
    }
};
