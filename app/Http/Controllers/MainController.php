<?php
// app/Http/Controllers/MainController.php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Currency;
use App\Models\DailyUsdtTotal;
use App\Models\Exchanger;
use App\Models\History;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\SaleCrypt;
use App\Models\Transfer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MainController extends Controller
{
    /**
     * Базовый запрос для выборки заявок с нужными связями.
     */
    protected function baseQuery()
    {
        return Application::with([
            'sellCurrency',
            'buyCurrency',
            'expenseCurrency',
            'user',          // если вам нужно сразу грузить автора изменений
        ]);
    }

    /**
     * Загрузка и синхронизация заявок с обоих обменников
     *
     * @param int $pageNum — номер страницы для пагинации API
     */
    private function fetchAndSyncRemote(int $pageNum): void
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
                $url = $cfg['url'];
                $cookieHeader = $cfg['cookies'];
                $headers = [
                    'Cookie' => $cookieHeader,
                    'User-Agent' => 'Mozilla/5.0',
                ];
                Log::info("fetchAndSyncRemote: отправляем запрос к $exchangerName", [
                    'url' => $url,
                    'headers' => $headers
                ]);
                $response = Http::withHeaders($headers)->timeout(15)->get($url);
                Log::info("fetchAndSyncRemote: получен ответ от $exchangerName", [
                    'status' => $response->status(),
                    'response_headers' => $response->headers(),
                    'set_cookie' => $response->header('Set-Cookie'),
                    'body_snippet' => mb_substr($response->body(), 0, 500)
                ]);
                $responses[$exchangerName] = $response;
            } catch (\Exception $e) {
                Log::error("fetchAndSyncRemote: ошибка запроса к {$exchangerName}", [
                    'error' => $e->getMessage()
                ]);
                $hasErrors = true;
                continue;
            }
        }

        if ($records->isEmpty()) {
            Log::info('fetchAndSyncRemote: не найдено ни одной записи');
            return;
        }

        // Сортируем по дате создания (DESC)
        $toUpsert = $records->sortByDesc('app_created_at')->values()->all();

        try {
            Application::upsert(
                $toUpsert,
                ['exchanger', 'app_id'],          // уникальный составной ключ
                ['app_created_at', 'status', 'updated_at']
            );
        } catch (\Exception $e) {
            Log::critical('fetchAndSyncRemote: ошибка при upsert в БД', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Показывает главную страницу со списком заявок (первые 20 строк).
     */
    public function viewMain(Request $request)
    {
        $pageNum = (int)$request->get('page', 1);
        $perPage = 20;

        try {
            $this->fetchAndSyncRemote($pageNum);
        } catch (\Exception $e) {
            Log::error("viewMain: ошибка синхронизации", [
                'error' => $e->getMessage()
            ]);
        }

        // Берём «выполненные» и «оплаченные» заявки, вместе с отношениями sellCurrency, buyCurrency, expenseCurrency, user
        $apps = $this->baseQuery()
            ->whereIn('status', ['выполненная заявка', 'оплаченная заявка', 'возврат'])
            ->orderByDesc('app_created_at')
            ->paginate($perPage, ['*'], 'page', $pageNum);

        // Все валюты (для селектов модалки)
        $currenciesForEdit = Currency::orderBy('code')->get();

        $perPageFour = 10;
        // Остальные блоки (Transfers, Payments, Purchase, SaleCrypt, History)
        $exchangers = Exchanger::orderBy('title')->get();
        // 2) Переводы — ПАГИНАЦИЯ
        $transfers = Transfer::orderByDesc('created_at')
            ->paginate($perPageFour, ['*'], 'transfers_page', $request->get('transfers_page', 1));

        // 3) Оплаты — ПАГИНАЦИЯ
        $payments = Payment::orderByDesc('created_at')
            ->paginate($perPageFour, ['*'], 'payments_page', $request->get('payments_page', 1));

        // 4) Покупка крипты — ПАГИНАЦИЯ
        $purchases = Purchase::orderByDesc('created_at')
            ->paginate($perPageFour, ['*'], 'purchases_page', $request->get('purchases_page', 1));

        // 5) Продажа крипты — ПАГИНАЦИЯ
        $saleCrypts = SaleCrypt::orderByDesc('created_at')
            ->paginate($perPageFour, ['*'], 'salecrypts_page', $request->get('salecrypts_page', 1));

        $histories = History::with('currency')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            // чтобы на странице они шли по возрастанию даты
            ->sortBy('created_at');

        // 2) Считаем итоги по всей базе, группируя по currency_id
        $rawTotals = History::whereNotNull('currency_id')
            ->groupBy('currency_id')
            ->select('currency_id', DB::raw('SUM(amount) as total'))
            ->pluck('total', 'currency_id');
        // возвращает коллекцию вида [ currency_id => total, ... ]

        // 3) Получим список валют из вашей модели (или из тех, что вообще есть в истории)
        $currencies = Currency::whereIn('id', $rawTotals->keys())->get();

        // 4) Сформируем массив итогов только для этих валют
        $totals = [];
        foreach ($currencies as $c) {
            $totals[$c->code] = $rawTotals->get($c->id, 0);
        }

        // достаём всё из daily_usdt_totals, отсортированное по дате
        $daily = DailyUsdtTotal::orderBy('date')->get();

        $labels = $daily
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->format('d.m'))
            ->toArray();

        $data = $daily->pluck('total')->toArray();

        // Цвет точки: зелёный если delta > 0, красный — если < 0
        $pointColors = $daily->map(fn($row) => $row->delta >= 0 ? '#22c55e' : '#ef4444'
        )->toArray();

        return view('pages.main', compact(
            'apps',
            'currenciesForEdit',
            'exchangers',
            'saleCrypts',
            'transfers',
            'payments',
            'purchases',
            'histories',
            'totals',
            'labels',
            'data',
            'pointColors'
        ));
    }

    public function usdtChart(Request $request)
    {
        $start = $request->query('start')
            ? Carbon::parse($request->query('start'))->startOfDay()
            : now()->subDays(6)->startOfDay();

        $end = $request->query('end')
            ? Carbon::parse($request->query('end'))->endOfDay()
            : now()->endOfDay();

        $daily = DailyUsdtTotal::whereBetween('date', [$start, $end])
            ->orderBy('date')
            ->get();

        $labels      = $daily->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->format('d.m'))
            ->toArray();
        $data        = $daily->pluck('total')->toArray();
        $deltas      = $daily->pluck('delta')->toArray();             // <-- вот он
        $pointColors = $daily->map(fn($row) => $row->delta >= 0
            ? '#22c55e'
            : '#ef4444')
            ->toArray();

        return response()->json([
            'labels'   => $labels,
            'datasets' => [[
                'label'                => 'USDT',
                'data'                 => $data,
                'deltas'               => $deltas,            // <-- передаём
                'backgroundColor'      => 'rgba(79, 70, 229, 0.2)',
                'borderColor'          => 'rgb(79, 70, 229)',
                'pointBackgroundColor' => $pointColors,
                'pointRadius'          => 6,
                'pointHoverRadius'     => 8,
                'tension'              => 0.4,
                'fill'                 => true,
            ]]
        ]);
    }


    /**
     * AJAX: возвращает JSON с очередной порцией (20 штук) заявок для «Загрузить ещё».
     */
    public function apiApplications(Request $request)
    {
        $pageNum = (int)$request->get('page', 1);
        $perPage = 20;

        try {
            $this->fetchAndSyncRemote($pageNum);
        } catch (\Exception $e) {
            Log::error("apiApplications: ошибка синхронизации", [
                'error' => $e->getMessage()
            ]);
        }

        // **ВАЖНО**: здесь обязательно with(...), чтобы в JSON-ответе были вложенные sellCurrency, buyCurrency, expenseCurrency и user
        $apps = Application::with(['sellCurrency', 'buyCurrency', 'expenseCurrency', 'user'])
            ->whereIn('status', ['выполненная заявка', 'оплаченная заявка'])
            ->orderByDesc('app_created_at')
            ->paginate($perPage, ['*'], 'page', $pageNum);

        return response()->json([
            'data' => $apps->items(),
            'has_more' => $apps->hasMorePages(),
        ]);
    }

    /**
     * AJAX: сохраняет правки одной заявки из модального окна.
     */

    public function update(Request $request, $id)
    {
        $request->validate([
            'sale_text' => 'nullable|string|max:64',
            'sell_amount' => 'nullable|numeric|min:0',
            'sell_currency' => 'nullable|string|max:8',
            'buy_amount' => 'nullable|numeric|min:0',
            'buy_currency' => 'nullable|string|max:8',
            'expense_amount' => 'nullable|numeric|min:0',
            'expense_currency' => 'nullable|string|max:8',
            'merchant' => 'nullable|string|max:128',
            'order_id' => 'nullable|string|max:128',
        ]);

        $app = Application::findOrFail($id);

        //
        // 1) Сохраняем sale_text (приход) — парсим «число + код валюты», если есть
        //
        $amount = null;
        $saleCurrencyId = null;

        if ($app->sale_text) {
            $saleText = $app->sale_text;
            Log::info("Update: received sale_text='{$saleText}' for app_id={$app->id}");

            $parts = explode(' ', $saleText, 2);
            $rawAmount = $parts[0] ?? null;
            $rawCode = $parts[1] ?? null;

            if (is_numeric($rawAmount)) {
                $amount = floatval($rawAmount);
            } else {
                Log::warning("Failed to parse amount from sale_text='{$saleText}'");
                $amount = null;
            }

            if ($rawCode) {
                $currencyCode = mb_strtoupper($rawCode);
            } else {
                Log::warning("No currency code found in sale_text='{$saleText}'");
                $currencyCode = null;
            }

            if ($amount !== null && $currencyCode) {
                $cur = Currency::firstOrCreate(
                    ['code' => $currencyCode],
                    ['name' => $currencyCode]
                );
                $saleCurrencyId = $cur->id;
                Log::info("Currency lookup/create for sale_text: code='{$currencyCode}', id={$saleCurrencyId}");
            } else {
                $saleCurrencyId = null;
            }
        } else {
            $app->sale_text = null;
            Log::info("No sale_text provided for app_id={$app->id}");
        }

        //
        // 2) «Продажа» (sell_*)
        //
        if ($request->filled('sell_currency')) {
            $curSell = Currency::firstOrCreate(
                ['code' => mb_strtoupper($request->sell_currency)],
                ['name' => mb_strtoupper($request->sell_currency)]
            );
            $app->sell_currency_id = $curSell->id;
            Log::info("Set sell_currency_id={$curSell->id} for app_id={$app->id}");
        } else {
            $app->sell_currency_id = null;
            Log::info("No sell_currency provided for app_id={$app->id}");
        }
        $app->sell_amount = $request->sell_amount ?: null;
        Log::info("Set sell_amount=" . ($app->sell_amount ?? 'null') . " for app_id={$app->id}");

        //
        // 3) «Купля» (buy_*)
        //
        if ($request->filled('buy_currency')) {
            $curBuy = Currency::firstOrCreate(
                ['code' => mb_strtoupper($request->buy_currency)],
                ['name' => mb_strtoupper($request->buy_currency)]
            );
            $app->buy_currency_id = $curBuy->id;
            Log::info("Set buy_currency_id={$curBuy->id} for app_id={$app->id}");
        } else {
            $app->buy_currency_id = null;
            Log::info("No buy_currency provided for app_id={$app->id}");
        }
        $app->buy_amount = $request->buy_amount ?: null;
        Log::info("Set buy_amount=" . ($app->buy_amount ?? 'null') . " for app_id={$app->id}");

        //
        // 4) «Расход» (expense_*)
        //
        if ($request->filled('expense_currency')) {
            $curExpense = Currency::firstOrCreate(
                ['code' => mb_strtoupper($request->expense_currency)],
                ['name' => mb_strtoupper($request->expense_currency)]
            );
            $app->expense_currency_id = $curExpense->id;
            Log::info("Set expense_currency_id={$curExpense->id} for app_id={$app->id}");
        } else {
            $app->expense_currency_id = null;
            Log::info("No expense_currency provided for app_id={$app->id}");
        }
        $app->expense_amount = $request->expense_amount ?: null;
        Log::info("Set expense_amount=" . ($app->expense_amount ?? 'null') . " for app_id={$app->id}");

        //
        // 5) «Мерчант» и «ID ордера»
        //
        $app->merchant = $request->merchant ?: null;
        $app->order_id = $request->order_id ?: null;
        $app->user_id = auth()->id();
        Log::info("Set merchant=" . ($app->merchant ?? 'null') .
            ", order_id=" . ($app->order_id ?? 'null') . " for app_id={$app->id}");

        $app->save();
        Log::info("Application saved for id={$app->id}");

        //
        // 6) Синхронизируем историю: удаляем все предыдущие записи (polymorphic) и создаём актуальные
        //
        History::where('sourceable_type', Application::class)
            ->where('sourceable_id', $app->id)
            ->delete();
        Log::info("Cleared existing history entries for Application id={$app->id}");

        // 6.1) «Приход» из sale_text
        if ($amount !== null && $saleCurrencyId !== null && $amount > 0) {
            History::create([
                'sourceable_type' => Application::class,
                'sourceable_id' => $app->id,
                'amount' => +$amount,
                'currency_id' => $saleCurrencyId,
            ]);
            Log::info("History created for sale_text (income) +{$amount}, currency_id={$saleCurrencyId}");
        }

        // 6.2) «Продажа» (sell_amount → расход)
        if ($app->sell_amount !== null && $app->sell_currency_id !== null && $app->sell_amount > 0) {
            History::create([
                'sourceable_type' => Application::class,
                'sourceable_id' => $app->id,
                'amount' => -$app->sell_amount,
                'currency_id' => $app->sell_currency_id,
            ]);
            Log::info("History created for sell_amount (expense) -{$app->sell_amount}, currency_id={$app->sell_currency_id}");
        }

        // 6.3) «Купля» (buy_amount → приход)
        if ($app->buy_amount !== null && $app->buy_currency_id !== null && $app->buy_amount > 0) {
            History::create([
                'sourceable_type' => Application::class,
                'sourceable_id' => $app->id,
                'amount' => +$app->buy_amount,
                'currency_id' => $app->buy_currency_id,
            ]);
            Log::info("History created for buy_amount (income) +{$app->buy_amount}, currency_id={$app->buy_currency_id}");
        }

        // 6.4) «Расход» (expense_amount → расход)
        if ($app->expense_amount !== null && $app->expense_currency_id !== null && $app->expense_amount > 0) {
            History::create([
                'sourceable_type' => Application::class,
                'sourceable_id' => $app->id,
                'amount' => -$app->expense_amount,
                'currency_id' => $app->expense_currency_id,
            ]);
            Log::info("History created for expense_amount (expense) -{$app->expense_amount}, currency_id={$app->expense_currency_id}");
        }

        return response()->json(['success' => true]);
    }


    /**
     * Возвращает JSON с общим итогом всей истории в USDT.
     * (Данные берутся из таблицы histories, группируются по currency_id,
     *  затем для каждой валюты получаем курс в USDT через Heleket и суммируем.)
     */
    public function usdtTotal(Request $request)
    {
        $totalsByCurrency = History::whereNotNull('currency_id')
            ->select('currency_id', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('currency_id')
            ->get();

        $currencyCodes = Currency::pluck('code', 'id'); // [id => "BTC", "USDT", ...]

        $usdtTotal = 0.0;

        // 1. Получаем актуальные курсы с Bybit
        try {
            $response = Http::timeout(10)->get('https://api.bybit.com/v5/market/tickers?category=spot');

            if (!$response->successful()) {
                Log::error('usdtTotal: ошибка загрузки курсов с Bybit: HTTP ' . $response->status());
                return response()->json(['error' => 'Курсы временно недоступны'], 500);
            }

            $tickers = $response->json()['result']['list'] ?? [];
            $rates = [];

            foreach ($tickers as $ticker) {
                $symbol = $ticker['symbol'] ?? '';
                if (str_ends_with($symbol, 'USDT')) {
                    $code = str_replace('USDT', '', strtoupper($symbol));
                    $rates[$code] = (float)$ticker['lastPrice'];
                }
            }
        } catch (\Throwable $e) {
            Log::error('usdtTotal: ошибка при загрузке курсов с Bybit: ' . $e->getMessage());
            return response()->json(['error' => 'Ошибка получения курсов'], 500);
        }

        // 2. Пересчёт в USDT
        foreach ($totalsByCurrency as $row) {
            $currencyId = $row->currency_id;
            $sum = (float)$row->total_amount;

            if (!isset($currencyCodes[$currencyId])) {
                Log::warning("usdtTotal: нет кода валюты для ID={$currencyId}");
                continue;
            }

            $code = strtoupper($currencyCodes[$currencyId]);

            if ($code === 'USDT') {
                $usdtTotal += $sum;
                continue;
            }

            if (!isset($rates[$code])) {
                Log::warning("usdtTotal: нет курса для {$code} к USDT");
                continue;
            }

            $usdtTotal += $sum * $rates[$code];
        }

        return response()->json([
            'usdt_total' => round($usdtTotal, 8),
        ]);
    }


    public function dashboard()
    {
        // Ваша обычная логика…
        $totalUsd = 0;
        $availableUsd = 0;
        $baseCurrency = Currency::where('code', 'USD')->first();

        // Попытка взять реальные данные
        $wallets = auth()->user()->get();
        $transactions = auth()->user()
            ->latest()
            ->take(5)
            ->get();

        // Если кошельков нет – создаём 5 случайных записей
        if ($wallets->isEmpty()) {
            $sampleCodes = ['BTC', 'ETH', 'USDT', 'XRP', 'LTC'];
            $wallets = collect($sampleCodes)->map(function ($code) {
                return (object)[
                    'currency' => (object)[
                        'code' => $code,
                        'icon_url' => "https://dummyimage.com/32x32/000/fff&text={$code}"
                    ],
                    'balance' => rand(1, 999999) / 10000  // от 0.0001 до 99.9999
                ];
            });
            // Пересчитаем общий баланс
            $totalUsd = $wallets->sum(fn($w) => $w->balance * rand(100, 50000) / 100);
            $availableUsd = $totalUsd * 0.8;
        } else {
            // если реальные есть – суммируем по ним
            $totalUsd = $wallets->sum(fn($w) => $w->balance_in_usd);
            $availableUsd = $wallets->sum(fn($w) => $w->available_in_usd);
        }

        // Если транзакций нет – создаём 5 рандомных
        if ($transactions->isEmpty()) {
            $types = ['Получено', 'Отправлено'];
            $transactions = collect(range(1, 5))->map(function () {
                $sampleCodes = ['BTC', 'ETH', 'USDT', 'XRP', 'LTC'];
                $amt = random_int(1, 999999) / 10000;
                $sign = random_int(0, 1) ? 1 : -1;
                $code = $sampleCodes[array_rand($sampleCodes)];
                return (object)[
                    'type' => $sign > 0 ? 'Получено' : 'Отправлено',
                    'date' => now()->subMinutes(random_int(1, 300)),
                    'amount' => $amt * $sign,
                    'currency' => (object)['code' => $code]
                ];
            });
        }

        return view('pages.profile', compact(
            'totalUsd',
            'availableUsd',
            'baseCurrency',
            'wallets',
            'transactions'
        ));
    }

    public function apiShowApplication($id)
    {
        $app = Application::with([
            'sellCurrency', 'buyCurrency', 'expenseCurrency',
            'purchases.receivedCurrency', 'purchases.saleCurrency',
            'saleCrypts.fixedCurrency', 'saleCrypts.saleCurrency',
        ])->findOrFail($id);

        // Иконки для основных валют
        $sellIcon = $app->sellCurrency ? asset('images/coins/' . $app->sellCurrency->code . '.svg') : null;
        $buyIcon = $app->buyCurrency ? asset('images/coins/' . $app->buyCurrency->code . '.svg') : null;
        $expenseIcon = $app->expenseCurrency ? asset('images/coins/' . $app->expenseCurrency->code . '.svg') : null;

        // Сопутствующие покупки
        $relatedPurchases = $app->purchases->map(fn($p) => [
            'id' => $p->id,
            'received_amount' => $p->received_amount,
            'received_currency' => $p->receivedCurrency?->code,
            'received_icon' => $p->receivedCurrency ? asset('images/coins/' . $p->receivedCurrency->code . '.svg') : null,
            'sale_amount' => $p->sale_amount,
            'sale_currency' => $p->saleCurrency?->code,
            'sale_icon' => $p->saleCurrency ? asset('images/coins/' . $p->saleCurrency->code . '.svg') : null,
        ]);
        // Сопутствующие продажи
        $relatedSaleCrypts = $app->saleCrypts->map(fn($s) => [
            'id' => $s->id,
            'fixed_amount' => $s->fixed_amount,
            'fixed_currency' => $s->fixedCurrency?->code,
            'fixed_icon' => $s->fixedCurrency ? asset('images/coins/' . $s->fixedCurrency->code . '.svg') : null,
            'sale_amount' => $s->sale_amount,
            'sale_currency' => $s->saleCurrency?->code,
            'sale_icon' => $s->saleCurrency ? asset('images/coins/' . $s->saleCurrency->code . '.svg') : null,
        ]);

        return response()->json([
            'app_id'                => $app->app_id,
            'app_created_at'        => $app->app_created_at ? Carbon::parse($app->app_created_at)->format('d.m.Y H:i:s') : null,
            'exchanger'             => $app->exchanger,
            'status'                => $app->status,
            'merchant'              => $app->merchant,
            'sell_amount'           => $app->sell_amount,
            'sell_currency'         => $app->sellCurrency?->code,
            'sell_icon'             => $sellIcon,
            'buy_amount'            => $app->buy_amount,
            'buy_currency'          => $app->buyCurrency?->code,
            'buy_icon'              => $buyIcon,
            'expense_amount'        => $app->expense_amount,
            'expense_currency'      => $app->expenseCurrency?->code,
            'expense_icon'          => $expenseIcon,
            'order_id'              => $app->order_id,
            'user_id'               => $app->user_id,
            'related_purchases'     => $relatedPurchases,
            'related_sale_crypts'   => $relatedSaleCrypts,
        ]);
    }

    /**
     * Страница с полной историей (все записи, без ограничения).
     */
    public function allHistory(Request $request)
    {
        // Группируем связи по типу sourceable
        // MorphTo::morphWith([
        //     \App\Models\Purchase::class => ['exchanger', 'saleCurrency', 'receivedCurrency'],
        //     \App\Models\SaleCrypt::class => ['exchanger', 'saleCurrency', 'fixedCurrency'],
        //     \App\Models\Payment::class => ['exchanger', 'sellCurrency'],
        //     \App\Models\Transfer::class => ['exchangerFrom', 'exchangerTo', 'commissionCurrency', 'amountCurrency'],
        //     \App\Models\Application::class => ['sellCurrency', 'buyCurrency', 'expenseCurrency', 'purchases', 'saleCrypts'],
        // ]);

        $histories = History::with(['currency', 'sourceable'])
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        // Подгружаем только реально существующие связи для каждого типа sourceable
        foreach ($histories as $history) {
            if ($history->sourceable instanceof \App\Models\Purchase) {
                $history->sourceable->loadMissing(['exchanger', 'saleCurrency', 'receivedCurrency']);
            } elseif ($history->sourceable instanceof \App\Models\SaleCrypt) {
                $history->sourceable->loadMissing(['exchanger', 'saleCurrency', 'fixedCurrency']);
            } elseif ($history->sourceable instanceof \App\Models\Payment) {
                $history->sourceable->loadMissing(['exchanger', 'sellCurrency']);
            } elseif ($history->sourceable instanceof \App\Models\Transfer) {
                $history->sourceable->loadMissing(['exchangerFrom', 'exchangerTo', 'commissionCurrency', 'amountCurrency']);
            } elseif ($history->sourceable instanceof \App\Models\Application) {
                $history->sourceable->loadMissing(['sellCurrency', 'buyCurrency', 'expenseCurrency', 'purchases', 'saleCrypts']);
            }
        }

        // Итоги по всем валютам (оставим для возможного футера)
        $rawTotals = History::whereNotNull('currency_id')
            ->groupBy('currency_id')
            ->select('currency_id', DB::raw('SUM(amount) as total'))
            ->pluck('total', 'currency_id');

        $currencies = Currency::whereIn('id', $rawTotals->keys())->get();
        $totals = [];
        foreach ($currencies as $c) {
            $totals[$c->id] = $rawTotals->get($c->id, 0);
        }

        return view('pages.all-history', compact('histories', 'currencies', 'totals'));
    }
}
