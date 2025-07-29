<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function dashboard()
    {
        return view('pages.profile');
    }

    public function balances(Request $request)
    {
        $prov    = $request->query('provider');    // heleket|rapira|bybit
        $exch    = $request->query('exchanger');   // obama|ural|main
        $cfg     = config("services.{$prov}.{$exch}")
            ?? abort(400, 'Неверный провайдер/обменник');

        try {
            if ($prov === 'heleket') {
                // Heleket: POST + merchant + sign
                $url     = $cfg['balance_url'];
                $body    = json_encode([]);
                $sign    = md5(base64_encode($body) . $cfg['api_key']);
                $resp    = Http::withHeaders([
                    'merchant'     => $cfg['merchant_uuid'],
                    'sign'         => $sign,
                    'Content-Type' => 'application/json',
                ])->timeout(5)->post($url, []);
                $resp->throw();
                $raw     = $resp->json();
                $balances= $this->normalizeHeleket($raw);
            } elseif ($prov === 'rapira') {
                // Rapira: JWT + POST
                $url = $cfg['balance_url'];
                $privateKey = $cfg['private_key'];
                Log::info('RAPIRA: Исходный ключ', [
                    'length' => strlen($privateKey),
                    'start' => substr($privateKey, 0, 50),
                    'end' => substr($privateKey, -50),
                    'contains_newlines' => str_contains($privateKey, "\n"),
                    'contains_backslash_n' => str_contains($privateKey, '\\n'),
                ]);
                if (str_contains($privateKey, '\\n')) {
                    $privateKey = str_replace('\\n', "\n", $privateKey);
                }
                $privateKey = trim($privateKey);
                if (base64_decode($privateKey, true) !== false && !str_contains($privateKey, '-----BEGIN')) {
                    Log::info('RAPIRA: Декодируем base64 ключ');
                    $privateKey = base64_decode($privateKey);
                }
                if (!str_starts_with($privateKey, '-----BEGIN')) {
                    Log::info('RAPIRA: Добавляем PEM заголовки');
                    $body = preg_replace('/\s+/', '', $privateKey);
                    $body = trim(chunk_split($body, 64, "\n"));
                    $privateKey = "-----BEGIN PRIVATE KEY-----\n" . $body . "\n-----END PRIVATE KEY-----";
                }
                if (str_starts_with($privateKey, '-----BEGIN RSA PRIVATE KEY-----')) {
                    Log::info('RAPIRA: Конвертируем RSA PRIVATE KEY в PKCS#8');
                    $tmpIn  = tempnam(sys_get_temp_dir(), 'rsa_in_');
                    $tmpOut = tempnam(sys_get_temp_dir(), 'rsa_out_');
                    file_put_contents($tmpIn, $privateKey);
                    $cmd = "openssl pkcs8 -topk8 -inform PEM -outform PEM -in $tmpIn -out $tmpOut -nocrypt 2>&1";
                    $output = shell_exec($cmd);
                    if (file_exists($tmpOut)) {
                        $converted = file_get_contents($tmpOut);
                        if (str_starts_with($converted, '-----BEGIN PRIVATE KEY-----')) {
                            $privateKey = $converted;
                            Log::info('RAPIRA: Конвертация успешна');
                        } else {
                            Log::error('RAPIRA: Ошибка конвертации', ['output' => $output]);
                        }
                        unlink($tmpOut);
                    } else {
                        Log::error('RAPIRA: Не удалось создать временный файл для конвертации');
                    }
                    unlink($tmpIn);
                }
                Log::info('RAPIRA: Финальный ключ', [
                    'length' => strlen($privateKey),
                    'start' => substr($privateKey, 0, 50),
                    'end' => substr($privateKey, -50),
                    'is_valid_pem' => str_starts_with($privateKey, '-----BEGIN') && str_ends_with($privateKey, '-----'),
                ]);
                $keyResource = openssl_pkey_get_private($privateKey);
                if ($keyResource === false) {
                    Log::error('RAPIRA: Невалидный приватный ключ', [
                        'openssl_error' => openssl_error_string(),
                    ]);
                    return response()->json(['balances'=>[], 'error'=>'Невалидный приватный ключ'], 500);
                }
                openssl_free_key($keyResource);
                Log::info('RAPIRA: Генерируем JWT...');
                $jwt = $this->makeJwt([
                    'exp' => time() + 1800,
                    'jti' => bin2hex(random_bytes(12)),
                ], $privateKey);
                Log::info('RAPIRA: JWT сгенерирован', ['jwt_length' => strlen($jwt), 'jwt_start' => substr($jwt,0,40)]);
                Log::info('RAPIRA: Получаем токен...');
                $tokenResp = Http::timeout(10)->post('https://api.rapira.net/open/generate_jwt', [
                    'kid' => $cfg['uid'],
                    'jwt_token' => $jwt,
                ]);
                Log::info('RAPIRA: Ответ generate_jwt', ['status' => $tokenResp->status(), 'body' => $tokenResp->body()]);
                if (!$tokenResp->ok()) {
                    Log::error('Rapira generate_jwt error', ['status' => $tokenResp->status(), 'body' => $tokenResp->body()]);
                    return response()->json(['balances'=>[], 'error'=>'Ошибка авторизации Rapira (generate_jwt)'], 500);
                }
                $tokenData = $tokenResp->json();
                $rapiraToken = $tokenData['token'] ?? null;
                if (!$rapiraToken) {
                    Log::error('Rapira generate_jwt: token not found', ['response' => $tokenData]);
                    return response()->json(['balances'=>[], 'error'=>'Ошибка авторизации Rapira (token not found)'], 500);
                }
                Log::info('RAPIRA: Токен получен', ['token_length' => strlen($rapiraToken), 'token_start' => substr($rapiraToken,0,40)]);
                Log::info('RAPIRA: Запрашиваем балансы...');
                $balanceResp = Http::timeout(10)->withHeaders([
                    'Authorization' => 'Bearer ' . $rapiraToken,
                ])->get($url);
                Log::info('RAPIRA: Ответ баланса', ['status' => $balanceResp->status(), 'body' => $balanceResp->body()]);
                if (!$balanceResp->ok()) {
                    Log::error('Rapira balance error', ['status' => $balanceResp->status(), 'body' => $balanceResp->body()]);
                    return response()->json(['balances'=>[], 'error'=>'Ошибка получения баланса Rapira'], 500);
                }
                $raw = $balanceResp->json();
                Log::info('RAPIRA: Сырые данные баланса', ['raw' => $raw]);
                $balances = $this->normalizeRapira($raw);
                Log::info('RAPIRA: Нормализованные балансы', ['balances' => $balances]);
            } elseif ($prov === 'bybit') {
                // Bybit: API Key + Secret
                $balances = $this->getBybitBalances($cfg);
            } else {
                return response()->json(['balances'=>[], 'error'=>'Неизвестный провайдер'], 400);
            }

            return response()->json(['balances'=>$balances]);
        } catch (\Throwable $e) {
            Log::error("balances [{$prov}/{$exch}]: ".$e->getMessage());
            return response()->json(['balances'=>[], 'error'=>'Не удалось'], 500);
        }
    }

    public function history(Request $r)
    {
        $prov  = $r->query('provider', 'heleket');
        $exch  = $r->query('exchanger', 'obama');
        $limit = (int) $r->query('limit', 20);

        $items = $this->fetchExternalHistory($prov, $exch);
        // последние N
        $latest = collect($items)
            ->sortByDesc('date')
            ->take($limit)
            ->values();

        return response()->json([
            'history' => $latest,
        ]);
    }
    private array $providers  = ['heleket' => 'Heleket', 'rapira' => 'Rapira', 'bybit' => 'Bybit'];
    private array $exchangers = ['obama'   => 'Obama'  , 'ural'   => 'Ural'  , 'main'  => 'Main'  ];

    /* ───────────── 1. Страница / «каркас» ───────────── */
    public function index(Request $request)
    {
        return view('pages.wallets.history', [
            'providers'     => $this->providers,
            'exchangers'    => $this->exchangers,
            'currentProv'   => $request->query('provider' , array_key_first($this->providers)),
            'currentExch'   => $request->query('exchanger', array_key_first($this->exchangers)),
        ]);
    }

    /* ───────────── 2. Данные (JSON) ───────────── */
    public function data(Request $request)
    {
        /* — валидация — */
        $request->validate([
            'provider'  => ['required', 'in:' . implode(',', array_keys($this->providers))],
            'exchanger' => ['required', 'in:' . implode(',', array_keys($this->exchangers))],
            'page'      => ['integer', 'min:1'],
        ]);

        $prov = $request->get('provider');
        $exch = $request->get('exchanger');
        $page = max(1, (int)$request->get('page', 1));
        $per  = 50;

        /* — ваш реальный вызов внешнего API — */
        $all = $this->fakeExternalHistory()   //  ←  замените на $this->fetchExternalHistory($prov,$exch)
        ->sortByDesc('date')
            ->values();                // сбрасываем ключи

        /* — пагинация вручную, т.к. это Collection — */
        $slice = $all->slice(($page - 1) * $per, $per)->values();
        $p     = new LengthAwarePaginator($slice, $all->count(), $per, $page);

        return response()->json([
            'data' => $p->items(),
            'meta' => [
                'page' => $p->currentPage(),
                'last' => $p->lastPage(),
            ],
        ]);
    }


    /* ───────────── Ниже — заглушки / примеры ───────────── */

    /** фейковые данные, чтобы всё рендерилось без API */
    private function fakeExternalHistory(): Collection
    {
        return collect(range(1, 300))->map(fn ($i) => [
            'date'     => Carbon::now()->subMinutes($i)->format('d.m.Y H:i:s'),
            'type'     => $i % 2 ? 'Получено' : 'Отправлено',
            'amount'   => $i % 2 ?  0.1234 : -0.2345,
            'currency' => $i % 3 ? 'USDT'   : 'BTC',
        ]);
    }

    /** пример реального запроса (оставил для справки) */
    private function fetchExternalHistory(string $prov, string $exch): Collection
    {
        $cfg = config("services.$prov.$exch");
        $url = data_get($cfg, 'history_url');

        /* …собираете body + sign, делаете Http::post()…  */
        $resp  = Http::timeout(10)->post($url, []);
        $items = data_get($resp->json(), 'result.items', []);

        return collect($items)->map(fn ($i) => [
            'type'     => $i['payment_status'],
            'amount'   => round((float)$i['payer_amount'], 4),
            'currency' => $i['payer_currency'],
            'date'     => Carbon::parse($i['created_at'])->format('d.m.Y H:i:s'),
        ]);
    }

    protected function normalizeHeleket(array $raw)
    {
        $bal = data_get($raw,'result.0.balance',[]);
        return [
            'merchant' => collect($bal['merchant'] ?? [])->map(fn($b)=>[
                'code'   => strtoupper($b['currency_code']),
                'amount' => (float)$b['balance'],
                'icon'   => asset("images/coins/".strtoupper($b['currency_code']).".svg"),
            ])->values(),
            'user' => collect($bal['user'] ?? [])->map(fn($b)=>[
                'code'   => strtoupper($b['currency_code']),
                'amount' => (float)$b['balance'],
                'icon'   => asset("images/coins/".strtoupper($b['currency_code']).".svg"),
            ])->values(),
        ];
    }

    protected function normalizeRapira(array $raw)
    {
        // Если массив приходит сразу, а не в data
        $list = isset($raw['data']) ? $raw['data'] : $raw;
        return collect($list)->map(fn($b)=>[
            'code'   => strtoupper($b['unit']),
            'amount' => (float)$b['balance'],
            'icon'   => asset("images/coins/".strtoupper($b['unit']).".svg"),
        ]);
    }

    protected function makeJwt(array $payload, string $privateKey): string
    {
        // простой RS256 через openssl
        $header = ['typ'=>'JWT','alg'=>'RS256'];
        $segments = [
            $this->urlsafeB64(json_encode($header)),
            $this->urlsafeB64(json_encode($payload)),
        ];
        $signingInput = implode('.',$segments);
        if (!openssl_sign($signingInput, $sig, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new \Exception('JWT sign failed');
        }
        $segments[] = $this->urlsafeB64($sig);
        return implode('.',$segments);
    }

    protected function urlsafeB64(string $input): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($input));
    }

    protected function getBybitBalances(array $cfg): array
    {
        $apiKey = $cfg['api_key'];
        $secretKey = $cfg['secret_key'];
        $testnet = $cfg['testnet'] ?? false;

        $baseUrl = $testnet ? 'https://api-testnet.bybit.com' : 'https://api.bybit.com';
        $timestamp = time() * 1000; // milliseconds

        // Получаем балансы кошелька
        $endpoint = '/v5/account/wallet-balance';
        $params = [
            'accountType' => 'UNIFIED'
            // Убираем 'coin' => 'USDT' чтобы получить все валюты
        ];

        $queryString = http_build_query($params);
        $signature = $this->generateBybitSignature($secretKey, $timestamp, $apiKey, 'GET', $endpoint, $queryString);

        $response = Http::timeout(10)
            ->withHeaders([
                'X-BAPI-API-KEY' => $apiKey,
                'X-BAPI-SIGN' => $signature,
                'X-BAPI-SIGN-TYPE' => '2',
                'X-BAPI-TIMESTAMP' => $timestamp,
                'X-BAPI-RECV-WINDOW' => '5000',
            ])
            ->get($baseUrl . $endpoint . '?' . $queryString);

        if (!$response->ok()) {
            Log::error('Bybit balance error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return [];
        }

        $data = $response->json();
        Log::info('Bybit raw response', ['data' => $data]);

        return $this->normalizeBybit($data);
    }

    protected function generateBybitSignature(string $secretKey, int $timestamp, string $apiKey, string $method, string $endpoint, string $queryString = ''): string
    {
        $paramStr = $timestamp . $apiKey . '5000' . $queryString;
        return hash_hmac('sha256', $paramStr, $secretKey);
    }

    protected function normalizeBybit(array $raw): array
    {
        $balances = [];

        if (isset($raw['result']['list']) && is_array($raw['result']['list'])) {
            foreach ($raw['result']['list'] as $account) {
                if (isset($account['coin']) && is_array($account['coin'])) {
                    foreach ($account['coin'] as $coin) {
                        if (isset($coin['coin']) && isset($coin['walletBalance'])) {
                            $amount = (float)$coin['walletBalance'];
                            if ($amount > 0) {
                                $balances[] = [
                                    'code' => strtoupper($coin['coin']),
                                    'amount' => $amount
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $balances;
    }
}
