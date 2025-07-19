<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SimpleCaptchaSolver
{
    /**
     * Решить арифметическую CAPTCHA с простым распознаванием
     */
    public function solveArithmeticCaptcha(array $captchaData): ?int
    {
        try {
            Log::info("=== Начинаем простое решение CAPTCHA ===");
            Log::info("Операция: " . $captchaData['operation']);
            Log::info("URL1: " . $captchaData['captcha1_url']);
            Log::info("URL2: " . $captchaData['captcha2_url']);

            // Загружаем изображения CAPTCHA
            $captcha1Response = \Illuminate\Support\Facades\Http::timeout(10)->get($captchaData['captcha1_url']);
            $captcha2Response = \Illuminate\Support\Facades\Http::timeout(10)->get($captchaData['captcha2_url']);

            if (!$captcha1Response->successful() || !$captcha2Response->successful()) {
                Log::error("Не удалось загрузить изображения CAPTCHA");
                return null;
            }

            // Сохраняем изображения во временные файлы
            $captcha1Path = tempnam(sys_get_temp_dir(), 'captcha1_') . '.png';
            $captcha2Path = tempnam(sys_get_temp_dir(), 'captcha2_') . '.png';

            file_put_contents($captcha1Path, $captcha1Response->body());
            file_put_contents($captcha2Path, $captcha2Response->body());

            // Сохраняем копии для отладки
            $debugPath1 = storage_path('logs/captcha1_debug.png');
            $debugPath2 = storage_path('logs/captcha2_debug.png');
            copy($captcha1Path, $debugPath1);
            copy($captcha2Path, $debugPath2);

            Log::info("Изображения сохранены: {$captcha1Path}, {$captcha2Path}");
            Log::info("Отладочные копии: {$debugPath1}, {$debugPath2}");

            // Распознаем числа простым алгоритмом
            $number1 = $this->recognizeDigitSimple($captcha1Path);
            $number2 = $this->recognizeDigitSimple($captcha2Path);

            Log::info("Распознанные числа: {$number1}, {$number2}");

            // Удаляем временные файлы
            unlink($captcha1Path);
            unlink($captcha2Path);

            if ($number1 === null || $number2 === null) {
                Log::error("Не удалось распознать числа в CAPTCHA");
                return null;
            }

            // Выполняем математическую операцию
            $operation = $captchaData['operation'];
            $result = $this->performOperation($number1, $number2, $operation);

            Log::info("CAPTCHA решена: {$number1} {$operation} {$number2} = {$result}");

            return $result;

        } catch (\Exception $e) {
            Log::error("Ошибка при решении CAPTCHA: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Простое распознавание цифры на изображении
     */
    private function recognizeDigitSimple(string $imagePath): ?int
    {
        try {
            // Загружаем изображение
            $image = imagecreatefrompng($imagePath);
            if (!$image) {
                Log::error("Не удалось загрузить изображение: {$imagePath}");
                return null;
            }

            $width = imagesx($image);
            $height = imagesy($image);

            Log::info("Размер изображения: {$width}x{$height}");

            // Простая бинаризация: черный = 0, белый/светлый = 1
            $binary = [];
            $blackPixels = 0;

            for ($y = 0; $y < $height; $y++) {
                $binary[$y] = [];
                for ($x = 0; $x < $width; $x++) {
                    $rgb = imagecolorat($image, $x, $y);
                    $r = ($rgb >> 16) & 0xFF;
                    $g = ($rgb >> 8) & 0xFF;
                    $b = $rgb & 0xFF;

                    // Простая бинаризация: если пиксель темный (черный), считаем его частью цифры
                    $gray = (int)(0.299 * $r + 0.587 * $g + 0.114 * $b);
                    $isBlack = ($gray < 128); // Порог 128

                    $binary[$y][$x] = $isBlack ? 1 : 0;
                    if ($isBlack) {
                        $blackPixels++;
                    }
                }
            }

            $totalPixels = $width * $height;
            $density = $blackPixels / $totalPixels;

            Log::info("Плотность черных пикселей: " . round($density * 100, 2) . "%");

            // Анализируем структуру цифры
            $features = $this->analyzeDigitStructure($binary, $width, $height);

            // Распознаем цифру по характеристикам
            $digit = $this->classifyDigitByFeatures($features, $density);

            imagedestroy($image);

            Log::info("Распознана цифра: {$digit}");
            return $digit;

        } catch (\Exception $e) {
            Log::error("Ошибка при распознавании цифры: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Анализ структуры цифры
     */
    private function analyzeDigitStructure(array $binary, int $width, int $height): array
    {
        $features = [
            'top_half' => 0,
            'bottom_half' => 0,
            'left_half' => 0,
            'right_half' => 0,
            'center' => 0,
            'top_left' => 0,
            'top_right' => 0,
            'bottom_left' => 0,
            'bottom_right' => 0,
            'holes' => 0,
        ];

        $midY = (int)($height / 2);
        $midX = (int)($width / 2);

        // Подсчитываем пиксели в разных областях
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if ($binary[$y][$x] == 1) {
                    // Верхняя половина
                    if ($y < $midY) {
                        $features['top_half']++;
                    }
                    // Нижняя половина
                    if ($y >= $midY) {
                        $features['bottom_half']++;
                    }
                    // Левая половина
                    if ($x < $midX) {
                        $features['left_half']++;
                    }
                    // Правая половина
                    if ($x >= $midX) {
                        $features['right_half']++;
                    }
                    // Центр
                    if ($x >= $midX - 2 && $x <= $midX + 2 && $y >= $midY - 2 && $y <= $midY + 2) {
                        $features['center']++;
                    }
                    // Углы
                    if ($x < $midX && $y < $midY) {
                        $features['top_left']++;
                    }
                    if ($x >= $midX && $y < $midY) {
                        $features['top_right']++;
                    }
                    if ($x < $midX && $y >= $midY) {
                        $features['bottom_left']++;
                    }
                    if ($x >= $midX && $y >= $midY) {
                        $features['bottom_right']++;
                    }
                }
            }
        }

        // Подсчитываем отверстия (области без черных пикселей)
        $features['holes'] = $this->countHoles($binary, $width, $height);

        Log::info("Характеристики цифры: " . json_encode($features));

        return $features;
    }

    /**
     * Подсчет отверстий в цифре
     */
    private function countHoles(array $binary, int $width, int $height): int
    {
        // Простой алгоритм подсчета отверстий
        $holes = 0;
        $visited = [];

        // Ищем белые области, окруженные черными пикселями
        for ($y = 1; $y < $height - 1; $y++) {
            for ($x = 1; $x < $width - 1; $x++) {
                if ($binary[$y][$x] == 0 && !isset($visited[$y][$x])) {
                    // Проверяем, окружена ли эта область черными пикселями
                    if ($this->isSurroundedByBlack($binary, $x, $y, $width, $height, $visited)) {
                        $holes++;
                    }
                }
            }
        }

        return $holes;
    }

    /**
     * Проверка, окружена ли область черными пикселями
     */
    private function isSurroundedByBlack(array $binary, int $x, int $y, int $width, int $height, array &$visited): bool
    {
        if ($x < 0 || $x >= $width || $y < 0 || $y >= $height || $binary[$y][$x] == 1 || isset($visited[$y][$x])) {
            return false;
        }

        $visited[$y][$x] = true;

        // Если достигли края изображения, это не отверстие
        if ($x == 0 || $x == $width - 1 || $y == 0 || $y == $height - 1) {
            return false;
        }

        // Проверяем соседние пиксели
        $surrounded = true;
        $directions = [[-1, 0], [1, 0], [0, -1], [0, 1]];

        foreach ($directions as [$dx, $dy]) {
            $nx = $x + $dx;
            $ny = $y + $dy;

            if ($nx >= 0 && $nx < $width && $ny >= 0 && $ny < $height) {
                if ($binary[$ny][$nx] == 0 && !isset($visited[$ny][$nx])) {
                    if (!$this->isSurroundedByBlack($binary, $nx, $ny, $width, $height, $visited)) {
                        $surrounded = false;
                    }
                }
            }
        }

        return $surrounded;
    }

    /**
     * Классификация цифры по характеристикам
     */
    private function classifyDigitByFeatures(array $features, float $density): int
    {
        // Простые правила для распознавания цифр
        $holes = $features['holes'];
        $topHalf = $features['top_half'];
        $bottomHalf = $features['bottom_half'];
        $leftHalf = $features['left_half'];
        $rightHalf = $features['right_half'];
        $center = $features['center'];

        Log::info("Анализ: отверстия={$holes}, верх={$topHalf}, низ={$bottomHalf}, лево={$leftHalf}, право={$rightHalf}, центр={$center}");

        // Правила распознавания
        if ($holes == 1) {
            // Цифры с одним отверстием: 0, 6, 9
            if ($topHalf > $bottomHalf * 1.5) {
                return 9; // 9 - больше в верхней части
            } elseif ($bottomHalf > $topHalf * 1.5) {
                return 6; // 6 - больше в нижней части
            } else {
                return 0; // 0 - равномерно распределена
            }
        } elseif ($holes == 2) {
            return 8; // 8 - два отверстия
        } elseif ($holes == 0) {
            // Цифры без отверстий: 1, 2, 3, 4, 5, 7
            if ($leftHalf < $rightHalf * 0.3) {
                return 1; // 1 - очень тонкая
            } elseif ($topHalf > $bottomHalf * 2) {
                return 7; // 7 - в основном в верхней части
            } elseif ($rightHalf > $leftHalf * 1.5) {
                return 3; // 3 - больше справа
            } elseif ($topHalf > $bottomHalf * 1.2 && $rightHalf > $leftHalf * 1.2) {
                return 2; // 2 - больше сверху и справа
            } elseif ($leftHalf > $rightHalf * 1.2) {
                return 5; // 5 - больше слева
            } else {
                return 4; // 4 - по умолчанию
            }
        }

        // Если не удалось определить, возвращаем 0
        return 0;
    }

    /**
     * Выполнить математическую операцию
     */
    private function performOperation(int $a, int $b, string $operation): int
    {
        switch ($operation) {
            case '+':
                return $a + $b;
            case '-':
                return $a - $b;
            case '*':
            case 'x':
                return $a * $b;
            case '/':
                return $b != 0 ? (int)($a / $b) : 0;
            default:
                Log::error("Неизвестная операция: {$operation}");
                return 0;
        }
    }
}
