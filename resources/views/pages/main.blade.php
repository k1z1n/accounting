{{-- resources/views/pages/main.blade.php --}}
@extends('template.app')

@section('title', 'История заявок')

@section('content')
    <div class="container mx-auto px-4 py-6 space-y-8">
        @include('pages.other')

        {{-- ◆========== Первый блок: «История заявок» (Applications) ==========◆ --}}
        <div class="bg-[#191919] rounded-xl shadow-md overflow-x-auto border border-[#2d2d2d]">
            {{-- Заголовок блока --}}
            <div class="px-6 py-4 border-b border-[#2d2d2d]">
                <h2 class="text-2xl font-semibold text-white">История заявок</h2>
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
                                        @foreach($currencies as $c)
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
                                        @foreach($currencies as $c)
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
                                        @foreach($currencies as $c)
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
            <table id="applicationsTable" class="min-w-full table-auto border-collapse">
                <thead class="bg-[#191919]">
                <tr class="sticky top-0">
                    @if(auth()->user()->role === 'admin')
                        <th class="px-5 py-3 text-xs font-semibold text-white uppercase whitespace-nowrap border-b border-[#2d2d2d]">
                            Действие
                        </th>
                        <th class="px-5 py-3 text-xs font-semibold text-white uppercase whitespace-nowrap border-b border-[#2d2d2d]">
                            Кто изменил
                        </th>
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
                        <th class="px-5 py-3 text-xs font-semibold text-white uppercase whitespace-nowrap border-b border-[#2d2d2d]">
                            {{ $col }}
                        </th>
                    @endforeach
                </tr>
                </thead>
                <tbody id="appsTbody" class="bg-gray-800 divide-y divide-[#2d2d2d]">
                @foreach($apps as $d)
                    <tr class="bg-[#191919] hover:bg-gray-700">
                        @if(auth()->user()->role === 'admin')
                            <td class="px-5 py-4 text-sm text-gray-200">
                                <button
                                    class="editBtn px-3 py-1 bg-cyan-600 text-white rounded-md hover:bg-cyan-700 transition text-xs"
                                    data-id="{{ $d->id }}"
                                    data-app_id="{{ $d->app_id }}"
                                    data-sell_amount="{{ $d->sell_amount }}"
                                    data-sell_currency="{{ optional($d->sellCurrency)->code }}"
                                    data-buy_amount="{{ $d->buy_amount }}"
                                    data-buy_currency="{{ optional($d->buyCurrency)->code }}"
                                    data-expense_amount="{{ $d->expense_amount }}"
                                    data-expense_currency="{{ optional($d->expenseCurrency)->code }}"
                                    data-merchant="{{ $d->merchant }}"
                                    data-order_id="{{ $d->order_id }}"
                                >Редактировать
                                </button>
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-200 text-center">
                                {{ $d->user->login ?? '-' }}
                            </td>
                        @endif

                        {{-- Номер заявки --}}
                        <td class="px-5 py-4 text-sm text-gray-200 text-center whitespace-nowrap">{{ $d->app_id }}</td>

                        {{-- Дата создания --}}
                        <td class="px-5 py-4 text-sm text-gray-200 text-center whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($d->app_created_at)->format('d.m.Y H:i:s') }}
                        </td>

                        {{-- Обменник --}}
                        <td class="px-5 py-4 text-sm text-gray-200 text-center whitespace-nowrap">{{ $d->exchanger }}</td>

                        {{-- Статус --}}
                        <td class="px-5 py-4 text-sm text-gray-200 text-center whitespace-nowrap">{{ $d->status }}</td>

                        {{-- Приход+ --}}
                        <td class="px-5 py-4 text-sm text-gray-200 whitespace-nowrap">
                            @if($d->sale_text)
                                @php
                                    // сначала убираем крайние пробелы, затем разбиваем по первому пробелу
                                    [$amount, $curCode] = explode(' ', trim($d->sale_text), 2);
                                    // на всякий случай обрежем ещё раз
                                    $curCode = trim($curCode);
                                @endphp
                                <div class="inline-flex items-center space-x-1">
                                    <span class="text-green-400">+{{ $amount }}</span>
                                    @if($curCode)
                                        <img
                                            src="{{ asset('images/coins/'.$curCode.'.svg') }}"
                                            alt="{{ $curCode }}"
                                            class="w-4 h-4"
                                        >
                                    @endif
                                </div>
                            @else
                                —
                            @endif
                        </td>

                        {{-- Продажа− --}}
                        <td class="px-5 py-4 text-sm text-gray-200 whitespace-nowrap">
                            @if(!is_null($d->sell_amount) && $d->sellCurrency)
                                <div class="inline-flex items-center space-x-1">
                                    <span
                                        class="text-red-400">-{{ rtrim(rtrim((string)$d->sell_amount,'0'),'.') }}</span>
                                    <img
                                        src="{{ asset('images/coins/'.$d->sellCurrency->code.'.svg') }}"
                                        alt="{{ $d->sellCurrency->code }}"
                                        class="w-4 h-4"
                                    >

                                </div>
                            @else
                                —
                            @endif
                        </td>

                        {{-- Купля+ --}}
                        <td class="px-5 py-4 text-sm text-gray-200 whitespace-nowrap">
                            @if(!is_null($d->buy_amount) && $d->buyCurrency)
                                @php $b = rtrim(rtrim((string)$d->buy_amount,'0'),'.'); @endphp
                                <div class="inline-flex items-center space-x-1">
                                <span class="{{ $d->buy_amount>0?'text-green-400':'text-red-400' }}">
                                {{ $d->buy_amount>0?'+':'' }}{{ $b }}
                            </span>
                                    <img
                                        src="{{ asset('images/coins/'.$d->buyCurrency->code.'.svg') }}"
                                        alt="{{ $d->buyCurrency->code }}"
                                        class="w-4 h-4"
                                    >
                                </div>
                            @else
                                —
                            @endif
                        </td>

                        {{-- Расход− --}}
                        <td class="px-5 py-4 text-sm text-gray-200 whitespace-nowrap">
                            @if(!is_null($d->expense_amount) && $d->expenseCurrency)
                                <div class="inline-flex items-center space-x-1">
                                    <span
                                        class="text-red-400">-{{ rtrim(rtrim((string)$d->expense_amount,'0'),'.') }}</span>
                                    {{ $d->expenseCurrency->code }}
                                    <img
                                        src="{{ asset('images/coins/'.$d->expenseCurrency->code.'.svg') }}"
                                        alt="{{ $d->expenseCurrency->code }}"
                                        class="w-4 h-4"
                                    >
                                </div>
                            @else
                                —
                            @endif
                        </td>

                        {{-- Мерчант --}}
                        <td class="px-5 py-4 text-sm text-gray-200 text-center whitespace-nowrap">{{ $d->merchant ?? '—' }}</td>

                        {{-- ID ордера --}}
                        <td class="px-5 py-4 text-sm text-gray-200 text-center whitespace-nowrap">{{ $d->order_id ?? '—' }}</td>
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
                    ['sell_amount', 'sell_currency', 'buy_amount', 'buy_currency', 'expense_amount', 'expense_currency', 'merchant', 'order_id']
                        .forEach(f => {
                            const errEl = document.getElementById('err_' + f);
                            if (errEl) errEl.textContent = '';
                        });

                    const id = document.getElementById('edit_app_id').value;
                    const data = {
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
                        .then(response => {
                            if (!response.ok) throw new Error("HTTP status " + response.status);
                            return response.json();
                        })
                        .then(json => {
                            const tbody = document.getElementById('appsTbody');

                            json.data.forEach(d => {
                                let rowHtml = '';

                                if (window.isAdmin) {
                                    // 1) Кнопка «Редактировать»
                                    rowHtml += `
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <button
                                            class="editBtn px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition text-xs"
                                            data-id="${d.id}"
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

                                    // 2) «Кто изменил»
                                    const whoEdited = (d.user && d.user.login) ? d.user.login : '-';
                                    rowHtml += `<td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                                ${whoEdited}
                                            </td>`;
                                }

                                // 3) Номер заявки
                                rowHtml += `<td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">${d.app_id}</td>`;

                                // 4) Дата создания (формат «dd.mm.YYYY hh:mm:ss»)
                                const created = new Date(d.app_created_at);
                                const dd = String(created.getDate()).padStart(2, '0');
                                const mm = String(created.getMonth() + 1).padStart(2, '0');
                                const yyyy = created.getFullYear();
                                const hh = String(created.getHours()).padStart(2, '0');
                                const min = String(created.getMinutes()).padStart(2, '0');
                                const ss = String(created.getSeconds()).padStart(2, '0');
                                rowHtml += `<td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ${dd}.${mm}.${yyyy} ${hh}:${min}:${ss}
                                        </td>`;

                                // 5) Обменник
                                rowHtml += `<td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">${d.exchanger}</td>`;

                                // 6) Статус
                                rowHtml += `<td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">${d.status}</td>`;

                                // 7) Приход+ (sale_text)
                                if (d.sale_text) {
                                    const parts = d.sale_text.split(' ');
                                    const amount = parts[0] || '';
                                    const cur = parts[1] || '';
                                    rowHtml += `<td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <span class="text-green-600">+${amount}</span>${cur ? '&nbsp;' + cur : ''}
                                            </td>`;
                                } else {
                                    rowHtml += `<td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">—</td>`;
                                }

                                // 8) Продажа− (d.sell_currency вместо d.sellCurrency)
                                if (d.sell_amount !== null && d.sell_currency) {
                                    let sell = String(d.sell_amount).replace(/\.?0+$/, '');
                                    sell = sell.replace(/^-/, '');
                                    rowHtml += `<td class="px-5 py-4 whitespace-nowrap text-sm">
                                                <span class="text-red-600">-${sell}</span>&nbsp;${d.sell_currency.code}
                                            </td>`;
                                } else {
                                    rowHtml += `<td class="px-5 py-4 whitespace-nowrap text-sm">—</td>`;
                                }

                                // 9) Купля+ (d.buy_currency вместо d.buyCurrency)
                                if (d.buy_amount !== null && d.buy_currency) {
                                    let buy = String(d.buy_amount).replace(/\.?0+$/, '');
                                    const sign = d.buy_amount > 0 ? '+' : '-';
                                    const cls = d.buy_amount > 0 ? 'text-green-600' : 'text-red-600';
                                    buy = buy.replace(/^-/, '');
                                    rowHtml += `<td class="px-5 py-4 whitespace-nowrap text-sm">
                                                <span class="${cls}">${sign}${buy}</span>&nbsp;${d.buy_currency.code}
                                            </td>`;
                                } else {
                                    rowHtml += `<td class="px-5 py-4 whitespace-nowrap text-sm">—</td>`;
                                }

                                // 10) Расход− (d.expense_currency вместо d.expenseCurrency)
                                if (d.expense_amount !== null && d.expense_currency) {
                                    let exp = String(d.expense_amount).replace(/\.?0+$/, '');
                                    exp = exp.replace(/^-/, '');
                                    rowHtml += `<td class="px-5 py-4 whitespace-nowrap text-sm">
                                                <span class="text-red-600">-${exp}</span>&nbsp;${d.expense_currency.code}
                                            </td>`;
                                } else {
                                    rowHtml += `<td class="px-5 py-4 whitespace-nowrap text-sm">—</td>`;
                                }

                                // 11) Мерчант
                                rowHtml += `<td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ${d.merchant ?? '—'}
                                        </td>`;

                                // 12) ID ордера
                                rowHtml += `<td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ${d.order_id ?? '—'}
                                        </td>`;

                                // Вставляем в <tbody>
                                const tr = document.createElement('tr');
                                tr.classList.add('hover:bg-gray-50');
                                tr.setAttribute('data-app-row-id', d.id);
                                tr.innerHTML = rowHtml;
                                tbody.appendChild(tr);
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

        {{-- ◆========== Второй блок: «История операций» (dynamic columns) ==========◆ --}}
        <div class="bg-[#191919] rounded-xl shadow-md overflow-x-auto border border-[#2d2d2d]">
            <div class="px-6 py-4 border-b border-[#2d2d2d]">
                <h2 class="text-2xl font-semibold text-white">История операций по валютам</h2>
            </div>
            <table class="min-w-full table-auto border-collapse divide-y divide-[#2d2d2d]">
                <thead class="bg-[#191919]">
                <tr class="sticky top-0">
                    @foreach($currencies as $currency)
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                            @php
                                // цветной бэкграунд (если есть)
                                $hex = ltrim($currency->color ?? '', '#');
                                // путь к файлу и URL
                                $iconPath = public_path("images/coins/{$currency->code}.svg");
                                $iconUrl  = asset("images/coins/{$currency->code}.svg");
                            @endphp

                            @if($hex)
                                <div
                                    class="inline-block px-2 py-1 rounded text-white text-xs"
                                    style="background-color: #{{ $hex }};"
                                >
                                    {{ $currency->code }}
                                </div>
                            @elseif(file_exists($iconPath))
                                <div class="inline-flex items-center space-x-1">
                                    <img src="{{ $iconUrl }}" alt="{{ $currency->code }}" class="w-6 h-6">
                                    {{--                                    <span>{{ $currency->code }}</span>--}}
                                </div>
                            @else
                                {{ $currency->code }}
                            @endif
                        </th>
                    @endforeach
                </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-[#2d2d2d]">
                @foreach($histories as $history)
                    <tr class="bg-[#191919] hover:bg-gray-700">
                        @foreach($currencies as $currency)
                            @php
                                $cell = '';
                                if ($history->currency_id === $currency->id && $history->amount !== null) {
                                    // абсолютное значение
                                    $abs = abs($history->amount);
                                    // форматируем с 8 знаками после точки (подставьте своё количество)
                                    $formatted = sprintf('%.8f', $abs);
                                    // обрезаем лишние нули в конце, а затем — возможную точку
                                    $trimmed = rtrim(rtrim($formatted, '0'), '.');
                                    // знак
                                    $sign = $history->amount > 0 ? '+' : '-';
                                    $cell = $sign . $trimmed;
                                }
                            @endphp
                            <td class="px-4 py-2 text-sm text-gray-200 whitespace-nowrap">
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
                <tr class="bg-[#191919]">
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
                                <span
                                    class="{{ $sum > 0 ? 'text-green-600' : ($sum < 0 ? 'text-red-600' : 'text-gray-900') }}">
                                    {{ $formatted }}
                                </span>
                        </td>
                    @endforeach
                </tr>
                </tfoot>
            </table>

            <!-- Стилизация контейнера итоговой суммы в USDT -->
            <div
                id="usdtTotalContainer"
                class="bg-[#191919]
                    rounded-lg
                    px-4 py-3
                    flex items-center justify-between
                    shadow-sm
                "
            >
                <div class="text-gray-700 text-sm font-medium">
                    Итог (в USDT):
                </div>
                <div
                    id="usdt-total-cell"
                    class="inline-block px-3 py-1 rounded font-bold text-lg"
                >
                    —
                </div>
            </div>
        </div>

        {{-- Итого USDT по дням — Flowbite Chart --}}
        <div class="bg-[#191919] rounded-xl shadow-md p-6 border border-[#2d2d2d] mb-6">
            <h2 class="text-2xl font-semibold text-white mb-4">Итоги USDT по дням</h2>
            <div class="flex gap-4 mb-6">
                <div>
                    <label class="block text-sm text-gray-400 mb-1">От</label>
                    <input type="date" id="start_date" class="bg-gray-800 text-white px-3 py-2 rounded-lg border border-gray-600" value="{{ now()->subDays(6)->format('Y-m-d') }}">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">До</label>
                    <input type="date" id="end_date" class="bg-gray-800 text-white px-3 py-2 rounded-lg border border-gray-600" value="{{ now()->format('Y-m-d') }}">
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
                <table class="min-w-full table-auto border-collapse">
                    <thead class="bg-[#191919]">
                    <tr class="sticky top-0">
                        <th class="px-5 py-3 text-xs font-semibold text-start text-white uppercase whitespace-nowrap border-b border-[#2d2d2d]">
                            Откуда
                        </th>
                        <th class="px-5 py-3 text-xs font-semibold text-start text-white uppercase whitespace-nowrap border-b border-[#2d2d2d]">
                            Куда
                        </th>
                        <th class="px-5 py-3 text-xs font-semibold text-start text-white uppercase whitespace-nowrap border-b border-[#2d2d2d]">
                            Сумма
                        </th>
                        <th class="px-5 py-3 text-xs font-semibold text-start text-white uppercase whitespace-nowrap border-b border-[#2d2d2d]">
                            Комиссия
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-[#2d2d2d]">
                    @foreach($transfers as $t)
                        <tr class="bg-[#191919] hover:bg-gray-700">
                            <td class="px-5 py-4 text-sm text-gray-200 whitespace-nowrap">
                                {{ optional($t->exchangerFrom)->title ?? '—' }}
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-200 whitespace-nowrap">
                                {{ optional($t->exchangerTo)->title ?? '—' }}
                            </td>
                            <td class="px-5 py-4 text-sm whitespace-nowrap">
                                @if($t->amount !== null)
                                    @php
                                        $amt     = rtrim(rtrim((string)$t->amount, '0'), '.');
                                        $codeAmt = optional($t->amountCurrency)->code;
                                        $pathAmt = public_path("images/coins/{$codeAmt}.svg");
                                        $urlAmt  = asset("images/coins/{$codeAmt}.svg");
                                    @endphp
                                    <div class="inline-flex items-center space-x-1">
                                        <span class="text-white">{{ ltrim($amt, '-') }}</span>
                                        @if($codeAmt && file_exists($pathAmt))
                                            <img src="{{ $urlAmt }}" alt="{{ $codeAmt }}" class="w-4 h-4">
                                        @else
                                            <span class="text-white">{{ $codeAmt }}</span>
                                        @endif
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm whitespace-nowrap">
                                @if($t->commission !== null)
                                    @php
                                        $comm      = rtrim(rtrim((string)$t->commission, '0'), '.');
                                        $codeComm  = optional($t->commissionCurrency)->code;
                                        $pathComm  = public_path("images/coins/{$codeComm}.svg");
                                        $urlComm   = asset("images/coins/{$codeComm}.svg");
                                    @endphp
                                    <div class="inline-flex items-center space-x-1">
                                        <span class="text-red-400">-{{ ltrim($comm, '-') }}</span>
                                        @if($codeComm && file_exists($pathComm))
                                            <img src="{{ $urlComm }}" alt="{{ $codeComm }}" class="w-4 h-4">
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
                <div class="px-6 py-2">
                    <span class="text-sm text-gray-500">Ещё</span>
                </div>
            </div>

            {{-- 4) Payments --}}
            <div class="bg-[#191919] rounded-xl shadow-md overflow-x-auto border border-[#2d2d2d]">
                <div class="px-6 py-4 border-b border-[#2d2d2d]">
                    <h2 class="text-2xl font-semibold text-white">Оплата</h2>
                </div>
                <table class="min-w-full table-auto border-collapse">
                    <thead class="bg-[#191919]">
                    <tr class="sticky top-0">
                        <th class="px-5 py-3 text-xs font-semibold text-white uppercase text-start whitespace-nowrap border-b border-[#2d2d2d]">
                            Платформа
                        </th>
                        <th class="px-5 py-3 text-xs font-semibold text-white uppercase text-start whitespace-nowrap border-b border-[#2d2d2d]">
                            Сумма
                        </th>
                        <th class="px-5 py-3 text-xs font-semibold text-white uppercase text-start whitespace-nowrap border-b border-[#2d2d2d]">
                            Комментарий
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-[#2d2d2d]">
                    @foreach($payments as $p)
                        <tr class="bg-[#191919] hover:bg-gray-700">
                            <td class="px-5 py-4 text-sm text-white whitespace-nowrap">
                                {{ optional($p->exchanger)->title ?? '—' }}
                            </td>
                            <td class="px-5 py-4 text-sm whitespace-nowrap">
                                @if($p->sell_amount !== null)
                                    @php
                                        $amount  = rtrim(rtrim((string)$p->sell_amount, '0'), '.');
                                        $code    = optional($p->sellCurrency)->code;
                                        $path    = public_path("images/coins/{$code}.svg");
                                        $url     = asset("images/coins/{$code}.svg");
                                    @endphp
                                    <div class="inline-flex items-center space-x-1">
                                        <span class="text-red-400">-{{ ltrim($amount, '-') }}</span>
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
                            <td class="px-5 py-4 text-sm text-white">
                                {{ $p->comment ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-2">
                    <span class="text-sm text-gray-500">Ещё</span>
                </div>
            </div>

            {{-- … предыдущие блоки … --}}

            {{-- 2) Покупка крипты --}}
            <div class="bg-[#191919] rounded-xl shadow-md overflow-x-auto border border-[#2d2d2d]">
                <div class="px-6 py-4 border-b border-[#2d2d2d]">
                    <h2 class="text-2xl font-semibold text-white">Покупка крипты</h2>
                </div>
                <table class="min-w-full table-auto border-collapse">
                    <thead class="bg-[#191919]">
                    <tr class="sticky top-0">
                        @if(auth()->user()->role === 'admin')
                            <th class="px-5 py-3 text-xs font-semibold text-start text-white uppercase whitespace-nowrap border-b border-[#2d2d2d]">
                                Действие
                            </th>
                        @endif
                        <th class="px-5 py-3 text-xs font-semibold text-start text-white uppercase whitespace-nowrap border-b border-[#2d2d2d]">
                            Платформа
                        </th>
                        <th class="px-5 py-3 text-xs font-semibold text-start text-white uppercase whitespace-nowrap border-b border-[#2d2d2d]">
                            Получено +
                        </th>
                        <th class="px-5 py-3 text-xs font-semibold text-start text-white uppercase whitespace-nowrap border-b border-[#2d2d2d]">
                            Продано −
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-[#2d2d2d]">
                    @foreach($purchases as $pc)
                        <tr class="bg-[#191919] hover:bg-gray-700">
                            @if(auth()->user()->role === 'admin')
                                <td class="px-5 py-4 text-gray-200 text-sm whitespace-nowrap">
                                    {{-- кнопки редактирования --}}
                                </td>
                            @endif
                            <td class="px-5 py-4 text-gray-200 text-sm whitespace-nowrap">
                                {{ optional($pc->exchanger)->title ?? '—' }}
                            </td>
                            <td class="px-5 py-4 text-sm whitespace-nowrap">
                                @if(!is_null($pc->received_amount))
                                    @php
                                        $recAmt  = rtrim(rtrim((string)$pc->received_amount, '0'), '.');
                                        $codeRec = optional($pc->receivedCurrency)->code;
                                        $pathRec = public_path("images/coins/{$codeRec}.svg");
                                        $urlRec  = asset("images/coins/{$codeRec}.svg");
                                    @endphp
                                    <div class="inline-flex items-center space-x-1">
                                        <span class="text-green-400">+{{ $recAmt }}</span>
                                        @if($codeRec && file_exists($pathRec))
                                            <img src="{{ $urlRec }}" alt="{{ $codeRec }}" class="w-4 h-4">
                                        @else
                                            <span class="text-white">{{ $codeRec }}</span>
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
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-2">
                    <span class="text-sm text-gray-500">Ещё</span>
                </div>
            </div>

            {{-- Модальное окно редактирования покупки (стиль «Добавить») --}}
            <div
                id="modalEditPurchaseBackdrop"
                class="fixed inset-0 flex items-center justify-center bg-black/20 backdrop-blur-sm z-50 hidden"
            >
                <div id="modalEditPurchaseClose" class="absolute inset-0"></div>

                <div class="bg-white rounded-xl shadow-xl p-6 relative z-10 w-full max-w-md animate-fadeIn">
                    <header class="mb-6 border-b border-gray-200 pb-3">
                        <h3 class="text-2xl font-semibold text-gray-800">
                            Редактировать покупку <span id="purchaseModalId" class="text-blue-600"></span>
                        </h3>
                    </header>
                    <form id="editPurchaseForm" class="space-y-5">
                        @csrf
                        <input type="hidden" id="edit_purchase_id">

                        {{-- 1) Платформа --}}
                        <div>
                            <label for="edit_purchase_exchanger" class="block text-sm font-medium text-gray-700 mb-1">
                                Платформа
                            </label>
                            <select
                                id="edit_purchase_exchanger"
                                class="block w-full bg-white border border-gray-300 rounded-md shadow-sm
                           px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                           hover:bg-gray-50 transition duration-150 ease-in-out"
                            >
                                <option value="" disabled>— Выберите платформу —</option>
                                @foreach($exchangers as $e)
                                    <option value="{{ $e->id }}">{{ $e->title }}</option>
                                @endforeach
                            </select>
                            <p class="text-red-600 text-sm mt-1" id="err_edit_purchase_exchanger"></p>
                        </div>

                        {{-- 2) «Получено»: сумма + валюта --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="edit_received_amount" class="block text-sm font-medium text-gray-700 mb-1">
                                    Получено +
                                </label>
                                <input
                                    type="number"
                                    step="0.00000001"
                                    id="edit_received_amount"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="0.00123456"
                                >
                                <p class="text-red-600 text-sm mt-1" id="err_edit_received_amount"></p>
                            </div>
                            <div>
                                <label for="edit_received_currency"
                                       class="block text-sm font-medium text-gray-700 mb-1">
                                    Валюта
                                </label>
                                <select
                                    id="edit_received_currency"
                                    class="block w-full bg-white border border-gray-300 rounded-md shadow-sm
                               px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               hover:bg-gray-50 transition duration-150 ease-in-out"
                                >
                                    <option value="" disabled>— Выберите валюту —</option>
                                    @foreach($currencies as $c)
                                        <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-red-600 text-sm mt-1" id="err_edit_received_currency"></p>
                            </div>
                        </div>

                        {{-- 3) «Продано»: сумма + валюта --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="edit_sale_amount" class="block text-sm font-medium text-gray-700 mb-1">
                                    Продано −
                                </label>
                                <input
                                    type="number"
                                    step="0.00000001"
                                    id="edit_sale_amount"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="0.00012345"
                                >
                                <p class="text-red-600 text-sm mt-1" id="err_edit_sale_amount"></p>
                            </div>
                            <div>
                                <label for="edit_sale_currency" class="block text-sm font-medium text-gray-700 mb-1">
                                    Валюта
                                </label>
                                <select
                                    id="edit_sale_currency"
                                    class="block w-full bg-white border border-gray-300 rounded-md shadow-sm
                               px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               hover:bg-gray-50 transition duration-150 ease-in-out"
                                >
                                    <option value="" disabled>— Выберите валюту —</option>
                                    @foreach($currencies as $c)
                                        <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-red-600 text-sm mt-1" id="err_edit_sale_currency"></p>
                            </div>
                        </div>

                        {{-- Кнопки --}}
                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                            <button
                                type="button"
                                id="cancelEditPurchase"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"
                            >
                                Отмена
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                            >
                                Сохранить
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Модальное окно подтверждения удаления покупки --}}
            <div
                id="modalDeletePurchaseBackdrop"
                class="fixed inset-0 flex items-center justify-center bg-black/20 backdrop-blur-sm z-50 hidden"
            >
                <div id="modalDeletePurchaseClose" class="absolute inset-0"></div>
                <div class="bg-white rounded-xl shadow-xl p-6 relative z-10 w-full max-w-sm animate-fadeIn">
                    <header class="mb-4">
                        <h3 class="text-xl font-semibold text-gray-800">
                            Удалить покупку <span id="deletePurchaseId" class="text-blue-600"></span>?
                        </h3>
                    </header>
                    <p class="mb-6 text-gray-700">Все связанные записи в истории будут удалены.</p>
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button
                            type="button"
                            id="cancelDeletePurchase"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"
                        >
                            Отмена
                        </button>
                        <button
                            id="confirmDeletePurchase"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                        >
                            Удалить
                        </button>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const editModal = document.getElementById('modalEditPurchaseBackdrop');
                    const deleteModal = document.getElementById('modalDeletePurchaseBackdrop');

                    // Закрытие
                    document.getElementById('modalEditPurchaseClose')
                        ?.addEventListener('click', () => editModal.classList.add('hidden'));
                    document.getElementById('cancelEditPurchase')
                        ?.addEventListener('click', () => editModal.classList.add('hidden'));

                    document.getElementById('modalDeletePurchaseClose')
                        ?.addEventListener('click', () => deleteModal.classList.add('hidden'));
                    document.getElementById('cancelDeletePurchase')
                        ?.addEventListener('click', () => deleteModal.classList.add('hidden'));

                    // Открытие редактирования
                    document.querySelectorAll('.edit-purchase-btn').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const id = btn.dataset.id;
                            const exchangerId = btn.dataset.exchangerId;
                            const receivedAmount = btn.dataset.receivedAmount;
                            const receivedCurrId = btn.dataset.receivedCurrencyId;
                            const saleAmount = btn.dataset.saleAmount;
                            const saleCurrId = btn.dataset.saleCurrencyId;

                            document.getElementById('edit_purchase_id').value = id;
                            document.getElementById('purchaseModalId').textContent = `#${id}`;
                            document.getElementById('edit_purchase_exchanger').value = exchangerId;
                            document.getElementById('edit_received_amount').value = receivedAmount;
                            document.getElementById('edit_received_currency').value = receivedCurrId;
                            document.getElementById('edit_sale_amount').value = saleAmount;
                            document.getElementById('edit_sale_currency').value = saleCurrId;

                            editModal.classList.remove('hidden');
                        });
                    });

                    // Открытие удаления
                    document.querySelectorAll('.delete-purchase-btn').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const id = btn.dataset.id;
                            document.getElementById('deletePurchaseId').textContent = `#${id}`;
                            document.getElementById('confirmDeletePurchase').dataset.id = id;
                            deleteModal.classList.remove('hidden');
                        });
                    });

                    // Сохранить изменения
                    document.getElementById('editPurchaseForm').addEventListener('submit', e => {
                        e.preventDefault();
                        const id = document.getElementById('edit_purchase_id').value;
                        const data = {
                            exchanger_id: document.getElementById('edit_purchase_exchanger').value,
                            received_amount: document.getElementById('edit_received_amount').value,
                            received_currency_id: document.getElementById('edit_received_currency').value,
                            sale_amount: document.getElementById('edit_sale_amount').value,
                            sale_currency_id: document.getElementById('edit_sale_currency').value,
                        };
                        fetch(`/admin/purchases/${id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(data)
                        })
                            .then(r => {
                                if (!r.ok) throw new Error(r.status);
                                return r.json();
                            })
                            .then(() => location.reload())
                            .catch(() => alert('Не удалось сохранить изменения'));
                    });

                    // Удалить
                    document.getElementById('confirmDeletePurchase').addEventListener('click', () => {
                        const id = document.getElementById('confirmDeletePurchase').dataset.id;
                        fetch(`/admin/purchases/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                            .then(r => {
                                if (!r.ok) throw new Error(r.status);
                                return r.json();
                            })
                            .then(() => location.reload())
                            .catch(() => alert('Не удалось удалить запись'));
                    });
                });
            </script>

            {{-- 3) Продажа крипты --}}
            <div class="bg-[#191919] rounded-xl shadow-md overflow-x-auto border border-[#2d2d2d]">
                <div class="px-6 py-4 border-b border-[#2d2d2d]">
                    <h2 class="text-2xl font-semibold text-white">Продажа крипты</h2>
                </div>
                <table class="min-w-full table-auto border-collapse">
                    <thead class="bg-[#191919]">
                    <tr class="sticky top-0">
                        <th class="px-5 py-3 text-xs font-semibold text-start text-white uppercase whitespace-nowrap border-b border-[#2d2d2d]">
                            Платформа
                        </th>
                        <th class="px-5 py-3 text-xs font-semibold text-start text-white uppercase whitespace-nowrap border-b border-[#2d2d2d]">
                            Продажа −
                        </th>
                        <th class="px-5 py-3 text-xs font-semibold text-start text-white uppercase whitespace-nowrap border-b border-[#2d2d2d]">
                            Получено +
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-[#2d2d2d]">
                    @foreach($saleCrypts as $sc)
                        <tr class="bg-[#191919] hover:bg-gray-700">
                            <td class="px-5 py-4 text-gray-200 text-sm whitespace-nowrap">
                                {{ optional($sc->exchanger)->title ?? '—' }}
                            </td>
                            <td class="px-5 py-4 text-sm whitespace-nowrap">
                                @if($sc->sale_amount !== null)
                                    @php
                                        $sa     = rtrim(rtrim((string)$sc->sale_amount, '0'), '.');
                                        $codeSa = optional($sc->saleCurrency)->code;
                                        $pathSa = public_path("images/coins/{$codeSa}.svg");
                                        $urlSa  = asset("images/coins/{$codeSa}.svg");
                                    @endphp
                                    <div class="inline-flex items-center space-x-1">
                                        <span class="text-red-400">-{{ ltrim($sa, '-') }}</span>
                                        @if($codeSa && file_exists($pathSa))
                                            <img src="{{ $urlSa }}" alt="{{ $codeSa }}" class="w-4 h-4">
                                        @else
                                            <span class="text-white">{{ $codeSa }}</span>
                                        @endif
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm whitespace-nowrap">
                                @if($sc->fixed_amount !== null)
                                    @php
                                        $fa      = rtrim(rtrim((string)$sc->fixed_amount, '0'), '.');
                                        $codeFa  = optional($sc->fixedCurrency)->code;
                                        $pathFa  = public_path("images/coins/{$codeFa}.svg");
                                        $urlFa   = asset("images/coins/{$codeFa}.svg");
                                    @endphp
                                    <div class="inline-flex items-center space-x-1">
                  <span class="{{ $sc->fixed_amount > 0 ? 'text-green-400' : 'text-red-400' }}">
                    {{ $sc->fixed_amount > 0 ? '+' : '-' }}{{ ltrim($fa, '-') }}
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
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-2">
                    <span class="text-sm text-gray-500">Ещё</span>
                </div>
            </div>
        </div>
    </div>
@endsection
