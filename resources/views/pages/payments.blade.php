{{-- ============================
         ТАБЛИЦА: Payments (в 1/3 ширины)
    ============================ --}}
{{-- resources/views/pages/payments.blade.php --}}
<div class="w-full md:w-1/2 px-2 mb-4">
    <h2 class="pl-4 text-lg font-medium text-gray-800 mb-2">Оплата reiowroi</h2>
    <div class="bg-white rounded-xl shadow-md overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 table-auto">
            <thead class="bg-gray-100">
            <tr class="sticky top-0">
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Платформа</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Сумма продажи –</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Комментарий</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @foreach($payments as $p)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                        {{ optional($p->exchanger)->title ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                        @if($p->sell_amount !== null)
                            @php
                                $amount = rtrim(rtrim((string)$p->sell_amount, '0'), '.');
                            @endphp
                            <span class="text-red-600">
{{--                                    -{{ ltrim($amount, '-') }}--}}
                                </span>
                            {{ optional($p->sellCurrency)->code ?? '' }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        {{ $p->comment ?? '—' }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <h2 class="pl-4 py-2 text-base font-medium text-gray-600">Ещё</h2>
</div>
