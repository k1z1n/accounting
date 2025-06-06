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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Платформа (exchangers) – nullable, FK → exchangers.id с ON DELETE SET NULL
            $table->foreignId('exchanger_id')
                ->nullable()
                ->constrained()      // ↦ по умолчанию ссылается на таблицу 'exchangers'
                ->onDelete('set null');

            // Пользователь (users) – nullable, FK → users.id с ON DELETE SET NULL
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()      // ↦ по умолчанию ссылается на таблицу 'users'
                ->onDelete('set null');

            // Сумма продажи (просто decimal)
            $table->decimal('sell_amount', 20, 8)->nullable();

            // Ссылка на валюту продажи (currencies.id)
            $table->unsignedBigInteger('sell_currency_id')->nullable();
            $table->foreign('sell_currency_id')
                ->references('id')
                ->on('currencies')
                ->onDelete('set null');

            // Дополнительное поле — комментарий
            $table->string('comment')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
