{{-- ==============================================
     МОДАЛЬНОЕ ОКНО: создать новую Payment
   ============================================== --}}
<div
        id="modalAddPaymentBackdrop"
        class="fixed inset-0 flex items-center justify-center bg-black/40 backdrop-blur-sm z-50 hidden mb-0"
>
    <div id="modalAddPaymentClose" class="absolute inset-0"></div>

    <div class="bg-[#1F1F1F] text-white rounded-xl shadow-2xl p-6 relative z-10 w-full max-w-md animate-fadeIn">
        <header class="mb-6 border-b border-gray-700 pb-3">
            <h3 class="text-2xl font-semibold">Добавить оплату</h3>
        </header>
        <form id="formAddPayment" class="space-y-5">
            @csrf

            {{-- Платформа --}}
            <div>
                <label for="add_exchanger_id" class="block text-sm font-medium text-gray-300 mb-1">
                    Платформа
                </label>
                <div class="relative">
                    <select
                            name="exchanger_id"
                            id="add_exchanger_id"
                            class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 pr-8
                               focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
                    >
                        <option value="" selected>— Выберите платформу —</option>
                        @foreach($exchangers as $e)
                            <option value="{{ $e->id }}">{{ $e->title }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <p id="err_add_exchanger_id" class="mt-1 text-sm text-red-500"></p>
            </div>

            {{-- Сумма --}}
            {{-- Получено --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="add_sell_amount" class="block text-sm font-medium text-gray-300 mb-1">
                        Сумма (продажа)
                    </label>
                    <input
                            type="number"
                            step="0.00000001"
                            name="sell_amount"
                            id="add_sell_amount"
                            class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2
                           focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
                            placeholder="Например, 100.50"
                    >
                    <p id="err_add_sell_amount" class="mt-1 text-sm text-red-500"></p>
                </div>
                {{-- Валюта --}}
                <div>
                    <label for="add_sell_currency_id" class="block text-sm font-medium text-gray-300 mb-1">
                        Валюта (продажа)
                    </label>
                    <div class="relative">
                        <select
                                name="sell_currency_id"
                                id="add_sell_currency_id"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 pr-8
                               focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
                        >
                            <option value="" selected>— Выберите валюту —</option>
                            @foreach($currenciesForEdit as $c)
                                <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    <p id="err_add_sell_currency_id" class="mt-1 text-sm text-red-500"></p>
                </div>
            </div>


            {{-- Комментарий --}}
            <div>
                <label for="add_comment" class="block text-sm font-medium text-gray-300 mb-1">
                    Комментарий
                </label>
                <textarea
                        name="comment"
                        id="add_comment"
                        rows="2"
                        class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2
                           focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
                        placeholder="Дополнительная информация"
                ></textarea>
                <p id="err_add_comment" class="mt-1 text-sm text-red-500"></p>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-700">
                <button
                        type="button"
                        id="btnCloseAddPayment"
                        class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition"
                >Отмена
                </button>
                <button
                        type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-500 transition"
                >Сохранить
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ==============================================
     МОДАЛЬНОЕ ОКНО: создать новый Transfer
   ============================================== --}}
<div
        id="modalAddTransferBackdrop"
        class="fixed inset-0 flex items-center justify-center bg-black/40 backdrop-blur-sm z-50 hidden mb-0"
>
    <div id="modalAddTransferClose" class="absolute inset-0"></div>

    <div class="bg-[#1F1F1F] text-white rounded-xl shadow-2xl p-6 relative z-10 w-full max-w-md animate-fadeIn">
        <header class="mb-6 border-b border-gray-700 pb-3">
            <h3 class="text-2xl font-semibold">Добавить перевод</h3>
        </header>
        <form id="formAddTransfer" class="space-y-5">
            @csrf

            {{-- Откуда --}}
            <div>
                <label for="add_from_id" class="block text-sm font-medium text-gray-300 mb-1">
                    Платформа «откуда»
                </label>
                <div class="relative">
                    <select
                            name="exchanger_from_id"
                            id="add_from_id"
                            class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 pr-8
                               focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
                    >
                        <option value="" selected>— Выберите платформу —</option>
                        @foreach($exchangers as $e)
                            <option value="{{ $e->id }}">{{ $e->title }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <p id="err_add_from_id" class="mt-1 text-sm text-red-500"></p>
            </div>

            {{-- Куда --}}
            <div>
                <label for="add_to_id" class="block text-sm font-medium text-gray-300 mb-1">
                    Платформа «куда»
                </label>
                <div class="relative">
                    <select
                            name="exchanger_to_id"
                            id="add_to_id"
                            class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 pr-8
                               focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
                    >
                        <option value="" selected>— Выберите платформу —</option>
                        @foreach($exchangers as $e)
                            <option value="{{ $e->id }}">{{ $e->title }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <p id="err_add_to_id" class="mt-1 text-sm text-red-500"></p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">                 {{-- Сумма --}}
                <div>
                    <label for="add_amount" class="block text-sm font-medium text-gray-300 mb-1">
                        Сумма
                    </label>
                    <input
                            type="number"
                            step="0.00000001"
                            name="amount"
                            id="add_amount"
                            class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2
                           focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
                            placeholder="Например, 50.00"
                    >
                    <p id="err_add_amount" class="mt-1 text-sm text-red-500"></p>
                </div>

                {{-- Валюта --}}
                <div>
                    <label for="add_amount_id" class="block text-sm font-medium text-gray-300 mb-1">
                        Валюта суммы
                    </label>
                    <div class="relative">
                        <select
                                name="amount_id"
                                id="add_amount_id"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 pr-8
                               focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
                        >
                            <option value="" selected>— Выберите валюту —</option>
                            @foreach($currenciesForEdit as $c)
                                <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
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
                    <p id="err_add_amount_id" class="mt-1 text-sm text-red-500"></p>
                </div>
            </div>
            {{-- Комиссия --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="add_commission" class="block text-sm font-medium text-gray-300 mb-1">
                        Комиссия
                    </label>
                    <input
                            type="number"
                            step="0.00000001"
                            name="commission"
                            id="add_commission"
                            class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2
                           focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
                            placeholder="Например, 0.0001"
                    >
                    <p id="err_add_commission" class="mt-1 text-sm text-red-500"></p>
                </div>

                {{-- Валюта комиссии --}}
                <div>
                    <label for="add_commission_id" class="block text-sm font-medium text-gray-300 mb-1">
                        Валюта комиссии
                    </label>
                    <div class="relative">
                        <select
                                name="commission_id"
                                id="add_commission_id"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 pr-8
                               focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
                        >
                            <option value="" selected>— Выберите валюту —</option>
                            @foreach($currenciesForEdit as $c)
                                <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    <p id="err_add_commission_id" class="mt-1 text-sm text-red-500"></p>
                </div>
            </div>
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-700">
                <button
                        type="button"
                        id="btnCloseAddTransfer"
                        class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition"
                >Отмена
                </button>
                <button
                        type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-500 transition"
                >Сохранить
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ==============================================
     МОДАЛЬНОЕ ОКНО: создать новый SaleCrypt
   ============================================== --}}
<div
        id="modalAddSaleCryptBackdrop"
        class="fixed inset-0 flex items-center justify-center bg-black/40 backdrop-blur-sm z-50 hidden mb-0"
>
    <div id="modalAddSaleCryptClose" class="absolute inset-0"></div>

    <div class="bg-[#1F1F1F] text-white rounded-xl shadow-2xl p-6 relative z-10 w-full max-w-lg animate-fadeIn">
        <header class="mb-6 border-b border-gray-700 pb-3">
            <h3 class="text-2xl font-semibold">Добавить продажу крипты</h3>
        </header>
        <form id="formAddSaleCrypt" class="space-y-5">
            @csrf

            {{-- Платформа --}}
            <div>
                <label for="add_sc_exchanger_id" class="block text-sm font-medium text-gray-300 mb-1">
                    Платформа
                </label>
                <div class="relative">
                    <select
                            name="exchanger_id"
                            id="add_sc_exchanger_id"
                            class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 pr-8
                               focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
                    >
                        <option value="" selected>— Выберите платформу —</option>
                        @foreach($exchangers as $e)
                            <option value="{{ $e->id }}">{{ $e->title }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <p id="err_add_sc_exchanger_id" class="mt-1 text-sm text-red-500"></p>
            </div>

            {{-- В начало формы Add SaleCrypt (после Платформа) --}}
            <div>
                <label for="add_sc_application_id" class="block text-sm font-medium text-gray-300 mb-1">
                    Заявка (optional)
                </label>
                <div class="relative">
                    <select
                        name="application_id"
                        id="add_sc_application_id"
                        class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 pr-8
                   focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
                    >
                        <option value="" selected>— Не привязывать —</option>
                        @foreach($apps as $app)
                            <option value="{{ $app->id }}">{{ $app->app_id }} ({{ $app->exchanger }})</option>
                        @endforeach
                    </select>
                </div>
                <p id="err_add_sc_application_id" class="mt-1 text-sm text-red-500"></p>
            </div>

            {{-- Продажа --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="add_sc_sale_amount" class="block text-sm font-medium text-gray-300 mb-1">
                        Сумма проданной валюты
                    </label>
                    <input
                            type="number"
                            step="0.00000001"
                            name="sale_amount"
                            id="add_sc_sale_amount"
                            class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
                            placeholder="0.12345678"
                    >
                    <p id="err_add_sc_sale_amount" class="mt-1 text-sm text-red-500"></p>
                </div>
                <div>
                    <label for="add_sc_sale_currency_id" class="block text-sm font-medium text-gray-300 mb-1">
                        Проданная валюты
                    </label>
                    <div class="relative">
                        <select
                                name="sale_currency_id"
                                id="add_sc_sale_currency_id"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 pr-8
                                   focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
                        >
                            <option value="" selected>— Выберите валюту —</option>
                            @foreach($currenciesForEdit as $c)
                                <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    <p id="err_add_sc_sale_currency_id" class="mt-1 text-sm text-red-500"></p>
                </div>
            </div>

            {{-- Фикс --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="add_sc_fixed_amount" class="block text-sm font-medium text-gray-300 mb-1">
                        Сумма полученной валюты
                    </label>
                    <input
                            type="number"
                            step="0.00000001"
                            name="fixed_amount"
                            id="add_sc_fixed_amount"
                            class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
                            placeholder="0.00123456"
                    >
                    <p id="err_add_sc_fixed_amount" class="mt-1 text-sm text-red-500"></p>
                </div>
                <div>
                    <label for="add_sc_fixed_currency_id" class="block text-sm font-medium text-gray-300 mb-1">
                        Полученная валюта
                    </label>
                    <div class="relative">
                        <select
                                name="fixed_currency_id"
                                id="add_sc_fixed_currency_id"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 pr-8
                                   focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
                        >
                            <option value="" selected>— Выберите валюту —</option>
                            @foreach($currenciesForEdit as $c)
                                <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    <p id="err_add_sc_fixed_currency_id" class="mt-1 text-sm text-red-500"></p>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-700">
                <button
                        type="button"
                        id="btnCloseAddSaleCrypt"
                        class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition"
                >Отмена
                </button>
                <button
                        type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-500 transition"
                >Сохранить
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ==============================================
     МОДАЛЬНОЕ ОКНО: создать новый Purchase
   ============================================== --}}
<div
        id="modalAddPurchaseBackdrop"
        class="fixed inset-0 flex items-center justify-center bg-black/40 backdrop-blur-sm z-50 hidden mb-0"
>
    <div id="modalAddPurchaseClose" class="absolute inset-0"></div>

    <div class="bg-[#1F1F1F] text-white rounded-xl shadow-2xl p-6 relative z-10 w-full max-w-md animate-fadeIn">
        <header class="mb-6 border-b border-gray-700 pb-3">
            <h3 class="text-2xl font-semibold">Добавить покупку крипты</h3>
        </header>
        <form id="formAddPurchase" class="space-y-5">
            @csrf

            {{-- Платформа --}}
            <div>
                <label for="add_purchase_exchanger_id" class="block text-sm font-medium text-gray-300 mb-1">
                    Платформа
                </label>
                <div class="relative">
                    <select
                            name="exchanger_id"
                            id="add_purchase_exchanger_id"
                            class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 pr-8
                               focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
                    >
                        <option value="" selected>— Выберите платформу —</option>
                        @foreach($exchangers as $e)
                            <option value="{{ $e->id }}">{{ $e->title }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <p id="err_add_purchase_exchanger_id" class="mt-1 text-sm text-red-500"></p>
            </div>

            <div>
                <label for="add_purchase_application_id" class="block text-sm font-medium text-gray-300 mb-1">
                    Заявка (optional)
                </label>
                <div class="relative">
                    <select
                        name="application_id"
                        id="add_purchase_application_id"
                        class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 pr-8
                   focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
                    >
                        <option value="" selected>— Не привязывать —</option>
                        @foreach($apps as $app)
                            <option value="{{ $app->id }}">{{ $app->app_id }} ({{ $app->exchanger }})</option>
                        @endforeach
                    </select>
                </div>
                <p id="err_add_purchase_application_id" class="mt-1 text-sm text-red-500"></p>
            </div>

            {{-- Продажа --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="add_purchase_sale_amount" class="block text-sm font-medium text-gray-300 mb-1">
                        Сумма потраченного USDT
                    </label>
                    <input
                            type="number"
                            step="0.00000001"
                            name="sale_amount"
                            id="add_purchase_sale_amount"
                            class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
                            placeholder="0.12345678"
                    >
                    <p id="err_add_purchase_sale_amount" class="mt-1 text-sm text-red-500"></p>
                </div>
                <div>
                    <label for="add_purchase_sale_currency_id"
                           class="block text-sm font-medium text-gray-300 mb-1 whitespace-nowrap">
                        Валюта потраченного USDT
                    </label>
                    <div class="relative">
                        <select
                                name="sale_currency_id"
                                id="add_purchase_sale_currency_id"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 pr-8
                                   focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
                        >
                            <option value="" selected>— Выберите валюту —</option>
                            @foreach($currenciesForEdit as $c)
                                <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    <p id="err_add_purchase_sale_currency_id" class="mt-1 text-sm text-red-500"></p>
                </div>
            </div>

            {{-- Получено --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="add_purchase_received_amount" class="block text-sm font-medium text-gray-300 mb-1">
                        Сумма полученной крипты
                    </label>
                    <input
                            type="number"
                            step="0.00000001"
                            name="received_amount"
                            id="add_purchase_received_amount"
                            class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2
                               focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
                            placeholder="0.00123456"
                    >
                    <p id="err_add_purchase_received_amount" class="mt-1 text-sm text-red-500"></p>
                </div>
                <div>
                    <label for="add_purchase_received_currency_id" class="block text-sm font-medium text-gray-300 mb-1">
                        Валюта полученной крипты
                    </label>
                    <div class="relative">
                        <select
                                name="received_currency_id"
                                id="add_purchase_received_currency_id"
                                class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 pr-8
                                   focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
                        >
                            <option value="" selected>— Выберите валюту —</option>
                            @foreach($currenciesForEdit as $c)
                                <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    <p id="err_add_purchase_received_currency_id" class="mt-1 text-sm text-red-500"></p>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-700">
                <button
                        type="button"
                        id="btnCloseAddPurchase"
                        class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition"
                >Отмена
                </button>
                <button
                        type="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-500 transition"
                >Сохранить
                </button>
            </div>
        </form>
    </div>
</div>

<div class="mb-6 flex flex-wrap gap-3 bg-[#1F1F1F] p-4 rounded-xl">
    <!-- Оплата -->
    <button
            id="btnShowAddPayment"
            class="flex items-center px-4 py-2 bg-cyan-600 text-white font-medium rounded-lg shadow hover:bg-cyan-500 transition border border-cyan-700"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 4v16m8-8H4"/>
        </svg>
        Создать оплату
    </button>

    <!-- Перевод -->
    <button
            id="btnShowAddTransfer"
            class="flex items-center px-4 py-2 bg-emerald-600 text-white font-medium rounded-lg shadow hover:bg-emerald-500 transition border border-emerald-700"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 12h16m-8-8v16"/>
        </svg>
        Создать перевод
    </button>

    <!-- Продажа крипты -->
    <button
            id="btnShowAddSaleCrypt"
            class="flex items-center px-4 py-2 bg-violet-600 text-white font-medium rounded-lg shadow hover:bg-violet-500 transition border border-violet-700"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11 11V5a2 2 0 114 0v6m-2 6v.01M12 16h.01M8 21h8a2 2 0 002-2v-1a2 2 0 00-2-2H8a2 2 0 00-2 2v1a2 2 0 002 2z"/>
        </svg>
        Создать продажу крипты
    </button>

    <!-- Покупка крипты -->
    <button
            id="btnShowAddPurchase"
            class="flex items-center px-4 py-2 bg-amber-600 text-white font-medium rounded-lg shadow hover:bg-amber-500 transition border border-amber-700"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 14l6-6m0 0l6 6m-6-6v12"/>
        </svg>
        Создать покупку крипты
    </button>
</div>


{{-- ==============================================
     JavaScript: открытие/закрытие модалок + AJAX-отправка
    ============================================== --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Удобная функция для показа/скрытия модального окна
        function showModal(backdropId) {
            document.getElementById(backdropId).style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function hideModal(backdropId) {
            document.getElementById(backdropId).style.display = 'none';
            document.body.style.overflow = '';
        }

        // ----------------------
        // 1. Модалка “Add Payment”
        // ----------------------
        const btnShowAddPayment = document.getElementById('btnShowAddPayment');
        const modalAddPaymentBackdrop = document.getElementById('modalAddPaymentBackdrop');
        const btnCloseAddPayment = document.getElementById('btnCloseAddPayment');
        const formAddPayment = document.getElementById('formAddPayment');

        btnShowAddPayment.addEventListener('click', () => showModal('modalAddPaymentBackdrop'));
        btnCloseAddPayment.addEventListener('click', () => hideModal('modalAddPaymentBackdrop'));
        document.getElementById('modalAddPaymentClose')
            .addEventListener('click', () => hideModal('modalAddPaymentBackdrop'));

        formAddPayment.addEventListener('submit', function (e) {
            e.preventDefault();
            // Сначала очищаем предыдущие ошибки
            ['exchanger_id', 'sell_amount', 'sell_currency_id', 'comment']
                .forEach(f => {
                    const el = document.getElementById('err_add_' + f);
                    if (el) el.textContent = '';
                });

            const data = {
                exchanger_id: document.getElementById('add_exchanger_id').value,
                sell_amount: document.getElementById('add_sell_amount').value.trim(),
                sell_currency_id: document.getElementById('add_sell_currency_id').value,
                comment: document.getElementById('add_comment').value.trim(),
            };

            fetch("{{ route('payments.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
                .then(res => {
                    if (res.status === 201 || res.status === 200) {
                        return res.json();
                    }
                    if (res.status === 422) {
                        return res.json().then(json => Promise.reject({validation: json.errors}));
                    }
                    throw new Error(`Статус ${res.status}`);
                })
                .then(json => {
                    // Можно: динамически вставить новую строку в таблицу, но для простоты – полный reload:
                    window.location.reload();
                })
                .catch(err => {
                    if (err.validation) {
                        Object.entries(err.validation).forEach(([field, messages]) => {
                            const el = document.getElementById('err_add_' + field);
                            if (el) el.textContent = messages[0];
                        });
                    } else {
                        console.error('Ошибка при добавлении Payment:', err);
                        alert('Не удалось сохранить Payment');
                    }
                });
        });

        // ----------------------
        // 2. Модалка “Add Transfer”
        // ----------------------
        const btnShowAddTransfer = document.getElementById('btnShowAddTransfer');
        const modalAddTransferBackdrop = document.getElementById('modalAddTransferBackdrop');
        const btnCloseAddTransfer = document.getElementById('btnCloseAddTransfer');
        const formAddTransfer = document.getElementById('formAddTransfer');

        btnShowAddTransfer.addEventListener('click', () => showModal('modalAddTransferBackdrop'));
        btnCloseAddTransfer.addEventListener('click', () => hideModal('modalAddTransferBackdrop'));
        document.getElementById('modalAddTransferClose')
            .addEventListener('click', () => hideModal('modalAddTransferBackdrop'));

        formAddTransfer.addEventListener('submit', function (e) {
            e.preventDefault();
            ['exchanger_from_id', 'exchanger_to_id', 'amount', 'amount_id', 'commission', 'commission_id']
                .forEach(f => {
                    const el = document.getElementById('err_add_' + f);
                    if (el) el.textContent = '';
                });

            const data = {
                exchanger_from_id: document.getElementById('add_from_id').value,
                exchanger_to_id: document.getElementById('add_to_id').value,
                amount: document.getElementById('add_amount').value.trim(),
                amount_id: document.getElementById('add_amount_id').value,
                commission: document.getElementById('add_commission').value.trim(),
                commission_id: document.getElementById('add_commission_id').value,
            };

            fetch("{{ route('transfers.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
                .then(res => {
                    if (res.status === 201 || res.status === 200) {
                        return res.json();
                    }
                    if (res.status === 422) {
                        return res.json().then(json => Promise.reject({validation: json.errors}));
                    }
                    throw new Error(`Статус ${res.status}`);
                })
                .then(json => {
                    window.location.reload();
                })
                .catch(err => {
                    if (err.validation) {
                        Object.entries(err.validation).forEach(([field, messages]) => {
                            const el = document.getElementById('err_add_' + field);
                            if (el) el.textContent = messages[0];
                        });
                    } else {
                        console.error('Ошибка при добавлении Transfer:', err);
                        alert('Не удалось сохранить Transfer');
                    }
                });
        });

        // ----------------------
        // 3. Модалка “Add SaleCrypt”
        // ----------------------
        const btnShowAddSaleCrypt = document.getElementById('btnShowAddSaleCrypt');
        const modalAddSaleCryptBackdrop = document.getElementById('modalAddSaleCryptBackdrop');
        const btnCloseAddSaleCrypt = document.getElementById('btnCloseAddSaleCrypt');
        const formAddSaleCrypt = document.getElementById('formAddSaleCrypt');

        btnShowAddSaleCrypt.addEventListener('click', () => showModal('modalAddSaleCryptBackdrop'));
        btnCloseAddSaleCrypt.addEventListener('click', () => hideModal('modalAddSaleCryptBackdrop'));
        document.getElementById('modalAddSaleCryptClose')
            .addEventListener('click', () => hideModal('modalAddSaleCryptBackdrop'));

        formAddSaleCrypt.addEventListener('submit', function (e) {
            e.preventDefault();
            ['exchanger_id', 'sale', 'fixed']
                .forEach(f => {
                    const el = document.getElementById('err_add_sc_' + f);
                    if (el) el.textContent = '';
                });

            const data = {
                application_id: document.getElementById('add_sc_application_id').value || null,
                exchanger_id: document.getElementById('add_sc_exchanger_id').value,
                sale_amount: document.getElementById('add_sc_sale_amount').value.trim(),
                sale_currency_id: document.getElementById('add_sc_sale_currency_id').value,
                fixed_amount: document.getElementById('add_sc_fixed_amount').value.trim(),
                fixed_currency_id: document.getElementById('add_sc_fixed_currency_id').value,
            };

            fetch("{{ route('salecrypts.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
                .then(res => {
                    if (res.status === 201 || res.status === 200) {
                        return res.json();
                    }
                    if (res.status === 422) {
                        return res.json().then(json => Promise.reject({validation: json.errors}));
                    }
                    throw new Error(`Статус ${res.status}`);
                })
                .then(json => {
                    window.location.reload();
                })
                .catch(err => {
                    if (err.validation) {
                        Object.entries(err.validation).forEach(([field, messages]) => {
                            const el = document.getElementById('err_add_sc_' + field);
                            if (el) el.textContent = messages[0];
                        });
                    } else {
                        console.error('Ошибка при добавлении SaleCrypt:', err);
                        alert('Не удалось сохранить SaleCrypt');
                    }
                });
        });

        // ===========================
        // 4. Новый блок: Add Purchase
        // ===========================
        const btnShowAddPurchase = document.getElementById('btnShowAddPurchase');
        const modalAddPurchaseBackdrop = document.getElementById('modalAddPurchaseBackdrop');
        const btnCloseAddPurchase = document.getElementById('btnCloseAddPurchase');
        const formAddPurchase = document.getElementById('formAddPurchase');

        btnShowAddPurchase.addEventListener('click', () => showModal('modalAddPurchaseBackdrop'));
        btnCloseAddPurchase.addEventListener('click', () => hideModal('modalAddPurchaseBackdrop'));
        document.getElementById('modalAddPurchaseClose')
            .addEventListener('click', () => hideModal('modalAddPurchaseBackdrop'));

        formAddPurchase.addEventListener('submit', function (e) {
            e.preventDefault();
            ['purchase_exchanger_id', 'purchase_sale_amount', 'purchase_sale_currency_id', 'purchase_received_amount', 'purchase_received_currency_id']
                .forEach(f => {
                    const el = document.getElementById('err_add_' + f);
                    if (el) el.textContent = '';
                });

            const data = {
                application_id: document.getElementById('add_purchase_application_id').value || null,
                exchanger_id: document.getElementById('add_purchase_exchanger_id').value,
                sale_amount: document.getElementById('add_purchase_sale_amount').value.trim(),
                sale_currency_id: document.getElementById('add_purchase_sale_currency_id').value,
                received_amount: document.getElementById('add_purchase_received_amount').value.trim(),
                received_currency_id: document.getElementById('add_purchase_received_currency_id').value,
            };

            fetch("{{ route('purchases.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
                .then(res => {
                    if (res.status === 201 || res.status === 200) {
                        return res.json();
                    }
                    if (res.status === 422) {
                        return res.json().then(json => Promise.reject({validation: json.errors}));
                    }
                    throw new Error(`Статус ${res.status}`);
                })
                .then(json => window.location.reload())
                .catch(err => {
                    if (err.validation) {
                        Object.entries(err.validation).forEach(([field, messages]) => {
                            const el = document.getElementById('err_add_' + field);
                            if (el) el.textContent = messages[0];
                        });
                    } else {
                        console.error('Ошибка при добавлении Purchase:', err);
                        alert('Не удалось сохранить Purchase');
                    }
                });
        });

    });
</script>
