<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AdvancedCaptchaSolver;
use Illuminate\Support\Facades\Log;

class TestAdvancedCaptchaFakeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'captcha:test-fake {--debug} {--operation=+} {--number1=5} {--number2=3}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тестирование улучшенного решателя CAPTCHA с фейковыми данными';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $debug = $this->option('debug');
        $operation = $this->option('operation');
        $number1 = (int)$this->option('number1');
        $number2 = (int)$this->option('number2');

        $this->info("Тестирование улучшенного решателя CAPTCHA с фейковыми данными");
        $this->line("Операция: {$number1} {$operation} {$number2}");

        try {
            // Создаем фейковые изображения CAPTCHA
            $captchaData = $this->createFakeCaptchaData($number1, $number2, $operation);

            if (!$captchaData) {
                $this->error("Не удалось создать фейковые данные CAPTCHA");
                return 1;
            }

            $this->info("Созданы фейковые изображения CAPTCHA:");
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

                // Проверяем правильность
                $expectedResult = $this->calculateExpectedResult($number1, $number2, $operation);
                if ($result === $expectedResult) {
                    $this->info("✅ Результат корректен!");
                } else {
                    $this->warn("⚠️  Результат некорректен. Ожидалось: {$expectedResult}");
                }

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
            Log::error("Ошибка в команде TestAdvancedCaptchaFakeCommand: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Создать фейковые данные CAPTCHA
     */
    private function createFakeCaptchaData(int $number1, int $number2, string $operation): ?array
    {
        try {
            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $captcha1Path = $tempDir . '/fake_captcha1.png';
            $captcha2Path = $tempDir . '/fake_captcha2.png';

            // Создаем изображения с цифрами
            $this->createDigitImage($number1, $captcha1Path);
            $this->createDigitImage($number2, $captcha2Path);

            return [
                'captcha1_url' => $captcha1Path,
                'captcha2_url' => $captcha2Path,
                'operation' => $operation,
            ];

        } catch (\Exception $e) {
            $this->error("Ошибка при создании фейковых данных: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Создать изображение с цифрой
     */
    private function createDigitImage(int $digit, string $path): void
    {
        $width = 50;
        $height = 70;

        $image = imagecreate($width, $height);

        // Цвета
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);

        // Заполняем белым
        imagefill($image, 0, 0, $white);

        // Рисуем цифру
        $this->drawDigit($image, $digit, $width, $height, $black);

        // Сохраняем
        imagepng($image, $path);
        imagedestroy($image);
    }

    /**
     * Нарисовать цифру
     */
    private function drawDigit($image, int $digit, int $width, int $height, int $color): void
    {
        $segments = $this->getDigitSegments($digit);

        foreach ($segments as $segment) {
            $this->drawSegment($image, $segment, $width, $height, $color);
        }
    }

    /**
     * Получить сегменты для цифры (7-сегментный дисплей)
     */
    private function getDigitSegments(int $digit): array
    {
        $segments = [
            0 => [0, 1, 2, 3, 4, 5],      // a, b, c, d, e, f
            1 => [1, 2],                   // b, c
            2 => [0, 1, 3, 4, 6],         // a, b, d, e, g
            3 => [0, 1, 2, 3, 6],         // a, b, c, d, g
            4 => [1, 2, 5, 6],            // b, c, f, g
            5 => [0, 2, 3, 5, 6],         // a, c, d, f, g
            6 => [0, 2, 3, 4, 5, 6],      // a, c, d, e, f, g
            7 => [0, 1, 2],               // a, b, c
            8 => [0, 1, 2, 3, 4, 5, 6],   // все сегменты
            9 => [0, 1, 2, 3, 5, 6],      // a, b, c, d, f, g
        ];

        return $segments[$digit] ?? [];
    }

    /**
     * Нарисовать сегмент
     */
    private function drawSegment($image, int $segment, int $width, int $height, int $color): void
    {
        $margin = 5;
        $segmentWidth = 3;

        $w = $width - 2 * $margin;
        $h = $height - 2 * $margin;

        switch ($segment) {
            case 0: // Верхняя горизонтальная
                $this->drawHorizontalLine($image, $margin, $margin, $w, $segmentWidth, $color);
                break;
            case 1: // Верхняя правая вертикальная
                $this->drawVerticalLine($image, $width - $margin - $segmentWidth, $margin, $h/2, $segmentWidth, $color);
                break;
            case 2: // Нижняя правая вертикальная
                $this->drawVerticalLine($image, $width - $margin - $segmentWidth, $height/2, $h/2, $segmentWidth, $color);
                break;
            case 3: // Нижняя горизонтальная
                $this->drawHorizontalLine($image, $margin, $height - $margin - $segmentWidth, $w, $segmentWidth, $color);
                break;
            case 4: // Нижняя левая вертикальная
                $this->drawVerticalLine($image, $margin, $height/2, $h/2, $segmentWidth, $color);
                break;
            case 5: // Верхняя левая вертикальная
                $this->drawVerticalLine($image, $margin, $margin, $h/2, $segmentWidth, $color);
                break;
            case 6: // Средняя горизонтальная
                $this->drawHorizontalLine($image, $margin, $height/2 - $segmentWidth/2, $w, $segmentWidth, $color);
                break;
        }
    }

    /**
     * Нарисовать горизонтальную линию
     */
    private function drawHorizontalLine($image, int $x, int $y, int $length, int $thickness, int $color): void
    {
        for ($i = 0; $i < $thickness; $i++) {
            imageline($image, $x, $y + $i, $x + $length, $y + $i, $color);
        }
    }

    /**
     * Нарисовать вертикальную линию
     */
    private function drawVerticalLine($image, int $x, int $y, int $length, int $thickness, int $color): void
    {
        for ($i = 0; $i < $thickness; $i++) {
            imageline($image, $x + $i, $y, $x + $i, $y + $length, $color);
        }
    }

    /**
     * Вычислить ожидаемый результат
     */
    private function calculateExpectedResult(int $a, int $b, string $operation): int
    {
        switch ($operation) {
            case '+':
                return $a + $b;
            case '-':
                return $a - $b;
            case '*':
                return $a * $b;
            case '/':
                return $b != 0 ? (int)($a / $b) : 0;
            default:
                return 0;
        }
    }

    /**
     * Показать отладочную информацию
     */
    private function showDebugInfo(array $captchaData, int $result): void
    {
        $this->info("\n📊 Отладочная информация:");

        $this->line("  Изображения созданы:");
        $this->line("    {$captchaData['captcha1_url']}");
        $this->line("    {$captchaData['captcha2_url']}");

        // Анализируем изображения
        $this->analyzeImage($captchaData['captcha1_url'], "Изображение 1");
        $this->analyzeImage($captchaData['captcha2_url'], "Изображение 2");
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





