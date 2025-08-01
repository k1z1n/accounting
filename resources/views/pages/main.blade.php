{{-- resources/views/pages/main.blade.php --}}
@extends('template.app')

@section('content')
    <div class="container mx-auto px-4 py-6 space-y-8">
        <!-- Упрощенная панель статистики -->
        <div class="bg-[#191919] border border-[#2d2d2d] rounded-xl p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Дата -->
                <div class="text-center md:text-left">
                    <div class="text-sm text-gray-400">Сегодня</div>
                    <div class="text-lg font-semibold text-white">{{ now()->format('d.m.Y') }}</div>
                </div>

                <!-- Прибыль за сегодня -->
                <div class="text-center md:text-left">
                    <div class="text-sm text-gray-400">Прибыль за сегодня</div>
                    <div id="profit-today" class="text-lg font-semibold text-white">...</div>
                </div>

                <!-- Прибыль за месяц -->
                <div class="text-center md:text-left">
                    <div class="text-sm text-gray-400">За месяц</div>
                    <div id="profit-month" class="text-lg font-semibold text-white">...</div>
                </div>
            </div>
        </div>

        <!-- Упрощенные быстрые действия для админов -->
        @if(auth()->user()->role === 'admin')
        <div class="bg-[#191919] border border-[#2d2d2d] rounded-xl p-6 mb-6">
            <h3 class="text-lg font-semibold text-white mb-4">Быстрые действия</h3>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('view.currency.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    Добавить валюту
                </a>
                <a href="{{ route('exchangers.create') }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                    Добавить платформу
                </a>
                <a href="{{ route('view.update.logs') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                    Логи изменений
                </a>
            </div>
        </div>
        @endif

        <script>
            // Форматируем число: до 4 знаков после точки, без лишних нулей
            function formatNum(value) {
                const num = parseFloat(value);
                if (isNaN(num)) return '0';
                const fixed = num.toFixed(4);
                return fixed.replace(/\.?0+$/, '');
            }

            document.addEventListener('DOMContentLoaded', () => {
                const todayEl = document.getElementById('profit-today');
                const monthEl = document.getElementById('profit-month');

                const today = new Date();
                const startMonth = new Date(today.getFullYear(), today.getMonth(), 1);
                const fmt = d => d.toISOString().slice(0,10);

                fetch(`/chart/usdt?start=${fmt(startMonth)}&end=${fmt(today)}`)
                    .then(res => res.json())
                    .then(({ datasets }) => {
                        const deltas = datasets[0].deltas || [];
                        const todayDelta = deltas.length ? deltas[deltas.length - 1] : 0;
                        const monthDelta = deltas.reduce((sum, d) => sum + d, 0);

                        const update = (el, val) => {
                            const formatted = formatNum(val);
                            const num = parseFloat(formatted);
                            el.classList.remove('text-gray-200', 'text-cyan-400', 'text-red-400', 'animate-pulse');
                            if (num > 0) el.classList.add('text-cyan-400');
                            else if (num < 0) el.classList.add('text-red-400');
                            el.textContent = formatted;
                        };

                        update(todayEl, todayDelta);
                        update(monthEl, monthDelta);
                    })
                    .catch(err => console.error('Ошибка загрузки прибыли:', err));
            });
        </script>
        @include('pages.other')

        <!-- История заявок -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <h2 class="text-xl font-semibold text-white">История заявок</h2>
                <!-- Панель поиска и фильтров -->
                <div class="flex flex-col md:flex-row gap-3 md:items-center">
                    <!-- Поиск -->
                    <div class="relative">
                        <input
                            type="text"
                            id="tableSearch"
                            placeholder="Поиск по заявкам..."
                            class="form-input form-input-dark pl-10 w-full md:w-64"
                        >
                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>

                    <!-- Фильтр по статусу -->
                    <select id="statusFilter" class="form-input form-input-dark">
                        <option value="">Все статусы</option>
                        <option value="выполненная заявка">Выполненные</option>
                        <option value="оплаченная заявка">Оплаченные</option>
                        <option value="возврат">Возврат</option>
                    </select>

                    <!-- Фильтр по обменнику -->
                    <select id="exchangerFilter" class="form-input form-input-dark">
                        <option value="">Все обменники</option>
                        <option value="obama">Obama</option>
                        <option value="ural">Ural</option>
                    </select>

                    <!-- Кнопка обновления -->
                    <button
                        id="refreshTable"
                        class="btn btn-primary btn-sm"
                        data-tooltip="Обновить данные"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Загрузочный индикатор -->
            <div id="tableLoader" class="hidden p-6 text-center">
                <div class="inline-flex items-center space-x-2">
                    <div class="loading-spinner"></div>
                    <span class="text-gray-400">Загрузка данных...</span>
                </div>
            </div>

            {{-- БЛОК МОДАЛЬНОГО ОКНА (не удаляйте эти ID!) --}}
            <div
                id="editModalBackdrop"
                class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 hidden"
            >
                <!-- кликабельный бэкдроп -->
                <div id="editModalBackdropClose" class="absolute inset-0"></div>

                <!-- само окно -->
                <div
                    class="bg-[#1F1F1F] text-white rounded-xl shadow-2xl p-6 relative z-10 w-full max-w-lg animate-fadeIn">
                    <h3 class="text-2xl font-semibold mb-4">
                        Редактировать заявку&nbsp;
                        <span id="modalAppId" class="text-cyan-400"></span>
                    </h3>

                    <form id="editForm" class="space-y-6">
                        <input type="hidden" name="id" id="edit_app_id">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Приход (sale_text) -->
                            <div>
                                <label for="edit_sale_amount" class="block text-sm font-medium text-gray-300 mb-1">
                                    Приход (сумма)
                                </label>
                                <input
                                    type="number"
                                    step="0.00000001"
                                    name="sale_amount"
                                    id="edit_sale_amount"
                                    class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                                >
                                <p id="err_sale_amount" class="mt-1 text-sm text-red-500"></p>
                            </div>
                            <div>
                                <label for="edit_sale_currency" class="block text-sm font-medium text-gray-300 mb-1">
                                    Приход (валюта)
                                </label>
                                <div class="relative">
                                    <select
                                        name="sale_currency"
                                        id="edit_sale_currency"
                                        class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-cyan-500 appearance-none"
                                    >
                                        <option value="" disabled selected>— Выберите валюту —</option>
                                        @foreach($currenciesForEdit as $c)
                                            <option value="{{ $c->code }}">{{ $c->code }} — {{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>
                                <p id="err_sale_currency" class="mt-1 text-sm text-red-500"></p>
                            </div>

                            <!-- Продажа -->
                            <div>
                                <label for="edit_sell_amount" class="block text-sm font-medium text-gray-300 mb-1">
                                    Продажа (сумма)
                                </label>
                                <input
                                    type="number"
                                    step="0.00000001"
                                    name="sell_amount"
                                    id="edit_sell_amount"
                                    class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                                >
                                <p id="err_sell_amount" class="mt-1 text-sm text-red-500"></p>
                            </div>
                            <div>
                                <label for="edit_sell_currency" class="block text-sm font-medium text-gray-300 mb-1">
                                    Продажа (валюта)
                                </label>
                                <div class="relative">
                                    <select
                                        name="sell_currency"
                                        id="edit_sell_currency"
                                        class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-cyan-500 appearance-none"
                                    >
                                        <option value="" disabled selected>— Выберите валюту —</option>
                                        @foreach($currenciesForEdit as $c)
                                            <option value="{{ $c->code }}">{{ $c->code }} — {{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>
                                <p id="err_sell_currency" class="mt-1 text-sm text-red-500"></p>
                            </div>

                            <!-- Купля -->
                            <div>
                                <label for="edit_buy_amount" class="block text-sm font-medium text-gray-300 mb-1">
                                    Купля (сумма)
                                </label>
                                <input
                                    type="number"
                                    step="0.00000001"
                                    name="buy_amount"
                                    id="edit_buy_amount"
                                    class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                                >
                                <p id="err_buy_amount" class="mt-1 text-sm text-red-500"></p>
                            </div>
                            <div>
                                <label for="edit_buy_currency" class="block text-sm font-medium text-gray-300 mb-1">
                                    Купля (валюта)
                                </label>
                                <div class="relative">
                                    <select
                                        name="buy_currency"
                                        id="edit_buy_currency"
                                        class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-cyan-500 appearance-none"
                                    >
                                        <option value="" disabled selected>— Выберите валюту —</option>
                                        @foreach($currenciesForEdit as $c)
                                            <option value="{{ $c->code }}">{{ $c->code }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>
                                <p id="err_buy_currency" class="mt-1 text-sm text-red-500"></p>
                            </div>

                            <!-- Расход -->
                            <div>
                                <label for="edit_expense_amount" class="block text-sm font-medium text-gray-300 mb-1">
                                    Расход (сумма)
                                </label>
                                <input
                                    type="number"
                                    step="0.00000001"
                                    name="expense_amount"
                                    id="edit_expense_amount"
                                    class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                                >
                                <p id="err_expense_amount" class="mt-1 text-sm text-red-500"></p>
                            </div>
                            <div>
                                <label for="edit_expense_currency" class="block text-sm font-medium text-gray-300 mb-1">
                                    Расход (валюта)
                                </label>
                                <div class="relative">
                                    <select
                                        name="expense_currency"
                                        id="edit_expense_currency"
                                        class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-cyan-500 appearance-none"
                                    >
                                        <option value="" disabled selected>— Выберите валюту —</option>
                                        @foreach($currenciesForEdit as $c)
                                            <option value="{{ $c->code }}">{{ $c->code }} — {{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </div>
                                <p id="err_expense_currency" class="mt-1 text-sm text-red-500"></p>
                            </div>

                            <!-- Мерчант -->
                            <div class="sm:col-span-2">
                                <label for="edit_merchant" class="block text-sm font-medium text-gray-300 mb-1">
                                    Мерчант
                                </label>
                                <input
                                    type="text"
                                    name="merchant"
                                    id="edit_merchant"
                                    class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                                >
                                <p id="err_merchant" class="mt-1 text-sm text-red-500"></p>
                            </div>

                            <!-- ID ордера -->
                            <div class="sm:col-span-2">
                                <label for="edit_order_id" class="block text-sm font-medium text-gray-300 mb-1">
                                    ID ордера
                                </label>
                                <input
                                    type="text"
                                    name="order_id"
                                    id="edit_order_id"
                                    class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                                >
                                <p id="err_order_id" class="mt-1 text-sm text-red-500"></p>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button
                                type="button"
                                id="closeEditModalBtn"
                                class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition"
                            >
                                Отмена
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-500 transition"
                            >
                                Сохранить
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Таблица --}}
            <!-- Обертка для скролла -->
            <div class="overflow-x-auto custom-scrollbar">
                <table id="applicationsTable" class="table-auto border-collapse whitespace-nowrap">
                    <thead class="bg-gray-100">
                    <tr>
                        @if(auth()->user()->role === 'admin')
                            <th class="px-5 py-3 text-xs font-semibold text-gray-700 uppercase whitespace-nowrap border-b border-gray-300">Действие</th>
                            <th class="px-5 py-3 text-xs font-semibold text-gray-700 uppercase whitespace-nowrap border-b border-gray-300">Кто изменил</th>
                        @endif
                        @foreach ([
                            'Номер заявки',
                            'Дата создания',
                            'Обменник',
                            'Статус',
                            'Приход+',
                            'Продажа−',
                            'Купля+',
                            'Расход−',
                            'Мерчант',
                            'ID ордера'
                        ] as $col)
                            <th class="px-5 py-3 text-xs font-semibold text-gray-700 uppercase whitespace-nowrap border-b border-gray-300">{{ $col }}</th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody id="appsTbody">
                    @foreach($apps as $d)
                        <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-blue-50 transition">
                            @if(auth()->user()->role === 'admin')
                                <td class="px-5 py-4 text-sm text-gray-800">
                                    <button class="editBtn btn btn-primary btn-sm" ...>Редактировать</button>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-800 text-center custom-tooltip" data-tooltip="Кто изменил">{{ $d->user->login ?? '-' }}</td>
                            @endif
                            <td class="px-5 py-4 text-base text-gray-900 text-center whitespace-nowrap font-bold custom-tooltip" data-tooltip="Номер заявки">{{ $d->app_id }}</td>
                            <td class="px-5 py-4 text-base text-gray-900 text-center whitespace-nowrap custom-tooltip" data-tooltip="Дата создания">{{ \Carbon\Carbon::parse($d->app_created_at)->format('d.m.Y H:i:s') }}</td>
                            <td class="px-5 py-4 text-base text-gray-900 text-center whitespace-nowrap custom-tooltip" data-tooltip="Обменник">{{ $d->exchanger }}</td>
                            <td class="px-5 py-4 text-base text-gray-900 text-center whitespace-nowrap custom-tooltip" data-tooltip="Статус">{{ $d->status }}</td>
                            <td class="px-5 py-4 text-lg whitespace-nowrap custom-tooltip" data-tooltip="Приход+">
                                @if($d->sale_text)
                                    @php [$amount, $curCode] = explode(' ', trim($d->sale_text), 2); $curCode = trim($curCode); @endphp
                                    <div class="inline-flex items-center space-x-1">
                                        <span class="text-green-500 font-bold">+{{ $amount }}</span>
                                        @if($curCode)
                                            <img src="{{ asset('images/coins/'.$curCode.'.svg') }}" alt="{{ $curCode }}" class="w-5 h-5">
                                        @endif
                                    </div>
                                @else — @endif
                            </td>
                            <td class="px-5 py-4 text-lg whitespace-nowrap custom-tooltip" data-tooltip="Продажа−">
                                @if(!is_null($d->sell_amount) && $d->sellCurrency)
                                    <div class="inline-flex items-center space-x-1">
                                        <span class="text-red-500 font-bold">-{{ rtrim(rtrim((string)$d->sell_amount,'0'),'.') }}</span>
                                        <img src="{{ asset('images/coins/'.$d->sellCurrency->code.'.svg') }}" alt="{{ $d->sellCurrency->code }}" class="w-5 h-5">
                                    </div>
                                @else — @endif
                            </td>
                            <td class="px-5 py-4 text-lg whitespace-nowrap custom-tooltip" data-tooltip="Купля+">
                                @if(!is_null($d->buy_amount) && $d->buyCurrency)
                                    @php $b = rtrim(rtrim((string)$d->buy_amount,'0'),'.'); @endphp
                                    <div class="inline-flex items-center space-x-1">
                                        <span class="{{ $d->buy_amount>0?'text-green-500':'text-red-500' }} font-bold">{{ $d->buy_amount>0?'+':'' }}{{ $b }}</span>
                                        <img src="{{ asset('images/coins/'.$d->buyCurrency->code.'.svg') }}" alt="{{ $d->buyCurrency->code }}" class="w-5 h-5">
                                    </div>
                                @else — @endif
                            </td>
                            <td class="px-5 py-4 text-lg whitespace-nowrap custom-tooltip" data-tooltip="Расход−">
                                @if(!is_null($d->expense_amount) && $d->expenseCurrency)
                                    <div class="inline-flex items-center space-x-1">
                                        <span class="text-red-500 font-bold">-{{ rtrim(rtrim((string)$d->expense_amount,'0'),'.') }}</span>
                                        <img src="{{ asset('images/coins/'.$d->expenseCurrency->code.'.svg') }}" alt="{{ $d->expenseCurrency->code }}" class="w-5 h-5">
                                    </div>
                                @else — @endif
                            </td>
                            <td class="px-5 py-4 text-base text-gray-900 text-center whitespace-nowrap custom-tooltip" data-tooltip="Мерчант">{{ $d->merchant ?? '—' }}</td>
                            <td class="px-5 py-4 text-base text-gray-900 text-center whitespace-nowrap custom-tooltip" data-tooltip="ID ордера">{{ $d->order_id ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- «Загрузить ещё» --}}
            <div id="loader" class="hidden mt-4 text-center">
                <svg class="animate-spin h-8 w-8 text-gray-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                          d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
            </div>

            <div class="text-center">
                <button
                    id="loadMoreBtn"
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                    data-next-page="{{ $apps->currentPage() + 1 }}"
                    data-has-more="{{ $apps->hasMorePages() ? 'true' : 'false' }}"
                >
                    Загрузить ещё
                </button>
            </div>

            {{-- Скрипты для модалки и «Загрузить ещё» --}}
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    // Установим флаг isAdmin, чтобы JS понимал, нужно ли отрисовывать колонки «Редактировать» и «Кто изменил»
                    window.isAdmin = @json(auth()->user()->role === 'admin');

                    // ========== USDT Total ==========
                    function fetchAndRenderUsdtTotal() {
                        fetch("{{ route('usdt.total') }}", {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                            .then(response => {
                                if (!response.ok) throw new Error("HTTP status " + response.status);
                                return response.json();
                            })
                            .then(json => {
                                const cell = document.getElementById('usdt-total-cell');
                                if (!cell) return;

                                cell.classList.remove('positive', 'negative');
                                let val = json.usdt_total;
                                let sign = '';
                                if (val > 0) sign = '+';
                                else if (val < 0) sign = '-';
                                val = Math.abs(val).toFixed(8).replace(/\.?0+$/, '');
                                cell.textContent = sign + val;
                                if (val !== '0') {
                                    if (sign === '+') cell.classList.add('positive');
                                    else if (sign === '-') cell.classList.add('negative');
                                }
                            })
                            .catch(err => console.error("Ошибка при получении usdt_total:", err));
                    }

                    fetchAndRenderUsdtTotal();
                    setInterval(fetchAndRenderUsdtTotal, 5000);

                    window.onerror = function (message, source, lineno, colno, error) {
                        console.error(`JS Error: ${message} at ${source}:${lineno}:${colno}`, error);
                    };

                    // ========== Модальное окно редактирования ==========
                    const editModal = document.getElementById('editModalBackdrop');
                    const closeEditModalBtn = document.getElementById('closeEditModalBtn');
                    const editModalBackdropClose = document.getElementById('editModalBackdropClose');
                    const modalAppIdLabel = document.getElementById('modalAppId');
                    const editForm = document.getElementById('editForm');

                    function showEditModal() {
                        editModal.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                    }

                    function hideEditModal() {
                        editModal.classList.add('hidden');
                        document.body.style.overflow = '';
                        ['sell_amount', 'sell_currency', 'buy_amount', 'buy_currency', 'expense_amount', 'expense_currency', 'merchant', 'order_id']
                            .forEach(f => {
                                const errEl = document.getElementById('err_' + f);
                                if (errEl) errEl.textContent = '';
                            });
                    }

                    closeEditModalBtn.addEventListener('click', hideEditModal);
                    editModalBackdropClose.addEventListener('click', hideEditModal);
                    document.addEventListener('keydown', (e) => {
                        if (!editModal.classList.contains('hidden') && e.key === 'Escape') {
                            hideEditModal();
                        }
                    });

                    // Навешиваем «Редактировать» на все кнопки (в том числе динамически добавленные)
                    function attachEditHandlers(root = document) {
                        root.querySelectorAll('.editBtn').forEach(button => {
                            if (button.dataset.listenerAdded) return;
                            button.dataset.listenerAdded = 'true';

                            button.addEventListener('click', () => {
                                const id = button.dataset.id;
                                const appId = button.dataset.app_id;
                                const saleAmount = button.dataset.sale_amount;
                                const saleCurrency = button.dataset.sale_currency;
                                const sellAmount = button.dataset.sell_amount;
                                const sellCurrency = button.dataset.sell_currency;
                                const buyAmount = button.dataset.buy_amount;
                                const buyCurrency = button.dataset.buy_currency;
                                const expenseAmount = button.dataset.expense_amount;
                                const expenseCurrency = button.dataset.expense_currency;
                                const merchant = button.dataset.merchant;
                                const orderId = button.dataset.order_id;

                                document.getElementById('edit_app_id').value = id;
                                modalAppIdLabel.textContent = `#${appId}`;
                                                                document.getElementById('edit_sale_amount').value = saleAmount || '';
                                document.getElementById('edit_sale_currency').value = saleCurrency || '';
                                document.getElementById('edit_sell_amount').value = sellAmount || '';
                                document.getElementById('edit_buy_amount').value = buyAmount || '';
                                document.getElementById('edit_expense_amount').value = expenseAmount || '';
                                document.getElementById('edit_merchant').value = merchant || '';
                                document.getElementById('edit_order_id').value = orderId || '';
                                document.getElementById('edit_sell_currency').value = sellCurrency || '';
                                document.getElementById('edit_buy_currency').value = buyCurrency || '';
                                document.getElementById('edit_expense_currency').value = expenseCurrency || '';

                                showEditModal();
                            });
                        });
                    }

                    attachEditHandlers();

                    // Отправка формы редактирования
                    editForm.addEventListener('submit', function (e) {
                        e.preventDefault();
                        ['sale_amount', 'sale_currency', 'sell_amount', 'sell_currency', 'buy_amount', 'buy_currency', 'expense_amount', 'expense_currency', 'merchant', 'order_id']
                            .forEach(f => {
                                const errEl = document.getElementById('err_' + f);
                                if (errEl) errEl.textContent = '';
                            });

                        const id = document.getElementById('edit_app_id').value;
                        const data = {
                            sale_amount: document.getElementById('edit_sale_amount').value.trim(),
                            sale_currency: document.getElementById('edit_sale_currency').value.trim(),
                            sell_amount: document.getElementById('edit_sell_amount').value.trim(),
                            sell_currency: document.getElementById('edit_sell_currency').value.trim(),
                            buy_amount: document.getElementById('edit_buy_amount').value.trim(),
                            buy_currency: document.getElementById('edit_buy_currency').value.trim(),
                            expense_amount: document.getElementById('edit_expense_amount').value.trim(),
                            expense_currency: document.getElementById('edit_expense_currency').value.trim(),
                            merchant: document.getElementById('edit_merchant').value.trim(),
                            order_id: document.getElementById('edit_order_id').value.trim(),
                        };

                        fetch(`/applications/${id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(data)
                        })
                            .then(res => {
                                if (res.ok) return res.json();
                                if (res.status === 422) return res.json().then(json => Promise.reject({validation: json.errors}));
                                throw new Error(`Статус ${res.status}`);
                            })
                            .then(() => {
                                window.location.reload();
                            })
                            .catch(err => {
                                if (err.validation) {
                                    Object.entries(err.validation).forEach(([field, messages]) => {
                                        const el = document.getElementById('err_' + field);
                                        if (el) el.textContent = messages[0];
                                    });
                                } else {
                                    console.error('JS: Ошибка редактирования:', err);
                                    alert('Не удалось сохранить изменения');
                                }
                            });
                    });

                    // ========== Динамическая подгрузка «Загрузить ещё» ==========
                    function loadMoreApplications() {
                        const btn = document.getElementById('loadMoreBtn');
                        if (!btn || btn.dataset.hasMore !== 'true') return;

                        const nextPage = btn.dataset.nextPage;
                        btn.disabled = true;
                        btn.textContent = 'Загрузка...';

                        fetch(`{{ route('api.applications') }}?page=${nextPage}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                            .then(r => {
                                if (!r.ok) throw new Error(r.status);
                                return r.json();
                            })
                            .then(json => {
                                const tbody = document.getElementById('appsTbody');

                                json.data.forEach(d => {
                                    // старт строки
                                    let rowHtml = `<tr class="bg-[#191919] hover:bg-gray-700">`;

                                    if (window.isAdmin) {
                                        // Действие (Редактировать)
                                        rowHtml += `
                <td class="px-5 py-4 text-sm text-gray-200 whitespace-nowrap">
                    <button
                        class="editBtn px-3 py-1 bg-cyan-600 text-white rounded-md hover:bg-cyan-700 transition text-xs"
                        data-id="${d.id}"
                        data-app_id="${d.app_id}"
                        data-sale_amount="${d.sale_amount ?? ''}"
                        data-sale_currency="${d.sale_currency ?? ''}"
                        data-sell_amount="${d.sell_amount ?? ''}"
                        data-sell_currency="${d.sell_currency?.code ?? ''}"
                        data-buy_amount="${d.buy_amount ?? ''}"
                        data-buy_currency="${d.buy_currency?.code ?? ''}"
                        data-expense_amount="${d.expense_amount ?? ''}"
                        data-expense_currency="${d.expense_currency?.code ?? ''}"
                        data-merchant="${d.merchant ?? ''}"
                        data-order_id="${d.order_id ?? ''}"
                    >Редактировать</button>
                </td>`;

                                        // Кто изменил
                                        const who = d.user?.login ?? '-';
                                        rowHtml += `
                <td class="px-5 py-4 text-sm text-gray-200 text-center whitespace-nowrap">
                    ${who}
                </td>`;
                                    }

                                    // Номер заявки
                                    rowHtml += `
            <td class="px-5 py-4 text-sm text-gray-200 text-center whitespace-nowrap">
                ${d.app_id}
            </td>`;

                                    // Дата создания
                                    const dt = new Date(d.app_created_at);
                                    const fmt = `${String(dt.getDate()).padStart(2, '0')}.${String(dt.getMonth() + 1).padStart(2, '0')}.${dt.getFullYear()} ${String(dt.getHours()).padStart(2, '0')}:${String(dt.getMinutes()).padStart(2, '0')}:${String(dt.getSeconds()).padStart(2, '0')}`;
                                    rowHtml += `
            <td class="px-5 py-4 text-sm text-gray-200 text-center whitespace-nowrap">
                ${fmt}
            </td>`;

                                    // Обменник
                                    rowHtml += `
            <td class="px-5 py-4 text-sm text-gray-200 text-center whitespace-nowrap">
                ${d.exchanger}
            </td>`;

                                    // Статус
                                    rowHtml += `
            <td class="px-5 py-4 text-sm text-gray-200 text-center whitespace-nowrap">
                ${d.status}
            </td>`;

                                    // Приход+
                                    if (d.sale_text) {
                                        const [amt, cur] = d.sale_text.trim().split(' ');
                                        rowHtml += `
                <td class="px-5 py-4 text-sm text-gray-200 whitespace-nowrap">
                    <div class="inline-flex items-center space-x-1">
                        <span class="text-green-400">+${amt}</span>
                        ${cur ? `<img src="/images/coins/${cur}.svg" alt="${cur}" class="w-4 h-4">` : ''}
                    </div>
                </td>`;
                                    } else {
                                        rowHtml += `<td class="px-5 py-4 text-sm text-gray-200 whitespace-nowrap">—</td>`;
                                    }

                                    // Продажа−
                                    if (d.sell_amount !== null && d.sell_currency) {
                                        const sell = String(d.sell_amount).replace(/\.?0+$/, '').replace(/^-/, '');
                                        rowHtml += `
                <td class="px-5 py-4 text-sm text-gray-200 whitespace-nowrap">
                    <div class="inline-flex items-center space-x-1">
                        <span class="text-red-400">-${sell}</span>
                        <img src="/images/coins/${d.sell_currency.code}.svg" alt="${d.sell_currency.code}" class="w-4 h-4">
                    </div>
                </td>`;
                                    } else {
                                        rowHtml += `<td class="px-5 py-4 text-sm text-gray-200 whitespace-nowrap">—</td>`;
                                    }

                                    // Купля+
                                    if (d.buy_amount !== null && d.buy_currency) {
                                        const buy = String(d.buy_amount).replace(/\.?0+$/, '').replace(/^-/, '');
                                        const sign = d.buy_amount > 0 ? '+' : '-';
                                        const cls = d.buy_amount > 0 ? 'text-green-400' : 'text-red-400';
                                        rowHtml += `
                <td class="px-5 py-4 text-sm text-gray-200 whitespace-nowrap">
                    <div class="inline-flex items-center space-x-1">
                        <span class="${cls}">${sign}${buy}</span>
                        <img src="/images/coins/${d.buy_currency.code}.svg" alt="${d.buy_currency.code}" class="w-4 h-4">
                    </div>
                </td>`;
                                    } else {
                                        rowHtml += `<td class="px-5 py-4 text-sm text-gray-200 whitespace-nowrap">—</td>`;
                                    }

                                    // Расход−
                                    if (d.expense_amount !== null && d.expense_currency) {
                                        const exp = String(d.expense_amount).replace(/\.?0+$/, '').replace(/^-/, '');
                                        rowHtml += `
                <td class="px-5 py-4 text-sm text-gray-200 whitespace-nowrap">
                    <div class="inline-flex items-center space-x-1">
                        <span class="text-red-400">-${exp}</span>
                        <img src="/images/coins/${d.expense_currency.code}.svg" alt="${d.expense_currency.code}" class="w-4 h-4">
                    </div>
                </td>`;
                                    } else {
                                        rowHtml += `<td class="px-5 py-4 text-sm text-gray-200 whitespace-nowrap">—</td>`;
                                    }

                                    // Мерчант
                                    rowHtml += `
            <td class="px-5 py-4 text-sm text-gray-200 text-center whitespace-nowrap">
                ${d.merchant ?? '—'}
            </td>`;

                                    // ID ордера
                                    rowHtml += `
            <td class="px-5 py-4 text-sm text-gray-200 text-center whitespace-nowrap">
                ${d.order_id ?? '—'}
            </td>`;

                                    // закрываем строку
                                    rowHtml += `</tr>`;

                                    tbody.insertAdjacentHTML('beforeend', rowHtml);
                                });

                                btn.dataset.nextPage = parseInt(nextPage) + 1;
                                btn.dataset.hasMore = json.has_more ? 'true' : 'false';
                                btn.disabled = false;
                                btn.textContent = json.has_more ? 'Загрузить ещё' : 'Больше заявок нет';

                                attachEditHandlers();
                            })
                            .catch(err => {
                                console.error("Ошибка при подгрузке ещё заявок:", err);
                                btn.disabled = false;
                                btn.textContent = 'Загрузить ещё';
                            });
                    }

                    document.getElementById('loadMoreBtn').addEventListener('click', loadMoreApplications);
                });
            </script>

            {{-- ◆========== Второй блок: «История операций» (банковский стиль) ==========◆ --}}
            <div class="bg-[#191919] rounded-xl shadow-md border border-[#2d2d2d]">
                <div class="px-6 py-4 border-b border-[#2d2d2d]">
                    <h2 class="text-2xl font-semibold text-white">История операций</h2>
                </div>
                <div class="divide-y divide-[#2d2d2d]">
                    @forelse($histories as $history)
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
                        <div class="flex items-center justify-between px-6 py-4 bg-[#191919] hover:bg-gray-800 transition"
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
                            <div class="flex-1 min-w-0">
                                <div class="text-white font-medium truncate">
                                    {{ $exchanger }}
                                    </div>
                                <div class="text-xs text-gray-400 mt-1">
                                    <span>{{ $date }}</span>
                                    </div>
                            </div>
                            <div class="flex items-center gap-2 ml-4">
                                <span class="text-lg font-bold {{ $amount > 0 ? 'text-green-400' : 'text-red-400' }}">{{ $sign }}{{ $formatted }}</span>
                                @if($currency)
                                    <img src="{{ $icon }}" alt="{{ $currency->code }}" class="w-6 h-6" onerror="this.style.display='none';this.insertAdjacentHTML('afterend', '<span>{{ $currency->code }}</span>')">
                                @endif
                </div>
                </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-400">Нет операций</div>
                @endforelse
            </div>
        </div>

        {{-- Кнопка "Показать все" --}}
        <div class="flex justify-center mt-6">
            <a href="{{ route('history.all') }}" class="px-6 py-3 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg font-medium transition-colors">
                Показать всю историю операций
            </a>
        </div>

        {{-- Итого USDT по дням — Flowbite Chart --}}
        <div class="bg-[#191919] rounded-xl shadow-md p-6 border border-[#2d2d2d] mb-6">
            <h2 class="text-2xl font-semibold text-white mb-4">Итоги USDT по дням</h2>
            <div class="flex gap-4 mb-6">
                <div>
                    <label class="block text-sm text-gray-400 mb-1">От</label>
                    <input type="date" id="start_date"
                           class="bg-gray-800 text-white px-3 py-2 rounded-lg border border-gray-600"
                           value="{{ now()->subDays(6)->format('Y-m-d') }}">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">До</label>
                    <input type="date" id="end_date"
                           class="bg-gray-800 text-white px-3 py-2 rounded-lg border border-gray-600"
                           value="{{ now()->format('Y-m-d') }}">
                </div>
            </div>
            @php
                $datasets = [[
                    'label' => 'USDT',
                    'data' => $data,
                    'backgroundColor' => 'rgba(79, 70, 229, 0.2)',
                    'borderColor' => 'rgb(79, 70, 229)',
                    'pointBackgroundColor' => $pointColors,
                    'pointRadius' => 6,
                    'pointHoverRadius' => 8,
                    'tension' => 0.4,
                    'fill' => true,
                ]];
            @endphp

            <div class="relative h-[400px]">
                <canvas
                    id="lineChart"
                    class="w-full h-64"
                    data-labels='@json($labels)'
                    data-datasets='@json($datasets)'
                ></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- 1) Переводы --}}
            <div class="bg-[#191919] rounded-xl shadow-md overflow-x-auto border border-[#2d2d2d]">
                <div class="px-6 py-4 border-b border-[#2d2d2d]">
                    <h2 class="text-2xl font-semibold text-white">Переводы</h2>
                </div>
                <table id="transfersTable" class="min-w-full table-auto border-collapse">
                    <thead class="bg-[#191919]">
                    <tr class="sticky top-0">
                        @if(auth()->user()->role === 'admin')
                            <th class="px-5 py-3 text-xs font-semibold text-white uppercase border-b border-[#2d2d2d]">
                                Действие
                            </th>
                        @endif
                        <th class="px-5 py-3 text-xs font-semibold text-white uppercase border-b border-[#2d2d2d]">
                            Откуда
                        </th>
                        <th class="px-5 py-3 text-xs font-semibold text-white uppercase border-b border-[#2d2d2d]">
                            Куда
                        </th>
                        <th class="px-5 py-3 text-xs font-semibold text-white uppercase border-b border-[#2d2d2d]">
                            Сумма
                        </th>
                        <th class="px-5 py-3 text-xs font-semibold text-white uppercase border-b border-[#2d2d2d]">
                            Комиссия
                        </th>
                    </tr>
                    </thead>
                    <tbody id="transfersTbody" class="bg-gray-800 divide-y divide-[#2d2d2d]">
                    @foreach($transfers as $t)
                        <tr class="bg-[#191919] hover:bg-gray-700">
                            @if(auth()->user()->role === 'admin')
                                <td class="px-5 py-4 whitespace-nowrap space-x-2">
                                    <button class="edit-transfer-btn px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs"
                                            data-id="{{ $t->id }}"
                                            data-from-id="{{ $t->exchanger_from_id }}"
                                            data-to-id="{{ $t->exchanger_to_id }}"
                                            data-amount="{{ $t->amount }}"
                                            data-amount-currency-id="{{ $t->amount_currency_id }}"
                                            data-commission="{{ $t->commission }}"
                                            data-commission-currency-id="{{ $t->commission_currency_id }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path d="M17 3a2.828 2.828 0 014 4L7 21H3v-4L17 3z"></path>
                                        </svg>
                                    </button>
                                    <button class="delete-transfer-btn px-2 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-xs"
                                            data-id="{{ $t->id }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </td>
                            @endif
                            <td class="px-5 py-4 text-sm whitespace-nowrap">{{ $t->exchangerFrom->title ?? '—' }}</td>
                            <td class="px-5 py-4 text-sm whitespace-nowrap">{{ $t->exchangerTo->title ?? '—' }}</td>
                            <td class="px-5 py-4 text-sm whitespace-nowrap">
                                @if(!is_null($t->amount))
                                    @php
                                        $amt     = rtrim(rtrim((string)$t->amount, '0'), '.');
                                        $codeAmt = optional($t->amountCurrency)->code;
                                        $pathAmt = public_path("images/coins/{$codeAmt}.svg");
                                        $urlAmt  = asset("images/coins/{$codeAmt}.svg");
                                    @endphp
                                    <div class="inline-flex items-center space-x-1">
                                        <span class="text-white">{{ ltrim($amt,'-') }}</span>
                                        @if($codeAmt && file_exists($pathAmt))
                                            <img src="{{ $urlAmt }}" class="w-4 h-4" alt="{{ $codeAmt }}">
                                        @else
                                            <span class="text-white">{{ $codeAmt }}</span>
                                        @endif
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm whitespace-nowrap">
                                @if(!is_null($t->commission))
                                    @php
                                        $comm     = rtrim(rtrim((string)$t->commission, '0'), '.');
                                        $codeComm = optional($t->commissionCurrency)->code;
                                        $pathComm = public_path("images/coins/{$codeComm}.svg");
                                        $urlComm  = asset("images/coins/{$codeComm}.svg");
                                    @endphp
                                    <div class="inline-flex items-center space-x-1">
                                        <span class="text-red-400">-{{ ltrim($comm,'-') }}</span>
                                        @if($codeComm && file_exists($pathComm))
                                            <img src="{{ $urlComm }}" class="w-4 h-4" alt="{{ $codeComm }}">
                                        @else
                                            <span class="text-white">{{ $codeComm }}</span>
                                        @endif
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-2 text-center">
                    <button id="loadMoreTransfers" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                            data-next-page="{{ $transfers->currentPage()+1 }}"
                            data-has-more="{{ $transfers->hasMorePages() ? 'true' : 'false' }}">
                        Загрузить ещё
                    </button>
                </div>
            </div>

            {{-- 2) Оплата --}}
            <div class="bg-[#191919] rounded-xl shadow-md overflow-x-auto border border-[#2d2d2d]">
                <div class="px-6 py-4 border-b border-[#2d2d2d]">
                    <h2 class="text-2xl font-semibold text-white">Оплата</h2>
                </div>
                <table id="paymentsTable" class="min-w-full table-auto border-collapse">
                    <thead class="bg-[#191919]">
                    <tr class="sticky top-0">
                        @if(auth()->user()->role === 'admin')
                            <th class="px-5 py-3 text-xs font-semibold text-white uppercase border-b border-[#2d2d2d]">
                                Действие
                            </th>
                        @endif
                        <th class="px-5 py-3 text-xs font-semibold text-white uppercase border-b border-[#2d2d2d]">
                            Платформа
                        </th>
                        <th class="px-5 py-3 text-xs font-semibold text-white uppercase border-b border-[#2d2d2d]">
                            Сумма
                        </th>
                        <th class="px-5 py-3 text-xs font-semibold text-white uppercase border-b border-[#2d2d2d]">
                            Комментарий
                        </th>
                    </tr>
                    </thead>
                    <tbody id="paymentsTbody" class="bg-gray-800 divide-y divide-[#2d2d2d]">
                    @foreach($payments as $p)
                        <tr class="bg-[#191919] hover:bg-gray-700">
                            @if(auth()->user()->role === 'admin')
                                <td class="px-5 py-4 whitespace-nowrap space-x-2">
                                    <button class="edit-payment-btn px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs"
                                            data-id="{{ $p->id }}"
                                            data-amount="{{ $p->sell_amount }}"
                                            data-comment="{{ $p->comment }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path d="M17 3a2.828 2.828 0 014 4L7 21H3v-4L17 3z"></path>
                                        </svg>
                                    </button>
                                    <button class="delete-payment-btn px-2 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-xs"
                                            data-id="{{ $p->id }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </td>
                            @endif
                            <td class="px-5 py-4 text-sm whitespace-nowrap">{{ $p->platform ?? '—' }}</td>
                            <td class="px-5 py-4 text-sm whitespace-nowrap">
                                @if(!is_null($p->sell_amount))
                                    @php
                                        $amt  = rtrim(rtrim((string)$p->sell_amount, '0'), '.');
                                        $code = optional($p->sellCurrency)->code;
                                        $path = public_path("images/coins/{$code}.svg");
                                        $url  = asset("images/coins/{$code}.svg");
                                    @endphp
                                    <div class="inline-flex items-center space-x-1">
                                        <span class="text-red-400">-{{ ltrim($amt, '-') }}</span>
                                        @if($code && file_exists($path))
                                            <img src="{{ $url }}" alt="{{ $code }}" class="w-4 h-4">
                                        @else
                                            <span class="text-white">{{ $code }}</span>
                                        @endif
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-4 text-white">{{ $p->comment ?? '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-2 text-center">
                    <button id="loadMorePayments" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                            data-next-page="{{ $payments->currentPage()+1 }}"
                            data-has-more="{{ $payments->hasMorePages() ? 'true' : 'false' }}">
                        Загрузить ещё
                    </button>
                </div>
            </div>

            {{-- 3) Покупка крипты --}}
            <div class="bg-[#191919] rounded-xl shadow-md overflow-x-auto border border-[#2d2d2d]">
                <div class="px-6 py-4 border-b border-[#2d2d2d]">
                    <h2 class="text-2xl font-semibold text-white">Покупка крипты</h2>
                </div>
                <table id="purchasesTable" class="min-w-full table-auto border-collapse">
                    <thead class="bg-[#191919]">
                    <tr class="sticky top-0">
                        @if(auth()->user()->role === 'admin')
                            <th class="px-5 py-3 text-xs font-semibold text-white uppercase border-b border-[#2d2d2d]">
                                Действие
                            </th>
                        @endif
                        <th class="px-5 py-3 text-xs font-semibold text-white uppercase border-b border-[#2d2d2d]">
                            Платформа
                        </th>
                        <th class="px-5 py-3 text-xs font-semibold text-white uppercase border-b border-[#2d2d2d]">
                            Получено +
                        </th>
                        <th class="px-5 py-3 text-xs font-semibold text-white uppercase border-b border-[#2d2d2d]">
                            Продано −
                        </th>
                        <th class="px-5 py-3 text-xs font-semibold text-white uppercase border-b border-[#2d2d2d]">
                            Заявка
                        </th>
                    </tr>
                    </thead>
                    <tbody id="purchasesTbody" class="bg-gray-800 divide-y divide-[#2d2d2d]">
                    @foreach($purchases as $pc)
                        <tr class="bg-[#191919] hover:bg-gray-700">
                            @if(auth()->user()->role === 'admin')
                                <td class="px-5 py-4 whitespace-nowrap space-x-2">
                                    <button class="edit-purchase-btn px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs"
                                            data-id="{{ $pc->id }}"
                                            data-exchanger-id="{{ $pc->exchanger_id }}"
                                            data-received-amount="{{ $pc->received_amount }}"
                                            data-received-currency-id="{{ $pc->received_currency_id }}"
                                            data-sale-amount="{{ $pc->sale_amount }}"
                                            data-sale-currency-id="{{ $pc->sale_currency_id }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path d="M17 3a2.828 2.828 0 014 4L7 21H3v-4L17 3z"></path>
                                        </svg>
                                    </button>
                                    <button class="delete-purchase-btn px-2 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-xs"
                                            data-id="{{ $pc->id }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </td>
                            @endif
                            <td class="px-5 py-4 text-sm whitespace-nowrap">{{ $pc->platform ?? '—' }}</td>
                            <td class="px-5 py-4 text-sm whitespace-nowrap">
                                @if(!is_null($pc->received_amount))
                                    @php
                                        $recvAmt  = rtrim(rtrim((string)$pc->received_amount, '0'), '.');
                                        $codeRecv = optional($pc->receivedCurrency)->code;
                                        $pathRecv = public_path("images/coins/{$codeRecv}.svg");
                                        $urlRecv  = asset("images/coins/{$codeRecv}.svg");
                                    @endphp
                                    <div class="inline-flex items-center space-x-1">
                                        <span class="text-green-400">+{{ $recvAmt }}</span>
                                        @if($codeRecv && file_exists($pathRecv))
                                            <img src="{{ $urlRecv }}" alt="{{ $codeRecv }}" class="w-4 h-4">
                                        @else
                                            <span class="text-white">{{ $codeRecv }}</span>
                                        @endif
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm whitespace-nowrap">
                                @if(!is_null($pc->sale_amount))
                                    @php
                                        $saleAmt  = rtrim(rtrim((string)$pc->sale_amount, '0'), '.');
                                        $codeSale = optional($pc->saleCurrency)->code;
                                        $pathSale = public_path("images/coins/{$codeSale}.svg");
                                        $urlSale  = asset("images/coins/{$codeSale}.svg");
                                    @endphp
                                    <div class="inline-flex items-center space-x-1">
                                        <span class="text-red-400">-{{ $saleAmt }}</span>
                                        @if($codeSale && file_exists($pathSale))
                                            <img src="{{ $urlSale }}" alt="{{ $codeSale }}" class="w-4 h-4">
                                        @else
                                            <span class="text-white">{{ $codeSale }}</span>
                                        @endif
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-200 text-center whitespace-nowrap">
                                <button class="details-btn text-cyan-400 hover:underline" data-id="{{ $pc->application_id }}">
                                    #{{ $pc->application_id }}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-2 text-center">
                    <button id="loadMorePurchases" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                            data-next-page="{{ $purchases->currentPage()+1 }}"
                            data-has-more="{{ $purchases->hasMorePages() ? 'true' : 'false' }}">
                        Загрузить ещё
                    </button>
                </div>
            </div>

            {{-- 4) Продажа крипты --}}
            <div class="bg-[#191919] rounded-xl shadow-md overflow-x-auto border border-[#2d2d2d]">
                <div class="px-6 py-4 border-b border-[#2d2d2d]">
                    <h2 class="text-2xl font-semibold text-white">Продажа крипты</h2>
                </div>
                <table id="saleCryptsTable" class="min-w-full table-auto border-collapse">
                    <thead class="bg-[#191919]">
                    <tr class="sticky top-0">
                        @if(auth()->user()->role === 'admin')
                            <th class="px-5 py-3 text-xs font-semibold text-white uppercase border-b border-[#2d2d2d]">
                                Действие
                            </th>
                        @endif
                        <th class="px-5 py-3 text-xs font-semibold text-white uppercase border-b border-[#2d2d2d]">
                            Платформа
                        </th>
                        <th class="px-5 py-3 text-xs font-semibold text-white uppercase border-b border-[#2d2d2d]">
                            Продажа −
                        </th>
                        <th class="px-5 py-3 text-xs font-semibold text-white uppercase border-b border-[#2d2d2d]">
                            Получено +
                        </th>
                        <th class="px-5 py-3 text-xs font-semibold text-white uppercase border-b border-[#2d2d2d]">
                            Заявка
                        </th>
                    </tr>
                    </thead>
                    <tbody id="saleCryptsTbody" class="bg-gray-800 divide-y divide-[#2d2d2d]">
                    @foreach($saleCrypts as $sc)
                        <tr class="bg-[#191919] hover:bg-gray-700">
                            @if(auth()->user()->role === 'admin')
                                <td class="px-5 py-4 whitespace-nowrap space-x-2">
                                    <button class="edit-salecrypt-btn px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs"
                                            data-id="{{ $sc->id }}"
                                            data-sale-amount="{{ $sc->sale_amount }}"
                                            data-sale-currency-id="{{ $sc->sale_currency_id }}"
                                            data-fixed-amount="{{ $sc->fixed_amount }}"
                                            data-fixed-currency-id="{{ $sc->fixed_currency_id }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path d="M17 3a2.828 2.828 0 014 4L7 21H3v-4L17 3z"></path>
                                        </svg>
                                    </button>
                                    <button class="delete-salecrypt-btn px-2 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-xs"
                                            data-id="{{ $sc->id }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </td>
                            @endif
                            <td class="px-5 py-4 text-sm whitespace-nowrap">{{ $sc->platform ?? '—' }}</td>
                            <td class="px-5 py-4 text-sm whitespace-nowrap">
                                @if(!is_null($sc->sale_amount))
                                    @php
                                        $saleAmt  = rtrim(rtrim((string)$sc->sale_amount, '0'), '.');
                                        $codeSale = optional($sc->saleCurrency)->code;
                                        $pathSale = public_path("images/coins/{$codeSale}.svg");
                                        $urlSale  = asset("images/coins/{$codeSale}.svg");
                                    @endphp
                                    <div class="inline-flex items-center space-x-1">
                                        <span class="text-red-400">-{{ $saleAmt }}</span>
                                        @if($codeSale && file_exists($pathSale))
                                            <img src="{{ $urlSale }}" alt="{{ $codeSale }}" class="w-4 h-4">
                                        @else
                                            <span class="text-white">{{ $codeSale }}</span>
                                        @endif
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm whitespace-nowrap">
                                @if(!is_null($sc->fixed_amount))
                                    @php
                                        $fa     = rtrim(rtrim((string)$sc->fixed_amount, '0'), '.');
                                        $codeFa = optional($sc->fixedCurrency)->code;
                                        $pathFa = public_path("images/coins/{$codeFa}.svg");
                                        $urlFa  = asset("images/coins/{$codeFa}.svg");
                                    @endphp
                                    <div class="inline-flex items-center space-x-1">
                                        <span class="{{ $sc->fixed_amount>0 ? 'text-green-400':'text-red-400' }}">
                                          {{ $sc->fixed_amount>0?'+':'' }}{{ ltrim($fa,'-') }}
                                        </span>
                                        @if($codeFa && file_exists($pathFa))
                                            <img src="{{ $urlFa }}" alt="{{ $codeFa }}" class="w-4 h-4">
                                        @else
                                            <span class="text-white">{{ $codeFa }}</span>
                                        @endif
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-200 text-center whitespace-nowrap">
                                <button class="details-btn text-cyan-400 hover:underline" data-id="{{ $sc->application_id }}">
                                    #{{ $sc->application_id }}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-2 text-center">
                    <button id="loadMoreSaleCrypts" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                            data-next-page="{{ $saleCrypts->currentPage()+1 }}"
                            data-has-more="{{ $saleCrypts->hasMorePages() ? 'true' : 'false' }}">
                        Загрузить ещё
                    </button>
                </div>
            </div>
        </div>
    </div>
    @include('modal.edit-payment')
    @include('modal.edit-purchase')
    @include('modal.edit-salecrypt')
    @include('modal.edit-transfer')
    @include('modal.show-application')
    @vite(['resources/js/crud.js'])
@endsection


@push('scripts')
<script>
document.querySelectorAll('.custom-tooltip').forEach(td => {
  const label = td.getAttribute('data-tooltip');
  if (!label) return;
  let tip;
  function showTooltip(e) {
    tip = document.createElement('div');
    tip.className = 'tooltip-box';
    tip.textContent = label;
    document.body.appendChild(tip);
    const rect = td.getBoundingClientRect();
    const tipRect = tip.getBoundingClientRect();
    let left = rect.left + rect.width/2 - tipRect.width/2;
    let top = rect.top - tipRect.height - 8;
    if (left < 8) left = 8;
    if (left + tipRect.width > window.innerWidth - 8) left = window.innerWidth - tipRect.width - 8;
    if (top < 8) top = rect.bottom + 8;
    tip.style.left = left + 'px';
    tip.style.top = top + 'px';
    tip.style.opacity = '1';
    tip.style.transform = 'scale(1)';
    console.log('TOOLTIP SHOWN:', label);
  }
  function hideTooltip() {
    if (tip) {
      tip.remove();
      tip = null;
      console.log('TOOLTIP HIDE');
    }
  }
  td.addEventListener('mouseenter', showTooltip);
  td.addEventListener('mouseleave', hideTooltip);
  td.addEventListener('focus', showTooltip);
  td.addEventListener('blur', hideTooltip);
});
</script>
@endpush
