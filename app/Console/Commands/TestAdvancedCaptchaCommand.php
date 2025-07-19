<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AdvancedCaptchaSolver;
use Illuminate\Support\Facades\Log;

class TestAdvancedCaptchaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'captcha:test-advanced {--exchanger=obama} {--debug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тестирование улучшенного решателя арифметической CAPTCHA';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $exchangerName = $this->option('exchanger');
        $debug = $this->option('debug');

        $this->info("Тестирование улучшенного решателя CAPTCHA для обменника: {$exchangerName}");

        try {
            // Получаем данные CAPTCHA с сайта
            $captchaData = $this->getCaptchaData($exchangerName);

            if (!$captchaData) {
                $this->error("Не удалось получить данные CAPTCHA с сайта {$exchangerName}");
                return 1;
            }

            $this->info("Получены данные CAPTCHA:");
            $this->line("  Операция: {$captchaData['operation']}");
            $this->line("  Изображение 1: {$captchaData['captcha1_url']}");
            $this->line("  Изображение 2: {$captchaData['captcha2_url']}");

            // Тестируем улучшенный решатель
            $solver = new AdvancedCaptchaSolver();

            $this->info("\nЗапуск улучшенного решателя CAPTCHA...");
            $startTime = microtime(true);

            $result = $solver->solveArithmeticCaptcha($captchaData);

            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2);

            if ($result !== null) {
                $this->info("✅ CAPTCHA решена успешно!");
                $this->line("  Результат: {$result}");
                $this->line("  Время выполнения: {$executionTime} мс");

                if ($debug) {
                    $this->showDebugInfo($captchaData, $result);
                }
            } else {
                $this->error("❌ Не удалось решить CAPTCHA");
                $this->line("  Время выполнения: {$executionTime} мс");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("Ошибка при тестировании: " . $e->getMessage());
            Log::error("Ошибка в команде TestAdvancedCaptchaCommand: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Получить данные CAPTCHA с сайта
     */
    private function getCaptchaData(string $exchangerName): ?array
    {
        $exchangers = [
            'obama' => [
                'login_url' => 'https://obama.ru/prmmxchngr',
            ],
            'ural' => [
                'login_url' => 'https://ural-obmen.ru/prmmxchngr',
            ]
        ];

        if (!isset($exchangers[$exchangerName])) {
            $this->error("Неизвестный обменник: {$exchangerName}");
            return null;
        }

        $config = $exchangers[$exchangerName];

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get($config['login_url']);

            if (!$response->successful()) {
                $this->error("Не удалось получить страницу логина");
                return null;
            }

            $html = $response->body();

            // Извлекаем данные CAPTCHA
            if (preg_match('/<img[^>]*class="captcha1"[^>]*src="([^"]+)"/', $html, $matches1) &&
                preg_match('/<img[^>]*class="captcha2"[^>]*src="([^"]+)"/', $html, $matches2) &&
                preg_match('/<span class="captcha_sym">([^<]+)</', $html, $matches3)) {

                return [
                    'captcha1_url' => $matches1[1],
                    'captcha2_url' => $matches2[1],
                    'operation' => trim($matches3[1]),
                ];
            }

            $this->error("Не удалось извлечь данные CAPTCHA из HTML");
            return null;

        } catch (\Exception $e) {
            $this->error("Ошибка при получении данных CAPTCHA: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Показать отладочную информацию
     */
    private function showDebugInfo(array $captchaData, int $result): void
    {
        $this->info("\n📊 Отладочная информация:");

        // Сохраняем изображения для анализа
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $captcha1Path = $tempDir . '/debug_captcha1.png';
        $captcha2Path = $tempDir . '/debug_captcha2.png';

        try {
            $captcha1Response = \Illuminate\Support\Facades\Http::timeout(10)->get($captchaData['captcha1_url']);
            $captcha2Response = \Illuminate\Support\Facades\Http::timeout(10)->get($captchaData['captcha2_url']);

            if ($captcha1Response->successful() && $captcha2Response->successful()) {
                file_put_contents($captcha1Path, $captcha1Response->body());
                file_put_contents($captcha2Path, $captcha2Response->body());

                $this->line("  Изображения сохранены:");
                $this->line("    {$captcha1Path}");
                $this->line("    {$captcha2Path}");

                // Анализируем изображения
                $this->analyzeImage($captcha1Path, "Изображение 1");
                $this->analyzeImage($captcha2Path, "Изображение 2");
            }
        } catch (\Exception $e) {
            $this->line("  Ошибка при сохранении изображений: " . $e->getMessage());
        }
    }

    /**
     * Анализ изображения
     */
    private function analyzeImage(string $imagePath, string $label): void
    {
        try {
            $image = imagecreatefrompng($imagePath);
            if (!$image) {
                $this->line("  {$label}: Не удалось загрузить изображение");
                return;
            }

            $width = imagesx($image);
            $height = imagesy($image);

            $this->line("  {$label}:");
            $this->line("    Размер: {$width}x{$height}");

            // Подсчет пикселей
            $blackPixels = 0;
            $totalPixels = $width * $height;

            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    $rgb = imagecolorat($image, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    if ($r < 100 && $g < 100 && $b < 100) {
                        $blackPixels++;
                    }
                }
            }

            $density = $blackPixels / $totalPixels;
            $this->line("    Плотность черных пикселей: " . round($density * 100, 2) . "%");

            imagedestroy($image);

        } catch (\Exception $e) {
            $this->line("  {$label}: Ошибка анализа - " . $e->getMessage());
        }
    }
}





