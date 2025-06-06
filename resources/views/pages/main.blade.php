{{-- resources/views/pages/main.blade.php --}}
@extends('template.app')

@section('title', 'История заявок')

@section('content')
    <div class="container mx-auto px-4 py-6 space-y-8">
        @include('pages.other')

        {{-- ◆========== Первый блок: «История заявок» (Applications) ==========◆ --}}
        <div class="bg-white rounded-xl shadow-md overflow-x-auto">
            <div class="px-6 py-4">
                <h2 class="text-2xl font-semibold text-gray-800">История заявок</h2>
            </div>

            {{-- БЛОК МОДАЛЬНОГО ОКНА (не удаляйте эти ID!) --}}
            <div
                id="editModalBackdrop"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden"
            >
                <div
                    class="absolute inset-0"
                    id="editModalBackdropClose"
                ></div>
                <div class="bg-white rounded-xl shadow-xl p-6 relative z-10 w-full max-w-lg animate-fadeIn">
                    <h3 class="text-2xl font-semibold mb-4">
                        Редактировать заявку <span id="modalAppId" class="text-blue-600"></span>
                    </h3>
                    <form id="editForm" class="space-y-6">
                        <input type="hidden" name="id" id="edit_app_id">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- — Продажа — --}}
                            <div>
                                <label for="edit_sell_amount" class="block text-sm font-medium text-gray-700 mb-1">
                                    Продажа (сумма)
                                </label>
                                <input
                                    type="number"
                                    step="0.00000001"
                                    name="sell_amount"
                                    id="edit_sell_amount"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                                >
                                <p class="text-red-600 text-sm mt-1" id="err_sell_amount"></p>
                            </div>
                            <div>
                                <label for="edit_sell_currency" class="block text-sm font-medium text-gray-700 mb-1">
                                    Продажа (валюта)
                                </label>
                                <select
                                    name="sell_currency"
                                    id="edit_sell_currency"
                                    class="block w-full bg-white border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 hover:bg-gray-50 transition duration-150 ease-in-out"
                                >
                                    <option value="" disabled selected>— Выберите валюту —</option>
                                    @foreach($currencies as $c)
                                        <option value="{{ $c->code }}">
                                            {{ $c->code }} — {{ $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-red-600 text-sm mt-1" id="err_sell_currency"></p>
                            </div>

                            {{-- — Купля — --}}
                            <div>
                                <label for="edit_buy_amount" class="block text-sm font-medium text-gray-700 mb-1">
                                    Купля (сумма)
                                </label>
                                <input
                                    type="number"
                                    step="0.00000001"
                                    name="buy_amount"
                                    id="edit_buy_amount"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                                >
                                <p class="text-red-600 text-sm mt-1" id="err_buy_amount"></p>
                            </div>
                            <div>
                                <label for="edit_buy_currency" class="block text-sm font-medium text-gray-700 mb-1">
                                    Купля (валюта)
                                </label>
                                <select
                                    name="buy_currency"
                                    id="edit_buy_currency"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400 hover:bg-gray-50 transition duration-150 ease-in-out"
                                >
                                    <option value="" disabled selected>— Выберите валюту —</option>
                                    @foreach($currencies as $c)
                                        <option value="{{ $c->code }}">{{ $c->code }} — {{ $c->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-red-600 text-sm mt-1" id="err_buy_currency"></p>
                            </div>

                            {{-- — Расход — --}}
                            <div>
                                <label for="edit_expense_amount" class="block text-sm font-medium text-gray-700 mb-1">
                                    Расход (сумма)
                                </label>
                                <input
                                    type="number"
                                    step="0.00000001"
                                    name="expense_amount"
                                    id="edit_expense_amount"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                                >
                                <p class="text-red-600 text-sm mt-1" id="err_expense_amount"></p>
                            </div>
                            <div>
                                <label for="edit_expense_currency" class="block text-sm font-medium text-gray-700 mb-1">
                                    Расход (валюта)
                                </label>
                                <select
                                    name="expense_currency"
                                    id="edit_expense_currency"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400 hover:bg-gray-50 transition duration-150 ease-in-out"
                                >
                                    <option value="" disabled selected>— Выберите валюту —</option>
                                    @foreach($currencies as $c)
                                        <option value="{{ $c->code }}">{{ $c->code }} — {{ $c->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-red-600 text-sm mt-1" id="err_expense_currency"></p>
                            </div>

                            {{-- — Мерчант — --}}
                            <div class="sm:col-span-2">
                                <label for="edit_merchant" class="block text-sm font-medium text-gray-700 mb-1">
                                    Мерчант
                                </label>
                                <input
                                    type="text"
                                    name="merchant"
                                    id="edit_merchant"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                                >
                                <p class="text-red-600 text-sm mt-1" id="err_merchant"></p>
                            </div>

                            {{-- — ID ордера — --}}
                            <div class="sm:col-span-2">
                                <label for="edit_order_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    ID ордера
                                </label>
                                <input
                                    type="text"
                                    name="order_id"
                                    id="edit_order_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                                >
                                <p class="text-red-600 text-sm mt-1" id="err_order_id"></p>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button
                                type="button"
                                id="closeEditModalBtn"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition"
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
            {{-- /END модальное окно редактирования — не удаляйте этот блок! — --}}

            <table id="applicationsTable" class="min-w-full divide-y divide-gray-200 table-auto">
                <thead class="bg-gray-100">
                <tr class="sticky top-0">
                    @if(auth()->user()->role === 'admin')
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Действие</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Кто изменил</th>
                    @endif
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Номер заявки</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Дата создания</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Обменник</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Статус</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Приход+</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Продажа−</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Купля+</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Расход−</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">Мерчант</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">ID ордера</th>
                </tr>
                </thead>
                <tbody id="appsTbody" class="bg-white divide-y divide-gray-200">
                @foreach($apps as $d)
                    <tr class="hover:bg-gray-50" data-app-row-id="{{ $d->id }}">
                        @if(auth()->user()->role === 'admin')
                            {{-- Кнопка «Редактировать» --}}
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">
                                <button
                                    class="editBtn px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition text-xs"
                                    data-id="{{ $d->id }}"
                                    data-sell_amount="{{ $d->sell_amount }}"
                                    data-sell_currency="{{ optional($d->sellCurrency)->code }}"
                                    data-buy_amount="{{ $d->buy_amount }}"
                                    data-buy_currency="{{ optional($d->buyCurrency)->code }}"
                                    data-expense_amount="{{ $d->expense_amount }}"
                                    data-expense_currency="{{ optional($d->expenseCurrency)->code }}"
                                    data-merchant="{{ $d->merchant }}"
                                    data-order_id="{{ $d->order_id }}"
                                >Редактировать</button>
                            </td>
                            {{-- Кто изменил --}}
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900 text-center">
                                @if($d->user)
                                    {{ $d->user->login }}
                                @else
                                    -
                                @endif
                            </td>
                        @endif

                        {{-- Номер заявки --}}
                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">{{ $d->app_id }}</td>

                        {{-- Дата создания --}}
                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($d->app_created_at)->format('d.m.Y H:i:s') }}
                        </td>

                        {{-- Обменник --}}
                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">{{ $d->exchanger }}</td>

                        {{-- Статус --}}
                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">{{ $d->status }}</td>

                        {{-- Приход+ (из sale_text) --}}
                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($d->sale_text)
                                @php
                                    $parts   = explode(' ', $d->sale_text, 2);
                                    $amount  = $parts[0] ?? '';
                                    $curCode = $parts[1] ?? '';
                                @endphp
                                <span class="text-green-600">+{{ $amount }}</span>
                                @if($curCode) &nbsp;{{ $curCode }} @endif
                            @else
                                —
                            @endif
                        </td>

                        {{-- Продажа− --}}
                        <td class="px-5 py-4 whitespace-nowrap text-sm">
                            @if($d->sell_amount !== null && $d->sellCurrency)
                                @php $sell = rtrim(rtrim((string)$d->sell_amount, '0'), '.'); @endphp
                                <span class="text-red-600">-{{ ltrim($sell, '-') }}</span>
                                {{ $d->sellCurrency->code }}
                            @else
                                —
                            @endif
                        </td>

                        {{-- Купля+ --}}
                        <td class="px-5 py-4 whitespace-nowrap text-sm">
                            @if($d->buy_amount !== null && $d->buyCurrency)
                                @php $buy = rtrim(rtrim((string)$d->buy_amount, '0'), '.'); @endphp
                                <span class="{{ $d->buy_amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $d->buy_amount > 0 ? '+' : '-' }}{{ ltrim($buy, '-') }}
                                    </span>
                                {{ $d->buyCurrency->code }}
                            @else
                                —
                            @endif
                        </td>

                        {{-- Расход− --}}
                        <td class="px-5 py-4 whitespace-nowrap text-sm">
                            @if($d->expense_amount !== null && $d->expenseCurrency)
                                @php $exp = rtrim(rtrim((string)$d->expense_amount, '0'), '.'); @endphp
                                <span class="text-red-600">-{{ ltrim($exp, '-') }}</span>
                                {{ $d->expenseCurrency->code }}
                            @else
                                —
                            @endif
                        </td>

                        {{-- Мерчант --}}
                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">{{ $d->merchant ?? '—' }}</td>

                        {{-- ID ордера --}}
                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-900">{{ $d->order_id ?? '—' }}</td>
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
                const editModal              = document.getElementById('editModalBackdrop');
                const closeEditModalBtn      = document.getElementById('closeEditModalBtn');
                const editModalBackdropClose = document.getElementById('editModalBackdropClose');
                const modalAppIdLabel        = document.getElementById('modalAppId');
                const editForm               = document.getElementById('editForm');

                function showEditModal() {
                    editModal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }

                function hideEditModal() {
                    editModal.classList.add('hidden');
                    document.body.style.overflow = '';
                    ['sell_amount','sell_currency','buy_amount','buy_currency','expense_amount','expense_currency','merchant','order_id']
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
                            const id              = button.dataset.id;
                            const sellAmount      = button.dataset.sell_amount;
                            const sellCurrency    = button.dataset.sell_currency;
                            const buyAmount       = button.dataset.buy_amount;
                            const buyCurrency     = button.dataset.buy_currency;
                            const expenseAmount   = button.dataset.expense_amount;
                            const expenseCurrency = button.dataset.expense_currency;
                            const merchant        = button.dataset.merchant;
                            const orderId         = button.dataset.order_id;

                            document.getElementById('edit_app_id').value = id;
                            modalAppIdLabel.textContent                  = `#${id}`;
                            document.getElementById('edit_sell_amount').value   = sellAmount   || '';
                            document.getElementById('edit_buy_amount').value    = buyAmount    || '';
                            document.getElementById('edit_expense_amount').value= expenseAmount|| '';
                            document.getElementById('edit_merchant').value      = merchant     || '';
                            document.getElementById('edit_order_id').value      = orderId      || '';
                            document.getElementById('edit_sell_currency').value   = sellCurrency   || '';
                            document.getElementById('edit_buy_currency').value    = buyCurrency    || '';
                            document.getElementById('edit_expense_currency').value= expenseCurrency|| '';

                            showEditModal();
                        });
                    });
                }

                attachEditHandlers();

                // Отправка формы редактирования
                editForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    ['sell_amount','sell_currency','buy_amount','buy_currency','expense_amount','expense_currency','merchant','order_id']
                        .forEach(f => {
                            const errEl = document.getElementById('err_' + f);
                            if (errEl) errEl.textContent = '';
                        });

                    const id = document.getElementById('edit_app_id').value;
                    const data = {
                        sell_amount:    document.getElementById('edit_sell_amount').value.trim(),
                        sell_currency:  document.getElementById('edit_sell_currency').value.trim(),
                        buy_amount:     document.getElementById('edit_buy_amount').value.trim(),
                        buy_currency:   document.getElementById('edit_buy_currency').value.trim(),
                        expense_amount: document.getElementById('edit_expense_amount').value.trim(),
                        expense_currency: document.getElementById('edit_expense_currency').value.trim(),
                        merchant:       document.getElementById('edit_merchant').value.trim(),
                        order_id:       document.getElementById('edit_order_id').value.trim(),
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
                                    const cur    = parts[1] || '';
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
                                    const cls  = d.buy_amount > 0 ? 'text-green-600' : 'text-red-600';
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
                            btn.dataset.hasMore  = json.has_more ? 'true' : 'false';
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
        <div class="bg-white rounded-xl shadow-md overflow-x-auto">
            <div class="px-6 py-4">
                <h2 class="text-2xl font-semibold text-gray-800">История операций по валютам</h2>
            </div>
            <table class="min-w-full table-auto divide-y divide-gray-200">
                <thead class="bg-gray-100">
                <tr>
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
                class="
                    bg-gray-50
                    border
                    border-gray-200
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

        {{-- ◆========== Третий блок: четыре мелких таблицы в две колонки ==========◆ --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- 1) Transfers --}}
            <div class="bg-white rounded-xl shadow-md overflow-x-auto">
                <div class="px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-800">Обмены</h2>
                </div>
                <table class="min-w-full divide-y divide-gray-200 table-auto">
                    <thead class="bg-gray-100">
                    <tr class="sticky top-0">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">
                            Платформа «Откуда»
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">
                            Платформа «Куда»
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">
                            Сумма
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">
                            Комиссия –
                        </th>
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
                                    @php $amt = rtrim(rtrim((string)$t->amount, '0'), '.'); @endphp
                                    <span class="">
                                            {{ ltrim($amt, '-') }}
                                        </span>
                                    {{ optional($t->amountCurrency)->code ?? '' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                @if($t->commission !== null)
                                    @php $comm = rtrim(rtrim((string)$t->commission, '0'), '.'); @endphp
                                    <span class="{{ $t->commission > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $t->commission > 0 ? '+' : '-' }}{{ ltrim($comm, '-') }}
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
                <div class="px-6 py-2">
                    <span class="text-sm text-gray-500">Ещё</span>
                </div>
            </div>

            {{-- 4) Payments --}}
            <div class="bg-white rounded-xl shadow-md overflow-x-auto">
                <div class="px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-800">Оплата</h2>
                </div>
                <table class="min-w-full divide-y divide-gray-200 table-auto">
                    <thead class="bg-gray-100">
                    <tr class="sticky top-0">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">
                            Платформа
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">
                            Сумма продажи –
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">
                            Комментарий
                        </th>
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
                                    @php $amount = rtrim(rtrim((string)$p->sell_amount, '0'), '.'); @endphp
                                    <span class="text-red-600">
                                            -{{ ltrim($amount, '-') }}
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
                <div class="px-6 py-2">
                    <span class="text-sm text-gray-500">Ещё</span>
                </div>
            </div>

            {{-- 3) Purchases --}}
            <div class="bg-white rounded-xl shadow-md overflow-x-auto">
                <div class="px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-800">Покупка крипты</h2>
                </div>
                <table class="min-w-full divide-y divide-gray-200 table-auto">
                    <thead class="bg-gray-100">
                    <tr class="sticky top-0">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">
                            Платформа
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">
                            Сумма получено +
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">
                            Сумма продажи –
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($purchases as $pc)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                {{ optional($pc->exchanger)->title ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                @if($pc->received_amount !== null)
                                    @php $sa = rtrim(rtrim((string)$pc->received_amount, '0'), '.'); @endphp
                                    <span class="{{ $pc->received_amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            +{{ ltrim($sa, '-') }}
                                        </span>
                                    {{ optional($pc->saleCurrency)->code ?? '—' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap">
                                @if($pc->sale_amount !== null)
                                    @php $ra = rtrim(rtrim((string)$pc->sale_amount, '0'), '.'); @endphp
                                    <span class="text-red-600">
                                            -{{ ltrim($ra, '-') }}
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
                <div class="px-6 py-2">
                    <span class="text-sm text-gray-500">Ещё</span>
                </div>
            </div>

            {{-- 2) SaleCrypts --}}
            <div class="bg-white rounded-xl shadow-md overflow-x-auto">
                <div class="px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-800">Продажа крипты</h2>
                </div>
                <table class="min-w-full divide-y divide-gray-200 table-auto">
                    <thead class="bg-gray-100">
                    <tr class="sticky top-0">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">
                            Платформа
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">
                            Сумма продажи –
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase whitespace-nowrap">
                            Сумма получена +
                        </th>
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
                                    @php $sa = rtrim(rtrim((string)$sc->sale_amount, '0'), '.'); @endphp
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
                                    @php $fa = rtrim(rtrim((string)$sc->fixed_amount, '0'), '.'); @endphp
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
                <div class="px-6 py-2">
                    <span class="text-sm text-gray-500">Ещё</span>
                </div>
            </div>

        </div>
    </div>
@endsection
