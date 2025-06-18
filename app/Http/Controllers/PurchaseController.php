<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Purchase;
use App\Models\UpdateLog;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 10;
        $page    = $request->get('page', 1);
        $data    = Purchase::with(['exchangerFrom','exchangerTo','amountCurrency','commissionCurrency'])
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data'     => $data->items(),
            'has_more' => $data->hasMorePages(),
        ]);
    }

    public function update(Request $req, Purchase $purchase)
    {
        $data = $req->validate([
            'exchanger_id'           => 'nullable|exists:exchangers,id',
            'received_amount'        => 'nullable|numeric|min:0',
            'received_currency_id'   => 'nullable|exists:currencies,id',
            'sale_amount'            => 'nullable|numeric|min:0',
            'sale_currency_id'       => 'nullable|exists:currencies,id',
        ]);

        $original = $purchase->getOriginal();
        $purchase->update($data);

        // Синхронизируем историю
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

        // Логируем изменения
        UpdateLog::create([
            'user_id'         => auth()->id(),
            'sourceable_type'=> Purchase::class,
            'sourceable_id'  => $purchase->id,
            'update'         => 'Обновлена покупка, old='.json_encode($original, JSON_UNESCAPED_UNICODE)
                .' new='.json_encode($data, JSON_UNESCAPED_UNICODE),
        ]);

        return response()->json(['success'=>true]);
    }

    public function destroy(Purchase $purchase)
    {
        // Удаляем связанные истории
        History::where('sourceable_type', Purchase::class)
            ->where('sourceable_id', $purchase->id)
            ->delete();

        // Логируем удаление
        UpdateLog::create([
            'user_id'         => auth()->id(),
            'sourceable_type'=> Purchase::class,
            'sourceable_id'  => $purchase->id,
            'update'         => 'Удалена покупка #'.$purchase->id,
        ]);

        $purchase->delete();
        return response()->json(['success'=>true]);
    }
}
