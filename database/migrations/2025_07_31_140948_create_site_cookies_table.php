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
        Schema::create('site_cookies', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // OBAMA, URAL
            $table->string('url'); // URL сайта
            $table->string('phpsessid')->nullable(); // PHPSESSID
            $table->string('premium_session_id')->nullable(); // premium_session_id
            $table->string('wordpress_logged_title')->nullable(); // wordpress_logged_in_...
            $table->text('wordpress_logged_value')->nullable(); // значение wordpress_logged_in
            $table->string('wordpress_sec_title')->nullable(); // wordpress_sec_...
            $table->text('wordpress_sec_value')->nullable(); // значение wordpress_sec
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_cookies');
    }
};
