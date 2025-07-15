<?php

namespace App\Services;

use App\Contracts\Repositories\ApplicationRepositoryInterface;
use App\Contracts\Repositories\CurrencyRepositoryInterface;
use App\Contracts\Repositories\HistoryRepositoryInterface;
use App\Contracts\Services\ApplicationServiceInterface;
use App\DTOs\ApplicationDTO;
use App\Models\Application;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class ApplicationService implements ApplicationServiceInterface
{
    public function __construct(
        private ApplicationRepositoryInterface $applicationRepository,
        private CurrencyRepositoryInterface $currencyRepository,
        private HistoryRepositoryInterface $historyRepository
    ) {}

    /**
     * Получить заявки с пагинацией
     */
    public function getPaginatedApplications(int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        return $this->applicationRepository->getPaginatedWithRelations($perPage);
    }

    /**
     * Синхронизировать заявки с внешними источниками
     */
    public function syncFromExternalSources(int $pageNum = 1): void
    {
        $allowedStatuses = ['выполненная заявка', 'оплаченная заявка', 'возврат'];

        $exchangers = [
            'obama' => [
                'url' => 'https://obama.ru/wp-admin/admin.php?page=pn_bids&page_num=' . $pageNum,
                'cookies' => config('exchanger.obama.cookie'),
            ],
            'ural' => [
                'url' => 'https://ural-obmen.ru/wp-admin/admin.php?page=pn_bids&page_num=' . $pageNum,
                'cookies' => config('exchanger.ural.cookie'),
            ],
        ];

        $records = collect();

        foreach ($exchangers as $exchangerName => $cfg) {
            try {
                $applications = $this->fetchFromExchanger($exchangerName, $cfg, $allowedStatuses);
                $records = $records->merge($applications);
            } catch (\Exception $e) {
                Log::error("Ошибка синхронизации с {$exchangerName}: " . $e->getMessage());
            }
        }

        if ($records->isNotEmpty()) {
            $this->applicationRepository->syncApplications($records->toArray());
        }
    }

    /**
     * Обновить заявку
     */
    public function updateApplication(int $applicationId, array $data): Application
    {
        $application = $this->applicationRepository->findOrFail($applicationId);
        $applicationDTO = ApplicationDTO::fromArray($data);

        if (!$applicationDTO->validate()) {
            throw new \InvalidArgumentException('Некорректные данные заявки');
        }

        // Обработка валютных данных
        if ($applicationDTO->hasCurrencyData()) {
            $this->processCurrencyData($application, $applicationDTO->toArray());
        }

        return $this->applicationRepository->update($applicationId, $applicationDTO->getModelData());
    }

    /**
     * Получить заявку с подробной информацией
     */
    public function getApplicationDetails(int $applicationId): Application
    {
        return $this->applicationRepository->getWithOperations($applicationId);
    }

    /**
     * Создать новую заявку
     */
    public function createApplication(array $data): Application
    {
        $applicationDTO = ApplicationDTO::fromArray($data);

        if (!$applicationDTO->validate()) {
            throw new \InvalidArgumentException('Некорректные данные заявки');
        }

        $application = $this->applicationRepository->create($applicationDTO->getModelData());

        // Обработка валютных данных
        if ($applicationDTO->hasCurrencyData()) {
            $this->processCurrencyData($application, $applicationDTO->toArray());
        }

        return $application;
    }

    /**
     * Получить статистику заявок
     */
    public function getApplicationStatistics(): array
    {
        return [
            'total' => $this->applicationRepository->count(),
            'by_status' => $this->applicationRepository->getStatusStatistics(),
            'by_exchanger' => $this->applicationRepository->getExchangerStatistics(),
            'by_period' => $this->applicationRepository->getPeriodStatistics(),
            'daily' => $this->applicationRepository->getDailyStatistics(),
        ];
    }

    /**
     * Получить заявки по фильтрам
     */
    public function getFilteredApplications(array $filters): Collection
    {
        // Реализация фильтрации
        if (isset($filters['status'])) {
            return $this->applicationRepository->findByStatus($filters['status']);
        }

        if (isset($filters['exchanger'])) {
            return $this->applicationRepository->findByExchanger($filters['exchanger']);
        }

        return $this->applicationRepository->getAllWithRelations();
    }

    /**
     * Получить данные для экспорта
     */
    public function getExportData(array $filters = []): Collection
    {
        return $this->getFilteredApplications($filters);
    }

    /**
     * Обработать валютные данные заявки
     */
    public function processCurrencyData(Application $application, array $currencyData): void
    {
        $updateData = [];

        // Обработка продаваемой валюты
        if (!empty($currencyData['sell_currency'])) {
            $currency = $this->currencyRepository->firstOrCreate(
                strtoupper($currencyData['sell_currency'])
            );
            $updateData['sell_currency_id'] = $currency->id;
        }

        // Обработка покупаемой валюты
        if (!empty($currencyData['buy_currency'])) {
            $currency = $this->currencyRepository->firstOrCreate(
                strtoupper($currencyData['buy_currency'])
            );
            $updateData['buy_currency_id'] = $currency->id;
        }

        // Обработка валюты расходов
        if (!empty($currencyData['expense_currency'])) {
            $currency = $this->currencyRepository->firstOrCreate(
                strtoupper($currencyData['expense_currency'])
            );
            $updateData['expense_currency_id'] = $currency->id;
        }

        if (!empty($updateData)) {
            $this->applicationRepository->update($application->id, $updateData);
            $this->createHistoryRecords($application, $currencyData);
        }
    }

    /**
     * Получить данные с обменника
     */
    private function fetchFromExchanger(string $exchangerName, array $config, array $allowedStatuses): Collection
    {
        $response = Http::withHeaders([
            'Cookie' => $config['cookies'],
            'User-Agent' => 'Mozilla/5.0',
        ])->timeout(15)->get($config['url']);

        if (!$response->successful()) {
            throw new \Exception("HTTP {$response->status()} при запросе к {$exchangerName}");
        }

        $html = $response->body();
        if (empty($html) || stripos($html, 'wp-login') !== false) {
            throw new \Exception("Требуются новые куки для {$exchangerName}");
        }

        return $this->parseHtml($html, $exchangerName, $allowedStatuses);
    }

    /**
     * Парсинг HTML для извлечения заявок
     */
    private function parseHtml(string $html, string $exchangerName, array $allowedStatuses): Collection
    {
        $crawler = new Crawler($html);
        $records = collect();

        $crawler->filter('table tbody tr')->each(function (Crawler $row) use (&$records, $exchangerName, $allowedStatuses) {
            $cells = $row->filter('td');

            if ($cells->count() >= 6) {
                $status = trim($cells->eq(5)->text());

                if (in_array($status, $allowedStatuses)) {
                    $records->push([
                        'exchanger' => $exchangerName,
                        'app_id' => trim($cells->eq(0)->text()),
                        'app_created_at' => $this->parseDate(trim($cells->eq(1)->text())),
                        'status' => $status,
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        return $records;
    }

    /**
     * Создать записи истории для заявки
     */
    private function createHistoryRecords(Application $application, array $currencyData): void
    {
        // Запись прихода
        if (!empty($currencyData['sell_amount']) && !empty($application->sell_currency_id)) {
            $this->historyRepository->createForOperation(
                Application::class,
                $application->id,
                [
                    'amount' => $currencyData['sell_amount'],
                    'currency_id' => $application->sell_currency_id,
                ]
            );
        }

        // Запись расхода
        if (!empty($currencyData['expense_amount']) && !empty($application->expense_currency_id)) {
            $this->historyRepository->createForOperation(
                Application::class,
                $application->id,
                [
                    'amount' => -$currencyData['expense_amount'],
                    'currency_id' => $application->expense_currency_id,
                ]
            );
        }
    }

    /**
     * Парсинг даты
     */
    private function parseDate(string $dateString): ?string
    {
        try {
            return \Carbon\Carbon::parse($dateString)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }
}
