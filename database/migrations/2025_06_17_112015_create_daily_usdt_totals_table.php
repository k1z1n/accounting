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
        Schema::create('daily_usdt_totals', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->decimal('total', 24, 8);
            $table->decimal('delta', 24, 8);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_usdt_totals');
    }
};
