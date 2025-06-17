<?php

namespace App\Console\Commands;

use App\Models\DailyUsdtTotal;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CalculateDailyUsdtSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate-daily-usdt-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();    // например "2025-06-17"

        // 1) Вычисляем накопительный итог на конец сегодняшнего дня
        //    Повторяем вашу логику из usdtTotal(), но без round в конце
        $totals = DB::table('histories')
            ->select('currency_id', DB::raw('SUM(amount) as sum'))
            ->groupBy('currency_id')
            ->get();

        // Получим все коды
        $currencyCodes = DB::table('currencies')->pluck('code', 'id')->map(fn($c)=>strtoupper($c));

        $totalUsdt = 0.0;
        foreach ($totals as $row) {
            $id   = $row->currency_id;
            $sum  = (float)$row->sum;
            $code = $currencyCodes[$id] ?? null;
            if (!$code) {
                Log::warning("DailySummary: валюта ID={$id} не найдена в currencies");
                continue;
            }
            if ($code === 'USDT') {
                $totalUsdt += $sum;
            } else {
                // запрос курса
                try {
                    $resp = Http::timeout(5)->get("https://api.heleket.com/v1/exchange-rate/{$code}/list");
                    if (!$resp->successful()) throw new \Exception("HTTP {$resp->status()}");
                    $data = $resp->json()['result'] ?? [];
                    $rate = collect($data)
                        ->first(fn($e)=>strtoupper($e['to']??'')==='USDT')['course'] ?? null;
                    if (!$rate) {
                        Log::error("DailySummary: не нашли курс {$code}->USDT");
                        continue;
                    }
                    $totalUsdt += $sum * (float)$rate;
                } catch (\Throwable $e) {
                    Log::error("DailySummary: ошибка при курсе {$code}: ".$e->getMessage());
                }
            }
        }
        // Округлим до 8 знаков
        $totalUsdt = round($totalUsdt, 8);

        // 2) Забираем вчерашний итог из БД
        $yesterday = Carbon::yesterday()->toDateString();
        $prev = DailyUsdtTotal::where('date', $yesterday)->first();

        if ($prev) {
            $delta = round($totalUsdt - (float)$prev->total, 8);
        } else {
            // если за вчера нет — дельта = весь сегодняшний итог
            $delta = $totalUsdt;
        }

        // 3) Сохраняем или обновляем запись за сегодня
        DailyUsdtTotal::updateOrCreate(
            ['date' => $today],
            ['total' => $totalUsdt, 'delta' => $delta]
        );

        $this->info("[$today] total={$totalUsdt}, delta={$delta}");
    }
}
