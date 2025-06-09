{{-- ==============================================
            МОДАЛЬНОЕ ОКНО: создать новую Payment
           ============================================== --}}
<div
    id="modalAddPaymentBackdrop"
    class="fixed inset-0 flex items-center justify-center bg-black/20 backdrop-blur-sm z-50 hidden"
>
    <div
        class="absolute inset-0"
        id="modalAddPaymentClose"
    ></div>
    <div class="bg-white rounded-xl shadow-xl p-6 relative z-10 w-full max-w-md animate-fadeIn">
        <header class="mb-6 border-b border-gray-200 pb-3">
            <h3 class="text-2xl font-semibold text-gray-800">Добавить оплату</h3>
        </header>
        <form id="formAddPayment" class="space-y-5">
            @csrf

            {{-- Платформа (exchanger) --}}
            <div>
                <label for="add_exchanger_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Платформа (откуда деньги переводятся)
                </label>
                <select
                    name="exchanger_id"
                    id="add_exchanger_id"
                    class="block w-full bg-white border border-gray-300 rounded-md shadow-sm px-3 py-2
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                           hover:bg-gray-50 transition duration-150 ease-in-out"
                >
                    <option value="" selected>— Выберите платформу —</option>
                    @foreach($exchangers as $e)
                        <option value="{{ $e->id }}">{{ $e->title }}</option>
                    @endforeach
                </select>
                <p class="text-red-600 text-sm mt-1" id="err_add_exchanger_id"></p>
            </div>

            {{-- Сумма продажи --}}
            <div>
                <label for="add_sell_amount" class="block text-sm font-medium text-gray-700 mb-1">
                    Сумма (продажа)
                </label>
                <input
                    type="number"
                    step="0.00000001"
                    name="sell_amount"
                    id="add_sell_amount"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Например, 100.50"
                >
                <p class="text-red-600 text-sm mt-1" id="err_add_sell_amount"></p>
            </div>

            {{-- Валюта продажи --}}
            <div>
                <label for="add_sell_currency_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Валюта (продажа)
                </label>
                <select
                    name="sell_currency_id"
                    id="add_sell_currency_id"
                    class="block w-full bg-white border border-gray-300 rounded-md shadow-sm px-3 py-2
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                           hover:bg-gray-50 transition duration-150 ease-in-out"
                >
                    <option value="" selected>— Выберите валюту —</option>
                    @foreach($currencies as $c)
                        <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                    @endforeach
                </select>
                <p class="text-red-600 text-sm mt-1" id="err_add_sell_currency_id"></p>
            </div>

            {{-- Комментарий --}}
            <div>
                <label for="add_comment" class="block text-sm font-medium text-gray-700 mb-1">
                    Комментарий
                </label>
                <textarea
                    name="comment"
                    id="add_comment"
                    rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Дополнительная информация"
                ></textarea>
                <p class="text-red-600 text-sm mt-1" id="err_add_comment"></p>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <button
                    type="button"
                    id="btnCloseAddPayment"
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
{{-- /END МОДАЛЬНОЕ ОКНО: добавить Payment --}}


{{-- ==============================================
     МОДАЛЬНОЕ ОКНО: создать новый Transfer
    ============================================== --}}
<div
    id="modalAddTransferBackdrop"
    class="fixed inset-0 flex items-center justify-center bg-black/20 backdrop-blur-sm z-50 hidden"
>
    <div
        class="absolute inset-0"
        id="modalAddTransferClose"
    ></div>
    <div class="bg-white rounded-xl shadow-xl p-6 relative z-10 w-full max-w-md animate-fadeIn">
        <header class="mb-6 border-b border-gray-200 pb-3">
            <h3 class="text-2xl font-semibold text-gray-800">Добавить обмен</h3>
        </header>
        <form id="formAddTransfer" class="space-y-5">
            @csrf

            {{-- Платформа: откуда --}}
            <div>
                <label for="add_from_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Платформа “откуда”
                </label>
                <select
                    name="exchanger_from_id"
                    id="add_from_id"
                    class="block w-full bg-white border border-gray-300 rounded-md shadow-sm
                           px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                           hover:bg-gray-50 transition duration-150 ease-in-out"
                >
                    <option value="" selected>— Выберите платформу —</option>
                    @foreach($exchangers as $e)
                        <option value="{{ $e->id }}">{{ $e->title }}</option>
                    @endforeach
                </select>
                <p class="text-red-600 text-sm mt-1" id="err_add_from_id"></p>
            </div>

            {{-- Платформа: куда --}}
            <div>
                <label for="add_to_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Платформа “куда”
                </label>
                <select
                    name="exchanger_to_id"
                    id="add_to_id"
                    class="block w-full bg-white border border-gray-300 rounded-md shadow-sm
                           px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                           hover:bg-gray-50 transition duration-150 ease-in-out"
                >
                    <option value="" selected>— Выберите платформу —</option>
                    @foreach($exchangers as $e)
                        <option value="{{ $e->id }}">{{ $e->title }}</option>
                    @endforeach
                </select>
                <p class="text-red-600 text-sm mt-1" id="err_add_to_id"></p>
            </div>

            {{-- Сумма --}}
            <div>
                <label for="add_amount" class="block text-sm font-medium text-gray-700 mb-1">
                    Сумма
                </label>
                <input
                    type="number"
                    step="0.00000001"
                    name="amount"
                    id="add_amount"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Например, 50.00"
                >
                <p class="text-red-600 text-sm mt-1" id="err_add_amount"></p>
            </div>

            {{-- Валюта суммы --}}
            <div>
                <label for="add_amount_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Валюта суммы
                </label>
                <select
                    name="amount_id"
                    id="add_amount_id"
                    class="block w-full bg-white border border-gray-300 rounded-md shadow-sm
                           px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                           hover:bg-gray-50 transition duration-150 ease-in-out"
                >
                    <option value="" selected>— Выберите валюту —</option>
                    @foreach($currencies as $c)
                        <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                    @endforeach
                </select>
                <p class="text-red-600 text-sm mt-1" id="err_add_amount_id"></p>
            </div>

            {{-- Комиссия --}}
            <div>
                <label for="add_commission" class="block text-sm font-medium text-gray-700 mb-1">
                    Комиссия
                </label>
                <input
                    type="number"
                    step="0.00000001"
                    name="commission"
                    id="add_commission"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Например, 0.0001"
                >
                <p class="text-red-600 text-sm mt-1" id="err_add_commission"></p>
            </div>

            {{-- Валюта комиссии --}}
            <div>
                <label for="add_commission_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Валюта комиссии
                </label>
                <select
                    name="commission_id"
                    id="add_commission_id"
                    class="block w-full bg-white border border-gray-300 rounded-md shadow-sm
                           px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                           hover:bg-gray-50 transition duration-150 ease-in-out"
                >
                    <option value="" selected>— Выберите валюту —</option>
                    @foreach($currencies as $c)
                        <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                    @endforeach
                </select>
                <p class="text-red-600 text-sm mt-1" id="err_add_commission_id"></p>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <button
                    type="button"
                    id="btnCloseAddTransfer"
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
{{-- /END МОДАЛЬНОЕ ОКНО: добавить Transfer --}}


{{-- ==============================================
     МОДАЛЬНОЕ ОКНО: создать новый SaleCrypt
    ============================================== --}}
<div
    id="modalAddSaleCryptBackdrop"
    class="fixed inset-0 flex items-center justify-center bg-black/20 backdrop-blur-sm z-50 hidden"
>
    <div
        class="absolute inset-0"
        id="modalAddSaleCryptClose"
    ></div>

    <div class="bg-white rounded-xl shadow-xl p-6 relative z-10 w-full max-w-lg animate-fadeIn">
        <header class="mb-6 border-b border-gray-200 pb-3">
            <h3 class="text-2xl font-semibold text-gray-800">Добавить продажу крипты</h3>
        </header>
        <form id="formAddSaleCrypt" class="space-y-5">
            @csrf

            {{-- 1) Выбор платформы (exchanger) --}}
            <div>
                <label for="add_sc_exchanger_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Платформа
                </label>
                <select
                    name="exchanger_id"
                    id="add_sc_exchanger_id"
                    class="block w-full bg-white border border-gray-300 rounded-md shadow-sm
                           px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                           hover:bg-gray-50 transition duration-150 ease-in-out"
                >
                    <option value="" selected>— Выберите платформу —</option>
                    @foreach($exchangers as $e)
                        <option value="{{ $e->id }}">{{ $e->title }}</option>
                    @endforeach
                </select>
                <p class="text-red-600 text-sm mt-1" id="err_add_sc_exchanger_id"></p>
            </div>

            {{-- 2) «Продажа»: сумма + валюта --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="add_sc_sale_amount" class="block text-sm font-medium text-gray-700 mb-1">
                        Сумма продажи
                    </label>
                    <input
                        type="number"
                        step="0.00000001"
                        name="sale_amount"
                        id="add_sc_sale_amount"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="0.12345678"
                    >
                    <p class="text-red-600 text-sm mt-1" id="err_add_sc_sale_amount"></p>
                </div>
                <div>
                    <label for="add_sc_sale_currency_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Валюта продажи
                    </label>
                    <select
                        name="sale_currency_id"
                        id="add_sc_sale_currency_id"
                        class="block w-full bg-white border border-gray-300 rounded-md shadow-sm
                               px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               hover:bg-gray-50 transition duration-150 ease-in-out"
                    >
                        <option value="" selected>— Выберите валюту —</option>
                        @foreach($currencies as $c)
                            <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-red-600 text-sm mt-1" id="err_add_sc_sale_currency_id"></p>
                </div>
            </div>

            {{-- 3) «Фикс»: сумма + валюта --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="add_sc_fixed_amount" class="block text-sm font-medium text-gray-700 mb-1">
                        Сумма «Фикс»
                    </label>
                    <input
                        type="number"
                        step="0.00000001"
                        name="fixed_amount"
                        id="add_sc_fixed_amount"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="0.00123456"
                    >
                    <p class="text-red-600 text-sm mt-1" id="err_add_sc_fixed_amount"></p>
                </div>
                <div>
                    <label for="add_sc_fixed_currency_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Валюта «Фикс»
                    </label>
                    <select
                        name="fixed_currency_id"
                        id="add_sc_fixed_currency_id"
                        class="block w-full bg-white border border-gray-300 rounded-md shadow-sm
                               px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               hover:bg-gray-50 transition duration-150 ease-in-out"
                    >
                        <option value="" selected>— Выберите валюту —</option>
                        @foreach($currencies as $c)
                            <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-red-600 text-sm mt-1" id="err_add_sc_fixed_currency_id"></p>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <button
                    type="button"
                    id="btnCloseAddSaleCrypt"
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
{{-- /END МОДАЛЬНОЕ ОКНО: добавить SaleCrypt --}}


{{-- ==============================================
     МОДАЛЬНОЕ ОКНО: создать новый Purchase
============================================== --}}
<div
    id="modalAddPurchaseBackdrop"
    class="fixed inset-0 flex items-center justify-center bg-black/20 backdrop-blur-sm z-50 hidden"
>
    <div
        class="absolute inset-0"
        id="modalAddPurchaseClose"
    ></div>

    <div class="bg-white rounded-xl shadow-xl p-6 relative z-10 w-full max-w-md animate-fadeIn">
        <header class="mb-6 border-b border-gray-200 pb-3">
            <h3 class="text-2xl font-semibold text-gray-800">Добавить покупку крипты</h3>
        </header>
        <form id="formAddPurchase" class="space-y-5">
            @csrf

            {{-- 1) Платформа (exchanger) --}}
            <div>
                <label for="add_purchase_exchanger_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Платформа (откуда продают крипту)
                </label>
                <select
                    name="exchanger_id"
                    id="add_purchase_exchanger_id"
                    class="block w-full bg-white border border-gray-300 rounded-md shadow-sm
                           px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                           hover:bg-gray-50 transition duration-150 ease-in-out"
                >
                    <option value="" selected>— Выберите платформу —</option>
                    @foreach($exchangers as $e)
                        <option value="{{ $e->id }}">{{ $e->title }}</option>
                    @endforeach
                </select>
                <p class="text-red-600 text-sm mt-1" id="err_add_purchase_exchanger_id"></p>
            </div>

            {{-- 2) «Продажа»: сумма + валюта --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="add_purchase_sale_amount" class="block text-sm font-medium text-gray-700 mb-1">
                        Сумма «Продажа»
                    </label>
                    <input
                        type="number"
                        step="0.00000001"
                        name="sale_amount"
                        id="add_purchase_sale_amount"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="0.12345678"
                    >
                    <p class="text-red-600 text-sm mt-1" id="err_add_purchase_sale_amount"></p>
                </div>
                <div>
                    <label for="add_purchase_sale_currency_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Валюта «Продажа»
                    </label>
                    <select
                        name="sale_currency_id"
                        id="add_purchase_sale_currency_id"
                        class="block w-full bg-white border border-gray-300 rounded-md shadow-sm
                               px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               hover:bg-gray-50 transition duration-150 ease-in-out"
                    >
                        <option value="" selected>— Выберите валюту —</option>
                        @foreach($currencies as $c)
                            <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-red-600 text-sm mt-1" id="err_add_purchase_sale_currency_id"></p>
                </div>
            </div>

            {{-- 3) «Получено»: сумма + валюта --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="add_purchase_received_amount" class="block text-sm font-medium text-gray-700 mb-1">
                        Сумма «Получено»
                    </label>
                    <input
                        type="number"
                        step="0.00000001"
                        name="received_amount"
                        id="add_purchase_received_amount"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="0.00123456"
                    >
                    <p class="text-red-600 text-sm mt-1" id="err_add_purchase_received_amount"></p>
                </div>
                <div>
                    <label for="add_purchase_received_currency_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Валюта «Получено»
                    </label>
                    <select
                        name="received_currency_id"
                        id="add_purchase_received_currency_id"
                        class="block w-full bg-white border border-gray-300 rounded-md shadow-sm
                               px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               hover:bg-gray-50 transition duration-150 ease-in-out"
                    >
                        <option value="" selected>— Выберите валюту —</option>
                        @foreach($currencies as $c)
                            <option value="{{ $c->id }}">{{ $c->code }} — {{ $c->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-red-600 text-sm mt-1" id="err_add_purchase_received_currency_id"></p>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <button
                    type="button"
                    id="btnCloseAddPurchase"
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
{{-- /END МОДАЛЬНОЕ ОКНО: добавить Purchase --}}

<div class="mb-6 flex flex-wrap gap-3">
    <button
        id="btnShowAddPayment"
        class="flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg shadow-sm hover:bg-blue-700 transition"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 4v16m8-8H4" />
        </svg>
        Создать оплату
    </button>

    <button
        id="btnShowAddTransfer"
        class="flex items-center px-4 py-2 bg-green-600 text-white font-medium rounded-lg shadow-sm hover:bg-green-700 transition"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 12h16m-8-8v16" />
        </svg>
        Создать перевод
    </button>

    <button
        id="btnShowAddSaleCrypt"
        class="flex items-center px-4 py-2 bg-purple-600 text-white font-medium rounded-lg shadow-sm hover:bg-purple-700 transition"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11 11V5a2 2 0 114 0v6m-2 6v.01M12 16h.01M8 21h8a2 2 0 002-2v-1a2 2 0 00-2-2H8a2 2 0 00-2 2v1a2 2 0 002 2z" />
        </svg>
        Создать продажу крипты
    </button>

    <button
        id="btnShowAddPurchase"
        class="flex items-center px-4 py-2 bg-yellow-600 text-white font-medium rounded-lg shadow-sm hover:bg-yellow-700 transition"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
             viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 14l6-6m0 0l6 6m-6-6v12" />
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
