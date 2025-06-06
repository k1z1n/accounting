<?php
// app/Http/Controllers/MainController.php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Currency;
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
        $allowedStatuses = ['выполненная заявка', 'оплаченная заявка'];

        $exchangers = [
            'obama' => [
                'url'     => 'https://obama.ru/wp-admin/admin.php?page=pn_bids&page_num=' . $pageNum,
                'cookies' => config('exchanger.obama.cookie'),
            ],
            'ural' => [
                'url'     => 'https://ural-obmen.ru/wp-admin/admin.php?page=pn_bids&page_num=' . $pageNum,
                'cookies' => config('exchanger.ural.cookie'),
            ],
        ];

        $records = collect();

        foreach ($exchangers as $exchangerName => $cfg) {
            try {
                $response = Http::withHeaders([
                    'Cookie'     => $cfg['cookies'],
                    'User-Agent' => 'Mozilla/5.0',
                ])->timeout(15)->get($cfg['url']);

                if (! $response->successful()) {
                    Log::error("fetchAndSyncRemote: HTTP {$response->status()} при запросе к {$exchangerName}", [
                        'url' => $cfg['url']
                    ]);
                    continue;
                }

                $html = $response->body();
                if (empty($html) || stripos($html, 'wp-login') !== false) {
                    Log::warning("fetchAndSyncRemote: требуются новые куки для {$exchangerName}");
                    continue;
                }

                $crawler = new Crawler($html);

                $crawler->filter('.one_bids_wrap')->each(function (Crawler $node) use (&$records, $allowedStatuses, $exchangerName) {
                    try {
                        // 1) Статус заявки
                        $status = trim($node->filter('.onebid_item.item_bid_status .stname')->text(''));
                        if (! in_array($status, $allowedStatuses, true)) {
                            return;
                        }

                        // 2) Дата создания заявки
                        $rawCreated = trim($node->filter('.onebid_item.item_bid_createdate')->text(''));
                        try {
                            $createdAt = Carbon::createFromFormat('d.m.Y H:i:s', $rawCreated)
                                ->toDateTimeString();
                        } catch (\Exception $e) {
                            Log::warning("fetchAndSyncRemote: неверный формат даты «{$rawCreated}»", [
                                'error' => $e->getMessage()
                            ]);
                            $createdAt = now()->toDateTimeString();
                        }

                        // 3) Приход (sale_text), например «75000 RUB»
                        $sum1dcText = trim($node->filter('.onebid_item.item_bid_sum1dc')->text(''));
                        $saleText    = $sum1dcText;

                        // 4) Номер заявки (ID)
                        $rawId = trim($node->filter('.bids_label_txt[title^="ID"]')->text(''));
                        $id    = (int) preg_replace('/\D/u', '', $rawId);

                        // 5) Собираем в коллекцию «на upsert»
                        $records->push([
                            'exchanger'         => $exchangerName,
                            'app_id'            => $id,
                            'app_created_at'    => $createdAt,
                            'status'            => $status,
                            'sale_text'         => $saleText,
                            'sell_amount'       => null,
                            'sell_currency_id'  => null,
                            'buy_amount'        => null,
                            'buy_currency_id'   => null,
                            'expense_amount'    => null,
                            'expense_currency_id'=> null,
                            'merchant'          => null,
                            'order_id'          => null,
                            'user_id'           => null,
                            'created_at'        => now()->toDateTimeString(),
                            'updated_at'        => now()->toDateTimeString(),
                        ]);
                    } catch (\Exception $e) {
                        Log::error("fetchAndSyncRemote: ошибка парсинга карточки у {$exchangerName}", [
                            'error'  => $e->getMessage(),
                            'snippet'=> substr($node->html(), 0, 200),
                        ]);
                    }
                });
            } catch (\Exception $e) {
                Log::error("fetchAndSyncRemote: исключение при запросе/парсинге {$exchangerName}", [
                    'error' => $e->getMessage(),
                    'url'   => $cfg['url']
                ]);
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
                ['app_created_at', 'status', 'sale_text', 'updated_at']
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
        $pageNum = (int) $request->get('page', 1);
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
            ->whereIn('status', ['выполненная заявка','оплаченная заявка'])
            ->orderByDesc('app_created_at')
            ->paginate($perPage, ['*'], 'page', $pageNum);

        // Все валюты (для селектов модалки)
        $currencies = Currency::orderBy('code')->get();

        // Остальные блоки (Transfers, Payments, Purchase, SaleCrypt, History)
        $exchangers = Exchanger::orderBy('title')->get();
        $saleCrypts = SaleCrypt::orderBy('created_at','desc')->get();
        $transfers  = Transfer::orderBy('created_at','desc')->get();
        $payments   = Payment::orderBy('created_at','desc')->get();
        $purchases  = Purchase::orderBy('created_at','desc')->get();

        // История операций (поле currency_id)
        $histories = History::with('currency')
            ->orderBy('created_at','asc')
            ->paginate(100, ['*'], 'page', $pageNum);

        // Итог по видимой странице истории (для второго блока таблицы)
        $totals = [];
        foreach ($currencies as $c) {
            $totals[$c->id] = 0;
        }
        foreach ($histories->items() as $h) {
            if ($h->currency_id) {
                $totals[$h->currency_id] += $h->amount;
            }
        }

        return view('pages.main', compact(
            'apps',
            'currencies',
            'exchangers',
            'saleCrypts',
            'transfers',
            'payments',
            'purchases',
            'histories',
            'totals'
        ));
    }



    /**
     * AJAX: возвращает JSON с очередной порцией (20 штук) заявок для «Загрузить ещё».
     */
    public function apiApplications(Request $request)
    {
        $pageNum = (int) $request->get('page', 1);
        $perPage = 20;

        try {
            $this->fetchAndSyncRemote($pageNum);
        } catch (\Exception $e) {
            Log::error("apiApplications: ошибка синхронизации", [
                'error' => $e->getMessage()
            ]);
        }

        // **ВАЖНО**: здесь обязательно with(...), чтобы в JSON-ответе были вложенные sellCurrency, buyCurrency, expenseCurrency и user
        $apps = Application::with(['sellCurrency','buyCurrency','expenseCurrency','user'])
            ->whereIn('status', ['выполненная заявка','оплаченная заявка'])
            ->orderByDesc('app_created_at')
            ->paginate($perPage, ['*'], 'page', $pageNum);

        return response()->json([
            'data'     => $apps->items(),
            'has_more' => $apps->hasMorePages(),
        ]);
    }

    /**
     * AJAX: сохраняет правки одной заявки из модального окна.
     */

    public function update(Request $request, $id)
    {
        $request->validate([
            'sale_text'        => 'nullable|string|max:64',
            'sell_amount'      => 'nullable|numeric|min:0',
            'sell_currency'    => 'nullable|string|max:8',
            'buy_amount'       => 'nullable|numeric|min:0',
            'buy_currency'     => 'nullable|string|max:8',
            'expense_amount'   => 'nullable|numeric|min:0',
            'expense_currency' => 'nullable|string|max:8',
            'merchant'         => 'nullable|string|max:128',
            'order_id'         => 'nullable|string|max:128',
        ]);

        $app = Application::findOrFail($id);

        //
        // 1) Сохраняем sale_text (приход) — парсим «число + код валюты», если есть
        //
        $amount         = null;
        $saleCurrencyId = null;

        if ($app->sale_text) {
            $saleText = $app->sale_text;
            Log::info("Update: received sale_text='{$saleText}' for app_id={$app->id}");

            $parts     = explode(' ', $saleText, 2);
            $rawAmount = $parts[0] ?? null;
            $rawCode   = $parts[1] ?? null;

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
                $cur            = Currency::firstOrCreate(
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
            $curSell               = Currency::firstOrCreate(
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
            $curBuy             = Currency::firstOrCreate(
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
            $curExpense                 = Currency::firstOrCreate(
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
        $app->user_id  = auth()->id();
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
                'sourceable_id'   => $app->id,
                'amount'          => +$amount,
                'currency_id'     => $saleCurrencyId,
            ]);
            Log::info("History created for sale_text (income) +{$amount}, currency_id={$saleCurrencyId}");
        }

        // 6.2) «Продажа» (sell_amount → расход)
        if ($app->sell_amount !== null && $app->sell_currency_id !== null && $app->sell_amount > 0) {
            History::create([
                'sourceable_type' => Application::class,
                'sourceable_id'   => $app->id,
                'amount'          => -$app->sell_amount,
                'currency_id'     => $app->sell_currency_id,
            ]);
            Log::info("History created for sell_amount (expense) -{$app->sell_amount}, currency_id={$app->sell_currency_id}");
        }

        // 6.3) «Купля» (buy_amount → приход)
        if ($app->buy_amount !== null && $app->buy_currency_id !== null && $app->buy_amount > 0) {
            History::create([
                'sourceable_type' => Application::class,
                'sourceable_id'   => $app->id,
                'amount'          => +$app->buy_amount,
                'currency_id'     => $app->buy_currency_id,
            ]);
            Log::info("History created for buy_amount (income) +{$app->buy_amount}, currency_id={$app->buy_currency_id}");
        }

        // 6.4) «Расход» (expense_amount → расход)
        if ($app->expense_amount !== null && $app->expense_currency_id !== null && $app->expense_amount > 0) {
            History::create([
                'sourceable_type' => Application::class,
                'sourceable_id'   => $app->id,
                'amount'          => -$app->expense_amount,
                'currency_id'     => $app->expense_currency_id,
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
        // 1) Сначала собираем из истории суммы по каждой валюте
        //    Получим коллекцию вида [{currency_id: 1, total_amount: 123.45}, ...]
        $totalsByCurrency = History::select('currency_id', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('currency_id')
            ->get();

        // 2) Чтобы проще находить код валюты по её ID, вытянем плук [id => code]
        $currencyCodes = Currency::pluck('code', 'id');
        // Т. е. $currencyCodes[5] == 'BTC', $currencyCodes[3] == 'RUB' и т.д.

        $usdtTotal = 0.0;

        foreach ($totalsByCurrency as $row) {
            $currencyId   = $row->currency_id;
            $sumInCurrency = (float)$row->total_amount; // сумма (положительная или отрицательная) в этой валюте

            // Код валюты, например "BTC", "RUB", "USDT"
            if (! isset($currencyCodes[$currencyId])) {
                // Если вдруг нет такого кода – пропускаем
                Log::warning("usdtTotal: нет записи о валюте с ID={$currencyId} в таблице currencies");
                continue;
            }
            $code = strtoupper($currencyCodes[$currencyId]);

            // 2.1) Если валюта уже USDT, просто добавляем сумму напрямую
            if ($code === 'USDT') {
                $usdtTotal += $sumInCurrency;
                continue;
            }

            // 2.2) Иначе — делаем запрос на Heleket, чтобы узнать курс из $code в USDT
            try {
                // Крайне важно: здесь мы используем именно эндпоинт /v1/exchange-rate/{currency}/list
                $response = Http::timeout(5)->get("https://api.heleket.com/v1/exchange-rate/{$code}/list");

                if (! $response->successful()) {
                    Log::error("usdtTotal: HTTP {$response->status()} при запросе курса {$code}_USDT");
                    continue;
                }

                $json = $response->json();
                if (! isset($json['result']) || ! is_array($json['result'])) {
                    Log::error("usdtTotal: неожиданный формат ответа для {$code}: " . $response->body());
                    continue;
                }

                // Ищем внутри массива "result" объект с полем "to" == "USDT"
                $foundRate = null;
                foreach ($json['result'] as $entry) {
                    // entry выглядит как ["from"=>"RUB", "to"=>"USDT", "course"=>"0.01320000"], например
                    if (isset($entry['to']) && strtoupper($entry['to']) === 'USDT') {
                        $foundRate = (float)$entry['course'];
                        break;
                    }
                }

                if ($foundRate === null) {
                    Log::error("usdtTotal: в списке курсов для {$code} не найден пункт → USDT");
                    continue;
                }

                // Пересчитываем сумму в USDT:
                // если сумма negative (расход) — тогда сумма * rate будет тоже negative
                $usdtTotal += $sumInCurrency * $foundRate;
            }
            catch (\Throwable $e) {
                Log::error("usdtTotal: ошибка при запросе курса {$code}_USDT: " . $e->getMessage());
                continue;
            }
        }

        // Округлим итог до, скажем, 8 знаков
        $usdtTotal = round($usdtTotal, 8);

        return response()->json([
            'usdt_total' => $usdtTotal,
        ]);
    }
}
