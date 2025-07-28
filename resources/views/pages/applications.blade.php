@extends('template.applications')

@section('title', 'Заявки')

@push('styles')
<style>
    /* Анимации для модального окна */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.95) translateY(-10px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out;
    }

    .animate-slideIn {
        animation: slideIn 0.2s ease-out;
    }

    /* Hover эффекты для карточек */
    .bg-gray-800\/50:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
        transition: all 0.2s ease-in-out;
    }

    /* Градиентные границы */
    .border-gradient {
        border-image: linear-gradient(45deg, #3b82f6, #8b5cf6, #06b6d4) 1;
    }

    /* Кастомный скроллбар */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(55, 65, 81, 0.3);
        border-radius: 3px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(107, 114, 128, 0.5);
        border-radius: 3px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(107, 114, 128, 0.7);
    }
</style>
@endpush

@section('content')
<!-- Сообщения сессии -->
@if (session('success'))
    <div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-green-900/20 border border-green-500 rounded-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <div class="text-green-400 font-medium">{{ session('success') }}</div>
        </div>
    </div>
@endif

@if (session('error'))
    <div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-red-900/20 border border-red-500 rounded-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
            <div class="text-red-400 font-medium">{{ session('error') }}</div>
        </div>
    </div>
@endif

@if(auth()->user()->role !== 'admin')
    <div class="fixed inset-0 bg-black bg-opacity-70 flex flex-col items-center justify-center z-50">
        <div class="bg-[#232b3a] p-8 rounded-2xl shadow-lg flex flex-col items-center">
            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 17v1m0-4a4 4 0 00-4 4v1a4 4 0 008 0v-1a4 4 0 00-4-4zm0 0V7a4 4 0 118 0v6"></path></svg>
            <div class="text-xl text-white font-bold mb-2">Доступ только для администраторов</div>
            <div class="text-gray-400 mb-4">Обратитесь к администратору для получения доступа к заявкам.</div>
            <a href="/choose" class="px-6 py-2 bg-cyan-700 hover:bg-cyan-800 text-white rounded-lg">Вернуться к выбору раздела</a>
        </div>
    </div>
@endif
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
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



    <!-- Независимые таблицы операций на всю ширину -->
    <div class="mt-12 space-y-8">
        <div class="bg-[#191919] border border-[#2d2d2d] rounded-xl p-4">
            <div class="flex justify-between items-center mb-2">
                <h2 class="text-lg font-semibold text-cyan-300">Оплаты</h2>
                <button onclick="openAddPaymentModal()" class="px-3 py-1 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg transition-colors text-sm">
                    <span class="flex items-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Добавить</span>
                    </span>
                </button>
            </div>
            <div id="paymentsGrid" class="ag-theme-alpine-dark" style="height: 400px; width: 100%; min-width: 600px;"></div>

            <!-- Кнопка "Показать еще" для платежей -->
            <div class="mt-4 text-center">
                <button
                    id="loadMorePaymentsBtn"
                    class="px-6 py-3 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg transition-colors font-medium hidden"
                    onclick="window.paymentsPage.loadMore()"
                >
                    <span class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                        <span>Показать еще</span>
                    </span>
                </button>

                <!-- Индикатор загрузки -->
                <div id="loadMorePaymentsSpinner" class="hidden">
                    <div class="inline-flex items-center space-x-2 text-gray-400">
                        <div class="animate-spin rounded-full h-5 w-5 border-2 border-gray-300 border-t-cyan-500"></div>
                        <span>Загрузка...</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-[#191919] border border-[#2d2d2d] rounded-xl p-4">
            <div class="flex justify-between items-center mb-2">
                <h2 class="text-lg font-semibold text-emerald-300">Покупки крипты</h2>
                <button onclick="openAddPurchaseModal()" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors text-sm">
                    <span class="flex items-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Добавить</span>
                    </span>
                </button>
            </div>
            <div id="purchaseGrid" class="ag-theme-alpine-dark" style="height: 400px; width: 100%; min-width: 600px;"></div>

            <!-- Кнопка "Показать еще" для покупок -->
            <div class="mt-4 text-center">
                <button
                    id="loadMorePurchaseBtn"
                    class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors font-medium hidden"
                    onclick="window.purchasePage.loadMore()"
                >
                    <span class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                        <span>Показать еще</span>
                    </span>
                </button>

                <!-- Индикатор загрузки -->
                <div id="loadMorePurchaseSpinner" class="hidden">
                    <div class="inline-flex items-center space-x-2 text-gray-400">
                        <div class="animate-spin rounded-full h-5 w-5 border-2 border-gray-300 border-t-emerald-500"></div>
                        <span>Загрузка...</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-[#191919] border border-[#2d2d2d] rounded-xl p-4">
            <div class="flex justify-between items-center mb-2">
                <h2 class="text-lg font-semibold text-pink-300">Продажи крипты</h2>
                <button onclick="openAddSaleCryptModal()" class="px-3 py-1 bg-pink-600 hover:bg-pink-700 text-white rounded-lg transition-colors text-sm">
                    <span class="flex items-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Добавить</span>
                    </span>
                </button>
            </div>
            <div id="saleCryptGrid" class="ag-theme-alpine-dark" style="height: 400px; width: 100%; min-width: 600px;"></div>

            <!-- Кнопка "Показать еще" для продаж -->
            <div class="mt-4 text-center">
                <button
                    id="loadMoreSaleCryptBtn"
                    class="px-6 py-3 bg-pink-600 hover:bg-pink-700 text-white rounded-lg transition-colors font-medium hidden"
                    onclick="window.saleCryptPage.loadMore()"
                >
                    <span class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                        <span>Показать еще</span>
                    </span>
                </button>

                <!-- Индикатор загрузки -->
                <div id="loadMoreSaleCryptSpinner" class="hidden">
                    <div class="inline-flex items-center space-x-2 text-gray-400">
                        <div class="animate-spin rounded-full h-5 w-5 border-2 border-gray-300 border-t-pink-500"></div>
                        <span>Загрузка...</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-[#191919] border border-[#2d2d2d] rounded-xl p-4">
            <div class="flex justify-between items-center mb-2">
                <h2 class="text-lg font-semibold text-yellow-300">Переводы</h2>
                <button onclick="openAddTransferModal()" class="px-3 py-1 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition-colors text-sm">
                    <span class="flex items-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Добавить</span>
                    </span>
                </button>
            </div>
            <div id="transferGrid" class="ag-theme-alpine-dark" style="height: 400px; width: 100%; min-width: 600px;"></div>

            <!-- Кнопка "Показать еще" для переводов -->
            <div class="mt-4 text-center">
                <button
                    id="loadMoreTransferBtn"
                    class="px-6 py-3 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition-colors font-medium hidden"
                    onclick="window.transferPage.loadMore()"
                >
                    <span class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                        <span>Показать еще</span>
                    </span>
                </button>

                <!-- Индикатор загрузки -->
                <div id="loadMoreTransferSpinner" class="hidden">
                    <div class="inline-flex items-center space-x-2 text-gray-400">
                        <div class="animate-spin rounded-full h-5 w-5 border-2 border-gray-300 border-t-yellow-500"></div>
                        <span>Загрузка...</span>
                    </div>
                </div>
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
<!-- Модальные окна для Payments -->
<div id="editPaymentModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="absolute inset-0" onclick="document.getElementById('editPaymentModal').classList.add('hidden')"></div>
    <div class="bg-[#1F1F1F] text-white rounded-xl shadow-2xl p-6 relative z-10 w-full max-w-lg">
        <h3 class="text-xl font-semibold mb-4">Редактировать оплату <span id="editPaymentId" class="text-cyan-400"></span></h3>
        <form id="editPaymentForm" class="space-y-4">
            <input type="hidden" name="id" id="edit_payment_id">

            <!-- Платформа -->
            <div>
                <label class="block text-sm mb-1 text-blue-300">Платформа</label>
                <select name="exchanger_id" id="edit_payment_exchanger_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                    <option value="">Выберите платформу</option>
                    @if(isset($exchangers))
                        @foreach($exchangers as $e)
                            <option value="{{ $e->id }}">{{ $e->title }}</option>
                        @endforeach
                    @endif
                </select>
            </div>



            <!-- Сумма продажи -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1 text-yellow-300">Сумма продажи</label>
                    <input type="number" step="0.01" name="sell_amount" id="edit_payment_sell_amount" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-yellow-300">Валюта продажи</label>
                    <select name="sell_currency_id" id="edit_payment_sell_currency_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                        <option value="">Выберите валюту</option>
                        @if(isset($currenciesForEdit))
                            @foreach($currenciesForEdit as $c)
                                <option value="{{ $c->id }}">{{ $c->code }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <!-- Комментарий -->
            <div>
                <label class="block text-sm mb-1 text-gray-300">Комментарий</label>
                <textarea name="comment" id="edit_payment_comment" rows="3" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2"></textarea>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('editPaymentModal').classList.add('hidden')" class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600">Отмена</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-500">Сохранить</button>
            </div>
        </form>
    </div>
</div>
<div id="deletePaymentModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="absolute inset-0" onclick="document.getElementById('deletePaymentModal').classList.add('hidden')"></div>
    <div class="bg-[#1F1F1F] text-white rounded-xl shadow-2xl p-6 relative z-10 w-full max-w-sm">
        <h3 class="text-xl font-semibold mb-4">Удалить оплату?</h3>
        <p class="mb-4">Вы уверены, что хотите удалить оплату <span id="deletePaymentId" class="text-red-400"></span>?</p>
        <div class="flex justify-end gap-2">
            <button type="button" onclick="document.getElementById('deletePaymentModal').classList.add('hidden')" class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600">Отмена</button>
            <button id="confirmDeletePaymentBtn" type="button" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Удалить</button>
        </div>
    </div>
</div>
<!-- Модальные окна для Purchase -->
<div id="editPurchaseModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="absolute inset-0" onclick="document.getElementById('editPurchaseModal').classList.add('hidden')"></div>
    <div class="bg-[#1F1F1F] text-white rounded-xl shadow-2xl p-6 relative z-10 w-full max-w-lg">
        <h3 class="text-xl font-semibold mb-4">Редактировать покупку <span id="editPurchaseId" class="text-cyan-400"></span></h3>
        <form id="editPurchaseForm" class="space-y-4">
            <input type="hidden" name="id" id="edit_purchase_id">



            <!-- Заявка -->
            <div>
                <label class="block text-sm mb-1 text-purple-300">Заявка</label>
                <select name="application_id" id="edit_purchase_application_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                    <option value="">Загрузка...</option>
                </select>
            </div>

            <!-- Платформа -->
            <div>
                <label class="block text-sm mb-1 text-blue-300">Платформа</label>
                <select name="exchanger_id" id="edit_purchase_exchanger_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                    <option value="">Выберите платформу</option>
                    @if(isset($exchangers))
                        @foreach($exchangers as $e)
                            <option value="{{ $e->id }}">{{ $e->title }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- Продано -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1 text-pink-300">Сумма продажи</label>
                    <input type="number" step="0.01" name="sale_amount" id="edit_purchase_sale_amount" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-pink-300">Валюта продажи</label>
                    <select name="sale_currency_id" id="edit_purchase_sale_currency_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                        <option value="">Выберите валюту</option>
                        @if(isset($currenciesForEdit))
                            @foreach($currenciesForEdit as $c)
                                <option value="{{ $c->id }}">{{ $c->code }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <!-- Получено -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1 text-emerald-300">Сумма получения</label>
                    <input type="number" step="0.01" name="received_amount" id="edit_purchase_received_amount" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-emerald-300">Валюта получения</label>
                    <select name="received_currency_id" id="edit_purchase_received_currency_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                        <option value="">Выберите валюту</option>
                        @if(isset($currenciesForEdit))
                            @foreach($currenciesForEdit as $c)
                                <option value="{{ $c->id }}">{{ $c->code }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('editPurchaseModal').classList.add('hidden')" class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600">Отмена</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-500">Сохранить</button>
            </div>
        </form>
    </div>
</div>
<div id="deletePurchaseModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="absolute inset-0" onclick="document.getElementById('deletePurchaseModal').classList.add('hidden')"></div>
    <div class="bg-[#1F1F1F] text-white rounded-xl shadow-2xl p-6 relative z-10 w-full max-w-sm">
        <h3 class="text-xl font-semibold mb-4">Удалить покупку?</h3>
        <p class="mb-4">Вы уверены, что хотите удалить покупку <span id="deletePurchaseId" class="text-red-400"></span>?</p>
        <div class="flex justify-end gap-2">
            <button type="button" onclick="document.getElementById('deletePurchaseModal').classList.add('hidden')" class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600">Отмена</button>
            <button id="confirmDeletePurchaseBtn" type="button" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Удалить</button>
        </div>
    </div>
</div>
<!-- Модальные окна для SaleCrypt -->
<div id="editSaleCryptModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="absolute inset-0" onclick="document.getElementById('editSaleCryptModal').classList.add('hidden')"></div>
    <div class="bg-[#1F1F1F] text-white rounded-xl shadow-2xl p-6 relative z-10 w-full max-w-lg">
        <h3 class="text-xl font-semibold mb-4">Редактировать продажу <span id="editSaleCryptId" class="text-cyan-400"></span></h3>
        <form id="editSaleCryptForm" class="space-y-4">
            <input type="hidden" name="id" id="edit_salecrypt_id">



            <!-- Заявка -->
            <div>
                <label class="block text-sm mb-1 text-purple-300">Заявка</label>
                <select name="application_id" id="edit_salecrypt_application_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                    <option value="">Загрузка...</option>
                </select>
            </div>

            <!-- Платформа -->
            <div>
                <label class="block text-sm mb-1 text-blue-300">Платформа</label>
                <select name="exchanger_id" id="edit_salecrypt_exchanger_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                    <option value="">Выберите платформу</option>
                    @if(isset($exchangers))
                        @foreach($exchangers as $e)
                            <option value="{{ $e->id }}">{{ $e->title }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- Продажа -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1 text-pink-300">Сумма продажи</label>
                    <input type="number" step="0.01" name="sale_amount" id="edit_salecrypt_sale_amount" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-pink-300">Валюта продажи</label>
                    <select name="sale_currency_id" id="edit_salecrypt_sale_currency_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                        <option value="">Выберите валюту</option>
                        @if(isset($currenciesForEdit))
                            @foreach($currenciesForEdit as $c)
                                <option value="{{ $c->id }}">{{ $c->code }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <!-- Фиксированная сумма -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1 text-emerald-300">Фиксированная сумма</label>
                    <input type="number" step="0.01" name="fixed_amount" id="edit_salecrypt_fixed_amount" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-emerald-300">Валюта фиксированной суммы</label>
                    <select name="fixed_currency_id" id="edit_salecrypt_fixed_currency_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                        <option value="">Выберите валюту</option>
                        @if(isset($currenciesForEdit))
                            @foreach($currenciesForEdit as $c)
                                <option value="{{ $c->id }}">{{ $c->code }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>



            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('editSaleCryptModal').classList.add('hidden')" class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600">Отмена</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-500">Сохранить</button>
            </div>
        </form>
    </div>
</div>
<div id="deleteSaleCryptModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="absolute inset-0" onclick="document.getElementById('deleteSaleCryptModal').classList.add('hidden')"></div>
    <div class="bg-[#1F1F1F] text-white rounded-xl shadow-2xl p-6 relative z-10 w-full max-w-sm">
        <h3 class="text-xl font-semibold mb-4">Удалить продажу?</h3>
        <p class="mb-4">Вы уверены, что хотите удалить продажу <span id="deleteSaleCryptId" class="text-red-400"></span>?</p>
        <div class="flex justify-end gap-2">
            <button type="button" onclick="document.getElementById('deleteSaleCryptModal').classList.add('hidden')" class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600">Отмена</button>
            <button id="confirmDeleteSaleCryptBtn" type="button" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Удалить</button>
        </div>
    </div>
</div>
<!-- Модальные окна для Transfer -->
<div id="editTransferModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="absolute inset-0" onclick="document.getElementById('editTransferModal').classList.add('hidden')"></div>
    <div class="bg-[#1F1F1F] text-white rounded-xl shadow-2xl p-6 relative z-10 w-full max-w-lg">
        <h3 class="text-xl font-semibold mb-4">Редактировать перевод <span id="editTransferId" class="text-cyan-400"></span></h3>
        <form id="editTransferForm" class="space-y-4">
            <input type="hidden" name="id" id="edit_transfer_id">



            <!-- Платформа отправления -->
            <div>
                <label class="block text-sm mb-1 text-red-300">Платформа отправления</label>
                <select name="exchanger_from_id" id="edit_transfer_exchanger_from_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                    <option value="">Выберите платформу</option>
                    @foreach($exchangers as $e)
                        <option value="{{ $e->id }}">{{ $e->title }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Платформа получения -->
            <div>
                <label class="block text-sm mb-1 text-green-300">Платформа получения</label>
                <select name="exchanger_to_id" id="edit_transfer_exchanger_to_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                    <option value="">Выберите платформу</option>
                    @foreach($exchangers as $e)
                        <option value="{{ $e->id }}">{{ $e->title }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Комиссия -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1 text-yellow-300">Сумма комиссии</label>
                    <input type="number" step="0.01" name="commission" id="edit_transfer_commission" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-yellow-300">Валюта комиссии</label>
                    <select name="commission_id" id="edit_transfer_commission_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                        @foreach($currenciesForEdit as $c)
                            <option value="{{ $c->id }}">{{ $c->code }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Сумма -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1 text-blue-300">Сумма перевода</label>
                    <input type="number" step="0.01" name="amount" id="edit_transfer_amount" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-blue-300">Валюта суммы</label>
                    <select name="amount_id" id="edit_transfer_amount_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                        @foreach($currenciesForEdit as $c)
                            <option value="{{ $c->id }}">{{ $c->code }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('editTransferModal').classList.add('hidden')" class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600">Отмена</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-500">Сохранить</button>
            </div>
        </form>
    </div>
</div>
<div id="deleteTransferModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="absolute inset-0" onclick="document.getElementById('deleteTransferModal').classList.add('hidden')"></div>
    <div class="bg-[#1F1F1F] text-white rounded-xl shadow-2xl p-6 relative z-10 w-full max-w-sm">
        <h3 class="text-xl font-semibold mb-4">Удалить перевод?</h3>
        <p class="mb-4">Вы уверены, что хотите удалить перевод <span id="deleteTransferId" class="text-red-400"></span>?</p>
        <div class="flex justify-end gap-2">
            <button type="button" onclick="document.getElementById('deleteTransferModal').classList.add('hidden')" class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600">Отмена</button>
            <button id="confirmDeleteTransferBtn" type="button" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Удалить</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // CSRF токен для AJAX запросов
    window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
</script>
<script>
// Автоматическое скрытие сообщений сессии
document.addEventListener('DOMContentLoaded', () => {
    const sessionMessages = document.querySelectorAll('[class*="fixed top-4"]');
    sessionMessages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            message.style.transform = 'translate(-50%, -100%)';
            setTimeout(() => message.remove(), 300);
        }, 3000);
    });
});
</script>
<script src="{{ asset('js/applications-page.js') }}"></script>
<script src="{{ asset('js/payments-page.js') }}"></script>
<script src="{{ asset('js/purchase-page.js') }}"></script>
<script src="{{ asset('js/sale-crypt-page.js') }}"></script>
<script src="{{ asset('js/transfer-page.js') }}"></script>
<script>
// Информация о текущем пользователе для диагностики
window.currentUser = {
    id: {{ auth()->id() }},
    name: '{{ auth()->user()->name }}',
    role: '{{ auth()->user()->role }}'
};
console.log('Applications page: текущий пользователь:', window.currentUser);

// Функция для показа деталей заявки
function showApplicationDetails(appId) {
    console.log('Applications page: показываем детали заявки:', appId);

    // Показываем модальное окно с индикатором загрузки
    document.getElementById('modalAppDetailsBackdrop').classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Показываем индикатор загрузки
    document.getElementById('detailsAppId').innerHTML = `
        <div class="flex items-center space-x-2">
            <svg class="animate-spin h-5 w-5 text-cyan-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Загрузка...</span>
        </div>
    `;

    fetch(`/api/applications/${appId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => {
        if (!res.ok) {
            throw new Error(`HTTP ${res.status}: ${res.statusText}`);
        }
        return res.json();
    })
    .then(data => {
        // Основные поля
        document.getElementById('detailsAppId').textContent = `#${data.app_id}`;
        document.getElementById('detailsAppNumber').innerHTML = `${data.app_id}${createCopyButton(data.app_id, 'Копировать номер заявки')}`;
        document.getElementById('detailsCreatedAt').textContent = data.app_created_at;
        document.getElementById('detailsExchanger').textContent = data.exchanger;
        document.getElementById('detailsStatus').textContent = data.status;
        document.getElementById('detailsMerchant').innerHTML = `${data.merchant || '—'}${data.merchant ? createCopyButton(data.merchant, 'Копировать мерчанта') : ''}`;
        document.getElementById('detailsOrderId').innerHTML = `${data.order_id || '—'}${data.order_id ? createCopyButton(data.order_id, 'Копировать ID ордера') : ''}`;

        // Приход+ (используем buy_amount)
        const incEl = document.getElementById('detailsIncoming');
        if (data.buy_amount != null && data.buy_currency) {
            incEl.innerHTML = getCurrencyImg(data.buy_currency) + stripZeros(data.buy_amount);
        } else {
            incEl.textContent = '—';
        }

        // Продажа− (используем sell_amount)
        const sellEl = document.getElementById('detailsSell');
        if (data.sell_amount != null && data.sell_currency) {
            sellEl.innerHTML = getCurrencyImg(data.sell_currency) + stripZeros(data.sell_amount);
        } else {
            sellEl.textContent = '—';
        }

        // Купля+ (используем buy_amount)
        const buyEl = document.getElementById('detailsBuy');
        if (data.buy_amount != null && data.buy_currency) {
            buyEl.innerHTML = getCurrencyImg(data.buy_currency) + stripZeros(data.buy_amount);
        } else {
            buyEl.textContent = '—';
        }

        // Расход− (используем expense_amount)
        const expEl = document.getElementById('detailsExpense');
        if (data.expense_amount != null && data.expense_currency) {
            expEl.innerHTML = getCurrencyImg(data.expense_currency) + stripZeros(data.expense_amount);
        } else {
            expEl.textContent = '—';
        }

        // Списки покупок и продаж крипты
        const ulBuy = document.getElementById('detailsPurchaseList');
        const ulSell = document.getElementById('detailsSaleList');
        ulBuy.innerHTML = '';
        ulSell.innerHTML = '';

        if (data.related_purchases && data.related_purchases.length > 0) {
            data.related_purchases.forEach(p => {
                ulBuy.insertAdjacentHTML('beforeend', `
                    <div class="bg-gray-700/50 rounded-xl p-4 border border-gray-600">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-green-500/20 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                    </svg>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="flex items-center">
                                        ${getCurrencyImg(p.received_currency)}
                                        <span class="text-white font-semibold">${stripZeros(p.received_amount)}</span>
                                    </span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                    <span class="flex items-center">
                                        ${getCurrencyImg(p.sale_currency)}
                                        <span class="text-white font-semibold">${stripZeros(p.sale_amount)}</span>
                                    </span>
                                </div>
                            </div>
                            <div class="text-xs text-gray-400">ID: ${p.id}</div>
                        </div>
                    </div>
                `);
            });
        } else {
            ulBuy.innerHTML = '<p class="text-gray-400 text-sm italic">Нет связанных покупок</p>';
        }

        if (data.related_sale_crypts && data.related_sale_crypts.length > 0) {
            data.related_sale_crypts.forEach(s => {
                ulSell.insertAdjacentHTML('beforeend', `
                    <div class="bg-gray-700/50 rounded-xl p-4 border border-gray-600">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-red-500/20 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                    </svg>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="flex items-center">
                                        ${getCurrencyImg(s.fixed_currency)}
                                        <span class="text-white font-semibold">${stripZeros(s.fixed_amount)}</span>
                                    </span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                    <span class="flex items-center">
                                        ${getCurrencyImg(s.sale_currency)}
                                        <span class="text-white font-semibold">${stripZeros(s.sale_amount)}</span>
                                    </span>
                                </div>
                            </div>
                            <div class="text-xs text-gray-400">ID: ${s.id}</div>
                        </div>
                    </div>
                `);
            });
        } else {
            ulSell.innerHTML = '<p class="text-gray-400 text-sm italic">Нет связанных продаж</p>';
        }
    })
    .catch(error => {
        console.error('Applications page: ошибка загрузки деталей заявки:', error);

        // Показываем ошибку в модальном окне
        document.getElementById('detailsAppId').innerHTML = `
            <div class="flex items-center space-x-2 text-red-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <span>Ошибка загрузки</span>
            </div>
        `;

        // Очищаем остальные поля
        document.getElementById('detailsAppNumber').textContent = '—';
        document.getElementById('detailsCreatedAt').textContent = '—';
        document.getElementById('detailsExchanger').textContent = '—';
        document.getElementById('detailsStatus').textContent = '—';
        document.getElementById('detailsMerchant').textContent = '—';
        document.getElementById('detailsOrderId').textContent = '—';
        document.getElementById('detailsIncoming').textContent = '—';
        document.getElementById('detailsSell').textContent = '—';
        document.getElementById('detailsBuy').textContent = '—';
        document.getElementById('detailsExpense').textContent = '—';
        document.getElementById('detailsPurchaseList').innerHTML = '<p class="text-red-400 text-sm">Ошибка загрузки данных</p>';
        document.getElementById('detailsSaleList').innerHTML = '<p class="text-red-400 text-sm">Ошибка загрузки данных</p>';

        if (window.notifications) {
            window.notifications.error('Ошибка загрузки деталей заявки: ' + error.message);
        }
    });
}

// Вспомогательные функции для модального окна
function stripZeros(value) {
    const num = parseFloat(value);
    if (isNaN(num)) return '0';
    const fixed = num.toFixed(4);
    return fixed.replace(/\.?0+$/, '');
}

function getCurrencyImg(code, size = 'w-6 h-6') {
    const url = `/images/coins/${code}.svg`;
    // если иконки нет — показываем текстовый код
    return `<img src="${url}" alt="${code}" class="${size} inline-block align-text-bottom mr-1" onerror="this.style.display='none'; this.nextSibling.style.display='inline';"><span class="hidden text-xs">${code}</span>`;
}

// Обработчики для модального окна заявки
document.addEventListener('DOMContentLoaded', function() {
    // Кнопки закрытия
    const btnCloseAppDetails = document.getElementById('btnCloseAppDetails');
    const modalAppDetailsClose = document.getElementById('modalAppDetailsClose');

    if (btnCloseAppDetails) {
        btnCloseAppDetails.addEventListener('click', hideDetailsModal);
    }

    if (modalAppDetailsClose) {
        modalAppDetailsClose.addEventListener('click', hideDetailsModal);
    }

    // Закрытие по Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') hideDetailsModal();
    });
});

// Скрыть модалку
function hideDetailsModal() {
    document.getElementById('modalAppDetailsBackdrop').classList.add('hidden');
    document.body.style.overflow = '';
}

// Функция для копирования в буфер обмена
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // Показываем уведомление об успешном копировании
        if (window.notifications) {
            window.notifications.success('Скопировано в буфер обмена');
        }
    }).catch(err => {
        console.error('Ошибка копирования:', err);
        if (window.notifications) {
            window.notifications.error('Ошибка копирования');
        }
    });
}

// Функция для создания кнопки копирования
function createCopyButton(text, label = 'Копировать') {
    return `<button onclick="copyToClipboard('${text}')" class="ml-2 p-1.5 bg-gray-600 hover:bg-gray-500 text-white rounded-lg transition-colors duration-200" title="${label}">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
        </svg>
    </button>`;
}

// Функция для проверки готовности AG-Grid и всех скриптов
function checkAGGridAndScriptsReady() {
    return typeof agGrid !== 'undefined' &&
           agGrid &&
           (agGrid.Grid || agGrid.createGrid) &&
           window.PaymentsPage &&
           window.PurchasePage &&
           window.SaleCryptPage &&
           window.TransferPage &&
           document.body && // Проверяем, что body существует
           document.readyState === 'complete'; // Проверяем, что страница полностью загружена
}

// Функция инициализации таблиц
function initializeTables() {
    console.log('Applications page: AG-Grid и все скрипты загружены, инициализируем таблицы');

    // Инициализируем таблицу платежей
    if (window.PaymentsPage) {
        console.log('Applications page: создаем PaymentsPage');
        window.paymentsPage = new window.PaymentsPage();
    }

    // Инициализируем таблицу покупок
    if (window.PurchasePage) {
        console.log('Applications page: создаем PurchasePage');
        window.purchasePage = new window.PurchasePage();
    }

    // Инициализируем таблицу продаж крипты
    if (window.SaleCryptPage) {
        console.log('Applications page: создаем SaleCryptPage');
        window.saleCryptPage = new window.SaleCryptPage();
    }

    // Инициализируем таблицу переводов
    if (window.TransferPage) {
        console.log('Applications page: создаем TransferPage');
        window.transferPage = new window.TransferPage();
    }
}

// Ждем загрузки DOM и всех ресурсов
window.addEventListener('load', function() {
    console.log('Applications page: страница полностью загружена, проверяем готовность...');

    // Проверяем готовность AG-Grid и скриптов
    if (checkAGGridAndScriptsReady()) {
        console.log('Applications page: все готово, инициализируем таблицы');
        initializeTables();
    } else {
        // Если что-то еще не загружено, ждем
        console.log('Applications page: ждем загрузки AG-Grid и скриптов...');
        let attempts = 0;
        const maxAttempts = 100; // 10 секунд максимум

        const checkInterval = setInterval(() => {
            attempts++;
            console.log(`Applications page: попытка ${attempts}/${maxAttempts} - AG-Grid: ${typeof agGrid !== 'undefined'}, PaymentsPage: ${!!window.PaymentsPage}`);

            if (checkAGGridAndScriptsReady()) {
                clearInterval(checkInterval);
                console.log('Applications page: все готово, инициализируем таблицы');
                initializeTables();
            } else if (attempts >= maxAttempts) {
                clearInterval(checkInterval);
                console.error('Applications page: не удалось загрузить AG-Grid или скрипты');
                if (typeof window.notifications !== 'undefined') {
                    window.notifications.error('Ошибка загрузки таблиц. Обновите страницу.');
                }
            }
        }, 100);
    }
});

// Функции для работы с модальными окнами добавления
function openAddPaymentModal() {
    document.getElementById('addPaymentModal').classList.remove('hidden');
    // Очищаем форму
    document.getElementById('addPaymentForm').reset();
}

function openAddPurchaseModal() {
    document.getElementById('addPurchaseModal').classList.remove('hidden');
    // Очищаем форму
    document.getElementById('addPurchaseForm').reset();
    // Загружаем заявки
    loadApplicationsForAddPurchase();
}

function openAddSaleCryptModal() {
    document.getElementById('addSaleCryptModal').classList.remove('hidden');
    // Очищаем форму
    document.getElementById('addSaleCryptForm').reset();
    // Загружаем заявки
    loadApplicationsForAddSaleCrypt();
}

function openAddTransferModal() {
    document.getElementById('addTransferModal').classList.remove('hidden');
    // Очищаем форму
    document.getElementById('addTransferForm').reset();
}

// Функции загрузки заявок для модальных окон добавления
async function loadApplicationsForAddPurchase() {
    const select = document.getElementById('add_purchase_application_id');
    if (!select) return;

    select.innerHTML = '<option value="">Загрузка...</option>';

    try {
        const resp = await fetch('/api/applications/list-temp', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            credentials: 'same-origin'
        });

        if (!resp.ok) {
            console.error('Ошибка загрузки заявок:', resp.status, resp.statusText);
            select.innerHTML = '<option value="">Ошибка загрузки</option>';
            return;
        }

        const apps = await resp.json();
        select.innerHTML = '<option value="">Выберите заявку</option>';
        apps.forEach(app => {
            const text = `${app.app_id}` + (app.order_id ? ` (${app.order_id})` : '');
            const option = document.createElement('option');
            option.value = app.id;
            option.textContent = text;
            select.appendChild(option);
        });
    } catch (e) {
        console.error('Ошибка загрузки заявок:', e);
        select.innerHTML = '<option value="">Ошибка загрузки</option>';
    }
}

async function loadApplicationsForAddSaleCrypt() {
    const select = document.getElementById('add_salecrypt_application_id');
    if (!select) return;

    select.innerHTML = '<option value="">Загрузка...</option>';

    try {
        const resp = await fetch('/api/applications/list-temp', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            credentials: 'same-origin'
        });

        if (!resp.ok) {
            console.error('Ошибка загрузки заявок:', resp.status, resp.statusText);
            select.innerHTML = '<option value="">Ошибка загрузки</option>';
            return;
        }

        const apps = await resp.json();
        select.innerHTML = '<option value="">Выберите заявку</option>';
        apps.forEach(app => {
            const text = `${app.app_id}` + (app.order_id ? ` (${app.order_id})` : '');
            const option = document.createElement('option');
            option.value = app.id;
            option.textContent = text;
            select.appendChild(option);
        });
    } catch (e) {
        console.error('Ошибка загрузки заявок:', e);
        select.innerHTML = '<option value="">Ошибка загрузки</option>';
    }
}

// Обработчики форм добавления
document.addEventListener('DOMContentLoaded', function() {
    // Форма добавления оплаты
    const addPaymentForm = document.getElementById('addPaymentForm');
    if (addPaymentForm) {
        addPaymentForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            try {
                const response = await fetch('/payments', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': window.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if (response.ok) {
                    const result = await response.json();
                    document.getElementById('addPaymentModal').classList.add('hidden');

                    // Обновляем таблицу
                    if (window.paymentsPage) {
                        await window.paymentsPage.refreshData();
                    }

                    if (window.notifications) {
                        window.notifications.success('Оплата успешно добавлена');
                    }
                } else {
                    const error = await response.json();
                    if (window.notifications) {
                        window.notifications.error('Ошибка добавления оплаты: ' + (error.message || 'Неизвестная ошибка'));
                    }
                }
            } catch (error) {
                console.error('Ошибка добавления оплаты:', error);
                if (window.notifications) {
                    window.notifications.error('Ошибка добавления оплаты');
                }
            }
        });
    }

    // Форма добавления покупки
    const addPurchaseForm = document.getElementById('addPurchaseForm');
    if (addPurchaseForm) {
        addPurchaseForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            try {
                const response = await fetch('/purchase', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': window.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if (response.ok) {
                    const result = await response.json();
                    document.getElementById('addPurchaseModal').classList.add('hidden');

                    // Обновляем таблицу
                    if (window.purchasePage) {
                        await window.purchasePage.refreshData();
                    }

                    if (window.notifications) {
                        window.notifications.success('Покупка успешно добавлена');
                    }
                } else {
                    const error = await response.json();
                    if (window.notifications) {
                        window.notifications.error('Ошибка добавления покупки: ' + (error.message || 'Неизвестная ошибка'));
                    }
                }
            } catch (error) {
                console.error('Ошибка добавления покупки:', error);
                if (window.notifications) {
                    window.notifications.error('Ошибка добавления покупки');
                }
            }
        });
    }

    // Форма добавления продажи
    const addSaleCryptForm = document.getElementById('addSaleCryptForm');
    if (addSaleCryptForm) {
        addSaleCryptForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            try {
                const response = await fetch('/sale-crypt', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': window.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if (response.ok) {
                    const result = await response.json();
                    document.getElementById('addSaleCryptModal').classList.add('hidden');

                    // Обновляем таблицу
                    if (window.saleCryptPage) {
                        await window.saleCryptPage.refreshData();
                    }

                    if (window.notifications) {
                        window.notifications.success('Продажа успешно добавлена');
                    }
                } else {
                    const error = await response.json();
                    if (window.notifications) {
                        window.notifications.error('Ошибка добавления продажи: ' + (error.message || 'Неизвестная ошибка'));
                    }
                }
            } catch (error) {
                console.error('Ошибка добавления продажи:', error);
                if (window.notifications) {
                    window.notifications.error('Ошибка добавления продажи');
                }
            }
        });
    }

    // Форма добавления перевода
    const addTransferForm = document.getElementById('addTransferForm');
    if (addTransferForm) {
        addTransferForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            try {
                const response = await fetch('/transfer', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': window.csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                if (response.ok) {
                    const result = await response.json();
                    document.getElementById('addTransferModal').classList.add('hidden');

                    // Обновляем таблицу
                    if (window.transferPage) {
                        await window.transferPage.refreshData();
                    }

                    if (window.notifications) {
                        window.notifications.success('Перевод успешно добавлен');
                    }
                } else {
                    const error = await response.json();
                    if (window.notifications) {
                        window.notifications.error('Ошибка добавления перевода: ' + (error.message || 'Неизвестная ошибка'));
                    }
                }
            } catch (error) {
                console.error('Ошибка добавления перевода:', error);
                if (window.notifications) {
                    window.notifications.error('Ошибка добавления перевода');
                }
            }
        });
    }
});
</script>

<!-- Модальное окно для просмотра заявки -->
@include('modal.show-application')

<!-- Модальные окна для добавления новых записей -->

<!-- Модальное окно добавления оплаты -->
<div id="addPaymentModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="absolute inset-0" onclick="document.getElementById('addPaymentModal').classList.add('hidden')"></div>
    <div class="bg-[#1F1F1F] text-white rounded-xl shadow-2xl p-6 relative z-10 w-full max-w-lg">
        <h3 class="text-xl font-semibold mb-4">Добавить оплату</h3>
        <form id="addPaymentForm" class="space-y-4">
            @csrf

            <!-- Платформа -->
            <div>
                <label class="block text-sm mb-1 text-blue-300">Платформа</label>
                <select name="exchanger_id" id="add_payment_exchanger_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                    <option value="">Выберите платформу</option>
                    @if(isset($exchangers))
                        @foreach($exchangers as $e)
                            <option value="{{ $e->id }}">{{ $e->title }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- Сумма продажи -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1 text-yellow-300">Сумма продажи</label>
                    <input type="number" step="0.01" name="sell_amount" id="add_payment_sell_amount" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-yellow-300">Валюта продажи</label>
                    <select name="sell_currency_id" id="add_payment_sell_currency_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                        <option value="">Выберите валюту</option>
                        @if(isset($currenciesForEdit))
                            @foreach($currenciesForEdit as $c)
                                <option value="{{ $c->id }}">{{ $c->code }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <!-- Комментарий -->
            <div>
                <label class="block text-sm mb-1 text-gray-300">Комментарий</label>
                <textarea name="comment" id="add_payment_comment" rows="3" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2"></textarea>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('addPaymentModal').classList.add('hidden')" class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600">Отмена</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-500">Добавить</button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно добавления покупки -->
<div id="addPurchaseModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="absolute inset-0" onclick="document.getElementById('addPurchaseModal').classList.add('hidden')"></div>
    <div class="bg-[#1F1F1F] text-white rounded-xl shadow-2xl p-6 relative z-10 w-full max-w-lg">
        <h3 class="text-xl font-semibold mb-4">Добавить покупку</h3>
        <form id="addPurchaseForm" class="space-y-4">
            @csrf

            <!-- Заявка -->
            <div>
                <label class="block text-sm mb-1 text-purple-300">Заявка</label>
                <select name="application_id" id="add_purchase_application_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                    <option value="">Загрузка...</option>
                </select>
            </div>

            <!-- Платформа -->
            <div>
                <label class="block text-sm mb-1 text-blue-300">Платформа</label>
                <select name="exchanger_id" id="add_purchase_exchanger_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                    <option value="">Выберите платформу</option>
                    @if(isset($exchangers))
                        @foreach($exchangers as $e)
                            <option value="{{ $e->id }}">{{ $e->title }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- Продано -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1 text-green-300">Продано</label>
                    <input type="number" step="0.01" name="sold_amount" id="add_purchase_sold_amount" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-green-300">Валюта продажи</label>
                    <select name="sold_currency_id" id="add_purchase_sold_currency_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                        <option value="">Выберите валюту</option>
                        @if(isset($currenciesForEdit))
                            @foreach($currenciesForEdit as $c)
                                <option value="{{ $c->id }}">{{ $c->code }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <!-- Куплено -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1 text-blue-300">Куплено</label>
                    <input type="number" step="0.01" name="bought_amount" id="add_purchase_bought_amount" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-blue-300">Валюта покупки</label>
                    <select name="bought_currency_id" id="add_purchase_bought_currency_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                        <option value="">Выберите валюту</option>
                        @if(isset($currenciesForEdit))
                            @foreach($currenciesForEdit as $c)
                                <option value="{{ $c->id }}">{{ $c->code }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('addPurchaseModal').classList.add('hidden')" class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600">Отмена</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-500">Добавить</button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно добавления продажи -->
<div id="addSaleCryptModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="absolute inset-0" onclick="document.getElementById('addSaleCryptModal').classList.add('hidden')"></div>
    <div class="bg-[#1F1F1F] text-white rounded-xl shadow-2xl p-6 relative z-10 w-full max-w-lg">
        <h3 class="text-xl font-semibold mb-4">Добавить продажу</h3>
        <form id="addSaleCryptForm" class="space-y-4">
            @csrf

            <!-- Заявка -->
            <div>
                <label class="block text-sm mb-1 text-purple-300">Заявка</label>
                <select name="application_id" id="add_salecrypt_application_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                    <option value="">Загрузка...</option>
                </select>
            </div>

            <!-- Платформа -->
            <div>
                <label class="block text-sm mb-1 text-blue-300">Платформа</label>
                <select name="exchanger_id" id="add_salecrypt_exchanger_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                    <option value="">Выберите платформу</option>
                    @if(isset($exchangers))
                        @foreach($exchangers as $e)
                            <option value="{{ $e->id }}">{{ $e->title }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <!-- Продано -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1 text-green-300">Продано</label>
                    <input type="number" step="0.01" name="sale_amount" id="add_salecrypt_sale_amount" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-green-300">Валюта продажи</label>
                    <select name="sale_currency_id" id="add_salecrypt_sale_currency_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                        <option value="">Выберите валюту</option>
                        @if(isset($currenciesForEdit))
                            @foreach($currenciesForEdit as $c)
                                <option value="{{ $c->id }}">{{ $c->code }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <!-- Фиксированная сумма -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1 text-yellow-300">Фиксированная сумма</label>
                    <input type="number" step="0.01" name="fixed_amount" id="add_salecrypt_fixed_amount" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-yellow-300">Валюта фиксированной суммы</label>
                    <select name="fixed_currency_id" id="add_salecrypt_fixed_currency_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                        <option value="">Выберите валюту</option>
                        @if(isset($currenciesForEdit))
                            @foreach($currenciesForEdit as $c)
                                <option value="{{ $c->id }}">{{ $c->code }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('addSaleCryptModal').classList.add('hidden')" class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600">Отмена</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-500">Добавить</button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно добавления перевода -->
<div id="addTransferModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="absolute inset-0" onclick="document.getElementById('addTransferModal').classList.add('hidden')"></div>
    <div class="bg-[#1F1F1F] text-white rounded-xl shadow-2xl p-6 relative z-10 w-full max-w-lg">
        <h3 class="text-xl font-semibold mb-4">Добавить перевод</h3>
        <form id="addTransferForm" class="space-y-4">
            @csrf

            <!-- От кого -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1 text-red-300">От кого</label>
                    <select name="exchanger_from_id" id="add_transfer_exchanger_from_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                        <option value="">Выберите платформу</option>
                        @if(isset($exchangers))
                            @foreach($exchangers as $e)
                                <option value="{{ $e->id }}">{{ $e->title }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div>
                    <label class="block text-sm mb-1 text-green-300">Кому</label>
                    <select name="exchanger_to_id" id="add_transfer_exchanger_to_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                        <option value="">Выберите платформу</option>
                        @if(isset($exchangers))
                            @foreach($exchangers as $e)
                                <option value="{{ $e->id }}">{{ $e->title }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <!-- Комиссия -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1 text-yellow-300">Сумма комиссии</label>
                    <input type="number" step="0.01" name="commission" id="add_transfer_commission" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2" value="0">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-yellow-300">Валюта комиссии</label>
                    <select name="commission_id" id="add_transfer_commission_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                        <option value="">Выберите валюту</option>
                        @if(isset($currenciesForEdit))
                            @foreach($currenciesForEdit as $c)
                                <option value="{{ $c->id }}">{{ $c->code }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <!-- Сумма перевода -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1 text-blue-300">Сумма перевода</label>
                    <input type="number" step="0.01" name="amount" id="add_transfer_amount" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1 text-blue-300">Валюта суммы</label>
                    <select name="amount_id" id="add_transfer_amount_id" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2">
                        <option value="">Выберите валюту</option>
                        @if(isset($currenciesForEdit))
                            @foreach($currenciesForEdit as $c)
                                <option value="{{ $c->id }}">{{ $c->code }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="document.getElementById('addTransferModal').classList.add('hidden')" class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600">Отмена</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-500">Добавить</button>
            </div>
        </form>
    </div>
</div>

@endpush
