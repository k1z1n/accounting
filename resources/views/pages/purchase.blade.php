@extends('template.app')

@section('content')
<div class="space-y-6">
    <!-- Заголовок -->
    <div class="bg-[#191919] border border-[#2d2d2d] rounded-xl p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Покупка крипты</h1>
                <p class="text-gray-400 mt-1">Управление покупкой криптовалюты</p>
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

                <!-- Кнопка обновления -->
                <button
                    id="refreshBtn"
                    class="action-button px-4 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-all duration-200 font-medium flex items-center space-x-2 border border-gray-600 hover:border-gray-500 shadow-sm"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Обновить</span>
                </button>
            </div>
        </div>
    </div>

    <!-- AG-Grid контейнер -->
    <div class="bg-[#191919] border border-[#2d2d2d] rounded-xl p-6">
        <div id="purchaseGrid" class="ag-theme-alpine-dark" style="height: 600px; width: 100%; min-width: 800px;"></div>

        <!-- Кнопка "Показать еще" -->
        <div class="mt-4 text-center">
            <button
                id="loadMoreBtn"
                class="px-6 py-3 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg transition-colors font-medium hidden"
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
            <div id="loadMoreSpinner" class="hidden">
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
                <div class="text-2xl font-bold text-cyan-400" id="totalPurchases">0</div>
                <div class="text-sm text-gray-400">Всего покупок</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-400" id="completedPurchases">0</div>
                <div class="text-sm text-gray-400">Выполненные</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-400" id="paidPurchases">0</div>
                <div class="text-sm text-gray-400">Оплаченные</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-red-400" id="returnPurchases">0</div>
                <div class="text-sm text-gray-400">Возвраты</div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/purchase-page.js') }}"></script>
@endsection
