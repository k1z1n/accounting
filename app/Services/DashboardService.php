<?php

namespace App\Services;

use App\Contracts\Repositories\ApplicationRepositoryInterface;
use App\Contracts\Repositories\CurrencyRepositoryInterface;
use App\Contracts\Repositories\HistoryRepositoryInterface;
use App\Contracts\Repositories\PaymentRepositoryInterface;
use App\Contracts\Repositories\TransferRepositoryInterface;
use App\Models\DailyUsdtTotal;
use App\Models\Exchanger;
use App\Models\Purchase;
use App\Models\SaleCrypt;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class DashboardService
{
    public function __construct(
        private ApplicationRepositoryInterface $applicationRepository,
        private CurrencyRepositoryInterface $currencyRepository,
        private HistoryRepositoryInterface $historyRepository,
        private TransferRepositoryInterface $transferRepository,
        private PaymentRepositoryInterface $paymentRepository
    ) {}

    /**
     * Получить данные для главной страницы
     */
    public function getDashboardData(int $page = 1, int $perPage = 20): array
    {
        return [
            'applications' => $this->getApplicationsPaginated($page, $perPage),
            'currencies' => $this->getCurrenciesForEdit(),
            'exchangers' => $this->getExchangers(),
            'transfers' => $this->getTransfersPaginated(),
            'payments' => $this->getPaymentsPaginated(),
            'purchases' => $this->getPurchasesPaginated(),
            'sale_crypts' => $this->getSaleCryptsPaginated(),
            'histories' => $this->getRecentHistory(),
            'totals' => $this->getCurrencyTotals(),
            'chart_data' => $this->getChartData(),
        ];
    }

    /**
     * Получить заявки с пагинацией
     */
    public function getApplicationsPaginated(int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        return $this->applicationRepository->getWithRelationsPaginated($page, $perPage);
    }

    /**
     * Получить валюты для редактирования
     */
    public function getCurrenciesForEdit(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->currencyRepository->getAllOrdered();
    }

    /**
     * Получить обменники
     */
    public function getExchangers(): \Illuminate\Database\Eloquent\Collection
    {
        return Exchanger::orderBy('title')->get();
    }

    /**
     * Получить переводы с пагинацией
     */
    public function getTransfersPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->transferRepository->getPaginated($perPage);
    }

    /**
     * Получить платежи с пагинацией
     */
    public function getPaymentsPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return $this->paymentRepository->getPaginated($perPage);
    }

    /**
     * Получить покупки с пагинацией
     */
    public function getPurchasesPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return Purchase::orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Получить продажи криптовалют с пагинацией
     */
    public function getSaleCryptsPaginated(int $perPage = 10): LengthAwarePaginator
    {
        return SaleCrypt::orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Получить недавнюю историю
     */
    public function getRecentHistory(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return $this->historyRepository->getLatest($limit)->sortBy('created_at');
    }

    /**
     * Получить итоги по валютам
     */
    public function getCurrencyTotals(): array
    {
        return $this->historyRepository->getCurrencyBalances();
    }

    /**
     * Получить данные для графика USDT
     */
    public function getChartData(): array
    {
        $daily = DailyUsdtTotal::orderBy('date')->get();

        return [
            'labels' => $daily->pluck('date')
                ->map(fn($d) => Carbon::parse($d)->format('d.m'))
                ->toArray(),
            'data' => $daily->pluck('total')->toArray(),
            'point_colors' => $daily->map(fn($row) => $row->delta >= 0 ? '#22c55e' : '#ef4444')
                ->toArray(),
        ];
    }

    /**
     * Получить данные графика за период
     */
    public function getUsdtChartData(?Carbon $start = null, ?Carbon $end = null): array
    {
        $start = $start ?: now()->subDays(6)->startOfDay();
        $end = $end ?: now()->endOfDay();

        $daily = DailyUsdtTotal::whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get();

        return [
            'labels' => $daily->pluck('date')
                ->map(fn($d) => Carbon::parse($d)->format('d.m'))
                ->toArray(),
            'datasets' => [[
                'label' => 'USDT',
                'data' => $daily->pluck('total')->toArray(),
                'deltas' => $daily->pluck('delta')->toArray(),
                'backgroundColor' => 'rgba(79, 70, 229, 0.2)',
                'borderColor' => 'rgb(79, 70, 229)',
                'pointBackgroundColor' => $daily->map(fn($row) => $row->delta >= 0 ? '#22c55e' : '#ef4444')
                    ->toArray(),
                'pointRadius' => 6,
                'pointHoverRadius' => 8,
                'tension' => 0.4,
                'fill' => true,
            ]]
        ];
    }
}
