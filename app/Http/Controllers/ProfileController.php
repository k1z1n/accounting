<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        // заглушка
        $data = [
            ['type'=>'Получено','currency'=>'USDT','amount'=>1.23,'date'=>now()->subMinute()],
            ['type'=>'Отправлено','currency'=>'BTC','amount'=>-0.001,'date'=>now()->subMinutes(5)],
        ];
        return response()->json(['history'=>$data]);
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
