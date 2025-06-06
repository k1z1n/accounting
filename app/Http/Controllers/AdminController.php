<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\LoginLog;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\SaleCrypt;
use App\Models\Transfer;
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
            'exchanger_id'     => ['required', 'integer', 'exists:exchangers,id'],
            'sell_amount'      => ['required', 'numeric', 'min:0'],
            'sell_currency_id' => ['required', 'integer', 'exists:currencies,id'],
            'comment'          => ['nullable', 'string', 'max:255'],
        ]);

        // 1) Создаём Payment
        $payment = Payment::create([
            'exchanger_id'     => $validated['exchanger_id'],
            'sell_amount'      => $validated['sell_amount'],
            'sell_currency_id' => $validated['sell_currency_id'],
            'comment'          => $validated['comment'] ?? null,
            'user_id'          => auth()->id(),
        ]);

        Log::info("Payment stored: id={$payment->id}");

        // 2) Синхронизируем history: удаляем старые записи для этого Payment, затем добавляем «расход»:
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
            Log::info("History created for Payment (expense) -{$payment->sell_amount}, currency_id={$payment->sell_currency_id}");
        }

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
            'exchanger_from_id' => ['required', 'integer', 'exists:exchangers,id'],
            'exchanger_to_id'   => ['required', 'integer', 'exists:exchangers,id',
                Rule::notIn([$request->input('exchanger_from_id')])],
            'amount'            => ['required', 'numeric', 'min:0'],
            'amount_id'         => ['required', 'integer', 'exists:currencies,id'],
            'commission'        => ['nullable', 'numeric', 'min:0'],
            'commission_id'     => ['nullable', 'integer', 'exists:currencies,id'],
        ]);

        // 1) Создаём Transfer
        $transfer = Transfer::create([
            'exchanger_from_id' => $validated['exchanger_from_id'],
            'exchanger_to_id'   => $validated['exchanger_to_id'],
            'amount'            => $validated['amount'],
            'amount_id'         => $validated['amount_id'],
            'commission'        => $validated['commission'] ?? null,
            'commission_id'     => $validated['commission_id'] ?? null,
            'user_id'           => auth()->id(),
        ]);

        Log::info("Transfer stored: id={$transfer->id}");

        // 2) Синхронизируем history: удаляем старые записи, затем добавляем «приход» и «расход»
        History::where('sourceable_type', Transfer::class)
            ->where('sourceable_id', $transfer->id)
            ->delete();

//        if ($transfer->amount > 0) {
//            History::create([
//                'sourceable_type' => Transfer::class,
//                'sourceable_id'   => $transfer->id,
//                'amount'          => +$transfer->amount,
//                'currency_id'     => $transfer->amount_id,
//            ]);
//            Log::info("History created for Transfer (income) +{$transfer->amount}, currency_id={$transfer->amount_id}");
//        }

        if (!is_null($transfer->commission) && $transfer->commission > 0) {
            History::create([
                'sourceable_type' => Transfer::class,
                'sourceable_id'   => $transfer->id,
                'amount'          => -$transfer->commission,
                'currency_id'     => $transfer->commission_id,
            ]);
            Log::info("History created for Transfer (expense) -{$transfer->commission}, currency_id={$transfer->commission_id}");
        }

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
            'exchanger_id'      => ['required', 'integer', 'exists:exchangers,id'],
            'sale_amount'       => ['required', 'numeric', 'min:0'],
            'sale_currency_id'  => ['required', 'integer', 'exists:currencies,id'],
            'fixed_amount'      => ['required', 'numeric', 'min:0'],
            'fixed_currency_id' => ['required', 'integer', 'exists:currencies,id'],
        ]);

        // 1) Создаём SaleCrypt
        $saleCrypt = SaleCrypt::create([
            'exchanger_id'      => $validated['exchanger_id'],
            'sale_amount'       => $validated['sale_amount'],
            'sale_currency_id'  => $validated['sale_currency_id'],
            'fixed_amount'      => $validated['fixed_amount'],
            'fixed_currency_id' => $validated['fixed_currency_id'],
            'user_id'           => auth()->id(),
        ]);

        Log::info("SaleCrypt stored: id={$saleCrypt->id}");

        // 2) Синхронизируем history: удаляем старые записи, затем «расход» и «приход»
        History::where('sourceable_type', SaleCrypt::class)
            ->where('sourceable_id', $saleCrypt->id)
            ->delete();

        if ($saleCrypt->sale_amount > 0) {
            History::create([
                'sourceable_type' => SaleCrypt::class,
                'sourceable_id'   => $saleCrypt->id,
                'amount'          => -$saleCrypt->sale_amount,
                'currency_id'     => $saleCrypt->sale_currency_id,
            ]);
            Log::info("History created for SaleCrypt (expense) -{$saleCrypt->sale_amount}, currency_id={$saleCrypt->sale_currency_id}");
        }

        if ($saleCrypt->fixed_amount > 0) {
            History::create([
                'sourceable_type' => SaleCrypt::class,
                'sourceable_id'   => $saleCrypt->id,
                'amount'          => +$saleCrypt->fixed_amount,
                'currency_id'     => $saleCrypt->fixed_currency_id,
            ]);
            Log::info("History created for SaleCrypt (income) +{$saleCrypt->fixed_amount}, currency_id={$saleCrypt->fixed_currency_id}");
        }

        return response()->json([
            'message'    => 'SaleCrypt успешно сохранён',
            'saleCrypt'  => $saleCrypt,
        ], 201);
    }

    /**
     * Сохраняет новую запись Purchase и пишет в history.
     */
    public function storePurchase(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exchanger_id'         => 'nullable|exists:exchangers,id',
            'sale_amount'          => 'nullable|numeric|min:0',
            'sale_currency_id'     => 'nullable|exists:currencies,id',
            'received_amount'      => 'nullable|numeric|min:0',
            'received_currency_id' => 'nullable|exists:currencies,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        // 1) Создаём Purchase
        $purchase = Purchase::create([
            'exchanger_id'         => $request->input('exchanger_id'),
            'sale_amount'          => $request->input('sale_amount'),
            'sale_currency_id'     => $request->input('sale_currency_id'),
            'received_amount'      => $request->input('received_amount'),
            'received_currency_id' => $request->input('received_currency_id'),
        ]);

        Log::info("Purchase created: id={$purchase->id}");

        // 2) Синхронизируем history: удаляем старые записи, затем «приход» и «расход»
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
            Log::info("History created for Purchase (income) +{$purchase->received_amount}, currency_id={$purchase->received_currency_id}");
        }

        if ($purchase->sale_amount > 0) {
            History::create([
                'sourceable_type' => Purchase::class,
                'sourceable_id'   => $purchase->id,
                'amount'          => -$purchase->sale_amount,
                'currency_id'     => $purchase->sale_currency_id,
            ]);
            Log::info("History created for Purchase (expense) -{$purchase->sale_amount}, currency_id={$purchase->sale_currency_id}");
        }

        return response()->json([
            'message'  => 'Purchase создана успешно',
            'purchase' => $purchase,
        ], 201);
    }
}
