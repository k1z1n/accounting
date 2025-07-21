<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\SaleCrypt;
use App\Models\Purchase;
use App\Models\Transfer;

class AllOperationsController extends Controller
{
    public function index(Request $request)
    {
        // Получаем все записи (можно добавить пагинацию при необходимости)
        $payments = Payment::with(['user', 'exchanger', 'sellCurrency'])->get()->map(function($item) {
            return [
                'id' => $item->id,
                'operation_type' => 'Оплата',
                'amount' => $item->sell_amount,
                'currency' => $item->sellCurrency->code ?? '',
                'exchanger' => $item->exchanger->title ?? '',
                'user' => $item->user->login ?? '',
                'created_at' => $item->created_at,
                'comment' => $item->comment,
            ];
        });
        $saleCrypts = SaleCrypt::with(['exchanger', 'saleCurrency'])->get()->map(function($item) {
            return [
                'id' => $item->id,
                'operation_type' => 'Продажа крипты',
                'amount' => $item->sale_amount,
                'currency' => $item->saleCurrency->code ?? '',
                'exchanger' => $item->exchanger->title ?? '',
                'user' => '',
                'created_at' => $item->created_at,
                'comment' => $item->comment,
            ];
        });
        $purchases = Purchase::with(['exchanger', 'saleCurrency'])->get()->map(function($item) {
            return [
                'id' => $item->id,
                'operation_type' => 'Покупка крипты',
                'amount' => $item->sale_amount,
                'currency' => $item->saleCurrency->code ?? '',
                'exchanger' => $item->exchanger->title ?? '',
                'user' => '',
                'created_at' => $item->created_at,
                'comment' => $item->comment,
            ];
        });
        $transfers = Transfer::with(['exchangerFrom', 'amountCurrency'])->get()->map(function($item) {
            return [
                'id' => $item->id,
                'operation_type' => 'Перевод',
                'amount' => $item->amount,
                'currency' => $item->amountCurrency->code ?? '',
                'exchanger' => $item->exchangerFrom->title ?? '',
                'user' => '',
                'created_at' => $item->created_at,
                'comment' => $item->comment,
            ];
        });

        $all = $payments->concat($saleCrypts)->concat($purchases)->concat($transfers)->sortByDesc('created_at')->values();
        return response()->json($all);
    }
}
