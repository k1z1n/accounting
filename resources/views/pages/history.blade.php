{{-- resources/views/pages/history.blade.php (пример) --}}
<div class="overflow-x-auto mb-10">
    <table class="min-w-full table-auto divide-y divide-gray-200">
        <thead class="bg-gray-100">
        <tr>
            {{-- Динамические колонки по каждой валюте --}}
            @foreach($currencies as $currency)
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                    {{ $currency->code }}
                </th>
            @endforeach
        </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
        @foreach($histories as $history)
            <tr class="hover:bg-gray-50">
                @foreach($currencies as $currency)
                    @php
                        $cell = '';
                        if ($history->currency_id === $currency->id && $history->amount !== null) {
                            $val = rtrim(rtrim((string) abs($history->amount), '0'), '.');
                            $sign = $history->amount > 0 ? '+' : '-';
                            $cell = $sign . $val;
                        }
                    @endphp
                    <td class="px-4 py-2 text-sm whitespace-nowrap">
                        @if($cell !== '')
                            <span class="{{ $history->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $cell }}
                                </span>
                        @else
                            —
                        @endif
                    </td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr class="bg-gray-100 font-semibold">
            @foreach($currencies as $currency)
                @php
                    $sum = $totals[$currency->id] ?? 0;
                    if ($sum > 0) {
                        $formatted = '+' . rtrim(rtrim((string) $sum, '0'), '.');
                    } elseif ($sum < 0) {
                        $formatted = '-' . ltrim(rtrim((string) abs($sum), '0'), '.');
                    } else {
                        $formatted = '0';
                    }
                @endphp
                <td class="px-4 py-2 text-sm whitespace-nowrap">
                        <span class="{{ $sum > 0 ? 'text-green-600' : ($sum < 0 ? 'text-red-600' : 'text-gray-900') }}">
                            {{ $formatted }}
                        </span>
                </td>
            @endforeach
        </tr>
        </tfoot>
    </table>
</div>
