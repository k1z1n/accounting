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
                        @foreach($currenciesForEdit as $c)
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
                        @foreach($currenciesForEdit as $c)
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
