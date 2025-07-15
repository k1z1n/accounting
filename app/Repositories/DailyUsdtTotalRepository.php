<?php

namespace App\Repositories;

use App\Contracts\Repositories\DailyUsdtTotalRepositoryInterface;
use App\Models\DailyUsdtTotal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DailyUsdtTotalRepository extends BaseRepository implements DailyUsdtTotalRepositoryInterface
{
    public function __construct(DailyUsdtTotal $dailyUsdtTotal)
    {
        parent::__construct($dailyUsdtTotal);
    }

    /**
     * Получить данные за период
     */
    public function getByDateRange(Carbon $from, Carbon $to): Collection
    {
        return $this->model
            ->whereBetween('date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->orderBy('date')
            ->get();
    }

    /**
     * Получить последние записи
     */
    public function getLatest(int $limit = 30): Collection
    {
        return $this->model
            ->orderByDesc('date')
            ->limit($limit)
            ->get()
            ->sortBy('date')
            ->values();
    }

    /**
     * Создать или обновить запись за дату
     */
    public function createOrUpdateForDate(Carbon $date, float $total, float $delta = 0): DailyUsdtTotal
    {
        return $this->model->updateOrCreate(
            ['date' => $date->format('Y-m-d')],
            ['total' => $total, 'delta' => $delta]
        );
    }

    /**
     * Получить итоги по месяцам
     */
    public function getMonthlyTotals(): array
    {
        return $this->model
            ->select(
                DB::raw('YEAR(date) as year'),
                DB::raw('MONTH(date) as month'),
                DB::raw('AVG(total) as avg_total'),
                DB::raw('MAX(total) as max_total'),
                DB::raw('MIN(total) as min_total'),
                DB::raw('SUM(delta) as total_delta')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'period' => Carbon::create($item->year, $item->month)->format('Y-m'),
                    'avg_total' => round($item->avg_total, 2),
                    'max_total' => round($item->max_total, 2),
                    'min_total' => round($item->min_total, 2),
                    'total_delta' => round($item->total_delta, 2),
                ];
            })
            ->toArray();
    }

    /**
     * Получить статистику
     */
    public function getStatistics(): array
    {
        $latest = $this->model->orderByDesc('date')->first();
        $previous = $this->model->orderByDesc('date')->skip(1)->first();

        return [
            'current_total' => $latest?->total ?? 0,
            'previous_total' => $previous?->total ?? 0,
            'current_delta' => $latest?->delta ?? 0,
            'change_percent' => $previous
                ? round((($latest->total - $previous->total) / $previous->total) * 100, 2)
                : 0,
            'total_records' => $this->model->count(),
            'date_range' => [
                'first' => $this->model->orderBy('date')->value('date'),
                'last' => $this->model->orderByDesc('date')->value('date'),
            ],
            'max_total' => $this->model->max('total'),
            'min_total' => $this->model->min('total'),
            'avg_total' => round($this->model->avg('total'), 2),
        ];
    }
}
