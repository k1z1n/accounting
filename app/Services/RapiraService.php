<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RapiraService
{
    public function getBalance(string $merchant, string $secret)
    {
        $sign = md5($merchant . $secret);

        $response = Http::withHeaders(['merchant' => $merchant,
            'sign' => $sign,
            'Content-Type' => 'application/json',])->post('https://api.heleket.com/v1/balance');

        if ($response->successful()) {
            return $response->json(); // Весь ответ API (или $response->json('data') если только данные нужны)
        }

        // Ошибка запроса, можно логировать
        return ['error' => $response->body()];
    }
}
