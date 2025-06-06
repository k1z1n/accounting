{{-- 4) Новая таблица: Purchases (1/4 ширины) --}}
{{-- resources/views/pages/purchases.blade.php --}}
<div class="w-full md:w-1/2 px-2 mb-4">
    <h2 class="pl-4 text-lg font-medium text-gray-800 mb-2">Покупки</h2>
    <div class="bg-white rounded-xl shadow-md overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 table-auto">
            <thead class="bg-gray-100">
            <tr class="sticky top-0">
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Платформа</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Сумма получено +</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Сумма продажи –</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @foreach($purchases as $pc)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                        {{ optional($pc->exchanger)->title ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                        @if($pc->sale_amount !== null)
                            @php
                                $sa = rtrim(rtrim((string)$pc->sale_amount, '0'), '.');
                            @endphp
                            <span class="{{ $pc->sale_amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $pc->sale_amount > 0 ? '+' : '-' }}{{ ltrim($sa, '-') }}
                                </span>
                            {{ optional($pc->saleCurrency)->code ?? '—' }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                        @if($pc->received_amount !== null)
                            @php
                                $ra = rtrim(rtrim((string)$pc->received_amount, '0'), '.');
                            @endphp
                            <span class="{{ $pc->received_amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $pc->received_amount > 0 ? '+' : '-' }}{{ ltrim($ra, '-') }}
                                </span>
                            {{ optional($pc->receivedCurrency)->code ?? '—' }}
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
