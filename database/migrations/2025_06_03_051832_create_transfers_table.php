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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();

            // Платформа, с которой переводят деньги
            $table->unsignedBigInteger('exchanger_from_id')->nullable();
            $table->foreign('exchanger_from_id')
                ->references('id')->on('exchangers')
                ->onDelete('set null');

            // Платформа, куда переводят деньги
            $table->unsignedBigInteger('exchanger_to_id')->nullable();
            $table->foreign('exchanger_to_id')
                ->references('id')->on('exchangers')
                ->onDelete('set null');

            // Комиссия и её валюта
            $table->decimal('commission', 30, 8)->nullable();
            $table->unsignedBigInteger('commission_id')->nullable();
            $table->foreign('commission_id')
                ->references('id')->on('currencies')
                ->onDelete('set null');

            // Сумма перевода и её валюта
            $table->decimal('amount', 30, 8)->nullable();
            $table->unsignedBigInteger('amount_id')->nullable();
            $table->foreign('amount_id')
                ->references('id')->on('currencies')
                ->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
