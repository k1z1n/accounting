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
        $prov    = $request->query('provider');    // heleket|rapira
        $exch    = $request->query('exchanger');   // obama|ural
        $cfg     = config("services.{$prov}.{$exch}")
            ?? abort(400, 'Неверный провайдер/обменник');
        $url     = $cfg['balance_url'];

        try {
            if ($prov === 'heleket') {
                // Heleket: POST + merchant + sign
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
            } else {
                // Rapira: JWT + POST
                $jwt      = $this->makeJwt([
                    'exp' => time() + 3600,
                    'jti' => bin2hex(random_bytes(12)),
                ], $cfg['private_key']);
                $resp     = Http::timeout(5)->post($url, [
                    'kid'       => $cfg['uid'],
                    'jwt_token' => $jwt,
                ]);
                $resp->throw();
                $raw      = $resp->json();
                $balances = $this->normalizeRapira($raw);
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
    private array $providers  = ['heleket' => 'Heleket', 'rapira' => 'Rapira'];
    private array $exchangers = ['obama'   => 'Obama'  , 'ural'   => 'Ural'  ];

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
        $all = array_merge($bal['merchant'] ?? [], $bal['user'] ?? []);
        return collect($all)->map(fn($b)=>[
            'code'   => strtoupper($b['currency_code']),
            'amount' => (float)$b['balance'],
            'icon'   => asset("img/coins/".strtolower($b['currency_code']).".png"),
        ]);
    }

    protected function normalizeRapira(array $raw)
    {
        // {data:[{unit,balance,...}], code:200}
        $list = data_get($raw,'data',[]);
        return collect($list)->map(fn($b)=>[
            'code'   => strtoupper($b['unit']),
            'amount' => (float)$b['balance'],
            'icon'   => asset("img/coins/".strtolower($b['unit']).".png"),
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
        return str_replace('=','',strtr(base64_encode($input), '+/','-_'));
    }
}
