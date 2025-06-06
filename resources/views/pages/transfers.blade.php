{{-- ============================
         ТАБЛИЦА: Transfers (в 1/3 ширины)
    ============================ --}}
{{-- resources/views/pages/transfers.blade.php --}}
<div class="w-full md:w-1/2 px-2 mb-4">
    <h2 class="pl-4 text-lg font-medium text-gray-800 mb-2">Обмены</h2>
    <div class="bg-white rounded-xl shadow-md overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 table-auto">
            <thead class="bg-gray-100">
            <tr class="sticky top-0">
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Платформа «Откуда»</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Платформа «Куда»</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Сумма</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Комиссия –</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @foreach($transfers as $t)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                        {{ optional($t->exchangerFrom)->title ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                        {{ optional($t->exchangerTo)->title ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                        @if($t->amount !== null)
                            @php
                                $amt = rtrim(rtrim((string)$t->amount, '0'), '.');
                            @endphp
                            <span class="{{ $t->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $t->amount > 0 ? '+' : '-' }}{{ ltrim($amt, '-') }}
                                </span>
                            {{ optional($t->amountCurrency)->code ?? '' }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                        @if($t->commission !== null)
                            @php
                                $comm = rtrim(rtrim((string)$t->commission, '0'), '.');
                            @endphp
                            <span class="text-red-600">
                                    -{{ ltrim($comm, '-') }}
                                </span>
                            {{ optional($t->commissionCurrency)->code ?? '' }}
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <h2 class="pl-4 py-2 text-base font-medium text-gray-600">Ещё</h2>
</div>
