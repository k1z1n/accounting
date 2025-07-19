<?php

namespace App\Console\Commands;

use App\Services\SimpleCaptchaSolver;
use Illuminate\Console\Command;

class TestCaptchaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:captcha {exchanger : Имя обменника (obama|ural)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тестирование CAPTCHA для обменников';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $exchanger = $this->argument('exchanger');

        if (!in_array($exchanger, ['obama', 'ural'])) {
            $this->error("Неизвестный обменник. Используйте 'obama' или 'ural'");
            return 1;
        }

        $this->info("🧪 Тестирование CAPTCHA для {$exchanger}...");

        // Создаем тестовые данные CAPTCHA
        $captchaData = [
            'operation' => '+',
            'captcha1_url' => 'https://example.com/captcha1.png',
            'captcha2_url' => 'https://example.com/captcha2.png'
        ];

        $solver = new SimpleCaptchaSolver();
        $result = $solver->solveArithmeticCaptcha($captchaData);

        if ($result !== null) {
            $this->info("✅ CAPTCHA решена: {$result}");
        } else {
            $this->error("❌ Не удалось решить CAPTCHA");
        }

        return 0;
    }
}












