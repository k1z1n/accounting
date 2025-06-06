{{-- ============================
         ТАБЛИЦА: SaleCrypts (в 1/3 ширины)
    ============================ --}}
{{-- resources/views/pages/salecrypts.blade.php --}}
<div class="w-full md:w-1/2 px-2 mb-4">
    <h2 class="pl-4 text-lg font-medium text-gray-800 mb-2">Продажа крипты</h2>
    <div class="bg-white rounded-xl shadow-md overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 table-auto">
            <thead class="bg-gray-100">
            <tr class="sticky top-0">
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Платформа</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Сумма продажи –</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Сумма получена +</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @foreach($saleCrypts as $sc)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                        {{ optional($sc->exchanger)->title ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                        @if($sc->sale_amount !== null)
                            @php
                                $sa = rtrim(rtrim((string)$sc->sale_amount, '0'), '.');
                            @endphp
                            <span class="text-red-600">
                                    -{{ ltrim($sa, '-') }}
                                </span>
                            {{ optional($sc->saleCurrency)->code ?? '' }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                        @if($sc->fixed_amount !== null)
                            @php
                                $fa = rtrim(rtrim((string)$sc->fixed_amount, '0'), '.');
                            @endphp
                            <span class="{{ $sc->fixed_amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $sc->fixed_amount > 0 ? '+' : '-' }}{{ ltrim($fa, '-') }}
                                </span>
                            {{ optional($sc->fixedCurrency)->code ?? '' }}
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
