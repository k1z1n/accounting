<?php

namespace App\Services;

use App\Contracts\Services\YandexWebmasterServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class YandexWebmasterService implements YandexWebmasterServiceInterface
{
    private string $baseUrl = 'https://api.webmaster.yandex.net/v4';
    private ?string $oauthToken = null;
    private array $siteUrls = [];

    public function __construct()
    {
        $this->oauthToken = config('services.yandex_webmaster.oauth_token');
        $this->siteUrls = config('services.yandex_webmaster.site_urls', []);
    }

        /**
     * Получить список всех сайтов пользователя
     */
    public function getUserSites(): array
    {
        $cacheKey = 'yandex_webmaster_sites';

        return Cache::remember($cacheKey, 3600, function () {
            try {
                $response = Http::withHeaders([
                    'Authorization' => "OAuth {$this->oauthToken}",
                    'Content-Type' => 'application/json',
                ])->get("{$this->baseUrl}/user/hosts");

                Log::info('YandexWebmaster: Ответ API сайтов', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);

                if ($response->successful()) {
                    return $response->json('hosts', []);
                }

                Log::error('YandexWebmaster: Ошибка получения сайтов', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                return [];
            } catch (\Exception $e) {
                Log::error('YandexWebmaster: Исключение при получении сайтов', [
                    'error' => $e->getMessage()
                ]);
                return [];
            }
        });
    }

        /**
     * Получить общую статистику по всем сайтам
     */
    public function getGeneralStats(Carbon $startDate, Carbon $endDate): array
    {
        $stats = [];

        foreach ($this->siteUrls as $siteName => $siteUrl) {
            // Пропускаем пустые URL
            if (empty($siteUrl) || !is_string($siteUrl)) {
                continue;
            }

            $stats[$siteName] = $this->getSiteStats($siteUrl, $startDate, $endDate);
        }

        return [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'sites' => $stats,
            'total' => $this->calculateTotalStats($stats),
        ];
    }

        /**
     * Получить статистику по конкретному сайту
     */
    public function getSiteStats(string $siteUrl, Carbon $startDate, Carbon $endDate): array
    {
        // Проверяем, что URL не пустой
        if (empty($siteUrl)) {
            return [
                'search_queries' => [],
                'indexing' => [],
                'crawl_errors' => [],
                'external_links' => [],
                'search_analytics' => [],
                'error' => 'Пустой URL сайта',
            ];
        }

        $hostId = $this->prepareHostId($siteUrl);
        $cacheKey = "yandex_webmaster_stats_{$hostId}_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";

        return Cache::remember($cacheKey, 1800, function () use ($hostId, $startDate, $endDate) {
            try {
                // Поисковые запросы
                $searchQueries = $this->getSearchQueries($hostId, $startDate, $endDate);

                // Индексация
                $indexing = $this->getIndexingStats($hostId);

                // Ошибки сканирования
                $crawlErrors = $this->getCrawlErrors($hostId);

                // Внешние ссылки
                $externalLinks = $this->getExternalLinks($hostId);

                // Статистика по поисковым запросам (аналитика)
                $searchAnalytics = $this->getSearchAnalytics($hostId, $startDate, $endDate);

                return [
                    'search_queries' => $searchQueries,
                    'indexing' => $indexing,
                    'crawl_errors' => $crawlErrors,
                    'external_links' => $externalLinks,
                    'search_analytics' => $searchAnalytics,
                ];

            } catch (\Exception $e) {
                Log::error('YandexWebmaster: Ошибка получения статистики сайта', [
                    'host_id' => $hostId,
                    'error' => $e->getMessage()
                ]);

                return [
                    'search_queries' => [],
                    'indexing' => [],
                    'crawl_errors' => [],
                    'external_links' => [],
                    'search_analytics' => [],
                    'error' => $e->getMessage(),
                ];
            }
        });
    }

    /**
     * Получить поисковые запросы
     */
    public function getSearchQueries(string $hostId, Carbon $startDate, Carbon $endDate, int $limit = 100): array
    {
        try {
            $encodedHostId = urlencode($hostId);
            $response = $this->makeApiRequest("/user/hosts/{$encodedHostId}/search-queries/", [
                'date_from' => $startDate->format('Y-m-d'),
                'date_to' => $endDate->format('Y-m-d'),
                'limit' => $limit,
                'offset' => 0,
            ]);

            return $this->processSearchQueries($response);

        } catch (\Exception $e) {
            Log::error('YandexWebmaster: Ошибка получения поисковых запросов', [
                'host_id' => $hostId,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Получить статистику индексации
     */
    public function getIndexingStats(string $hostId): array
    {
        try {
            $encodedHostId = urlencode($hostId);
            $response = $this->makeApiRequest("/user/hosts/{$encodedHostId}/indexing-stats/");
            return $this->processIndexingStats($response);

        } catch (\Exception $e) {
            Log::error('YandexWebmaster: Ошибка получения статистики индексации', [
                'host_id' => $hostId,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Получить ошибки сканирования
     */
    public function getCrawlErrors(string $hostId): array
    {
        try {
            $encodedHostId = urlencode($hostId);
            $response = $this->makeApiRequest("/user/hosts/{$encodedHostId}/crawl-errors/");
            return $this->processCrawlErrors($response);

        } catch (\Exception $e) {
            Log::error('YandexWebmaster: Ошибка получения ошибок сканирования', [
                'host_id' => $hostId,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Получить внешние ссылки
     */
    public function getExternalLinks(string $hostId): array
    {
        try {
            $encodedHostId = urlencode($hostId);
            $response = $this->makeApiRequest("/user/hosts/{$encodedHostId}/links/external/samples/", [
                'limit' => 100,
                'offset' => 0,
            ]);

            return $this->processExternalLinks($response);

        } catch (\Exception $e) {
            Log::error('YandexWebmaster: Ошибка получения внешних ссылок', [
                'host_id' => $hostId,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Получить аналитику поиска (показы, клики, CTR, позиции)
     */
    public function getSearchAnalytics(string $hostId, Carbon $startDate, Carbon $endDate): array
    {
        try {
            $encodedHostId = urlencode($hostId);
            $response = $this->makeApiRequest("/user/hosts/{$encodedHostId}/search-queries/analytics/", [
                'date_from' => $startDate->format('Y-m-d'),
                'date_to' => $endDate->format('Y-m-d'),
                'device_type_indicator' => 'ALL',
                'text_indicator' => 'ALL',
            ]);

            return $this->processSearchAnalytics($response);

        } catch (\Exception $e) {
            Log::error('YandexWebmaster: Ошибка получения аналитики поиска', [
                'host_id' => $hostId,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Получить информацию о сайте
     */
    public function getSiteInfo(string $siteUrl): array
    {
        $hostId = $this->prepareHostId($siteUrl);

        try {
            $encodedHostId = urlencode($hostId);
            $response = $this->makeApiRequest("/user/hosts/{$encodedHostId}/");
            return $response;

        } catch (\Exception $e) {
            Log::error('YandexWebmaster: Ошибка получения информации о сайте', [
                'host_id' => $hostId,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Получить историю обхода сайта
     */
    public function getCrawlHistory(string $hostId, Carbon $startDate, Carbon $endDate): array
    {
        try {
            $response = $this->makeApiRequest("/user/hosts/{$hostId}/crawl-stats/history/", [
                'date_from' => $startDate->format('Y-m-d'),
                'date_to' => $endDate->format('Y-m-d'),
            ]);

            return $this->processCrawlHistory($response);

        } catch (\Exception $e) {
            Log::error('YandexWebmaster: Ошибка получения истории обхода', [
                'host_id' => $hostId,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Получить статистику загрузки страниц
     */
    public function getPageLoadStats(string $hostId): array
    {
        try {
            $response = $this->makeApiRequest("/user/hosts/{$hostId}/load-speed/");
            return $this->processPageLoadStats($response);

        } catch (\Exception $e) {
            Log::error('YandexWebmaster: Ошибка получения статистики загрузки', [
                'host_id' => $hostId,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

        /**
     * Выполнить запрос к API
     */
    private function makeApiRequest(string $endpoint, array $params = []): array
    {
        if (!$this->oauthToken) {
            throw new \Exception("OAuth токен не настроен. Проверьте переменную YANDEX_WEBMASTER_TOKEN в .env");
        }

        $url = $this->baseUrl . $endpoint;

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        Log::info('YandexWebmaster: API запрос', ['url' => $url, 'endpoint' => $endpoint, 'params' => $params]);

        $response = Http::withHeaders([
            'Authorization' => "OAuth {$this->oauthToken}",
            'Content-Type' => 'application/json',
        ])->get($url);

        Log::info('YandexWebmaster: API ответ', [
            'status' => $response->status(),
            'response' => $response->json()
        ]);

        if (!$response->successful()) {
            throw new \Exception("API Error: {$response->status()} - {$response->body()}");
        }

        return $response->json();
    }

            /**
     * Подготовить Host ID для API
     */
    private function prepareHostId(string $siteUrl): string
    {
        // Для Яндекс.Вебмастер API нужен полный формат с протоколом и портом
        if (!str_contains($siteUrl, '://')) {
            $siteUrl = "https://{$siteUrl}";
        }

        // Убираем trailing slash, но оставляем протокол
        $hostId = rtrim($siteUrl, '/');

        // Если это HTTPS на стандартном порту, добавляем :443
        if (str_starts_with($hostId, 'https://') && !str_contains($hostId, ':443')) {
            $hostId .= ':443';
        }

        Log::info('YandexWebmaster: Подготовленный host_id', ['original' => $siteUrl, 'host_id' => $hostId]);

        return $hostId;
    }

    /**
     * Обработать поисковые запросы
     */
    private function processSearchQueries(array $data): array
    {
        if (empty($data['queries'])) {
            return [];
        }

        $queries = [];
        foreach ($data['queries'] as $query) {
            $queries[] = [
                'query' => $query['query_text'] ?? '',
                'impressions' => $query['impressions'] ?? 0,
                'clicks' => $query['clicks'] ?? 0,
                'ctr' => $query['ctr'] ?? 0,
                'position' => $query['position'] ?? 0,
            ];
        }

        return $queries;
    }

    /**
     * Обработать статистику индексации
     */
    private function processIndexingStats(array $data): array
    {
        return [
            'indexed_pages' => $data['indexed_pages'] ?? 0,
            'excluded_pages' => $data['excluded_pages'] ?? 0,
            'total_pages' => $data['total_pages'] ?? 0,
            'last_update' => $data['last_update'] ?? null,
        ];
    }

    /**
     * Обработать ошибки сканирования
     */
    private function processCrawlErrors(array $data): array
    {
        if (empty($data['errors'])) {
            return [];
        }

        $errors = [];
        foreach ($data['errors'] as $error) {
            $errors[] = [
                'url' => $error['url'] ?? '',
                'error_type' => $error['error_type'] ?? '',
                'error_code' => $error['http_code'] ?? 0,
                'last_access' => $error['last_access'] ?? null,
            ];
        }

        return $errors;
    }

    /**
     * Обработать внешние ссылки
     */
    private function processExternalLinks(array $data): array
    {
        if (empty($data['links'])) {
            return [];
        }

        $links = [];
        foreach ($data['links'] as $link) {
            $links[] = [
                'source_url' => $link['source_url'] ?? '',
                'destination_url' => $link['destination_url'] ?? '',
                'discovery_date' => $link['discovery_date'] ?? null,
            ];
        }

        return $links;
    }

    /**
     * Обработать аналитику поиска
     */
    private function processSearchAnalytics(array $data): array
    {
        if (empty($data['queries'])) {
            return [];
        }

        $analytics = [];
        foreach ($data['queries'] as $query) {
            $analytics[] = [
                'query' => $query['query_text'] ?? '',
                'impressions' => $query['impressions'] ?? 0,
                'clicks' => $query['clicks'] ?? 0,
                'ctr' => round(($query['ctr'] ?? 0) * 100, 2), // Переводим в проценты
                'position' => round($query['position'] ?? 0, 1),
            ];
        }

        return $analytics;
    }

    /**
     * Обработать историю обхода
     */
    private function processCrawlHistory(array $data): array
    {
        if (empty($data['points'])) {
            return [];
        }

        $history = [];
        foreach ($data['points'] as $point) {
            $history[] = [
                'date' => $point['date'] ?? '',
                'pages_crawled' => $point['pages_crawled'] ?? 0,
                'crawl_budget' => $point['crawl_budget'] ?? 0,
                'robots_txt_size' => $point['robots_txt_size'] ?? 0,
            ];
        }

        return $history;
    }

    /**
     * Обработать статистику загрузки страниц
     */
    private function processPageLoadStats(array $data): array
    {
        return [
            'average_load_time' => $data['average_load_time'] ?? 0,
            'fast_pages_percentage' => $data['fast_pages_percentage'] ?? 0,
            'slow_pages_percentage' => $data['slow_pages_percentage'] ?? 0,
            'last_update' => $data['last_update'] ?? null,
        ];
    }

    /**
     * Подсчитать общую статистику
     */
    private function calculateTotalStats(array $stats): array
    {
        $total = [
            'total_clicks' => 0,
            'total_impressions' => 0,
            'total_indexed_pages' => 0,
            'sites_count' => count($stats),
            'average_ctr' => 0,
            'average_position' => 0,
        ];

        $clicksSum = 0;
        $impressionsSum = 0;
        $ctrSum = 0;
        $positionSum = 0;
        $queriesCount = 0;

        foreach ($stats as $siteStats) {
            if (isset($siteStats['search_analytics'])) {
                foreach ($siteStats['search_analytics'] as $query) {
                    $clicksSum += $query['clicks'] ?? 0;
                    $impressionsSum += $query['impressions'] ?? 0;
                    $ctrSum += $query['ctr'] ?? 0;
                    $positionSum += $query['position'] ?? 0;
                    $queriesCount++;
                }
            }

            if (isset($siteStats['indexing']['indexed_pages'])) {
                $total['total_indexed_pages'] += $siteStats['indexing']['indexed_pages'];
            }
        }

        $total['total_clicks'] = $clicksSum;
        $total['total_impressions'] = $impressionsSum;

        if ($queriesCount > 0) {
            $total['average_ctr'] = round($ctrSum / $queriesCount, 2);
            $total['average_position'] = round($positionSum / $queriesCount, 1);
        }

        return $total;
    }

    /**
     * Проверить настройки API
     */
    public function testConnection(): array
    {
        try {
            $sites = $this->getUserSites();

            return [
                'success' => true,
                'message' => 'Подключение к Яндекс.Вебмастеру успешно',
                'user_sites_count' => count($sites),
                'configured_sites_count' => count($this->siteUrls),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Ошибка подключения к Яндекс.Вебмастеру',
                'error' => $e->getMessage(),
            ];
        }
    }
}
