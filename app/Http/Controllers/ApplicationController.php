<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Currency;
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

        return view('pages.applications', compact('currenciesForEdit'));
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

            return response()->json([
                'id' => $application->id,
                'app_id' => $application->app_id,
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

    /**
     * Загрузка и синхронизация заявок с обменников (оптимизированная версия)
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
