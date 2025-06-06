    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class CreateApplicationsTable extends Migration
    {
        public function up(): void
        {
            Schema::create('applications', function (Blueprint $table) {
                $table->bigIncrements('id');

                // Номер заявки (ID из обменника)
                $table->unsignedBigInteger('app_id');

                // Дата создания заявки (из админки)
                $table->dateTime('app_created_at')->nullable();

                // Обменник: 'obama' или 'ural' и т. д.
                $table->string('exchanger', 64);

                // Статус заявки (например, «выполненная заявка», «оплаченная заявка»)
                $table->string('status', 64)->nullable();

                //
                // --- ЗАМЕНА: вместо (sale_amount + sale_currency_id) — единое текстовое поле
                //
                $table->string('sale_text', 64)->nullable()
                    ->comment('«Приход» как raw-строка, например "75000 RUB"');

                //
                // --- ПАРАМЕТРЫ ДЛЯ РУЧНОГО РЕДАКТИРОВАНИЯ: sell_*, buy_*, expense_* (не меняем) ---
                //
                $table->decimal('sell_amount', 20, 8)->nullable();
                $table->unsignedBigInteger('sell_currency_id')->nullable();
                $table->foreign('sell_currency_id')
                    ->references('id')->on('currencies')
                    ->onDelete('set null');

                $table->decimal('buy_amount', 20, 8)->nullable();
                $table->unsignedBigInteger('buy_currency_id')->nullable();
                $table->foreign('buy_currency_id')
                    ->references('id')->on('currencies')
                    ->onDelete('set null');

                $table->decimal('expense_amount', 20, 8)->nullable();
                $table->unsignedBigInteger('expense_currency_id')->nullable();
                $table->foreign('expense_currency_id')
                    ->references('id')->on('currencies')
                    ->onDelete('set null');

                // Мерчант (например, e-mail или имя мерчанта)
                $table->string('merchant', 128)->nullable();

                // ID ордера (например, txid или другой идентификатор)
                $table->string('order_id', 128)->nullable();

                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

                // Laravel-timestamps: created_at, updated_at
                $table->timestamps();

                // Составной уникальный индекс, чтобы не дублировать одну и ту же заявку от одного обменника
                $table->unique(['exchanger', 'app_id']);
            });
        }

        public function down()
        {
            Schema::dropIfExists('applications');
        }
    }
