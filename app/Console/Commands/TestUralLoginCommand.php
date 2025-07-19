<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestUralLoginCommand extends Command
{
    protected $signature = 'test:ural-login';
    protected $description = 'Тестирование логина на ural-obmen.ru';

    public function handle()
    {
        $this->info('🧪 Тестирование логина на ural-obmen.ru...');

        // Получаем URL из конфигурации
        $loginUrl = config('exchanger.ural.login_url');
        $this->info("URL логина: {$loginUrl}");

        // Шаг 1: Получаем страницу логина
        $this->info('1. Получаем страницу логина...');
        $response = Http::timeout(15)->get($loginUrl);

        if (!$response->successful()) {
            $this->error("❌ Ошибка получения страницы: HTTP {$response->status()}");
            $this->warn("⚠️ Продолжаем анализ содержимого...");
        } else {
            $this->info("✅ Страница получена успешно (HTTP {$response->status()})");
        }

        $html = $response->body();
        $this->info("📄 Размер HTML: " . strlen($html) . " байт");

        // Шаг 2: Ищем форму логина
        $this->info('2. Анализируем форму логина...');

        if (strpos($html, 'form') !== false) {
            $this->info('✅ Форма найдена');
        } else {
            $this->warn('⚠️ Форма не найдена');
        }

        // Ищем поля формы
        $fields = ['logmail', 'pass', 'user_pin', 'number', 'salt'];
        foreach ($fields as $field) {
            if (strpos($html, "name=\"{$field}\"") !== false) {
                $this->info("✅ Поле {$field} найдено");
            } else {
                $this->warn("⚠️ Поле {$field} не найдено");
            }
        }

        // Ищем CAPTCHA
        if (strpos($html, 'captcha') !== false) {
            $this->info('✅ CAPTCHA найдена');

            // Ищем изображения CAPTCHA
            if (preg_match_all('/<img[^>]*class="[^"]*captcha[^"]*"[^>]*src="([^"]+)"/', $html, $matches)) {
                $this->info('✅ Изображения CAPTCHA найдены:');
                foreach ($matches[1] as $i => $url) {
                    $this->info("   {$i}: {$url}");
                }
            }

            // Ищем операцию
            if (preg_match('/<span[^>]*class="[^"]*captcha[^"]*"[^>]*>([^<]+)</', $html, $matches)) {
                $this->info("✅ Операция CAPTCHA: {$matches[1]}");
            }
        } else {
            $this->warn('⚠️ CAPTCHA не найдена');
        }

        // Ищем salt
        if (preg_match('/name="salt" value="([^"]+)"/', $html, $matches)) {
            $this->info("✅ Salt найден: {$matches[1]}");
        } else {
            $this->warn('⚠️ Salt не найден');
        }

        // Шаг 3: Проверяем action URL
        if (preg_match('/<form[^>]*action="([^"]+)"/', $html, $matches)) {
            $this->info("✅ Action URL: {$matches[1]}");
        } else {
            $this->warn('⚠️ Action URL не найден');
        }

        $this->info('🎯 Тестирование завершено');
        return 0;
    }
}
