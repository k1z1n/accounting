<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Transfer;
use App\Models\UpdateLog;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 10;
        $page    = $request->get('page', 1);
        $data    = Transfer::with(['exchangerFrom','exchangerTo','amountCurrency','commissionCurrency'])
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data'     => $data->items(),
            'has_more' => $data->hasMorePages(),
        ]);
    }

    public function update(Request $req, Transfer $transfer)
    {
        $data = $req->validate([
            'exchanger_from_id'      => 'nullable|exists:exchangers,id',
            'exchanger_to_id'        => 'nullable|exists:exchangers,id',
            'amount'                 => 'nullable|numeric',
            'amount_currency_id'     => 'nullable|exists:currencies,id',
            'commission'             => 'nullable|numeric',
            'commission_currency_id' => 'nullable|exists:currencies,id',
        ]);

        $original = $transfer->getOriginal();
        $transfer->update($data);

        // Перезаписываем историю
        History::where('sourceable_type', Transfer::class)
            ->where('sourceable_id', $transfer->id)
            ->delete();

        if ($transfer->amount !== null) {
            History::create([
                'sourceable_type' => Transfer::class,
                'sourceable_id'   => $transfer->id,
                'amount'          => +$transfer->amount,
                'currency_id'     => $transfer->amount_currency_id,
            ]);
        }
        if ($transfer->commission !== null) {
            History::create([
                'sourceable_type' => Transfer::class,
                'sourceable_id'   => $transfer->id,
                'amount'          => -$transfer->commission,
                'currency_id'     => $transfer->commission_currency_id,
            ]);
        }

        UpdateLog::create([
            'user_id'         => auth()->id(),
            'sourceable_type'=> Transfer::class,
            'sourceable_id'  => $transfer->id,
            'update'         => 'Обновлён перевод, old='.json_encode($original).' new='.json_encode($data),
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(Transfer $transfer)
    {
        History::where('sourceable_type', Transfer::class)
            ->where('sourceable_id', $transfer->id)
            ->delete();

        UpdateLog::create([
            'user_id' => auth()->id(),
            'sourceable_type' => Transfer::class,
            'sourceable_id' => $transfer->id,
            'update' => 'Удалён перевод #' . $transfer->id,
        ]);

        $transfer->delete();

        return response()->json(['success' => true]);
    }
}
