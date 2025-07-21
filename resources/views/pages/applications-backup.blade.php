@extends('template.applications')

@section('title', 'Заявки')

@push('styles')
<style>
    /* Дополнительные стили для AG-Grid */
    .ag-theme-alpine-dark {
        --ag-background-color: #191919 !important;
        --ag-header-background-color: #2d2d2d !important;
        --ag-odd-row-background-color: #1a1a1a !important;
        --ag-row-hover-color: #2a2a2a !important;
        --ag-selected-row-background-color: #3a3a3a !important;
        --ag-font-family: 'Inter', sans-serif !important;
        --ag-font-size: 14px !important;
        --ag-text-color: #e5e7eb !important;
        --ag-header-foreground-color: #e5e7eb !important;
        --ag-border-color: #404040 !important;
        --ag-row-border-color: #404040 !important;
    }

    /* Принудительное отображение */
    .ag-root-wrapper {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    .ag-root {
        display: block !important;
        visibility: visible !important;
    }

    .ag-body-viewport {
        display: block !important;
        visibility: visible !important;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-6 space-y-8">
    <!-- Заголовок и панель управления -->
    <div class="bg-[#191919] border border-[#2d2d2d] rounded-xl p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Заявки</h1>
                <p class="text-gray-400 mt-1">Управление заявками с обменников</p>
            </div>

            <!-- Панель фильтров -->
            <div class="flex flex-col md:flex-row gap-4 md:items-center">
                                <!-- Фильтр по статусу -->
                <div class="relative">
                    <select
                        id="statusFilter"
                        class="filter-select w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-3 pr-10 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all duration-200 appearance-none hover:border-gray-600"
                    >
                        <option value="" class="bg-gray-800 text-gray-300">Все статусы</option>
                        <option value="выполненная заявка" class="bg-gray-800 text-green-400">✅ Выполненные</option>
                        <option value="оплаченная заявка" class="bg-gray-800 text-blue-400">💰 Оплаченные</option>
                        <option value="возврат" class="bg-gray-800 text-red-400">↩️ Возврат</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <!-- Фильтр по обменнику -->
                <div class="relative">
                    <select
                        id="exchangerFilter"
                        class="filter-select w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-3 pr-10 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all duration-200 appearance-none hover:border-gray-600"
                    >
                        <option value="" class="bg-gray-800 text-gray-300">Все обменники</option>
                        <option value="obama" class="bg-gray-800 text-purple-400">🟣 Obama</option>
                        <option value="ural" class="bg-gray-800 text-orange-400">🟠 Ural</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>

                <!-- Кнопки -->
                <div class="flex gap-2">
                    <button
                        id="refreshBtn"
                        class="action-button px-4 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-all duration-200 font-medium flex items-center space-x-2 border border-gray-600 hover:border-gray-500 shadow-sm"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Обновить</span>
                    </button>

                    <button
                        id="syncBtn"
                        class="action-button px-4 py-3 bg-green-600 hover:bg-green-500 text-white rounded-lg transition-all duration-200 font-medium flex items-center space-x-2 border border-green-500 hover:border-green-400 shadow-sm"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Синхронизировать</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- AG-Grid контейнер -->
    <div class="bg-[#191919] border border-[#2d2d2d] rounded-xl p-6">
        <h2 class="text-xl font-semibold text-white mb-4">Заявки</h2>
        <div id="applicationsGrid" class="ag-theme-alpine-dark" style="height: 600px; width: 100%; min-width: 800px;"></div>

        <!-- Кнопка "Показать еще" -->
        <div class="mt-4 text-center">
            <button
                id="loadMoreBtn"
                class="px-6 py-3 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg transition-colors font-medium hidden"
                onclick="window.applicationsPage.loadMore()"
            >
                <span class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7l-7-7"></path>
                    </svg>
                    <span>Показать еще</span>
                </span>
            </button>

            <!-- Индикатор загрузки -->
            <div id="loadMoreSpinner" class="hidden">
                <div class="inline-flex items-center space-x-2 text-gray-400">
                    <div class="animate-spin rounded-full h-5 w-5 border-2 border-gray-300 border-t-cyan-500"></div>
                    <span>Загрузка...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица платежей -->
    <div class="bg-[#191919] border border-[#2d2d2d] rounded-xl p-6">
        <h2 class="text-xl font-semibold text-white mb-4">Платежи</h2>
        <div id="paymentsGrid" class="ag-theme-alpine-dark" style="height: 600px; width: 100%; min-width: 800px;"></div>

        <!-- Кнопка "Показать еще" для платежей -->
        <div class="mt-4 text-center">
            <button
                id="paymentsLoadMoreBtn"
                class="px-6 py-3 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg transition-colors font-medium hidden"
                onclick="window.paymentsPage.loadMore()"
            >
                <span class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7l-7-7"></path>
                    </svg>
                    <span>Показать еще</span>
                </span>
            </button>
            <div id="paymentsLoadMoreSpinner" class="hidden">
                <div class="inline-flex items-center space-x-2 text-gray-400">
                    <div class="animate-spin rounded-full h-5 w-5 border-2 border-gray-300 border-t-cyan-500"></div>
                    <span>Загрузка...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица переводов -->
    <div class="bg-[#191919] border border-[#2d2d2d] rounded-xl p-6">
        <h2 class="text-xl font-semibold text-white mb-4">Переводы</h2>
        <div id="transfersGrid" class="ag-theme-alpine-dark" style="height: 600px; width: 100%; min-width: 800px;"></div>

        <!-- Кнопка "Показать еще" для переводов -->
        <div class="mt-4 text-center">
            <button
                id="transfersLoadMoreBtn"
                class="px-6 py-3 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg transition-colors font-medium hidden"
                onclick="window.transferPage.loadMore()"
            >
                <span class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7l-7-7"></path>
                    </svg>
                    <span>Показать еще</span>
                </span>
            </button>
            <div id="transfersLoadMoreSpinner" class="hidden">
                <div class="inline-flex items-center space-x-2 text-gray-400">
                    <div class="animate-spin rounded-full h-5 w-5 border-2 border-gray-300 border-t-cyan-500"></div>
                    <span>Загрузка...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица продажи крипты -->
    <div class="bg-[#191919] border border-[#2d2d2d] rounded-xl p-6">
        <h2 class="text-xl font-semibold text-white mb-4">Продажа крипты</h2>
        <div id="saleCryptsGrid" class="ag-theme-alpine-dark" style="height: 600px; width: 100%; min-width: 800px;"></div>

        <!-- Кнопка "Показать еще" для продажи крипты -->
        <div class="mt-4 text-center">
            <button
                id="saleCryptsLoadMoreBtn"
                class="px-6 py-3 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg transition-colors font-medium hidden"
                onclick="window.saleCryptPage.loadMore()"
            >
                <span class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7l-7-7"></path>
                    </svg>
                    <span>Показать еще</span>
                </span>
            </button>
            <div id="saleCryptsLoadMoreSpinner" class="hidden">
                <div class="inline-flex items-center space-x-2 text-gray-400">
                    <div class="animate-spin rounded-full h-5 w-5 border-2 border-gray-300 border-t-cyan-500"></div>
                    <span>Загрузка...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица покупок -->
    <div class="bg-[#191919] border border-[#2d2d2d] rounded-xl p-6">
        <h2 class="text-xl font-semibold text-white mb-4">Покупки</h2>
        <div id="purchasesGrid" class="ag-theme-alpine-dark" style="height: 600px; width: 100%; min-width: 800px;"></div>

        <!-- Кнопка "Показать еще" для покупок -->
        <div class="mt-4 text-center">
            <button
                id="purchasesLoadMoreBtn"
                class="px-6 py-3 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg transition-colors font-medium hidden"
                onclick="window.purchasePage.loadMore()"
            >
                <span class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7l-7-7"></path>
                    </svg>
                    <span>Показать еще</span>
                </span>
            </button>
            <div id="purchasesLoadMoreSpinner" class="hidden">
                <div class="inline-flex items-center space-x-2 text-gray-400">
                    <div class="animate-spin rounded-full h-5 w-5 border-2 border-gray-300 border-t-cyan-500"></div>
                    <span>Загрузка...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика -->
    <div class="bg-[#191919] border border-[#2d2d2d] rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-4">Статистика</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-cyan-400" id="totalApplications">0</div>
                <div class="text-sm text-gray-400">Всего заявок</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-400" id="completedApplications">0</div>
                <div class="text-sm text-gray-400">Выполненные</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-400" id="paidApplications">0</div>
                <div class="text-sm text-gray-400">Оплаченные</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-red-400" id="returnApplications">0</div>
                <div class="text-sm text-gray-400">Возвраты</div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно редактирования -->
<div id="editModalBackdrop" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div id="editModalBackdropClose" class="absolute inset-0"></div>
    <div class="bg-[#1F1F1F] text-white rounded-xl shadow-2xl p-6 relative z-10 w-full max-w-lg">
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
                            @foreach($currenciesForEdit as $c)
                                <option value="{{ $c->code }}">{{ $c->code }} — {{ $c->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
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
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
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
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
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
@endsection

@push('scripts')
<script src="{{ asset('js/applications-page.js') }}"></script>
<script src="{{ asset('js/payments-page.js') }}"></script>
<script src="{{ asset('js/transfer-page.js') }}"></script>
<script src="{{ asset('js/sale-crypt-page.js') }}"></script>
<script src="{{ asset('js/purchase-page.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM загружен, начинаем инициализацию...');

    // Проверяем доступность AG-Grid
    if (typeof agGrid === 'undefined') {
        console.error('AG-Grid не загружен!');
        return;
    }
    console.log('AG-Grid доступен:', agGrid);

    // Проверяем доступность классов
    if (typeof ApplicationsPage === 'undefined') {
        console.error('ApplicationsPage не загружен!');
        return;
    }
    if (typeof PaymentsPage === 'undefined') {
        console.error('PaymentsPage не загружен!');
        return;
    }
    if (typeof TransferPage === 'undefined') {
        console.error('TransferPage не загружен!');
        return;
    }
    if (typeof SaleCryptPage === 'undefined') {
        console.error('SaleCryptPage не загружен!');
        return;
    }
    if (typeof PurchasePage === 'undefined') {
        console.error('PurchasePage не загружен!');
        return;
    }

    console.log('Все классы доступны, инициализируем...');

    try {
        // Инициализация всех страниц
        window.applicationsPage = new ApplicationsPage();
        console.log('ApplicationsPage инициализирован');

        window.paymentsPage = new PaymentsPage();
        console.log('PaymentsPage инициализирован');

        window.transferPage = new TransferPage();
        console.log('TransferPage инициализирован');

        window.saleCryptPage = new SaleCryptPage();
        console.log('SaleCryptPage инициализирован');

        window.purchasePage = new PurchasePage();
        console.log('PurchasePage инициализирован');

        console.log('Все страницы успешно инициализированы!');
    } catch (error) {
        console.error('Ошибка при инициализации:', error);
    }
});
</script>
@endpush
