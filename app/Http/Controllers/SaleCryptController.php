<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\SaleCrypt;
use App\Models\UpdateLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SaleCryptController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 20;
        $page    = $request->get('page', 1);
        $data    = SaleCrypt::with(['exchangerFrom','exchangerTo','amountCurrency','commissionCurrency'])
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data'     => $data->items(),
            'has_more' => $data->hasMorePages(),
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'exchanger_id' => ['nullable', Rule::exists('exchangers', 'id')],
            'sale' => 'required|string|max:255',
            'fixed' => 'required|string|max:255',
        ]);

        $saleCrypt = SaleCrypt::create([
            'exchanger_id' => $request->input('exchanger_id'),
            'sale' => $request->input('sale'),
            'fixed' => $request->input('fixed'),
        ]);

        return response()->json([
            'success' => true,
            'saleCrypt' => $saleCrypt
        ]);
    }

    public function update(Request $req, SaleCrypt $saleCrypt)
    {
        $data = $req->validate([
            'exchanger_id' => 'nullable|exists:exchangers,id',
            'sale_amount' => 'nullable|numeric',
            'sale_currency_id' => 'nullable|exists:currencies,id',
            'fixed_amount' => 'nullable|numeric',
            'fixed_currency_id' => 'nullable|exists:currencies,id',
        ]);

        $original = $saleCrypt->getOriginal();
        $saleCrypt->update($data);

        // почистим старую историю
        History::where('sourceable_type', SaleCrypt::class)
            ->where('sourceable_id', $saleCrypt->id)
            ->delete();

        if ($saleCrypt->sale_amount) {
            History::create([
                'sourceable_type' => SaleCrypt::class,
                'sourceable_id' => $saleCrypt->id,
                'amount' => -$saleCrypt->sale_amount,
                'currency_id' => $saleCrypt->sale_currency_id,
            ]);
        }
        if ($saleCrypt->fixed_amount) {
            History::create([
                'sourceable_type' => SaleCrypt::class,
                'sourceable_id' => $saleCrypt->id,
                'amount' => +$saleCrypt->fixed_amount,
                'currency_id' => $saleCrypt->fixed_currency_id,
            ]);
        }

        UpdateLog::create([
            'user_id' => auth()->id(),
            'sourceable_type' => SaleCrypt::class,
            'sourceable_id' => $saleCrypt->id,
            'update' => 'Изменена продажа, old='
                . json_encode($original, JSON_UNESCAPED_UNICODE)
                . ' new=' . json_encode($data, JSON_UNESCAPED_UNICODE),
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(SaleCrypt $saleCrypt)
    {
        History::where('sourceable_type', SaleCrypt::class)
            ->where('sourceable_id', $saleCrypt->id)
            ->delete();

        UpdateLog::create([
            'user_id' => auth()->id(),
            'sourceable_type' => SaleCrypt::class,
            'sourceable_id' => $saleCrypt->id,
            'update' => 'Удалена продажа #' . $saleCrypt->id,
        ]);

        $saleCrypt->delete();

        return response()->json(['success' => true]);
    }
}
