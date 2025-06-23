<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WalletHistoryController extends Controller
{
    private const PROVIDERS  = ['heleket' => 'Heleket', 'rapira' => 'Rapira'];
    private const EXCHANGERS = ['obama'   => 'Obama',   'ural'   => 'Ural'];

    public function index(Request $r)
    {
        // с 1-го января текущего года до сегодня
        $defaultFrom = $r->query('date_from', Carbon::now()->startOfYear()->format('Y-m-d'));
        $defaultTo   = $r->query('date_to',   Carbon::now()->format('Y-m-d'));

        return view('pages.wallets.history', [
            'providers'   => self::PROVIDERS,
            'exchangers'  => self::EXCHANGERS,
            'currentProv' => $r->query('provider',  'heleket'),
            'currentExch' => $r->query('exchanger', 'obama'),
            'defaultFrom' => $defaultFrom,
            'defaultTo'   => $defaultTo,
        ]);
    }

    public function data(Request $r)
    {
        // жёсткий формат, чтобы валидатор не пропустил кривые строки
        $r->validate([
            'provider'  => 'required|in:' . implode(',', array_keys(self::PROVIDERS)),
            'exchanger' => 'required|in:' . implode(',', array_keys(self::EXCHANGERS)),
            'cursor'    => 'nullable|string',
            'date_from' => 'required|date_format:Y-m-d H:i:s',
            'date_to'   => 'required|date_format:Y-m-d H:i:s',
        ]);

        // получаем одну «страницу»
        [$items, $prevCursor, $nextCursor] = $this->fetchPage(
            $r->provider,
            $r->exchanger,
            $r->date_from,
            $r->date_to,
            $r->query('cursor')
        );

        // перевод статусов
        $statusMap = [
            'pending'      => 'В ожидании',
            'paid'         => 'Оплачено',
            'cancel'       => 'Отменён',
            'wrong_amount' => 'Неверная сумма',
            'expired'      => 'Истёк',
            'complete'     => 'Завершено',
        ];

        // приводим к «тонкому» виду, который JS удобнее рендерить
        $data = array_map(function($i) use ($statusMap) {
            $rawStatus = $i['payment_status'] ?? $i['status'] ?? '';
            return [
                // для краткой таблицы
                'date'               => Carbon::parse($i['created_at'])->format('d.m.Y H:i:s'),
                'order_id'           => $i['order_id']  ?? '-',
                'uuid'               => $i['uuid'],
                'amount'             => (float) ($i['payer_amount'] ?? 0),
                'status_text'        => $statusMap[$rawStatus] ?? $rawStatus,

                // для подробного модального
                'payment_amount'     => (float) ($i['payment_amount'] ?? 0),
                'payer_amount'       => (float) ($i['payer_amount'] ?? 0),
                'discount_percent'   => (int)   ($i['discount_percent']   ?? 0),
                'discount'           => (float) ($i['discount']           ?? 0),
                'payment_amount_usd' => (float) ($i['payment_amount_usd'] ?? 0),
                'merchant_amount'    => (float) ($i['merchant_amount']    ?? 0),
                'currency'           => $i['payer_currency'] ?? '-',
                'network'            => $i['network']         ?? '-',
                'address'            => $i['address']         ?? '-',
                'from_address'       => $i['from']            ?? '-',
                'txid'               => $i['txid']            ?? '-',
                'url'                => $i['url']             ?? null,
                'expired_at'         => $i['expired_at']
                    ? Carbon::createFromTimestamp($i['expired_at'])->format('d.m.Y H:i:s')
                    : '-',
                'is_final'           => $i['is_final'] ? 'Да' : 'Нет',
                'additional_data'    => $i['additional_data'] ?? '-',
                'address_qr_code'    => $i['address_qr_code'] ?? null,
                'updated_at'         => Carbon::parse($i['updated_at'])->format('d.m.Y H:i:s'),
            ];
        }, $items);

        return response()->json([
            'data' => $data,
            'meta' => [
                'prevCursor' => $prevCursor,
                'nextCursor' => $nextCursor,
            ],
        ]);
    }

    private function fetchPage(string $prov, string $ex, string $from, string $to, ?string $cursor): array
    {
        $cfg  = config("services.{$prov}.{$ex}");
        // добавляем курсор в URL, если есть
        $url  = $cfg['history_url'] . ($cursor ? '?cursor=' . urlencode($cursor) : '');
        $body = ['date_from' => $from, 'date_to' => $to];
        $json = json_encode($body);
        $sign = md5(base64_encode($json) . $cfg['api_key']);

        Log::debug('Heleket API request', compact('url','body'));

        $res = Http::withHeaders([
            'merchant'      => $cfg['merchant_uuid'],
            'sign'          => $sign,
            'Content-Type'  => 'application/json',
        ])->timeout(15)->post($url, $body);

        if (! $res->successful()) {
            Log::error('Heleket API error', ['status'=>$res->status(), 'body'=>$res->body()]);
            return [[], null, null];
        }

        $j  = $res->json();
        $pg = data_get($j, 'result.paginate', []);

        return [
            data_get($j, 'result.items', []),
            $pg['previousCursor'] ?? null,
            $pg['nextCursor']     ?? null,
        ];
    }
}
