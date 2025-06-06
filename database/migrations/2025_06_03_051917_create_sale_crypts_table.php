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
        Schema::create('sale_crypts', function (Blueprint $table) {
            $table->id();

            // Ссылка на платформу (exchanger), из которой продают крипту
            $table->unsignedBigInteger('exchanger_id')->nullable();
            $table->foreign('exchanger_id')
                ->references('id')
                ->on('exchangers')
                ->onDelete('set null');

            //
            // --- «Продажа» (sale): сумма + валюта ---
            //
            $table->decimal('sale_amount', 30, 8)->nullable();
            $table->unsignedBigInteger('sale_currency_id')->nullable();
            $table->foreign('sale_currency_id')
                ->references('id')
                ->on('currencies')
                ->onDelete('set null');

            //
            // --- «Фикс» (fixed): сумма + валюта ---
            //
            $table->decimal('fixed_amount', 30, 8)->nullable();
            $table->unsignedBigInteger('fixed_currency_id')->nullable();
            $table->foreign('fixed_currency_id')
                ->references('id')
                ->on('currencies')
                ->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_crypts');
    }
};
