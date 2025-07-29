<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Exchanger;
use App\Models\History;
use App\Models\LoginLog;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\SaleCrypt;
use App\Models\Transfer;
use App\Models\UpdateLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function viewUserLogs()
    {
        $loginLogs = LoginLog::paginate(10);
        foreach ($loginLogs as $loginLog) {
            if ($loginLog->user->role === 'admin') {
                $loginLog->user->role = 'Админ';
            } else {
                $loginLog->user->role = 'Пользователь';
            }
        }
        return view('admin.user-logs', compact('loginLogs'));
    }

    public function updateBlocked($id)
    {
        $user = User::findOrFail($id);
        $authId = auth()->id();
        if ($authId !== $id) {
            if ($user->blocked === 'block') {
                $user->blocked = 'none';
            } else {
                $user->blocked = 'block';
            }
            $user->save();
        }

        return redirect()->back();
    }

    public function updateStatus($id)
    {
        $user = User::findOrFail($id);
        $authId = auth()->id();
        if ($authId !== $id) {
            if ($user->role === 'admin') {
                $user->role = 'user';
            } else {
                $user->role = 'admin';
            }
            $user->save();
        }
        return redirect()->back();
    }

    public function viewRegisterUser()
    {
        return view('pages.register');
    }
    /**
     * Сохраняет новый Payment и пишет в history.
     */
    public function storePayment(Request $request)
    {
        $validated = $request->validate([
            'exchanger_id'     => 'required|integer|exists:exchangers,id',
            'sell_amount'      => 'required|numeric|min:0',
            'sell_currency_id' => 'required|integer|exists:currencies,id',
            'comment'          => 'nullable|string|max:255',
        ]);

        $payment = Payment::create(array_merge($validated, [
            'user_id' => auth()->id(),
        ]));

        // History
        History::where('sourceable_type', Payment::class)
            ->where('sourceable_id', $payment->id)
            ->delete();

        if ($payment->sell_amount > 0) {
            History::create([
                'sourceable_type' => Payment::class,
                'sourceable_id'   => $payment->id,
                'amount'          => -$payment->sell_amount,
                'currency_id'     => $payment->sell_currency_id,
            ]);
        }

        // Log
        UpdateLog::create([
            'user_id'          => auth()->id(),
            'sourceable_type' => Payment::class,
            'sourceable_id'   => $payment->id,
            'update'          => json_encode($validated, JSON_UNESCAPED_UNICODE),
        ]);

        return response()->json([
            'message' => 'Payment успешно сохранён',
            'payment' => $payment,
        ], 201);
    }

    /**
     * Сохраняет новый Transfer и пишет в history.
     */
    public function storeTransfer(Request $request)
    {
        $validated = $request->validate([
            'exchanger_from_id' => 'required|integer|exists:exchangers,id',
            'exchanger_to_id'   => ['required','integer','exists:exchangers,id',
                Rule::notIn([$request->input('exchanger_from_id')])],
            'amount'            => 'required|numeric|min:0',
            'amount_id'         => 'required|integer|exists:currencies,id',
            'commission'        => 'nullable|numeric|min:0',
            'commission_id'     => 'nullable|integer|exists:currencies,id',
        ]);

        $transfer = Transfer::create(array_merge($validated, [
            'user_id' => auth()->id(),
        ]));

        // History
        History::where('sourceable_type', Transfer::class)
            ->where('sourceable_id', $transfer->id)
            ->delete();

        if (! is_null($transfer->commission) && $transfer->commission > 0) {
            History::create([
                'sourceable_type' => Transfer::class,
                'sourceable_id'   => $transfer->id,
                'amount'          => -$transfer->commission,
                'currency_id'     => $transfer->commission_id,
            ]);
        }

        // Log
        UpdateLog::create([
            'user_id'          => auth()->id(),
            'sourceable_type' => Transfer::class,
            'sourceable_id'   => $transfer->id,
            'update'          => json_encode($validated, JSON_UNESCAPED_UNICODE),
        ]);

        return response()->json([
            'message'  => 'Transfer успешно сохранён',
            'transfer' => $transfer,
        ], 201);
    }

    /**
     * Сохраняет новый SaleCrypt и пишет в history.
     */
    public function storeSaleCrypt(Request $request)
    {
        $validated = $request->validate([
            'exchanger_id'      => 'required|integer|exists:exchangers,id',
            'sale_amount'       => 'required|numeric|min:0',
            'sale_currency_id'  => 'required|integer|exists:currencies,id',
            'fixed_amount'      => 'required|numeric|min:0',
            'fixed_currency_id' => 'required|integer|exists:currencies,id',
            'application_id'       => 'nullable|integer|exists:applications,id',
        ]);

//        dd($validated);

        $saleCrypt = SaleCrypt::create(array_merge($validated, [
            'user_id' => auth()->id(),
        ]));

        // History
        History::where('sourceable_type', SaleCrypt::class)
            ->where('sourceable_id', $saleCrypt->id)
            ->delete();

        History::create([
            'sourceable_type' => SaleCrypt::class,
            'sourceable_id'   => $saleCrypt->id,
            'amount'          => -$saleCrypt->sale_amount,
            'currency_id'     => $saleCrypt->sale_currency_id,
        ]);

        History::create([
            'sourceable_type' => SaleCrypt::class,
            'sourceable_id'   => $saleCrypt->id,
            'amount'          => +$saleCrypt->fixed_amount,
            'currency_id'     => $saleCrypt->fixed_currency_id,
        ]);

        // Log
        UpdateLog::create([
            'user_id'          => auth()->id(),
            'sourceable_type' => SaleCrypt::class,
            'sourceable_id'   => $saleCrypt->id,
            'update'          => json_encode($validated, JSON_UNESCAPED_UNICODE),
        ]);

        return response()->json([
            'message'   => 'SaleCrypt успешно сохранён',
            'saleCrypt' => $saleCrypt,
        ], 201);
    }

    /**
     * Сохраняет новую запись Purchase и пишет в history.
     */
    public function storePurchase(Request $request)
    {
        $validated = $request->validate([
            'exchanger_id'         => 'nullable|exists:exchangers,id',
            'sale_amount'          => 'nullable|numeric|min:0',
            'sale_currency_id'     => 'nullable|exists:currencies,id',
            'received_amount'      => 'nullable|numeric|min:0',
            'received_currency_id' => 'nullable|exists:currencies,id',
            'application_id'       => 'nullable|integer|exists:applications,id',
        ]);

        // 1) Создаём Purchase
        $purchase = Purchase::create($validated);

        // 2) History
        History::where('sourceable_type', Purchase::class)
            ->where('sourceable_id', $purchase->id)
            ->delete();

        if ($purchase->received_amount > 0) {
            History::create([
                'sourceable_type' => Purchase::class,
                'sourceable_id'   => $purchase->id,
                'amount'          => +$purchase->received_amount,
                'currency_id'     => $purchase->received_currency_id,
            ]);
        }

        if ($purchase->sale_amount > 0) {
            History::create([
                'sourceable_type' => Purchase::class,
                'sourceable_id'   => $purchase->id,
                'amount'          => -$purchase->sale_amount,
                'currency_id'     => $purchase->sale_currency_id,
            ]);
        }

        // 3) Log изменений
        UpdateLog::create([
            'user_id'         => auth()->id(),
            'sourceable_type'=> Purchase::class,
            'sourceable_id'  => $purchase->id,
            'update'         => json_encode($validated, JSON_UNESCAPED_UNICODE),
        ]);

        return response()->json([
            'message'  => 'Purchase создана успешно',
            'purchase' => $purchase,
        ], 201);
    }


    /**
     * Показывает форму добавления новой платформы.
     */
    public function createExchanger()
    {
        return view('admin.platform-create');
    }

    /**
     * Сохраняет новую платформу в БД.
     */
    public function storeExchanger(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        Exchanger::create([
            'title' => $request->input('title'),
        ]);

        return redirect()
            ->route('exchangers.create')
            ->with('success', 'Новая платформа успешно добавлена');
    }


    public function viewExchangers()
    {
        $exchangers = Exchanger::all();
        return view('admin.list.exchangers', compact('exchangers'));
    }

    public function viewCurrencies()
    {
        $currencies = Currency::all();
        return view('admin.list.currencies', compact('currencies'));
    }

    /**
     * Показывает страницу с логами обновлений.
     */
    public function viewUpdateLogs(Request $request)
    {
        // 1) Логи обновлений
        $logs = UpdateLog::with(['user', 'sourceable'])
            ->orderByDesc('created_at')
            ->paginate(20);

        // 2) Справочники валют и обменников
        //    currencyCodes[id] = code, exchangerNames[id] = title
        $currencyCodes   = Currency::pluck('code', 'id');
        $exchangerNames  = Exchanger::pluck('title', 'id');

        return view('admin.update-logs', compact(
            'logs', 'currencyCodes', 'exchangerNames'
        ));
    }

    /**
     * Страница балансов обменников (реальные балансы через API)
     */
    public function exchangerBalancesPage()
    {
        $providers = ['heleket' => 'Heleket', 'rapira' => 'Rapira', 'bybit' => 'Bybit'];
        $exchangers = ['obama' => 'Obama', 'ural' => 'Ural', 'main' => 'Main'];
        return view('admin.exchanger-balances', compact('providers', 'exchangers'));
    }

    /**
     * Отправить балансы обменников в Telegram
     */
    public function sendBalancesToTelegram(Request $request)
    {
        try {
            $provider = $request->query('provider');
            $exchanger = $request->query('exchanger');

            // Если параметры не указаны, отправляем все балансы
            if (empty($provider) && empty($exchanger)) {
                $command = "telegram:send-balances";
            } else {
                // Запускаем команду через Artisan
                $command = "telegram:send-balances";
                if ($provider && $provider !== 'all') {
                    $command .= " --provider={$provider}";
                }
                if ($exchanger && $exchanger !== 'all') {
                    $command .= " --exchanger={$exchanger}";
                }
            }

            $exitCode = \Artisan::call($command);

            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => empty($provider) && empty($exchanger)
                        ? 'Все балансы успешно отправлены в Telegram'
                        : 'Балансы успешно отправлены в Telegram'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка отправки балансов'
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Ошибка отправки балансов в Telegram', [
                'error' => $e->getMessage(),
                'provider' => $provider ?? 'all',
                'exchanger' => $exchanger ?? 'all'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Отправить балансы Heleket в Telegram
     */
    public function sendHeleketBalancesToTelegram()
    {
        try {
            $exitCode = \Artisan::call('telegram:send-balances', ['--provider' => 'heleket']);

            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Балансы Heleket успешно отправлены в Telegram'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка отправки балансов Heleket'
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Ошибка отправки балансов Heleket в Telegram', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Отправить балансы Rapira в Telegram
     */
    public function sendRapiraBalancesToTelegram()
    {
        try {
            $exitCode = \Artisan::call('telegram:send-balances', ['--provider' => 'rapira']);

            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Балансы Rapira успешно отправлены в Telegram'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка отправки балансов Rapira'
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Ошибка отправки балансов Rapira в Telegram', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Отправить общий итог в Telegram
     */
    public function sendTotalBalanceToTelegram()
    {
        try {
            $exitCode = \Artisan::call('telegram:send-total');

            if ($exitCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Общий итог успешно отправлен в Telegram'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка отправки общего итога'
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Ошибка отправки общего итога в Telegram', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Отправить все сообщения последовательно: Heleket -> Rapira
     */
    public function sendAllMessagesSequentially()
    {
        try {
            $results = [];
            $success = true;

            // 1. Отправляем Heleket
            \Log::info('Отправляем Heleket...');
            $heleketExitCode = \Artisan::call('telegram:send-balances', ['--provider' => 'heleket']);
            $results['heleket'] = $heleketExitCode === 0 ? 'success' : 'error';
            if ($heleketExitCode !== 0) $success = false;

            // 2. Отправляем Rapira
            \Log::info('Отправляем Rapira...');
            $rapiraExitCode = \Artisan::call('telegram:send-balances', ['--provider' => 'rapira']);
            $results['rapira'] = $rapiraExitCode === 0 ? 'success' : 'error';
            if ($rapiraExitCode !== 0) $success = false;

            // 3. Отправляем Bybit
            \Log::info('Отправляем Bybit...');
            $bybitExitCode = \Artisan::call('telegram:send-balances', ['--provider' => 'bybit']);
            $results['bybit'] = $bybitExitCode === 0 ? 'success' : 'error';
            if ($bybitExitCode !== 0) $success = false;

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Сообщения Heleket, Rapira и Bybit успешно отправлены в Telegram',
                    'results' => $results
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка отправки некоторых сообщений',
                    'results' => $results
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Ошибка отправки сообщений в Telegram', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage()
            ], 500);
        }
    }

    public function dashboardPage()
    {
        return view('admin.dashboard');
    }

    public function dashboardStats(Request $request)
    {
        // Параметры фильтра
        $interval = $request->query('interval', 'day'); // day|month|year
        $start = $request->query('start_date') ? \Carbon\Carbon::parse($request->query('start_date')) : now()->subDays(30)->startOfDay();
        $end = $request->query('end_date') ? \Carbon\Carbon::parse($request->query('end_date')) : now()->endOfDay();

        // 1. Ключевые метрики
        $users = \App\Models\User::count();
        $apps = \App\Models\Application::count();
        $ops = \App\Models\Payment::count() + \App\Models\SaleCrypt::count() + \App\Models\Purchase::count() + \App\Models\Transfer::count();

        // 2. Оборот USDT (по History, currency_id = USDT)
        $usdtCurrency = \App\Models\Currency::where('code', 'USDT')->first();
        $usdt = 0;
        if ($usdtCurrency) {
            $usdt = \App\Models\History::where('currency_id', $usdtCurrency->id)->sum('amount');
        }

        // 3. График по выбранному периоду
        $periods = collect();
        if ($interval === 'day') {
            $periods = collect(\Carbon\CarbonPeriod::create($start, '1 day', $end))->map(fn($d)=>$d->format('Y-m-d'));
        } elseif ($interval === 'month') {
            $periods = collect();
            $cur = $start->copy()->startOfMonth();
            while ($cur <= $end) {
                $periods->push($cur->format('Y-m'));
                $cur->addMonth();
            }
        } elseif ($interval === 'year') {
            $periods = collect();
            $cur = $start->copy()->startOfYear();
            while ($cur <= $end) {
                $periods->push($cur->format('Y'));
                $cur->addYear();
            }
        }

        // Заявки
        $appsByPeriod = $periods->mapWithKeys(function($p) use ($interval) {
            $q = \App\Models\Application::query();
            if ($interval === 'day') {
                $q->whereDate('app_created_at', $p);
            } elseif ($interval === 'month') {
                $q->whereYear('app_created_at', substr($p,0,4))->whereMonth('app_created_at', substr($p,5,2));
            } elseif ($interval === 'year') {
                $q->whereYear('app_created_at', $p);
            }
            return [$p => $q->count()];
        });
        // USDT оборот
        $usdtByPeriod = $periods->mapWithKeys(function($p) use ($interval, $usdtCurrency) {
            if (!$usdtCurrency) return [$p => 0];
            $q = \App\Models\History::where('currency_id', $usdtCurrency->id);
            if ($interval === 'day') {
                $q->whereDate('created_at', $p);
            } elseif ($interval === 'month') {
                $q->whereYear('created_at', substr($p,0,4))->whereMonth('created_at', substr($p,5,2));
            } elseif ($interval === 'year') {
                $q->whereYear('created_at', $p);
            }
            return [$p => $q->sum('amount')];
        });
        // Операции по периодам
        $paymentsByPeriod = $periods->mapWithKeys(function($p) use ($interval) {
            $q = \App\Models\Payment::query();
            if ($interval === 'day') {
                $q->whereDate('created_at', $p);
            } elseif ($interval === 'month') {
                $q->whereYear('created_at', substr($p,0,4))->whereMonth('created_at', substr($p,5,2));
            } elseif ($interval === 'year') {
                $q->whereYear('created_at', $p);
            }
            return [$p => $q->count()];
        });
        $transfersByPeriod = $periods->mapWithKeys(function($p) use ($interval) {
            $q = \App\Models\Transfer::query();
            if ($interval === 'day') {
                $q->whereDate('created_at', $p);
            } elseif ($interval === 'month') {
                $q->whereYear('created_at', substr($p,0,4))->whereMonth('created_at', substr($p,5,2));
            } elseif ($interval === 'year') {
                $q->whereYear('created_at', $p);
            }
            return [$p => $q->count()];
        });
        $purchasesByPeriod = $periods->mapWithKeys(function($p) use ($interval) {
            $q = \App\Models\Purchase::query();
            if ($interval === 'day') {
                $q->whereDate('created_at', $p);
            } elseif ($interval === 'month') {
                $q->whereYear('created_at', substr($p,0,4))->whereMonth('created_at', substr($p,5,2));
            } elseif ($interval === 'year') {
                $q->whereYear('created_at', $p);
            }
            return [$p => $q->count()];
        });
        $salecryptsByPeriod = $periods->mapWithKeys(function($p) use ($interval) {
            $q = \App\Models\SaleCrypt::query();
            if ($interval === 'day') {
                $q->whereDate('created_at', $p);
            } elseif ($interval === 'month') {
                $q->whereYear('created_at', substr($p,0,4))->whereMonth('created_at', substr($p,5,2));
            } elseif ($interval === 'year') {
                $q->whereYear('created_at', $p);
            }
            return [$p => $q->count()];
        });

        // 4. ТОП валют по обороту (History)
        $topCurrencies = \App\Models\History::select('currency_id', \DB::raw('SUM(amount) as total'))
            ->groupBy('currency_id')
            ->orderByDesc('total')
            ->with('currency')
            ->take(5)
            ->get()
            ->map(function($row) {
                return [
                    'code' => $row->currency->code ?? '',
                    'name' => $row->currency->name ?? '',
                    'amount' => round($row->total, 2),
                ];
            });

        // 5. ТОП обменников по обороту (History + Exchanger)
        $topExchangers = \App\Models\History::select('sourceable_id', \DB::raw('SUM(amount) as total'))
            ->where('sourceable_type', 'App\\Models\\Application')
            ->groupBy('sourceable_id')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(function($row) {
                $app = \App\Models\Application::find($row->sourceable_id);
                return [
                    'name' => $app->exchanger ?? '',
                    'amount' => round($row->total, 2),
                ];
            });

        // 6. Детализация по операциям
        $operations = [
            'payments' => [
                'count' => \App\Models\Payment::count(),
                'sum' => \App\Models\Payment::sum('sell_amount'),
            ],
            'transfers' => [
                'count' => \App\Models\Transfer::count(),
                'sum' => \App\Models\Transfer::sum('amount'),
            ],
            'purchases' => [
                'count' => \App\Models\Purchase::count(),
                'sum' => \App\Models\Purchase::sum('sale_amount'),
            ],
            'salecrypts' => [
                'count' => \App\Models\SaleCrypt::count(),
                'sum' => \App\Models\SaleCrypt::sum('sale_amount'),
            ],
        ];

        return response()->json([
            'users' => $users,
            'apps' => $apps,
            'ops' => $ops,
            'usdt' => round($usdt, 2),
            'chart' => [
                'labels' => $periods->map(function($p) use ($interval) {
                    if ($interval === 'day') return date('d.m', strtotime($p));
                    if ($interval === 'month') return date('M Y', strtotime($p.'-01'));
                    if ($interval === 'year') return $p;
                })->toArray(),
                'apps' => $appsByPeriod->values()->toArray(),
                'usdt' => $usdtByPeriod->values()->toArray(),
            ],
            'operations_chart' => [
                'labels' => $periods->map(function($p) use ($interval) {
                    if ($interval === 'day') return date('d.m', strtotime($p));
                    if ($interval === 'month') return date('M Y', strtotime($p.'-01'));
                    if ($interval === 'year') return $p;
                })->toArray(),
                'payments' => $paymentsByPeriod->values()->toArray(),
                'transfers' => $transfersByPeriod->values()->toArray(),
                'purchases' => $purchasesByPeriod->values()->toArray(),
                'salecrypts' => $salecryptsByPeriod->values()->toArray(),
            ],
            'topCurrencies' => $topCurrencies,
            'topExchangers' => $topExchangers,
            'operations' => $operations,
        ]);
    }

    public function bybitCandles(Request $request)
    {
        $category = $request->query('category', 'spot');
        $symbol = $request->query('symbol', 'BTCUSDT');
        $interval = $request->query('interval', '1h');
        $limit = $request->query('limit', 100);
        $params = [
            'category' => $category,
            'symbol' => $symbol,
            'interval' => $interval,
            'limit' => $limit,
        ];
        if ($request->has('start')) {
            $params['start'] = $request->query('start');
        }
        if ($request->has('end')) {
            $params['end'] = $request->query('end');
        }
        // Логируем все параметры и итоговый массив
        \Log::info('bybitCandles: входящие параметры', [
            'category' => $category,
            'symbol' => $symbol,
            'interval' => $interval,
            'limit' => $limit,
            'start' => $request->query('start'),
            'end' => $request->query('end'),
            'params' => $params
        ]);
        if ($category === 'spot') {
            $url = 'https://api.bybit.com/v5/market/kline';
            \Log::info('bybitCandles: URL Bybit', ['url' => $url, 'params' => $params]);
            $resp = \Http::timeout(10)->get($url, $params);
            $data = $resp->json();
            \Log::info('bybitCandles: ответ Bybit', ['data' => $data]);
            $list = data_get($data, 'result.list', []);
            if (empty($list)) {
                $binanceIntervals = [
                    '1m'=>'1m','5m'=>'5m','15m'=>'15m','1h'=>'1h','4h'=>'4h','1d'=>'1d'
                ];
                $binanceInt = $binanceIntervals[$interval] ?? '1h';
                $binanceUrl = 'https://api.binance.com/api/v3/klines';
                $binanceParams = [
                    'symbol' => $symbol,
                    'interval' => $binanceInt,
                    'limit' => $limit,
                ];
                if (isset($params['start'])) $binanceParams['startTime'] = $params['start'];
                if (isset($params['end'])) $binanceParams['endTime'] = $params['end'];
                \Log::info('bybitCandles: URL Binance', ['url' => $binanceUrl, 'params' => $binanceParams]);
                $binanceResp = \Http::timeout(10)->get($binanceUrl, $binanceParams);
                $binanceData = $binanceResp->json();
                \Log::info('bybitCandles: ответ Binance', ['data' => $binanceData]);
                $list = collect($binanceData)->map(function($row) {
                    return [
                        (string)$row[0],
                        (string)$row[1],
                        (string)$row[2],
                        (string)$row[3],
                        (string)$row[4],
                        (string)$row[5],
                        (string)$row[7],
                    ];
                })->toArray();
                \Log::info('bybitCandles: BINANCE klines parsed for frontend', ['list' => $list]);
                return response()->json(['list' => $list]);
            }
            return response()->json($data['result'] ?? $data);
        } else {
            $url = 'https://api-testnet.bybit.com/v5/market/kline';
            \Log::info('bybitCandles: URL Bybit (inverse)', ['url' => $url, 'params' => $params]);
            $resp = \Http::timeout(10)->get($url, $params);
            $data = $resp->json();
            \Log::info('bybitCandles: ответ Bybit (inverse)', ['data' => $data]);
            return response()->json($data['result'] ?? $data);
        }
    }

    public function cryptoTrades(Request $request)
    {
        $currencyCode = $request->query('currency');
        $from = $request->query('from');
        $to = $request->query('to');
        if (!$currencyCode) {
            return response()->json(['error' => 'currency required'], 400);
        }
        $currency = \App\Models\Currency::where('code', $currencyCode)->first();
        $usdt = \App\Models\Currency::where('code', 'USDT')->first();
        if (!$currency || !$usdt) {
            return response()->json(['error' => 'currency not found'], 404);
        }
        $saleQuery = \App\Models\SaleCrypt::where('sale_currency_id', $currency->id)
            ->where('fixed_currency_id', $usdt->id);
        $purchaseQuery = \App\Models\Purchase::where('sale_currency_id', $currency->id)
            ->where('received_currency_id', $usdt->id);
        if ($from) {
            $saleQuery->where('created_at', '>=', $from);
            $purchaseQuery->where('created_at', '>=', $from);
        }
        if ($to) {
            $toObj = \Carbon\Carbon::parse($to);
            if ($toObj->format('H:i:s') === '00:00:00') {
                $toObj = $toObj->endOfDay();
            }
            $saleQuery->where('created_at', '<=', $toObj);
            $purchaseQuery->where('created_at', '<=', $toObj);
        }
        $sales = $saleQuery->get(['sale_amount as amount', 'created_at'])->map(function($row) {
            return [
                'type' => 'sale',
                'amount' => (float)$row->amount,
                'created_at' => $row->created_at,
            ];
        });
        $purchases = $purchaseQuery->get(['sale_amount as amount', 'created_at'])->map(function($row) {
            return [
                'type' => 'purchase',
                'amount' => (float)$row->amount,
                'created_at' => $row->created_at,
            ];
        });
        $all = $sales->concat($purchases)->sortBy('created_at')->values();
        return response()->json($all);
    }
}
