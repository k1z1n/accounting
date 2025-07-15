<?php

namespace App\Repositories;

use App\Contracts\Repositories\ApplicationRepositoryInterface;
use App\Models\Application;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ApplicationRepository extends BaseRepository implements ApplicationRepositoryInterface
{
    public function __construct(Application $application)
    {
        parent::__construct($application);
    }

        /**
     * Получить заявки с отношениями
     */
    public function getAllWithRelations(): Collection
    {
        return $this->model->with([
            'sellCurrency',
            'buyCurrency',
            'expenseCurrency',
            'user'
        ])->get();
    }

    /**
     * Получить заявки с отношениями и пагинацией
     */
    public function getWithRelationsPaginated(int $page = 1, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->with([
            'sellCurrency',
            'buyCurrency',
            'expenseCurrency',
            'user'
        ])->orderByDesc('app_created_at')->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Получить пагинированные заявки с отношениями
     */
    public function getPaginatedWithRelations(int $perPage = 20): LengthAwarePaginator
    {
        return $this->model->with([
            'sellCurrency',
            'buyCurrency',
            'expenseCurrency',
            'user'
        ])->orderByDesc('app_created_at')->paginate($perPage);
    }

    /**
     * Найти заявки по статусу
     */
    public function findByStatus(array $statuses): Collection
    {
        return $this->model->whereIn('status', $statuses)->get();
    }

    /**
     * Найти заявки по обменнику
     */
    public function findByExchanger(string $exchanger): Collection
    {
        return $this->model->where('exchanger', $exchanger)->get();
    }

    /**
     * Получить заявки за период
     */
    public function getByDateRange(Carbon $from, Carbon $to): Collection
    {
        return $this->model->whereBetween('app_created_at', [$from, $to])->get();
    }

    /**
     * Синхронизация заявок (upsert)
     */
    public function syncApplications(array $applications): void
    {
        if (empty($applications)) {
            return;
        }

        $this->model->upsert(
            $applications,
            ['exchanger', 'app_id'], // уникальный составной ключ
            ['app_created_at', 'status', 'updated_at'] // поля для обновления
        );
    }

    /**
     * Получить статистику по статусам
     */
    public function getStatusStatistics(): array
    {
        return $this->model
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status')
            ->toArray();
    }

    /**
     * Получить статистику по обменникам
     */
    public function getExchangerStatistics(): array
    {
        return $this->model
            ->select('exchanger', DB::raw('count(*) as total'))
            ->groupBy('exchanger')
            ->get()
            ->pluck('total', 'exchanger')
            ->toArray();
    }

    /**
     * Получить заявки по ID обменника и ID заявки
     */
    public function findByExchangerAndAppId(string $exchanger, string $appId): ?Application
    {
        return $this->model
            ->where('exchanger', $exchanger)
            ->where('app_id', $appId)
            ->first();
    }

    /**
     * Получить динамику заявок по дням
     */
    public function getDailyStatistics(int $days = 30): array
    {
        return $this->model
            ->select(
                DB::raw('DATE(app_created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->where('app_created_at', '>=', Carbon::now()->subDays($days))
            ->groupBy(DB::raw('DATE(app_created_at)'))
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();
    }

    /**
     * Обновить связанные валюты заявки
     */
    public function updateCurrencies(int $applicationId, array $currencyData): Application
    {
        $application = $this->findOrFail($applicationId);
        $application->update($currencyData);

        return $application->fresh();
    }

    /**
     * Получить заявки со связанными покупками и продажами
     */
    public function getWithOperations(int $applicationId): Application
    {
        return $this->model->with([
            'sellCurrency',
            'buyCurrency',
            'expenseCurrency',
            'user',
            'purchases.receivedCurrency',
            'purchases.saleCurrency',
            'saleCrypts.fixedCurrency',
            'saleCrypts.saleCurrency'
        ])->findOrFail($applicationId);
    }

    /**
     * Получить статистику по периодам
     */
    public function getPeriodStatistics(): array
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();
        $thisYear = Carbon::now()->startOfYear();

        return [
            'today' => $this->model->whereDate('app_created_at', $today)->count(),
            'this_week' => $this->model->where('app_created_at', '>=', $thisWeek)->count(),
            'this_month' => $this->model->where('app_created_at', '>=', $thisMonth)->count(),
            'this_year' => $this->model->where('app_created_at', '>=', $thisYear)->count(),
        ];
    }
}
