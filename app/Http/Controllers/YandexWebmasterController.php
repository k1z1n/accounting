<?php

namespace App\Http\Controllers;

use App\Contracts\Services\YandexWebmasterServiceInterface;
use App\Traits\HasApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class YandexWebmasterController extends Controller
{
    use HasApiResponse;

    public function __construct(
        private YandexWebmasterServiceInterface $webmasterService
    ) {}

    /**
     * Показать дашборд Яндекс.Вебмастера
     */
    public function dashboard(): View
    {
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays(30);

        try {
            $stats = $this->webmasterService->getGeneralStats($startDate, $endDate);
            $connection = $this->webmasterService->testConnection();
        } catch (\Exception $e) {
            // Если есть ошибки, создаем пустую структуру с сообщением об ошибке
            $stats = [
                'period' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                ],
                'sites' => [
                    'main_site' => [
                        'indexing' => ['indexed_pages' => 0, 'excluded_pages' => 0],
                        'crawl_errors' => [],
                        'external_links' => [],
                        'search_analytics' => [],
                        'error' => 'Ошибка подключения к API: ' . $e->getMessage(),
                    ],
                    'landing' => [
                        'indexing' => ['indexed_pages' => 0, 'excluded_pages' => 0],
                        'crawl_errors' => [],
                        'external_links' => [],
                        'search_analytics' => [],
                        'error' => 'Сайт не настроен',
                    ],
                    'blog' => [
                        'indexing' => ['indexed_pages' => 0, 'excluded_pages' => 0],
                        'crawl_errors' => [],
                        'external_links' => [],
                        'search_analytics' => [],
                        'error' => 'Сайт не настроен',
                    ],
                ],
                'total' => [],
            ];

            $connection = [
                'success' => false,
                'message' => 'Ошибка подключения',
                'error' => $e->getMessage(),
            ];
        }

        return view('webmaster.dashboard', compact('stats', 'connection'));
    }

    /**
     * API: Получить общую статистику
     */
    public function getStats(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'counter_id' => 'nullable|integer',
        ]);

        try {
            $startDate = $request->start_date
                ? Carbon::parse($request->start_date)
                : Carbon::now()->subDays(30);

            $endDate = $request->end_date
                ? Carbon::parse($request->end_date)
                : Carbon::now();

            if ($request->site_url) {
                $stats = $this->webmasterService->getSiteStats(
                    $request->site_url,
                    $startDate,
                    $endDate
                );
            } else {
                $stats = $this->webmasterService->getGeneralStats($startDate, $endDate);
            }

            return $this->successResponse($stats, 'Статистика получена успешно');

        } catch (\Exception $e) {
            return $this->errorResponse('Ошибка получения статистики: ' . $e->getMessage(), 500);
        }
    }

    /**
     * API: Получить real-time данные
     */
    public function getRealTime(Request $request): JsonResponse
    {
        $request->validate([
            'counter_id' => 'required|integer',
        ]);

        try {
            $stats = $this->metrikaService->getRealTimeStats($request->counter_id);

            return $this->successResponse($stats, 'Real-time данные получены');

        } catch (\Exception $e) {
            return $this->errorResponse('Ошибка получения real-time данных: ' . $e->getMessage(), 500);
        }
    }

    /**
     * API: Получить поисковые фразы
     */
    public function getSearchQueries(Request $request): JsonResponse
    {
        $request->validate([
            'counter_id' => 'required|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        try {
            $startDate = $request->start_date
                ? Carbon::parse($request->start_date)
                : Carbon::now()->subDays(7);

            $endDate = $request->end_date
                ? Carbon::parse($request->end_date)
                : Carbon::now();

            $limit = $request->limit ?? 20;

            $queries = $this->metrikaService->getSearchQueries(
                $request->counter_id,
                $startDate,
                $endDate,
                $limit
            );

            return $this->successResponse($queries, 'Поисковые фразы получены');

        } catch (\Exception $e) {
            return $this->errorResponse('Ошибка получения поисковых фраз: ' . $e->getMessage(), 500);
        }
    }

    /**
     * API: Получить статистику целей
     */
    public function getGoals(Request $request): JsonResponse
    {
        $request->validate([
            'counter_id' => 'required|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        try {
            $startDate = $request->start_date
                ? Carbon::parse($request->start_date)
                : Carbon::now()->subDays(30);

            $endDate = $request->end_date
                ? Carbon::parse($request->end_date)
                : Carbon::now();

            $goals = $this->metrikaService->getGoalStats(
                $request->counter_id,
                $startDate,
                $endDate
            );

            return $this->successResponse($goals, 'Статистика целей получена');

        } catch (\Exception $e) {
            return $this->errorResponse('Ошибка получения статистики целей: ' . $e->getMessage(), 500);
        }
    }

    /**
     * API: Получить статистику устройств
     */
    public function getDevices(Request $request): JsonResponse
    {
        $request->validate([
            'counter_id' => 'required|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        try {
            $startDate = $request->start_date
                ? Carbon::parse($request->start_date)
                : Carbon::now()->subDays(30);

            $endDate = $request->end_date
                ? Carbon::parse($request->end_date)
                : Carbon::now();

            $devices = $this->metrikaService->getDeviceStats(
                $request->counter_id,
                $startDate,
                $endDate
            );

            return $this->successResponse($devices, 'Статистика устройств получена');

        } catch (\Exception $e) {
            return $this->errorResponse('Ошибка получения статистики устройств: ' . $e->getMessage(), 500);
        }
    }

    /**
     * API: Получить список счетчиков
     */
    public function getCounters(): JsonResponse
    {
        try {
            $counters = $this->metrikaService->getCounters();

            return $this->successResponse($counters, 'Список счетчиков получен');

        } catch (\Exception $e) {
            return $this->errorResponse('Ошибка получения счетчиков: ' . $e->getMessage(), 500);
        }
    }

    /**
     * API: Проверить подключение
     */
    public function testConnection(): JsonResponse
    {
        try {
            $result = $this->metrikaService->testConnection();

            if ($result['status'] === 'success') {
                return $this->successResponse($result, $result['message']);
            } else {
                return $this->errorResponse($result['message'], 400, $result);
            }

        } catch (\Exception $e) {
            return $this->errorResponse('Ошибка проверки подключения: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Показать сравнение сайтов
     */
    public function comparison(): View
    {
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subDays(30);

        try {
            $stats = $this->metrikaService->getGeneralStats($startDate, $endDate);
            $comparison = $this->prepareComparisonData($stats);

            return view('metrika.comparison', compact('comparison', 'stats'));

        } catch (\Exception $e) {
            return view('metrika.comparison', [
                'error' => $e->getMessage(),
                'comparison' => [],
                'stats' => []
            ]);
        }
    }

    /**
     * Экспорт статистики
     */
    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'format' => 'required|in:json,csv,xlsx',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'counter_ids' => 'nullable|array',
            'counter_ids.*' => 'integer',
        ]);

        try {
            $startDate = $request->start_date
                ? Carbon::parse($request->start_date)
                : Carbon::now()->subDays(30);

            $endDate = $request->end_date
                ? Carbon::parse($request->end_date)
                : Carbon::now();

            $stats = $this->metrikaService->getGeneralStats($startDate, $endDate);

            // Подготовка данных для экспорта
            $exportData = $this->prepareExportData($stats, $request->format);

            return $this->successResponse($exportData, 'Данные подготовлены для экспорта');

        } catch (\Exception $e) {
            return $this->errorResponse('Ошибка экспорта: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Подготовить данные для сравнения
     */
    private function prepareComparisonData(array $stats): array
    {
        if (empty($stats['sites'])) {
            return [];
        }

        $comparison = [];
        foreach ($stats['sites'] as $siteName => $siteStats) {
            if (isset($siteStats['main']['totals'])) {
                $comparison[] = [
                    'site' => $siteName,
                    'visits' => $siteStats['main']['totals']['visits'] ?? 0,
                    'pageviews' => $siteStats['main']['totals']['pageviews'] ?? 0,
                    'users' => $siteStats['main']['totals']['users'] ?? 0,
                    'bounce_rate' => $siteStats['main']['totals']['bounce_rate'] ?? 0,
                    'page_depth' => $siteStats['main']['totals']['page_depth'] ?? 0,
                    'avg_duration' => $siteStats['main']['totals']['avg_duration'] ?? 0,
                ];
            }
        }

        // Сортировка по количеству визитов
        usort($comparison, fn($a, $b) => $b['visits'] <=> $a['visits']);

        return $comparison;
    }

    /**
     * Подготовить данные для экспорта
     */
    private function prepareExportData(array $stats, string $format): array
    {
        switch ($format) {
            case 'csv':
                return $this->prepareCsvData($stats);
            case 'xlsx':
                return $this->prepareXlsxData($stats);
            default:
                return $stats;
        }
    }

    /**
     * Подготовить CSV данные
     */
    private function prepareCsvData(array $stats): array
    {
        $csv = [];
        $csv[] = ['Сайт', 'Визиты', 'Просмотры', 'Пользователи', 'Отказы %', 'Глубина', 'Время на сайте'];

        foreach ($stats['sites'] ?? [] as $siteName => $siteStats) {
            if (isset($siteStats['main']['totals'])) {
                $totals = $siteStats['main']['totals'];
                $csv[] = [
                    $siteName,
                    $totals['visits'] ?? 0,
                    $totals['pageviews'] ?? 0,
                    $totals['users'] ?? 0,
                    $totals['bounce_rate'] ?? 0,
                    $totals['page_depth'] ?? 0,
                    $totals['avg_duration'] ?? 0,
                ];
            }
        }

        return $csv;
    }

    /**
     * Подготовить XLSX данные
     */
    private function prepareXlsxData(array $stats): array
    {
        // Здесь можно добавить более сложную обработку для Excel
        return $this->prepareCsvData($stats);
    }
}
