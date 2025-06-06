<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CurrencyController extends Controller
{
    /**
     * Показывает форму создания новой валюты.
     */
    public function create()
    {
        return view('admin.currencies-create');
    }

    /**
     * Сохраняет новую валюту в БД.
     */
    public function store(Request $request)
    {
        // Валидация: код должен быть уникальным и состоять из 1–8 символов (латиница, цифры).
        // name может быть до 64 символов.
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:8',
                'regex:/^[A-Za-z0-9]+$/u',
                Rule::unique('currencies', 'code'),
            ],
            'name' => 'required|string|max:64',
        ]);

        Currency::create([
            'code' => mb_strtoupper($data['code']),
            'name' => $data['name'],
        ]);

        // После успешного сохранения перенаправим обратно на форму с флеш-сообщением.
        return redirect()
            ->route('view.currency.create')
            ->with('success', 'Валюта «' . mb_strtoupper($data['code']) . '» успешно добавлена.');
    }
}
