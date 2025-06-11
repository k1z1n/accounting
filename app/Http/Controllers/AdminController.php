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
        ]);

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
}
