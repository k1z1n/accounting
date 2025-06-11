<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CurrencyController extends Controller
{
    public function create()
    {
        return view('admin.currencies-create');
    }

    public function edit(Currency $currency)
    {
        return view('admin.currency.edit', compact('currency'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:8|unique:currencies,code',
            'color' => 'nullable|string|max:7',
            'name' => 'required|string|max:64',
        ]);
        Currency::create($data);
        return redirect()->route('view.currencies')
            ->with('success', 'Валюта добавлена');
    }

    public function update(Request $request, Currency $currency)
    {
        $data = $request->validate([
            'code' => 'required|string|max:8|unique:currencies,code,' . $currency->id,
            'color' => 'nullable|string|max:7',
            'name' => 'required|string|max:64',
        ]);
        $currency->update($data);
        return redirect()->route('view.currencies')
            ->with('success', 'Валюта обновлена');
    }

    public function destroy(Currency $currency)
    {
        $currency->delete();
        return redirect()->route('view.currencies')
            ->with('success', 'Валюта удалена');
    }
}
