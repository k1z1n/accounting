<div
    id="modalAppDetailsBackdrop"
    class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-start justify-center z-50 hidden pt-8 pb-8 px-4"
>
    <div id="modalAppDetailsClose" class="absolute inset-0"></div>
    <div
        class="bg-gradient-to-br from-gray-900 to-gray-800 text-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto relative z-10 animate-fadeIn border border-gray-700 custom-scrollbar"
    >
        <!-- Header -->
        <header class="sticky top-0 bg-gradient-to-r from-gray-900 to-gray-800 border-b border-gray-700 p-6 rounded-t-2xl">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-white">
                            Заявка <span id="detailsAppId" class="text-cyan-400 font-mono"></span>
            </h3>
                        <p class="text-gray-400 text-sm">Детальная информация о заявке</p>
                    </div>
                </div>
            <button
                id="btnCloseAppDetails"
                    class="text-gray-400 hover:text-white p-2 rounded-lg hover:bg-gray-700 transition-colors duration-200"
                aria-label="Закрыть"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            </div>
        </header>

        <!-- Content -->
        <div class="p-6 space-y-8">
            <!-- Основная информация -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Номер заявки -->
                <div class="bg-gray-800/50 rounded-xl p-4 border border-gray-700">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs font-medium uppercase tracking-wide">Номер заявки</p>
                            <p id="detailsAppNumber" class="text-white font-mono text-lg font-semibold">—</p>
                        </div>
                    </div>
                </div>

                <!-- Дата создания -->
                <div class="bg-gray-800/50 rounded-xl p-4 border border-gray-700">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-green-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs font-medium uppercase tracking-wide">Дата создания</p>
                            <p id="detailsCreatedAt" class="text-white text-sm">—</p>
                        </div>
                    </div>
                </div>

                <!-- Обменник -->
                <div class="bg-gray-800/50 rounded-xl p-4 border border-gray-700">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-purple-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs font-medium uppercase tracking-wide">Обменник</p>
                            <p id="detailsExchanger" class="text-white font-semibold">—</p>
                        </div>
                    </div>
                </div>

                <!-- Статус -->
                <div class="bg-gray-800/50 rounded-xl p-4 border border-gray-700">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-yellow-500/20 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-gray-400 text-xs font-medium uppercase tracking-wide">Статус</p>
                            <p id="detailsStatus" class="text-white font-semibold">—</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Финансовая информация -->
            <div class="bg-gray-800/30 rounded-2xl p-6 border border-gray-700">
                <h4 class="text-lg font-semibold text-white mb-4 flex items-center space-x-2">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    <span>Финансовая сводка</span>
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Приход+ -->
                    <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-400 text-xs font-medium uppercase tracking-wide">Приход +</p>
                                <p id="detailsIncoming" class="text-white font-semibold text-lg">—</p>
                            </div>
                            <div class="w-8 h-8 bg-green-500/20 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Продажа− -->
                    <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-400 text-xs font-medium uppercase tracking-wide">Продажа −</p>
                                <p id="detailsSell" class="text-white font-semibold text-lg">—</p>
                            </div>
                            <div class="w-8 h-8 bg-red-500/20 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Купля+ -->
                    <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-400 text-xs font-medium uppercase tracking-wide">Купля +</p>
                                <p id="detailsBuy" class="text-white font-semibold text-lg">—</p>
                            </div>
                            <div class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                                </svg>
                            </div>
                        </div>
        </div>

                    <!-- Расход− -->
                    <div class="bg-orange-500/10 border border-orange-500/20 rounded-xl p-4">
                        <div class="flex items-center justify-between">
            <div>
                                <p class="text-orange-400 text-xs font-medium uppercase tracking-wide">Расход −</p>
                                <p id="detailsExpense" class="text-white font-semibold text-lg">—</p>
                            </div>
                            <div class="w-8 h-8 bg-orange-500/20 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Мерчант и ID ордера -->
            <div class="bg-gray-800/30 rounded-2xl p-6 border border-gray-700">
                <h4 class="text-lg font-semibold text-white mb-4 flex items-center space-x-2">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span>Информация о мерчанте</span>
                </h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-400 text-sm mb-1">Мерчант</p>
                        <p id="detailsMerchant" class="text-gray-300 text-lg">—</p>
            </div>
            <div>
                        <p class="text-gray-400 text-sm mb-1">ID ордера</p>
                        <p id="detailsOrderId" class="text-gray-300 text-lg">—</p>
                    </div>
                </div>
            </div>

            <!-- Связанные операции -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Сопутствующие покупки -->
                <div class="bg-gray-800/30 rounded-2xl p-6 border border-gray-700">
                    <h4 class="text-lg font-semibold text-white mb-4 flex items-center space-x-2">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"></path>
                        </svg>
                        <span>Сопутствующие покупки</span>
                    </h4>
                    <div id="detailsPurchaseList" class="space-y-3">
                        <p class="text-gray-400 text-sm italic">Нет связанных покупок</p>
                    </div>
                </div>

                <!-- Продажи крипты -->
                <div class="bg-gray-800/30 rounded-2xl p-6 border border-gray-700">
                    <h4 class="text-lg font-semibold text-white mb-4 flex items-center space-x-2">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"></path>
                        </svg>
                        <span>Продажи крипты</span>
                    </h4>
                    <div id="detailsSaleList" class="space-y-3">
                        <p class="text-gray-400 text-sm italic">Нет связанных продаж</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
