<?php

namespace App\Console\Commands;

use App\Contracts\Services\YandexWebmasterServiceInterface;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestYandexWebmasterCommand extends Command
{
    protected $signature = 'webmaster:test {--site-url= : URL сайта для детального тестирования}';
    protected $description = 'Тестирование подключения к Яндекс.Вебмастеру';

    public function __construct(
        private YandexWebmasterServiceInterface $webmasterService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🚀 Тестирование интеграции с Яндекс.Вебмастером...');
        $this->newLine();

        // 1. Проверка подключения
        $this->info('1. Проверка подключения...');
        $connectionResult = $this->webmasterService->testConnection();

        if ($connectionResult['success']) {
            $this->line('✅ Подключение к Яндекс.Вебмастеру успешно');
            $this->line("   Найдено сайтов: {$connectionResult['user_sites_count']}");
            $this->line("   Настроено сайтов: {$connectionResult['configured_sites_count']}");
        } else {
            $this->error('❌ Ошибка подключения: ' . $connectionResult['error']);
            return 1;
        }
        $this->newLine();

        // 2. Получение списка сайтов - ПРЯМОЙ ЗАПРОС
        $this->info('2. Получение списка сайтов (прямой запрос)...');
        try {
            $token = config('services.yandex_webmaster.oauth_token');
            $response = Http::withHeaders([
                'Authorization' => "OAuth {$token}",
                'Content-Type' => 'application/json',
            ])->get('https://api.webmaster.yandex.net/v4/user/hosts');

            if ($response->successful()) {
                $data = $response->json();
                $this->line('✅ Прямой запрос успешен');
                $this->line('📄 Полный ответ API:');
                $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                if (isset($data['hosts']) && !empty($data['hosts'])) {
                    $this->info('🌐 Найденные сайты:');
                    foreach ($data['hosts'] as $host) {
                        $this->line("   • {$host['host_id']} (Статус: {$host['verification']['verification_state']})");
                    }
                } else {
                    $this->warn('⚠️  В аккаунте нет добавленных сайтов');
                }
            } else {
                $this->error('❌ Ошибка API: ' . $response->status() . ' - ' . $response->body());
            }
        } catch (\Exception $e) {
            $this->error('❌ Исключение: ' . $e->getMessage());
        }
        $this->newLine();

        // 3. Тестирование статистики для указанного сайта
        $siteUrl = $this->option('site-url') ?? config('services.yandex_webmaster.site_urls.site_1');

        if ($siteUrl) {
            $this->info("3. Тестирование статистики для сайта {$siteUrl}...");

            $startDate = Carbon::now()->subMonth();
            $endDate = Carbon::now();

            $stats = $this->webmasterService->getSiteStats($siteUrl, $startDate, $endDate);

            if (empty($stats['error'])) {
                $this->line('✅ Статистика получена успешно:');
                $this->line("   Проиндексировано страниц: " . ($stats['indexing']['indexed_pages'] ?? 0));
                $this->line("   Исключено страниц: " . ($stats['indexing']['excluded_pages'] ?? 0));
                $this->line("   Ошибок сканирования: " . count($stats['crawl_errors'] ?? []));
                $this->line("   Внешних ссылок: " . count($stats['external_links'] ?? []));
            } else {
                $this->warn('⚠️  Статистика недоступна: ' . $stats['error']);
            }
        } else {
            $this->warn('⚠️  URL сайта не указан');
        }

        $this->newLine();
        $this->info('🎉 Тестирование завершено!');
        $this->newLine();

        // Рекомендации
        $this->info('💡 Рекомендации:');
        $this->line('   • Добавьте OAuth токен в .env: YANDEX_WEBMASTER_TOKEN');
        $this->line('   • Настройте сайты в Яндекс.Вебмастере: https://webmaster.yandex.ru/');
        $this->line('   • Используйте веб-интерфейс: /webmaster/dashboard');
        $this->line('   • Используйте API эндпоинты для получения данных');
        $this->line('   • Проверьте логи Laravel при возникновении ошибок');

        return 0;
    }
}
