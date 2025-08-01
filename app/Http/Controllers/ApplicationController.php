<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Currency;
use App\Models\Exchanger;
use App\Models\SiteCookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class ApplicationController extends Controller
{
    /**
     * Показывает страницу с таблицей заявок
     */
    public function index(Request $request)
    {
        // Все валюты (для селектов модалки редактирования)
        $currenciesForEdit = Currency::orderBy('code')->get();

        // Все заявки (для селектов в модальных окнах покупок и продаж)
        $applications = Application::orderBy('id', 'desc')->get();

        // Все обменники (для селектов в модальных окнах)
        $exchangers = \App\Models\Exchanger::orderBy('title')->get();

        return view('pages.applications', compact('currenciesForEdit', 'applications', 'exchangers'));
    }

        /**
     * Синхронизация данных с обменников
     */
    public function sync(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $this->fetchAndSyncRemote($page);

            return response()->json([
                'success' => true,
                'message' => 'Синхронизация выполнена успешно'
            ]);
        } catch (\Exception $e) {
            Log::error('ApplicationController::sync error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка синхронизации: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API для получения заявок (для AG-Grid)
     */
    public function getApplications(Request $request)
    {
        Log::info("ApplicationController::getApplications: начало обработки запроса", [
            'page' => $request->get('page', 1),
            'perPage' => $request->get('perPage', 50),
            'search' => $request->get('search', ''),
            'statusFilter' => $request->get('statusFilter', ''),
            'exchangerFilter' => $request->get('exchangerFilter', ''),
            'sync' => $request->get('sync', false)
        ]);

        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('perPage', 50);
            $search = $request->get('search', '');
            $statusFilter = $request->get('statusFilter', '');
            $exchangerFilter = $request->get('exchangerFilter', '');

            // Синхронизируем данные только при явном запросе
            $syncParam = $request->get('sync', false);
            Log::info("ApplicationController: проверка синхронизации", [
                'sync_param' => $syncParam,
                'sync_type' => gettype($syncParam),
                'page' => $page
            ]);

            if ($syncParam === 'true' || $syncParam === true) {
                try {
                    Log::info("ApplicationController: начинаем синхронизацию по запросу...", [
                        'page' => $page,
                        'perPage' => $perPage
                    ]);
                    $this->fetchAndSyncRemote($page);
                    Log::info("ApplicationController: синхронизация завершена успешно");
                } catch (\Exception $e) {
                    Log::error("ApplicationController: ошибка синхронизации", [
                        'error' => $e->getMessage(),
                        'page' => $page
                    ]);
                }
            } else {
                Log::info("ApplicationController: синхронизация не запрошена");
            }

            $query = Application::with(['user', 'sellCurrency', 'buyCurrency', 'expenseCurrency'])
                ->whereIn('status', ['выполненная заявка', 'оплаченная заявка', 'возврат']);

            // Применяем фильтры
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('app_id', 'like', "%{$search}%")
                      ->orWhere('merchant', 'like', "%{$search}%")
                      ->orWhere('order_id', 'like', "%{$search}%");
                });
            }

            if ($statusFilter) {
                $query->where('status', $statusFilter);
            }

            if ($exchangerFilter) {
                $query->where('exchanger', $exchangerFilter);
            }

            $applications = $query->orderByDesc('app_created_at')
                ->paginate($perPage, ['*'], 'page', $page);

            Log::info("ApplicationController::getApplications: результат запроса", [
                'total_records' => $applications->total(),
                'current_page' => $applications->currentPage(),
                'per_page' => $applications->perPage(),
                'last_page' => $applications->lastPage(),
                'has_more_pages' => $applications->hasMorePages(),
                'items_count' => count($applications->items())
            ]);

            return response()->json([
                'data' => $applications->items(),
                'total' => $applications->total(),
                'perPage' => $applications->perPage(),
                'currentPage' => $applications->currentPage(),
                'lastPage' => $applications->lastPage(),
                'hasMorePages' => $applications->hasMorePages(),
            ]);
        } catch (\Exception $e) {
            Log::error('ApplicationController::getApplications error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Ошибка загрузки данных',
                'data' => [],
                'total' => 0,
                'perPage' => 50,
                'currentPage' => 1,
                'lastPage' => 1,
                'hasMorePages' => false,
            ], 500);
        }
    }

    /**
     * Получение данных заявки для редактирования
     */
    public function edit($id)
    {
        Log::info("ApplicationController::edit: запрос данных заявки", ['id' => $id]);

        try {
            $application = Application::with(['sellCurrency', 'buyCurrency', 'expenseCurrency'])
                ->findOrFail($id);

            Log::info("ApplicationController::edit: заявка найдена", [
                'id' => $application->id,
                'app_id' => $application->app_id
            ]);

            // Разбираем sale_text на сумму и валюту
            $saleAmount = null;
            $saleCurrency = null;

            if ($application->sale_text) {
                $parts = explode(' ', trim($application->sale_text), 2);
                if (count($parts) >= 2) {
                    $saleAmount = is_numeric($parts[0]) ? floatval($parts[0]) : null;
                    $saleCurrency = $parts[1];
                }
            }

            return response()->json([
                'id' => $application->id,
                'app_id' => $application->app_id,
                'sale_amount' => $saleAmount,
                'sale_currency' => $saleCurrency,
                'sale_text' => $application->sale_text,
                'sell_amount' => $application->sell_amount,
                'sell_currency' => $application->sellCurrency?->code,
                'buy_amount' => $application->buy_amount,
                'buy_currency' => $application->buyCurrency?->code,
                'expense_amount' => $application->expense_amount,
                'expense_currency' => $application->expenseCurrency?->code,
                'merchant' => $application->merchant,
                'order_id' => $application->order_id,
            ]);
        } catch (\Exception $e) {
            Log::error("ApplicationController::edit: ошибка получения данных заявки", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Заявка не найдена'
            ], 404);
        }
    }

    /**
     * Обновление заявки
     */
    public function update(Request $request, $id)
    {
        Log::info("ApplicationController::update: ЗАПРОС ДОШЕЛ ДО КОНТРОЛЛЕРА", [
            'id' => $id,
            'method' => $request->method(),
            'url' => $request->url(),
            'headers' => $request->headers->all(),
            'data' => $request->all(),
            'timestamp' => now()->toDateTimeString()
        ]);

        try {
            $request->validate([
                'sale_amount' => 'nullable|numeric|min:0',
                'sale_currency' => 'nullable|string|max:8',
                'sell_amount' => 'nullable|numeric|min:0',
                'sell_currency' => 'nullable|string|max:8',
                'buy_amount' => 'nullable|numeric|min:0',
                'buy_currency' => 'nullable|string|max:8',
                'expense_amount' => 'nullable|numeric|min:0',
                'expense_currency' => 'nullable|string|max:8',
                'merchant' => 'nullable|string|max:128',
                'order_id' => 'nullable|string|max:128',
            ]);

            Log::info("ApplicationController::update: валидация прошла успешно");

            $app = Application::findOrFail($id);
            Log::info("ApplicationController::update: заявка найдена", ['app_id' => $app->app_id]);

        // Обновляем поля
        // Обрабатываем sale_text (приход)
        if ($request->has('sale_amount') || $request->has('sale_currency')) {
            $saleAmount = $request->get('sale_amount');
            $saleCurrency = $request->get('sale_currency');

            if ($saleAmount !== null && $saleCurrency) {
                $app->sale_text = $saleAmount . ' ' . $saleCurrency;
                Log::info("ApplicationController::update: обновлен sale_text", ['sale_text' => $app->sale_text]);
            } else {
                $app->sale_text = null;
                Log::info("ApplicationController::update: sale_text очищен");
            }
        }

        if ($request->has('sell_amount')) {
            $app->sell_amount = $request->sell_amount;
            if ($request->sell_currency) {
                $currency = Currency::where('code', $request->sell_currency)->first();
                $app->sell_currency_id = $currency?->id;
            } else {
                $app->sell_currency_id = null;
            }
        }

        if ($request->has('buy_amount')) {
            $app->buy_amount = $request->buy_amount;
            if ($request->buy_currency) {
                $currency = Currency::where('code', $request->buy_currency)->first();
                $app->buy_currency_id = $currency?->id;
            } else {
                $app->buy_currency_id = null;
            }
        }

        if ($request->has('expense_amount')) {
            $app->expense_amount = $request->expense_amount;
            if ($request->expense_currency) {
                $currency = Currency::where('code', $request->expense_currency)->first();
                $app->expense_currency_id = $currency?->id;
            } else {
                $app->expense_currency_id = null;
            }
        }

        if ($request->has('merchant')) {
            $app->merchant = $request->merchant;
        }

        if ($request->has('order_id')) {
            $app->order_id = $request->order_id;
        }

        // Сохраняем, кто редактировал заявку
        $app->user_id = auth()->id();

        $app->save();

        // Загружаем связи с валютами для корректной обработки истории
        $app->load(['sellCurrency', 'buyCurrency', 'expenseCurrency']);

        // Создаем/обновляем записи истории
        $applicationService = app(\App\Services\ApplicationService::class);
        $currencyData = [
            'sell_amount' => $app->sell_amount,
            'sell_currency' => $app->sellCurrency?->code,
            'buy_amount' => $app->buy_amount,
            'buy_currency' => $app->buyCurrency?->code,
            'expense_amount' => $app->expense_amount,
            'expense_currency' => $app->expenseCurrency?->code,
        ];
        $applicationService->processCurrencyData($app, $currencyData);

        Log::info("ApplicationController::update: заявка успешно обновлена", [
            'id' => $id,
            'app_id' => $app->app_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Заявка обновлена успешно'
        ]);
        } catch (\Exception $e) {
            Log::error("ApplicationController::update: ошибка обновления заявки", [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении заявки: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listForSelect()
    {
        \Log::info('ApplicationController::listForSelect вызван', [
            'user' => auth()->check() ? auth()->user()->name : 'not authenticated',
            'session_id' => session()->getId(),
            'request_headers' => request()->headers->all()
        ]);

        $apps = Application::orderBy('id', 'desc')->get(['id', 'app_id', 'order_id', 'merchant']);
        return response()->json($apps);
    }

    public function listForSelectTemp()
    {
        \Log::info('ApplicationController::listForSelectTemp вызван', [
            'user' => auth()->check() ? auth()->user()->name : 'not authenticated',
            'session_id' => session()->getId(),
            'request_headers' => request()->headers->all()
        ]);

        // Проверяем аутентификацию внутри метода
        if (!auth()->check()) {
            \Log::warning('ApplicationController::listForSelectTemp - пользователь не аутентифицирован');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        \Log::info('ApplicationController::listForSelectTemp - пользователь аутентифицирован, загружаем данные');

        $apps = Application::orderBy('id', 'desc')->get(['id', 'app_id']);

        \Log::info('ApplicationController::listForSelectTemp - данные загружены', [
            'count' => $apps->count(),
            'first_app' => $apps->first(),
            'last_app' => $apps->last()
        ]);

        return response()->json($apps);
    }

    /**
     * Показать детали заявки
     */
    public function show($id)
    {
        try {
            $application = Application::with([
                'purchases.receivedCurrency',
                'purchases.saleCurrency',
                'saleCrypts.fixedCurrency',
                'saleCrypts.saleCurrency'
            ])->findOrFail($id);

            // Подготавливаем данные для ответа
            $data = [
                'id' => $application->id,
                'app_id' => $application->app_id,
                'app_created_at' => $application->app_created_at,
                'exchanger' => $application->exchanger,
                'status' => $application->status,
                'merchant' => $application->merchant,
                'order_id' => $application->order_id,

                // Основные суммы
                'sell_amount' => $application->sell_amount,
                'sell_currency_code' => $application->sellCurrency ? $application->sellCurrency->code : null,
                'buy_amount' => $application->buy_amount,
                'buy_currency_code' => $application->buyCurrency ? $application->buyCurrency->code : null,
                'expense_amount' => $application->expense_amount,
                'expense_currency_code' => $application->expenseCurrency ? $application->expenseCurrency->code : null,

                // Связанные покупки
                'related_purchases' => $application->purchases->map(function($purchase) {
                    return [
                        'id' => $purchase->id,
                        'received_amount' => $purchase->received_amount,
                        'received_currency_code' => $purchase->receivedCurrency ? $purchase->receivedCurrency->code : null,
                        'sale_amount' => $purchase->sale_amount,
                        'sale_currency_code' => $purchase->saleCurrency ? $purchase->saleCurrency->code : null,
                    ];
                }),

                // Связанные продажи крипты
                'related_sales' => $application->saleCrypts->map(function($sale) {
                    return [
                        'id' => $sale->id,
                        'fixed_amount' => $sale->fixed_amount,
                        'fixed_currency_code' => $sale->fixedCurrency ? $sale->fixedCurrency->code : null,
                        'sale_amount' => $sale->sale_amount,
                        'sale_currency_code' => $sale->saleCurrency ? $sale->saleCurrency->code : null,
                    ];
                }),
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('ApplicationController::show error', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Заявка не найдена'
            ], 404);
        }
    }

    /**
     * Получить и синхронизировать данные с внешних источников
     */
    private function fetchAndSyncRemote(int $pageNum): void
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

        // Последовательные запросы (более надежно)
        $responses = [];
        $hasErrors = false;
        foreach ($exchangers as $exchangerName => $cfg) {
            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Cookie' => $cfg['cookies'],
                    'User-Agent' => 'Mozilla/5.0',
                ])->timeout(15)->get($cfg['url']); // Увеличили timeout до 15 секунд

                $responses[$exchangerName] = $response;
            } catch (\Exception $e) {
                Log::error("fetchAndSyncRemote: ошибка запроса к {$exchangerName}", [
                    'error' => $e->getMessage()
                ]);
                $hasErrors = true;
                continue;
            }
        }

        // Если все запросы завершились с ошибкой, выходим
        if ($hasErrors && empty($responses)) {
            Log::warning("fetchAndSyncRemote: все запросы завершились с ошибкой");
            return;
        }

        foreach ($responses as $exchangerName => $response) {
            try {
                if (!$response->successful()) {
                    Log::error("fetchAndSyncRemote: HTTP {$response->status()} при запросе к {$exchangerName}");
                    continue;
                }

                $html = $response->body();
                if (empty($html) || stripos($html, 'wp-login') !== false) {
                    Log::warning("fetchAndSyncRemote: требуются новые куки для {$exchangerName}");
                    continue;
                }

                $crawler = new \Symfony\Component\DomCrawler\Crawler($html);

                $crawler->filter('.one_bids_wrap')->each(function (Crawler $node) use (&$records, $allowedStatuses, $exchangerName) {
                    try {
                        // 1) Статус заявки
                        $status = trim($node->filter('.onebid_item.item_bid_status .stname')->text(''));
                        if (!in_array($status, $allowedStatuses, true)) {
                            return;
                        }

                        // 2) Дата создания заявки
                        $rawCreated = trim($node->filter('.onebid_item.item_bid_createdate')->text(''));
                        try {
                            $createdAt = \Carbon\Carbon::createFromFormat('d.m.Y H:i:s', $rawCreated)
                                ->toDateTimeString();
                        } catch (\Exception $e) {
                            $createdAt = now()->toDateTimeString();
                        }

                        // 3) Приход (sale_text), например «75000 RUB»
                        $sum1dcText = trim($node->filter('.onebid_item.item_bid_sum1dc')->text(''));
                        $saleText = $sum1dcText;

                        // 4) Номер заявки (ID)
                        $rawId = trim($node->filter('.bids_label_txt[title^="ID"]')->text(''));
                        $id = (int)preg_replace('/\D/u', '', $rawId);

                        // 5) Собираем в коллекцию «на upsert»
                        $records->push([
                            'exchanger' => $exchangerName,
                            'app_id' => $id,
                            'app_created_at' => $createdAt,
                            'status' => $status,
                            'sale_text' => $saleText,
                            'sell_amount' => null,
                            'sell_currency_id' => null,
                            'buy_amount' => null,
                            'buy_currency_id' => null,
                            'expense_amount' => null,
                            'expense_currency_id' => null,
                            'merchant' => null,
                            'order_id' => null,
                            'user_id' => null,
                            'created_at' => now()->toDateTimeString(),
                            'updated_at' => now()->toDateTimeString(),
                        ]);
                    } catch (\Exception $e) {
                        // Убираем логирование для ускорения
                    }
                });
            } catch (\Exception $e) {
                Log::error("fetchAndSyncRemote: исключение при запросе/парсинге {$exchangerName}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        if ($records->isEmpty()) {
            Log::info("fetchAndSyncRemote: не найдено записей для обновления");
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
            Log::info("fetchAndSyncRemote: успешно обновлено записей в БД", [
                'count' => count($toUpsert)
            ]);
        } catch (\Exception $e) {
            Log::critical('fetchAndSyncRemote: ошибка при upsert в БД', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
