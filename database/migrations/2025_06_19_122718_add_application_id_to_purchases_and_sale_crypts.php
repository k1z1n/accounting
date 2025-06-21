<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Для таблицы покупок
        Schema::table('purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('application_id')
                ->nullable()
                ->after('id')
                ->comment('Связь с заявкой из applications');

            $table->foreign('application_id')
                ->references('id')
                ->on('applications')
                ->onDelete('set null');
        });

        // Для таблицы продаж крипты
        Schema::table('sale_crypts', function (Blueprint $table) {
            $table->unsignedBigInteger('application_id')
                ->nullable()
                ->after('id')
                ->comment('Связь с заявкой из applications');

            $table->foreign('application_id')
                ->references('id')
                ->on('applications')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sale_crypts', function (Blueprint $table) {
            $table->dropForeign(['application_id']);
            $table->dropColumn('application_id');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['application_id']);
            $table->dropColumn('application_id');
        });
    }
};
