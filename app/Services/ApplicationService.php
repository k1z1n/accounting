<?php

namespace App\Services;

use App\Contracts\Repositories\ApplicationRepositoryInterface;
use App\Contracts\Repositories\CurrencyRepositoryInterface;
use App\Contracts\Repositories\HistoryRepositoryInterface;
use App\Contracts\Services\ApplicationServiceInterface;
use App\DTOs\ApplicationDTO;
use App\Models\Application;
use App\Models\SiteCookie;
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

        // Получаем данные из БД вместо config
        $siteCookies = SiteCookie::all()->keyBy('name');

        $exchangers = [];

        if ($siteCookies->has('OBAMA')) {
            $obama = $siteCookies['OBAMA'];
            $exchangers['obama'] = [
                'url' => $obama->url . '&page_num=' . $pageNum,
                'cookies' => $obama->getCookiesString(),
            ];
        }

        if ($siteCookies->has('URAL')) {
            $ural = $siteCookies['URAL'];
            $exchangers['ural'] = [
                'url' => $ural->url . '&page_num=' . $pageNum,
                'cookies' => $ural->getCookiesString(),
            ];
        }

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

        // Обновляем данные заявки если есть изменения
        if (!empty($updateData)) {
            $this->applicationRepository->update($application->id, $updateData);
            // Обновляем объект в памяти
            $application->refresh();
        }

        // Всегда создаем/обновляем записи истории при наличии валютных данных
        $this->createHistoryRecords($application, $currencyData);
    }

    /**
     * Обработать данные продажи крипты
     */
    public function processSaleCryptData(\App\Models\SaleCrypt $saleCrypt): void
    {
        // Получаем существующие записи истории для этой продажи
        $existingHistories = $this->historyRepository->findByOperation(\App\Models\SaleCrypt::class, $saleCrypt->id);
        $existingByCurrency = $existingHistories->keyBy('currency_id');

        // 1. Продаваемая валюта (отрицательная сумма)
        if (!empty($saleCrypt->sale_amount) && !empty($saleCrypt->sale_currency_id)) {
            $this->updateOrCreateHistoryRecord(
                $saleCrypt->id,
                $saleCrypt->sale_currency_id,
                -$saleCrypt->sale_amount, // Отрицательная сумма для продажи
                $existingByCurrency,
                \App\Models\SaleCrypt::class
            );
        }

        // 2. Получаемая валюта (положительная сумма)
        if (!empty($saleCrypt->fixed_amount) && !empty($saleCrypt->fixed_currency_id)) {
            $this->updateOrCreateHistoryRecord(
                $saleCrypt->id,
                $saleCrypt->fixed_currency_id,
                $saleCrypt->fixed_amount, // Положительная сумма для получения
                $existingByCurrency,
                \App\Models\SaleCrypt::class
            );
        }
    }

    /**
     * Обработать данные покупки
     */
    public function processPurchaseData(\App\Models\Purchase $purchase): void
    {
        // Получаем существующие записи истории для этой покупки
        $existingHistories = $this->historyRepository->findByOperation(\App\Models\Purchase::class, $purchase->id);
        $existingByCurrency = $existingHistories->keyBy('currency_id');

        // 1. Продаваемая валюта (отрицательная сумма)
        if (!empty($purchase->sale_amount) && !empty($purchase->sale_currency_id)) {
            $this->updateOrCreateHistoryRecord(
                $purchase->id,
                $purchase->sale_currency_id,
                -$purchase->sale_amount, // Отрицательная сумма для продажи
                $existingByCurrency,
                \App\Models\Purchase::class
            );
        }

        // 2. Покупаемая валюта (положительная сумма)
        if (!empty($purchase->received_amount) && !empty($purchase->received_currency_id)) {
            $this->updateOrCreateHistoryRecord(
                $purchase->id,
                $purchase->received_currency_id,
                $purchase->received_amount, // Положительная сумма для покупки
                $existingByCurrency,
                \App\Models\Purchase::class
            );
        }
    }

    /**
     * Обработать данные перевода (только комиссия)
     */
    public function processTransferData(\App\Models\Transfer $transfer): void
    {
        // Получаем существующие записи истории для этого перевода
        $existingHistories = $this->historyRepository->findByOperation(\App\Models\Transfer::class, $transfer->id);
        $existingByCurrency = $existingHistories->keyBy('currency_id');

        // Только комиссия (отрицательная сумма - расход)
        if (!empty($transfer->commission) && !empty($transfer->commission_id)) {
            $this->updateOrCreateHistoryRecord(
                $transfer->id,
                $transfer->commission_id,
                -$transfer->commission, // Отрицательная сумма для комиссии (расход)
                $existingByCurrency,
                \App\Models\Transfer::class
            );
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
     * Создать или обновить записи истории для заявки
     */
    private function createHistoryRecords(Application $application, array $currencyData): void
    {
        // Получаем существующие записи истории для этой заявки
        $existingHistories = $this->historyRepository->findByOperation(Application::class, $application->id);
        $existingByCurrency = $existingHistories->keyBy('currency_id');

        // 1. Приход (продаваемая валюта) - положительная сумма
        if (!empty($currencyData['sell_amount']) && !empty($application->sell_currency_id)) {
            $this->updateOrCreateHistoryRecord(
                $application->id,
                $application->sell_currency_id,
                $currencyData['sell_amount'],
                $existingByCurrency,
                Application::class
            );
        }

        // 2. Продажа (покупаемая валюта) - отрицательная сумма
        if (!empty($currencyData['buy_amount']) && !empty($application->buy_currency_id)) {
            $this->updateOrCreateHistoryRecord(
                $application->id,
                $application->buy_currency_id,
                -$currencyData['buy_amount'], // Отрицательная сумма для продажи
                $existingByCurrency,
                Application::class
            );
        }

        // 3. Купля (покупаемая валюта) - положительная сумма (если есть отдельное поле)
        if (!empty($currencyData['buy_amount']) && !empty($application->buy_currency_id)) {
            // Если есть отдельное поле для купли, используем его
            $buyAmount = $currencyData['buy_amount'] ?? 0;
            if ($buyAmount > 0) {
                $this->updateOrCreateHistoryRecord(
                    $application->id,
                    $application->buy_currency_id,
                    $buyAmount, // Положительная сумма для купли
                    $existingByCurrency,
                    Application::class
                );
            }
        }

        // 4. Расход (валюта расходов) - отрицательная сумма
        if (!empty($currencyData['expense_amount']) && !empty($application->expense_currency_id)) {
            $this->updateOrCreateHistoryRecord(
                $application->id,
                $application->expense_currency_id,
                -$currencyData['expense_amount'], // Отрицательная сумма для расхода
                $existingByCurrency,
                Application::class
            );
        }
    }

    /**
     * Обновить или создать запись истории
     */
    private function updateOrCreateHistoryRecord(int $operationId, int $currencyId, float $amount, $existingByCurrency, string $modelType = Application::class): void
    {
        $existingRecord = $existingByCurrency->get($currencyId);

        if ($existingRecord) {
            // Обновляем существующую запись через репозиторий
            $this->historyRepository->updateOrCreateForOperation(
                $modelType,
                $operationId,
                [
                    'amount' => $amount,
                    'currency_id' => $currencyId,
                ]
            );
        } else {
            // Создаем новую запись
            $this->historyRepository->createForOperation(
                $modelType,
                $operationId,
                [
                    'amount' => $amount,
                    'currency_id' => $currencyId,
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
