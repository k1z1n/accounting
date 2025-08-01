{{-- resources/views/pages/all-history.blade.php --}}
@extends('template.app')

@section('content')
<div class="min-h-screen bg-[#0a0a0a]">
    <!-- Header -->
    <div class="bg-[#191919] border-b border-[#2d2d2d] sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">История операций</h1>
                        <p class="text-sm text-gray-400">Все ваши финансовые операции</p>
                    </div>
                </div>
                <button id="filterToggle" class="bg-[#2d2d2d] border border-[#404040] rounded-lg px-4 py-2 text-sm font-medium text-white hover:bg-[#404040] transition-all duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Фильтры
                </button>
            </div>
        </div>
    </div>

    <!-- Filters Panel -->
    <div id="filtersPanel" class="bg-[#191919] border-b border-[#2d2d2d] hidden">
        <div class="container mx-auto px-4 py-6">
            <form method="GET" action="{{ route('history.all') }}" class="space-y-6">
                <!-- Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Тип операции</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        @foreach($types as $key => $label)
                            <label class="relative">
                                <input type="radio" name="type" value="{{ $key }}" {{ $typeFilter === $key ? 'checked' : '' }} class="sr-only">
                                <div class="cursor-pointer rounded-lg border-2 p-3 text-center transition-all duration-200 {{ $typeFilter === $key ? 'border-blue-500 bg-blue-500/10 text-blue-400' : 'border-[#2d2d2d] bg-[#191919] text-gray-300 hover:border-[#404040]' }}">
                                    <div class="text-sm font-medium">{{ $label }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Дата с</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full rounded-lg border border-[#2d2d2d] bg-[#191919] px-3 py-2 text-sm text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Дата по</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full rounded-lg border border-[#2d2d2d] bg-[#191919] px-3 py-2 text-sm text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Amount Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Сумма от</label>
                        <input type="number" step="0.00000001" name="amount_min" value="{{ $amountMin }}" placeholder="0.00" class="w-full rounded-lg border border-[#2d2d2d] bg-[#191919] px-3 py-2 text-sm text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Сумма до</label>
                        <input type="number" step="0.00000001" name="amount_max" value="{{ $amountMax }}" placeholder="0.00" class="w-full rounded-lg border border-[#2d2d2d] bg-[#191919] px-3 py-2 text-sm text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Currency Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Валюта</label>
                    <select name="currency" class="w-full rounded-lg border border-[#2d2d2d] bg-[#191919] px-3 py-2 text-sm text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="all" {{ $currencyFilter === 'all' ? 'selected' : '' }}>Все валюты</option>
                        @foreach($currencies as $currency)
                            <option value="{{ $currency->code }}" {{ $currencyFilter === $currency->code ? 'selected' : '' }}>
                                {{ $currency->code }} - {{ $currency->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Actions -->
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 bg-blue-600 text-white font-medium py-3 px-6 rounded-lg hover:bg-blue-700 transition-all duration-200">
                        Применить фильтры
                    </button>
                    <a href="{{ route('history.all') }}" class="bg-[#2d2d2d] text-gray-300 font-medium py-3 px-6 rounded-lg hover:bg-[#404040] transition-all duration-200">
                        Сбросить
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    @if($groupedHistoriesWithInfo->count() > 0)
        <div class="container mx-auto px-4 py-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                @php
                    $totalPositive = 0;
                    $totalNegative = 0;
                    $totalRecords = 0;
                    foreach($groupedHistoriesWithInfo as $groupData) {
                        $groupHistories = $groupData['histories'];
                        $totalPositive += $groupHistories->where('amount', '>', 0)->sum('amount');
                        $totalNegative += $groupHistories->where('amount', '<', 0)->sum('amount');
                        $totalRecords += $groupHistories->count();
                    }
                    $totalNet = $totalPositive + $totalNegative;
                @endphp

                <div class="bg-[#191919] border border-[#2d2d2d] rounded-xl p-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-400">Всего операций</span>
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="text-2xl font-bold text-white">{{ $totalRecords }}</div>
                </div>

                <div class="bg-[#191919] border border-[#2d2d2d] rounded-xl p-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-400">Доходы</span>
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <div class="text-2xl font-bold text-cyan-400">+{{ number_format($totalPositive, 8) }}</div>
                </div>

                <div class="bg-[#191919] border border-[#2d2d2d] rounded-xl p-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-400">Расходы</span>
                        <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                        </svg>
                    </div>
                    <div class="text-2xl font-bold text-red-400">{{ number_format($totalNegative, 8) }}</div>
                </div>
            </div>
        </div>
    @endif

    <!-- Operations List -->
    <div class="container mx-auto px-4 pb-6">
        @if($groupedHistoriesWithInfo->count() > 0)
            <div class="space-y-4">
                @foreach($groupedHistoriesWithInfo as $groupKey => $groupData)
                    @php
                        $groupInfo = $groupData['info'];
                        $groupHistories = $groupData['histories'];
                        $groupType = $groupInfo['type'];
                        $groupId = $groupInfo['id'];
                        $groupDisplayName = $groupInfo['display_name'];
                        $groupColor = $groupInfo['color'];
                        $groupCreatedAt = $groupInfo['created_at'];

                        $positiveTotal = $groupHistories->where('amount', '>', 0)->sum('amount');
                        $negativeTotal = $groupHistories->where('amount', '<', 0)->sum('amount');
                        $netTotal = $positiveTotal + $negativeTotal;
                    @endphp

                    <!-- Operation Group -->
                    <div class="bg-[#191919] border border-[#2d2d2d] rounded-xl overflow-hidden">
                        <!-- Group Header -->
                        <div class="p-6 border-b border-[#2d2d2d]">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-{{ $groupColor }}-600 rounded-xl flex items-center justify-center text-white">
                                        @switch($groupType)
                                            @case('Application')
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                @break
                                            @case('Purchase')
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m6 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                                                </svg>
                                                @break
                                            @case('SaleCrypt')
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                </svg>
                                                @break
                                            @case('Transfer')
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                                </svg>
                                                @break
                                            @case('Payment')
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                                </svg>
                                                @break
                                            @default
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                        @endswitch
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-white">{{ $groupDisplayName }}</h3>
                                        <p class="text-sm text-gray-400">{{ \Carbon\Carbon::parse($groupCreatedAt)->format('d.m.Y в H:i') }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-400">{{ $groupHistories->count() }} операций</div>
                                    @if($netTotal != 0)
                                        <div class="text-lg font-bold {{ $netTotal > 0 ? 'text-cyan-400' : 'text-red-400' }}">
                                            {{ $netTotal > 0 ? '+' : '' }}{{ number_format($netTotal, 8) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Operations List -->
                        <div class="divide-y divide-[#2d2d2d]">
                            @foreach($groupHistories as $history)
                                @php
                                    $amount = $history->amount;
                                    $sign = $amount > 0 ? '+' : '-';
                                    $abs = abs($amount);
                                    $formatted = rtrim(rtrim(sprintf('%.8f', $abs), '0'), '.');
                                    $currency = $history->currency;
                                    $icon = $currency ? "/images/coins/{$currency->code}.svg" : null;
                                    $date = $history->created_at ? \Carbon\Carbon::parse($history->created_at)->format('d.m.Y H:i') : '';
                                    $type = class_basename($history->sourceable_type);
                                    $source = $history->sourceable;
                                    $typeRu = $type;
                                    if ($type === 'Purchase') $typeRu = 'Покупка крипты';
                                    elseif ($type === 'SaleCrypt') $typeRu = 'Продажа крипты';
                                    elseif ($type === 'Payment') $typeRu = 'Оплата';
                                    elseif ($type === 'Transfer') $typeRu = 'Перевод';
                                    elseif ($type === 'Application') $typeRu = 'Заявка';
                                    $exchanger = null;
                                    if ($type === 'Purchase' && $source && $source->exchanger) {
                                        $exchanger = $source->exchanger->title;
                                    } elseif ($type === 'SaleCrypt' && $source && $source->exchanger) {
                                        $exchanger = $source->exchanger->title;
                                    } elseif ($type === 'Payment' && $source && $source->exchanger) {
                                        $exchanger = $source->exchanger->title;
                                    } elseif ($type === 'Transfer' && $source && $source->exchangerFrom) {
                                        $exchanger = $source->exchangerFrom->title . ' → ' . ($source->exchangerTo->title ?? '');
                                    } elseif ($type === 'Application' && $source && $source->merchant) {
                                        $exchanger = $source->merchant;
                                    } else {
                                        $exchanger = $typeRu;
                                    }
                                @endphp

                                <div class="p-4 hover:bg-[#2d2d2d]/50 transition-colors duration-200 cursor-pointer"
                                     data-type="{{ $type }}"
                                     data-id="{{ $source->id ?? '' }}"
                                     data-exchanger="{{ $exchanger ?? '' }}"
                                     data-amount="{{ $amount ?? '' }}"
                                     data-currency="{{ $currency->code ?? '' }}"
                                     data-date="{{ $date ?? '' }}"
                                     data-type-ru="{{ $typeRu ?? '' }}"
                                     @if($source)
                                        data-sale-amount="{{ $source->sale_amount ?? '' }}"
                                        data-sale-currency="{{ optional($source->saleCurrency)->code ?? '' }}"
                                        data-fixed-amount="{{ $source->fixed_amount ?? '' }}"
                                        data-fixed-currency="{{ optional($source->fixedCurrency)->code ?? '' }}"
                                        data-received-amount="{{ $source->received_amount ?? '' }}"
                                        data-received-currency="{{ optional($source->receivedCurrency)->code ?? '' }}"
                                        data-sell-amount="{{ $source->sell_amount ?? '' }}"
                                        data-sell-currency="{{ optional($source->sellCurrency)->code ?? '' }}"
                                        data-comment="{{ $source->comment ?? '' }}"
                                        data-commission="{{ $source->commission ?? '' }}"
                                        data-commission-currency="{{ optional($source->commissionCurrency)->code ?? '' }}"
                                        data-amount-transfer="{{ $source->amount ?? '' }}"
                                        data-amount-currency="{{ optional($source->amountCurrency)->code ?? '' }}"
                                     @endif
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            @if($currency)
                                                <div class="w-8 h-8 rounded-full bg-[#2d2d2d] flex items-center justify-center">
                                                    <img src="{{ $icon }}" class="w-6 h-6" onerror="this.style.display='none';this.insertAdjacentHTML('afterend', '<span class=\"text-xs font-medium text-white\"></span>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="font-medium text-white">{{ $exchanger }}</div>
                                                <div class="text-sm text-gray-400">{{ $date }}</div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-lg font-bold {{ $amount > 0 ? 'text-cyan-400' : 'text-red-400' }} flex items-center justify-end">
                                                <span>{{ $sign }}{{ $formatted }}</span>
                                                @if($currency)
                                                    <img src="/images/coins/{{ strtoupper($currency->code) }}.svg" class="w-4 h-4 ml-1">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @if($source)
                                        <div class="mt-2">
                                            <button class="show-modal-btn bg-[#2d2d2d] text-gray-300 text-xs px-3 py-1 rounded-full hover:bg-[#404040] transition-colors duration-200" data-type="{{ $type }}" data-id="{{ $source->id ?? '' }}">
                                                {{ $typeRu }}
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($histories->hasPages())
                <div class="mt-8 flex justify-center">
                    <div class="bg-[#191919] border border-[#2d2d2d] rounded-xl px-6 py-4">
                        {{ $histories->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="bg-[#191919] border border-[#2d2d2d] rounded-xl p-12 text-center">
                <div class="w-24 h-24 bg-[#2d2d2d] rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Нет операций</h3>
                <p class="text-gray-400 mb-6">Попробуйте изменить фильтры или добавить новые операции</p>
                <a href="{{ route('history.all') }}" class="bg-blue-600 text-white font-medium py-3 px-6 rounded-lg hover:bg-blue-700 transition-all duration-200">
                    Сбросить фильтры
                </a>
            </div>
        @endif
    </div>
</div>

{{-- ВАЖНО: view-модалки подключаются только здесь, чтобы не было дублирования id на странице! --}}
@include('modal/view-purchase')
@include('modal/view-salecrypt')
@include('modal/view-payment')
@include('modal/view-transfer')
@include('modal/view-application')

<!-- JavaScript for Filters and Modals -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter toggle
    const filterToggle = document.getElementById('filterToggle');
    const filtersPanel = document.getElementById('filtersPanel');

    filterToggle.addEventListener('click', function() {
        filtersPanel.classList.toggle('hidden');
        filterToggle.classList.toggle('bg-blue-600');
        filterToggle.classList.toggle('text-white');
    });

    // Auto-submit on filter change
    const filterInputs = document.querySelectorAll('input[name="type"], select[name="currency"]');
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });

    // Date inputs with debounce
    let dateTimeout;
    const dateInputs = document.querySelectorAll('input[type="date"], input[type="number"]');
    dateInputs.forEach(input => {
        input.addEventListener('change', function() {
            clearTimeout(dateTimeout);
            dateTimeout = setTimeout(() => {
                this.closest('form').submit();
            }, 1000);
        });
    });

    // Modal functionality
    function trimZeros(val) {
        if (val === null || val === undefined) return '';
        let s = String(val);
        if (s.indexOf('.') !== -1) {
            s = s.replace(/(?:\.\d*?[1-9])0+$/,'$1'); // убираем конечные нули
            s = s.replace(/\.0+$/,''); // убираем .000...
            s = s.replace(/\.$/, ''); // убираем точку в конце
        }
        return s;
    }

    document.querySelectorAll('.show-modal-btn').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.stopPropagation();
            const type = this.dataset.type;
            const id = this.dataset.id;

            // Ajax-запрос по типу
            let url = null;
            if (type === 'Purchase') url = `/purchase/${id}`;
            else if (type === 'SaleCrypt') url = `/salecrypt/${id}`;
            else if (type === 'Payment') url = `/payment/${id}`;
            else if (type === 'Transfer') url = `/transfer/${id}`;
            else if (type === 'Application') url = `/api/applications/${id}`;

            if (!url) return;

            try {
                const resp = await fetch(url);
                if (!resp.ok) throw new Error('Ошибка загрузки данных');
                const data = await resp.json();

                if (type === 'Purchase') {
                    document.getElementById('modalViewPurchase').classList.remove('hidden');
                    // UX: ПРИХОД+ → ПРОДАЖА−
                    let rec = document.getElementById('viewPurchaseReceived');
                    let sale = document.getElementById('viewPurchaseSale');
                    rec.innerHTML = sale.innerHTML = '';
                    rec.innerHTML = `<div class="flex items-center justify-center gap-2">
                        <span class='text-cyan-400 text-2xl font-bold'>+${trimZeros(data.received_amount) || ''}</span>
                        ${data.received_icon ? `<img src="${data.received_icon}" alt="${data.received_currency}" class="w-8 h-8">` : ''}
                    </div>`;

                    sale.innerHTML = `<div class="flex items-center justify-center gap-2">
                        <span class='text-red-400 text-2xl font-bold'>-${trimZeros(data.sale_amount) || ''}</span>
                        ${data.sale_icon ? `<img src="${data.sale_icon}" alt="${data.sale_currency}" class="w-8 h-8">` : ''}
                    </div>`;
                    document.getElementById('viewPurchaseDate').textContent = data.date || '';
                } else if (type === 'SaleCrypt') {
                    document.getElementById('modalViewSaleCrypt').classList.remove('hidden');
                    // UX: ПРОДАЖА− → ПРИХОД+
                    let sale = document.getElementById('viewSaleCryptSale');
                    let fixed = document.getElementById('viewSaleCryptFixed');
                    sale.innerHTML = fixed.innerHTML = '';
                    sale.innerHTML = `<div class="flex items-center justify-center gap-2">
                        <span class='text-red-400 text-2xl font-bold'>-${trimZeros(data.sale_amount) || ''}</span>
                        ${data.sale_icon ? `<img src="${data.sale_icon}" alt="${data.sale_currency}" class="w-8 h-8">` : ''}
                    </div>`;

                    fixed.innerHTML = `<div class="flex items-center justify-center gap-2">
                        <span class='text-cyan-400 text-2xl font-bold'>+${trimZeros(data.fixed_amount) || ''}</span>
                        ${data.fixed_icon ? `<img src="${data.fixed_icon}" alt="${data.fixed_currency}" class="w-8 h-8">` : ''}
                    </div>`;
                    document.getElementById('viewSaleCryptDate').textContent = data.date || '';
                } else if (type === 'Payment') {
                    document.getElementById('modalViewPayment').classList.remove('hidden');
                    let amount = document.getElementById('viewPaymentAmount');
                    amount.innerHTML = '';
                    amount.innerHTML = `<div class="flex items-center justify-center gap-2">
                        <span class='text-red-400 text-2xl font-bold'>-${trimZeros(data.sell_amount) || ''}</span>
                        ${data.sell_icon ? `<img src="${data.sell_icon}" alt="${data.sell_currency}" class="w-8 h-8">` : ''}
                    </div>`;

                    document.getElementById('viewPaymentDate').textContent = data.date || '';
                } else if (type === 'Transfer') {
                    document.getElementById('modalViewTransfer').classList.remove('hidden');
                    // ОТКУДА → КУДА
                    let from = document.getElementById('viewTransferFrom');
                    let to = document.getElementById('viewTransferTo');
                    let amount = document.getElementById('viewTransferAmount');
                    let comm = document.getElementById('viewTransferCommission');
                    from.innerHTML = `<span class='text-gray-300 text-lg font-medium'>${data.from || ''}</span>`;
                    to.innerHTML = `<span class='text-gray-300 text-lg font-medium'>${data.to || ''}</span>`;

                    let amountFrom = document.getElementById('viewTransferAmountFrom');
                    let amountTo = document.getElementById('viewTransferAmountTo');
                    amountFrom.innerHTML = `<div class="flex items-center justify-center gap-2">
                        <span class='text-red-400 text-2xl font-bold'>-${trimZeros(data.amount) || ''}</span>
                        ${data.amount_icon ? `<img src="${data.amount_icon}" alt="${data.amount_currency}" class="w-8 h-8">` : ''}
                    </div>`;
                    amountTo.innerHTML = `<div class="flex items-center justify-center gap-2">
                        <span class='text-cyan-400 text-2xl font-bold'>+${trimZeros(data.amount) || ''}</span>
                        ${data.amount_icon ? `<img src="${data.amount_icon}" alt="${data.amount_currency}" class="w-8 h-8">` : ''}
                    </div>`;

                    // Комиссия
                    comm.innerHTML = '';
                    if (data.commission) {
                        comm.innerHTML = `<div class="flex items-center justify-center gap-2">
                            <span class='text-red-400 text-lg font-bold'>-${trimZeros(data.commission) || ''}</span>
                            ${data.commission_icon ? `<img src="${data.commission_icon}" alt="${data.commission_currency}" class="w-6 h-6">` : ''}
                        </div>`;
                    }
                    document.getElementById('viewTransferDate').textContent = data.date || '';
                } else if (type === 'Application') {
                    document.getElementById('modalViewApplication').classList.remove('hidden');
                    document.getElementById('viewApplicationId').textContent = `#${data.app_id}`;
                    document.getElementById('viewApplicationMerchant').textContent = data.merchant || '';
                    document.getElementById('viewApplicationStatus').textContent = data.status || '';
                    document.getElementById('viewApplicationDate').textContent = data.app_created_at || '';
                    // Современный UX: все блоки одинаково наглядно
                    let details = `<div class='grid grid-cols-2 gap-4 my-2'>`;
                    // ПРИХОД+
                    if (data.related_purchases && data.related_purchases.length) {
                        data.related_purchases.forEach(p => {
                            details += `<div class='flex items-center gap-2 bg-[#18291a] rounded-lg px-3 py-2 w-full'>
                                <span class='text-cyan-400 text-lg font-bold'>+${trimZeros(p.received_amount) || ''}</span>`;
                            if (p.received_icon) details += `<img src="${p.received_icon}" alt="${p.received_currency}" class="w-6 h-6">`;
                            details += `<span class='text-cyan-400 font-medium'>${p.received_currency || ''}</span>
                                <span class='ml-2 text-xs text-gray-400'>ПРИХОД+</span>
                            </div>`;
                        });
                    } else {
                        details += `<div class='col-span-2 text-center text-gray-500'>Нет прихода</div>`;
                    }
                    // ПРОДАЖА−
                    if (data.related_purchases && data.related_purchases.length) {
                        data.related_purchases.forEach(p => {
                            details += `<div class='flex items-center gap-2 bg-[#2a1919] rounded-lg px-3 py-2 w-full'>
                                <span class='text-red-400 text-lg font-bold'>-${trimZeros(p.sale_amount) || ''}</span>`;
                            if (p.sale_icon) details += `<img src="${p.sale_icon}" alt="${p.sale_currency}" class="w-6 h-6">`;
                            details += `<span class='text-red-400 font-medium'>${p.sale_currency || ''}</span>
                                <span class='ml-2 text-xs text-gray-400'>ПРОДАЖА−</span>
                            </div>`;
                        });
                    } else {
                        details += `<div class='col-span-2 text-center text-gray-500'>Нет продажи</div>`;
                    }
                    // КУПЛЯ+
                    if (data.buy_amount) {
                        details += `<div class='flex items-center gap-2 bg-[#18291a] rounded-lg px-3 py-2 w-full'>
                            <span class='text-cyan-400 text-lg font-bold'>+${trimZeros(data.buy_amount)}</span>`;
                        if (data.buy_icon) details += `<img src="${data.buy_icon}" alt="${data.buy_currency}" class="w-6 h-6">`;
                        details += `<span class='text-cyan-400 font-medium'>${data.buy_currency || ''}</span>
                            <span class='ml-2 text-xs text-gray-400'>КУПЛЯ+</span>
                        </div>`;
                    } else {
                        details += `<div class='col-span-2 text-center text-gray-500'>Нет купли</div>`;
                    }
                    // РАСХОД−
                    if (data.expense_amount) {
                        details += `<div class='flex items-center gap-2 bg-[#2a1919] rounded-lg px-3 py-2 w-full'>
                            <span class='text-red-400 text-lg font-bold'>-${trimZeros(data.expense_amount)}</span>`;
                        if (data.expense_icon) details += `<img src="${data.expense_icon}" alt="${data.expense_currency}" class="w-6 h-6">`;
                        details += `<span class='text-red-400 font-medium'>${data.expense_currency || ''}</span>
                            <span class='ml-2 text-xs text-gray-400'>РАСХОД−</span>
                        </div>`;
                    } else {
                        details += `<div class='col-span-2 text-center text-gray-500'>Нет расхода</div>`;
                    }
                    details += `</div>`;
                    // Списки связанных продаж
                    if (data.related_sale_crypts && data.related_sale_crypts.length) {
                        details += `<div class='mt-2'><b>Связанные продажи:</b><ul class='ml-4 list-disc'>`;
                        data.related_sale_crypts.forEach(s => {
                            details += `<li class='flex items-center gap-2'>`;
                            if (s.sale_icon) details += `<img src="${s.sale_icon}" alt="${s.sale_currency}" class="w-5 h-5">`;
                            details += `<span class='text-red-400'>-${trimZeros(s.sale_amount) || ''} ${s.sale_currency || ''}</span>`;
                            details += `<span class='mx-1 text-gray-400'>→</span>`;
                            if (s.fixed_icon) details += `<img src="${s.fixed_icon}" alt="${s.fixed_currency}" class="w-5 h-5">`;
                            details += `<span class='text-cyan-400'>+${trimZeros(s.fixed_amount) || ''} ${s.fixed_currency || ''}</span>`;
                            details += `</li>`;
                        });
                        details += `</ul></div>`;
                    }
                    // Основная информация
                    let merchant = document.getElementById('viewApplicationMerchant');
                    if (merchant) merchant.innerHTML = (data.merchant || '') + details;
                }
            } catch (err) {
                alert('Ошибка загрузки данных для модального окна');
            }
        });
    });

    // Закрытие всех view-модалок
    [
        ['modalViewPurchase', 'closeViewPurchase'],
        ['modalViewSaleCrypt', 'closeViewSaleCrypt'],
        ['modalViewPayment', 'closeViewPayment'],
        ['modalViewTransfer', 'closeViewTransfer'],
        ['modalViewApplication', 'closeViewApplication'],
    ].forEach(([modalId, btnId]) => {
        const modal = document.getElementById(modalId);
        const btn = document.getElementById(btnId);
        if (modal && btn) {
            btn.addEventListener('click', () => modal.classList.add('hidden'));
            modal.addEventListener('click', e => { if (e.target === modal) modal.classList.add('hidden'); });
        }
    });
});
</script>
@endsection
