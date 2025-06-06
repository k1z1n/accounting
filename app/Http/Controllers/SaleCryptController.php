<?php

namespace App\Http\Controllers;

use App\Models\SaleCrypt;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SaleCryptController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'exchanger_id' => ['nullable', Rule::exists('exchangers', 'id')],
            'sale'         => 'required|string|max:255',
            'fixed'        => 'required|string|max:255',
        ]);

        $saleCrypt = SaleCrypt::create([
            'exchanger_id' => $request->input('exchanger_id'),
            'sale'         => $request->input('sale'),
            'fixed'        => $request->input('fixed'),
        ]);

        return response()->json([
            'success'    => true,
            'saleCrypt'  => $saleCrypt
        ]);
    }
}
