<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Payment;
use App\Models\UpdateLog;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 20;
        $page    = $request->get('page', 1);
        $data    = Payment::with(['exchangerFrom','exchangerTo','amountCurrency','commissionCurrency'])
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data'     => $data->items(),
            'has_more' => $data->hasMorePages(),
        ]);
    }

    public function update(Request $req, Payment $payment)
    {
        $data = $req->validate([
            'exchanger_id'      => 'nullable|exists:exchangers,id',
            'sell_amount'       => 'nullable|numeric',
            'sell_currency_id'  => 'nullable|exists:currencies,id',
            'comment'           => 'nullable|string',
        ]);

        $original = $payment->getOriginal();
        $payment->update($data);

        History::where('sourceable_type', Payment::class)
            ->where('sourceable_id', $payment->id)
            ->delete();

        if ($payment->sell_amount) {
            History::create([
                'sourceable_type' => Payment::class,
                'sourceable_id'   => $payment->id,
                'amount'          => -$payment->sell_amount,
                'currency_id'     => $payment->sell_currency_id,
            ]);
        }

        UpdateLog::create([
            'user_id'           => auth()->id(),
            'sourceable_type'   => Payment::class,
            'sourceable_id'     => $payment->id,
            'update'            => 'Обновлена оплата, old='
                .json_encode($original, JSON_UNESCAPED_UNICODE)
                .' new='
                .json_encode($data, JSON_UNESCAPED_UNICODE),
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(Payment $payment)
    {
        History::where('sourceable_type', Payment::class)
            ->where('sourceable_id', $payment->id)
            ->delete();

        UpdateLog::create([
            'user_id'           => auth()->id(),
            'sourceable_type'   => Payment::class,
            'sourceable_id'     => $payment->id,
            'update'            => 'Удалена оплата #'.$payment->id,
        ]);

        $payment->delete();

        return response()->json(['success' => true]);
    }
}
