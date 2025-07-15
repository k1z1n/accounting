<?php

namespace App\Repositories;

use App\Contracts\Repositories\CurrencyRepositoryInterface;
use App\Models\Currency;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CurrencyRepository extends BaseRepository implements CurrencyRepositoryInterface
{
    public function __construct(Currency $currency)
    {
        parent::__construct($currency);
    }

    public function findByCode(string $code): ?Currency
    {
        return $this->model->where('code', $code)->first();
    }

    public function firstOrCreate(string $code, string $name = null): Currency
    {
        return $this->model->firstOrCreate(
            ['code' => $code],
            ['name' => $name ?? $code]
        );
    }

    public function getAllWithIcons(): Collection
    {
        return $this->model->orderBy('code')->get();
    }

    public function getAllOrdered(): Collection
    {
        return $this->model->orderBy('code')->get();
    }

    public function findByCodes(array $codes): Collection
    {
        return $this->model->whereIn('code', $codes)->get();
    }

    public function getPopularCurrencies(int $limit = 10): Collection
    {
        return $this->model
            ->leftJoin('histories', 'currencies.id', '=', 'histories.currency_id')
            ->select('currencies.*', DB::raw('COUNT(histories.id) as usage_count'))
            ->groupBy('currencies.id')
            ->orderByDesc('usage_count')
            ->limit($limit)
            ->get();
    }

    public function updateColor(int $currencyId, string $color): bool
    {
        return $this->model->where('id', $currencyId)->update(['color' => $color]);
    }

    public function getUsageStatistics(): array
    {
        return $this->model
            ->leftJoin('histories', 'currencies.id', '=', 'histories.currency_id')
            ->select('currencies.code', DB::raw('SUM(ABS(histories.amount)) as total_volume'))
            ->whereNotNull('histories.currency_id')
            ->groupBy('currencies.id', 'currencies.code')
            ->orderByDesc('total_volume')
            ->get()
            ->pluck('total_volume', 'code')
            ->toArray();
    }

    public function search(string $query): Collection
    {
        return $this->model
            ->where('code', 'LIKE', "%{$query}%")
            ->orWhere('name', 'LIKE', "%{$query}%")
            ->get();
    }
}
