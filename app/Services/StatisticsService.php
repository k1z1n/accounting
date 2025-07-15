<?php

namespace App\Services;

use App\Contracts\Repositories\ApplicationRepositoryInterface;
use App\Contracts\Repositories\HistoryRepositoryInterface;
use App\Contracts\Repositories\PaymentRepositoryInterface;
use App\Contracts\Repositories\TransferRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\StatisticsServiceInterface;
use App\Models\Purchase;
use App\Models\SaleCrypt;
use Carbon\Carbon;

class StatisticsService implements StatisticsServiceInterface
{
    public function __construct(
        private ApplicationRepositoryInterface $applicationRepository,
        private UserRepositoryInterface $userRepository,
        private HistoryRepositoryInterface $historyRepository,
        private TransferRepositoryInterface $transferRepository,
        private PaymentRepositoryInterface $paymentRepository
    ) {}

    /**
     * Получить общую статистику системы
     */
    public function getGeneralStatistics(): array
    {
        return [
            'applications' => $this->applicationRepository->count(),
            'users' => $this->userRepository->count(),
            'total_operations' => $this->historyRepository->count(),
            'transfers' => $this->transferRepository->count(),
            'payments' => $this->paymentRepository->count(),
            'purchases' => Purchase::count(),
            'sale_crypts' => SaleCrypt::count(),
        ];
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
     * Получить статистику операций
     */
    public function getOperationStatistics(): array
    {
        return [
            'transfers' => $this->transferRepository->getStatistics(),
            'payments' => $this->paymentRepository->getStatistics(),
            'purchases' => [
                'total_count' => Purchase::count(),
                'today_count' => Purchase::whereDate('created_at', today())->count(),
            ],
            'sale_crypts' => [
                'total_count' => SaleCrypt::count(),
                'today_count' => SaleCrypt::whereDate('created_at', today())->count(),
            ],
        ];
    }

    /**
     * Получить статистику пользователей
     */
    public function getUserStatistics(): array
    {
        return [
            'total' => $this->userRepository->count(),
            'by_role' => $this->userRepository->getRoleStatistics(),
            'active_today' => $this->userRepository->getActiveInPeriod(
                Carbon::today(),
                Carbon::tomorrow()
            )->count(),
            'blocked' => $this->userRepository->getBlocked()->count(),
        ];
    }

    /**
     * Получить статистику валют
     */
    public function getCurrencyStatistics(): array
    {
        return [
            'volumes' => $this->historyRepository->getCurrencyVolumes(10),
            'balances' => $this->historyRepository->getCurrencyBalances(),
        ];
    }

    /**
     * Получить данные для графиков
     */
    public function getChartData(string $type, array $params = []): array
    {
        switch ($type) {
            case 'daily':
                $days = $params['days'] ?? 30;
                return $this->getDailyChartData($days);

            case 'currency':
                return $this->getCurrencyChartData();

            case 'operations':
                return $this->getOperationsChartData();

            case 'users':
                return $this->getUsersChartData();

            default:
                throw new \InvalidArgumentException("Неизвестный тип графика: {$type}");
        }
    }

    /**
     * Получить статистику за период
     */
    public function getPeriodStatistics(Carbon $from, Carbon $to): array
    {
        return [
            'applications' => $this->applicationRepository->getByDateRange($from, $to)->count(),
            'history' => $this->historyRepository->getByDateRange($from, $to)->count(),
            'transfers' => $this->transferRepository->getByDateRange($from, $to)->count(),
            'payments' => $this->paymentRepository->getByDateRange($from, $to)->count(),
        ];
    }

    /**
     * Экспорт статистики
     */
    public function exportStatistics(string $format = 'json'): array
    {
        $data = [
            'general' => $this->getGeneralStatistics(),
            'applications' => $this->getApplicationStatistics(),
            'operations' => $this->getOperationStatistics(),
            'users' => $this->getUserStatistics(),
            'currencies' => $this->getCurrencyStatistics(),
            'exported_at' => now()->toISOString(),
        ];

        return match ($format) {
            'json' => $data,
            'csv' => $this->convertToCSV($data),
            default => throw new \InvalidArgumentException("Неподдерживаемый формат: {$format}")
        };
    }

    /**
     * Данные для графика по дням
     */
    private function getDailyChartData(int $days): array
    {
        $dailyStats = $this->applicationRepository->getDailyStatistics($days);

        return [
            'labels' => array_keys($dailyStats),
            'data' => array_values($dailyStats),
        ];
    }

    /**
     * Данные для графика по валютам
     */
    private function getCurrencyChartData(): array
    {
        $volumes = $this->historyRepository->getCurrencyVolumes(10);

        return [
            'labels' => array_column($volumes, 'currency'),
            'data' => array_column($volumes, 'volume'),
        ];
    }

    /**
     * Данные для графика по операциям
     */
    private function getOperationsChartData(): array
    {
        return [
            'labels' => ['Переводы', 'Платежи', 'Покупки', 'Продажи'],
            'data' => [
                $this->transferRepository->count(),
                $this->paymentRepository->count(),
                Purchase::count(),
                SaleCrypt::count(),
            ],
        ];
    }

    /**
     * Данные для графика по пользователям
     */
    private function getUsersChartData(): array
    {
        $roleStats = $this->userRepository->getRoleStatistics();

        return [
            'labels' => array_keys($roleStats),
            'data' => array_values($roleStats),
        ];
    }

    /**
     * Конвертация в CSV
     */
    private function convertToCSV(array $data): array
    {
        // Упрощенная конвертация для демонстрации
        $csv = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $csv[] = [$key, json_encode($value)];
            } else {
                $csv[] = [$key, $value];
            }
        }
        return $csv;
    }
}
