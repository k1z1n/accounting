<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AdvancedCaptchaSolver
{
    /**
     * Решить арифметическую CAPTCHA с улучшенным распознаванием
     */
    public function solveArithmeticCaptcha(array $captchaData): ?int
    {
        try {
            Log::info("=== Начинаем решение CAPTCHA ===");
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

            // Распознаем числа с улучшенным алгоритмом
            $number1 = $this->recognizeNumberAdvanced($captcha1Path);
            $number2 = $this->recognizeNumberAdvanced($captcha2Path);

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
     * Улучшенное распознавание числа на изображении
     */
    private function recognizeNumberAdvanced(string $imagePath): ?int
    {
        try {
            // Определяем тип изображения и загружаем
            $imageInfo = getimagesize($imagePath);
            if (!$imageInfo) {
                Log::error("Не удалось определить тип изображения: {$imagePath}");
                return null;
            }

            $image = null;
            switch ($imageInfo[2]) {
                case IMAGETYPE_PNG:
                    $image = imagecreatefrompng($imagePath);
                    break;
                case IMAGETYPE_JPEG:
                    $image = imagecreatefromjpeg($imagePath);
                    break;
                case IMAGETYPE_GIF:
                    $image = imagecreatefromgif($imagePath);
                    break;
                default:
                    Log::error("Неподдерживаемый тип изображения: {$imagePath}");
                    return null;
            }

            if (!$image) {
                Log::error("Не удалось загрузить изображение: {$imagePath}");
                return null;
            }

            $width = imagesx($image);
            $height = imagesy($image);

            // Предобработка изображения
            $processedImage = $this->preprocessImage($image, $width, $height);

            // Анализ характеристик цифр
            $features = $this->extractDigitFeatures($processedImage, $width, $height);

            // Распознавание по характеристикам
            $recognizedNumber = $this->classifyDigit($features);

            imagedestroy($image);
            imagedestroy($processedImage);

            return $recognizedNumber;

        } catch (\Exception $e) {
            Log::error("Ошибка при распознавании числа: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Предобработка изображения
     */
    private function preprocessImage($image, int $width, int $height)
    {
        // Создаем новое изображение для обработки
        $processed = imagecreate($width, $height);

        // Устанавливаем цвета
        $white = imagecolorallocate($processed, 255, 255, 255);
        $black = imagecolorallocate($processed, 0, 0, 0);

        // Бинаризация и шумоподавление
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                // Улучшенная бинаризация с адаптивным порогом
                $gray = (int)(0.299 * $r + 0.587 * $g + 0.114 * $b);
                $threshold = $this->calculateAdaptiveThreshold($image, $x, $y, $width, $height);

                $color = ($gray < $threshold) ? $black : $white;
                imagesetpixel($processed, $x, $y, $color);
            }
        }

        return $processed;
    }

    /**
     * Вычисление адаптивного порога для бинаризации
     */
    private function calculateAdaptiveThreshold($image, int $x, int $y, int $width, int $height): int
    {
        $windowSize = 5;
        $sum = 0;
        $count = 0;

        for ($i = max(0, $x - $windowSize); $i <= min($width - 1, $x + $windowSize); $i++) {
            for ($j = max(0, $y - $windowSize); $j <= min($height - 1, $y + $windowSize); $j++) {
                $rgb = imagecolorat($image, $i, $j);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $gray = (int)(0.299 * $r + 0.587 * $g + 0.114 * $b);
                $sum += $gray;
                $count++;
            }
        }

        $average = $count > 0 ? $sum / $count : 128;
        return (int)($average * 0.8); // Порог на 20% ниже среднего
    }

    /**
     * Извлечение характеристик цифры
     */
    private function extractDigitFeatures($image, int $width, int $height): array
    {
        $features = [];

        // 1. Плотность пикселей
        $blackPixels = 0;
        $totalPixels = $width * $height;

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $color = imagecolorat($image, $x, $y);
                if ($color == 0) { // Черный пиксель
                    $blackPixels++;
                }
            }
        }

        $features['density'] = $blackPixels / $totalPixels;

        // 2. Центр масс
        $centerX = 0;
        $centerY = 0;
        $mass = 0;

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $color = imagecolorat($image, $x, $y);
                if ($color == 0) {
                    $centerX += $x;
                    $centerY += $y;
                    $mass++;
                }
            }
        }

        if ($mass > 0) {
            $features['center_x'] = $centerX / $mass;
            $features['center_y'] = $centerY / $mass;
        } else {
            $features['center_x'] = $width / 2;
            $features['center_y'] = $height / 2;
        }

        // 3. Анализ горизонтальных линий
        $features['horizontal_lines'] = $this->countHorizontalLines($image, $width, $height);

        // 4. Анализ вертикальных линий
        $features['vertical_lines'] = $this->countVerticalLines($image, $width, $height);

        // 5. Анализ областей (верх, низ, лево, право)
        $features['regions'] = $this->analyzeRegions($image, $width, $height);

        // 6. Анализ контуров и отверстий
        $features['holes'] = $this->countHoles($image, $width, $height);

        // 7. Соотношение сторон
        $features['aspect_ratio'] = $width / $height;

        return $features;
    }

    /**
     * Подсчет горизонтальных линий
     */
    private function countHorizontalLines($image, int $width, int $height): int
    {
        $lines = 0;
        $threshold = $height * 0.3; // Минимальная длина линии

        for ($y = 0; $y < $height; $y++) {
            $lineLength = 0;
            $maxLineLength = 0;

            for ($x = 0; $x < $width; $x++) {
                $color = imagecolorat($image, $x, $y);
                if ($color == 0) {
                    $lineLength++;
                } else {
                    if ($lineLength > $maxLineLength) {
                        $maxLineLength = $lineLength;
                    }
                    $lineLength = 0;
                }
            }

            if ($lineLength > $maxLineLength) {
                $maxLineLength = $lineLength;
            }

            if ($maxLineLength > $threshold) {
                $lines++;
            }
        }

        return $lines;
    }

    /**
     * Подсчет вертикальных линий
     */
    private function countVerticalLines($image, int $width, int $height): int
    {
        $lines = 0;
        $threshold = $width * 0.3; // Минимальная длина линии

        for ($x = 0; $x < $width; $x++) {
            $lineLength = 0;
            $maxLineLength = 0;

            for ($y = 0; $y < $height; $y++) {
                $color = imagecolorat($image, $x, $y);
                if ($color == 0) {
                    $lineLength++;
                } else {
                    if ($lineLength > $maxLineLength) {
                        $maxLineLength = $lineLength;
                    }
                    $lineLength = 0;
                }
            }

            if ($lineLength > $maxLineLength) {
                $maxLineLength = $lineLength;
            }

            if ($maxLineLength > $threshold) {
                $lines++;
            }
        }

        return $lines;
    }

    /**
     * Анализ областей изображения
     */
    private function analyzeRegions($image, int $width, int $height): array
    {
        $regions = [];

        // Разделяем изображение на 4 квадранта
        $halfWidth = $width / 2;
        $halfHeight = $height / 2;

        $regions['top_left'] = $this->countBlackPixelsInRegion($image, 0, 0, $halfWidth, $halfHeight);
        $regions['top_right'] = $this->countBlackPixelsInRegion($image, $halfWidth, 0, $halfWidth, $halfHeight);
        $regions['bottom_left'] = $this->countBlackPixelsInRegion($image, 0, $halfHeight, $halfWidth, $halfHeight);
        $regions['bottom_right'] = $this->countBlackPixelsInRegion($image, $halfWidth, $halfHeight, $halfWidth, $halfHeight);

        return $regions;
    }

    /**
     * Подсчет черных пикселей в области
     */
    private function countBlackPixelsInRegion($image, int $startX, int $startY, int $regionWidth, int $regionHeight): int
    {
        $count = 0;

        for ($x = $startX; $x < $startX + $regionWidth; $x++) {
            for ($y = $startY; $y < $startY + $regionHeight; $y++) {
                $color = imagecolorat($image, $x, $y);
                if ($color == 0) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Подсчет отверстий в цифре
     */
    private function countHoles($image, int $width, int $height): int
    {
        // Простой алгоритм подсчета замкнутых областей
        $holes = 0;
        $visited = [];

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                if (!isset($visited[$x][$y])) {
                    $color = imagecolorat($image, $x, $y);
                    if ($color == 1) { // Белый пиксель (потенциальное отверстие)
                        $area = $this->floodFill($image, $x, $y, $width, $height, $visited);
                        if ($area > 10 && $area < ($width * $height * 0.3)) { // Размер отверстия
                            $holes++;
                        }
                    }
                }
            }
        }

        return $holes;
    }

    /**
     * Flood fill для подсчета области
     */
    private function floodFill($image, int $x, int $y, int $width, int $height, array &$visited): int
    {
        if ($x < 0 || $x >= $width || $y < 0 || $y >= $height) {
            return 0;
        }

        if (isset($visited[$x][$y])) {
            return 0;
        }

        $color = imagecolorat($image, $x, $y);
        if ($color != 1) { // Не белый пиксель
            return 0;
        }

        $visited[$x][$y] = true;
        $area = 1;

        // Рекурсивно заполняем соседние пиксели
        $area += $this->floodFill($image, $x + 1, $y, $width, $height, $visited);
        $area += $this->floodFill($image, $x - 1, $y, $width, $height, $visited);
        $area += $this->floodFill($image, $x, $y + 1, $width, $height, $visited);
        $area += $this->floodFill($image, $x, $y - 1, $width, $height, $visited);

        return $area;
    }

    /**
     * Классификация цифры по характеристикам
     */
    private function classifyDigit(array $features): int
    {
        // Улучшенная классификация на основе множественных характеристик
        $scores = [];

        // Оценка для каждой цифры (0-9)
        for ($digit = 0; $digit <= 9; $digit++) {
            $scores[$digit] = $this->calculateDigitScore($features, $digit);
        }

        Log::info("Баллы для цифр: " . json_encode($scores));

        // Возвращаем цифру с наивысшим баллом (наименьшим по модулю, если все отрицательные)
        $bestDigit = 0;
        $bestScore = $scores[0];

        foreach ($scores as $digit => $score) {
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestDigit = $digit;
            }
        }

        // Если все баллы отрицательные, выбираем наименьший по модулю
        if ($bestScore < 0) {
            $bestDigit = 0;
            $bestScore = abs($scores[0]);

            foreach ($scores as $digit => $score) {
                if (abs($score) < $bestScore) {
                    $bestScore = abs($score);
                    $bestDigit = $digit;
                }
            }
        }

        Log::info("Выбрана цифра: {$bestDigit} с баллом: {$bestScore}");

        return $bestDigit;
    }

    /**
     * Вычисление балла для конкретной цифры
     */
    private function calculateDigitScore(array $features, int $digit): float
    {
        $score = 0;

        // Базовые характеристики для каждой цифры
        $digitCharacteristics = $this->getDigitCharacteristics($digit);

        // Сравнение плотности
        $densityDiff = abs($features['density'] - $digitCharacteristics['density']);
        $score += (1 - $densityDiff) * 0.3;

        // Сравнение количества отверстий
        $holesDiff = abs($features['holes'] - $digitCharacteristics['holes']);
        $score += (1 - $holesDiff) * 0.25;

        // Сравнение горизонтальных линий
        $horizontalDiff = abs($features['horizontal_lines'] - $digitCharacteristics['horizontal_lines']);
        $score += (1 - $horizontalDiff) * 0.2;

        // Сравнение вертикальных линий
        $verticalDiff = abs($features['vertical_lines'] - $digitCharacteristics['vertical_lines']);
        $score += (1 - $verticalDiff) * 0.15;

        // Анализ областей
        $regionScore = $this->compareRegions($features['regions'], $digitCharacteristics['regions']);
        $score += $regionScore * 0.1;

        return $score;
    }

    /**
     * Получить характеристики для конкретной цифры
     */
    private function getDigitCharacteristics(int $digit): array
    {
        $characteristics = [
            0 => [
                'density' => 0.35,
                'holes' => 1,
                'horizontal_lines' => 2,
                'vertical_lines' => 2,
                'regions' => ['top_left' => 1, 'top_right' => 1, 'bottom_left' => 1, 'bottom_right' => 1]
            ],
            1 => [
                'density' => 0.15,
                'holes' => 0,
                'horizontal_lines' => 0,
                'vertical_lines' => 1,
                'regions' => ['top_left' => 0, 'top_right' => 1, 'bottom_left' => 0, 'bottom_right' => 1]
            ],
            2 => [
                'density' => 0.30,
                'holes' => 0,
                'horizontal_lines' => 3,
                'vertical_lines' => 0,
                'regions' => ['top_left' => 0, 'top_right' => 1, 'bottom_left' => 1, 'bottom_right' => 0]
            ],
            3 => [
                'density' => 0.32,
                'holes' => 0,
                'horizontal_lines' => 3,
                'vertical_lines' => 0,
                'regions' => ['top_left' => 0, 'top_right' => 1, 'bottom_left' => 0, 'bottom_right' => 1]
            ],
            4 => [
                'density' => 0.28,
                'holes' => 0,
                'horizontal_lines' => 1,
                'vertical_lines' => 2,
                'regions' => ['top_left' => 1, 'top_right' => 1, 'bottom_left' => 0, 'bottom_right' => 1]
            ],
            5 => [
                'density' => 0.30,
                'holes' => 0,
                'horizontal_lines' => 3,
                'vertical_lines' => 0,
                'regions' => ['top_left' => 1, 'top_right' => 0, 'bottom_left' => 0, 'bottom_right' => 1]
            ],
            6 => [
                'density' => 0.33,
                'holes' => 1,
                'horizontal_lines' => 2,
                'vertical_lines' => 1,
                'regions' => ['top_left' => 1, 'top_right' => 0, 'bottom_left' => 1, 'bottom_right' => 1]
            ],
            7 => [
                'density' => 0.25,
                'holes' => 0,
                'horizontal_lines' => 1,
                'vertical_lines' => 0,
                'regions' => ['top_left' => 0, 'top_right' => 1, 'bottom_left' => 0, 'bottom_right' => 0]
            ],
            8 => [
                'density' => 0.38,
                'holes' => 2,
                'horizontal_lines' => 3,
                'vertical_lines' => 2,
                'regions' => ['top_left' => 1, 'top_right' => 1, 'bottom_left' => 1, 'bottom_right' => 1]
            ],
            9 => [
                'density' => 0.35,
                'holes' => 1,
                'horizontal_lines' => 2,
                'vertical_lines' => 1,
                'regions' => ['top_left' => 1, 'top_right' => 1, 'bottom_left' => 0, 'bottom_right' => 1]
            ]
        ];

        return $characteristics[$digit] ?? $characteristics[0];
    }

    /**
     * Сравнение областей
     */
    private function compareRegions(array $actual, array $expected): float
    {
        $score = 0;
        $totalRegions = count($actual);

        foreach ($actual as $region => $value) {
            if (isset($expected[$region])) {
                $diff = abs($value - $expected[$region]);
                $score += (1 - $diff) / $totalRegions;
            }
        }

        return $score;
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
                return $a * $b;
            case '/':
                return $b != 0 ? (int)($a / $b) : 0;
            default:
                Log::error("Неизвестная операция: {$operation}");
                return 0;
        }
    }
}

