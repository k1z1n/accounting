{{-- Модалка редактирования --}}
<div id="modalEditSaleCryptBackdrop" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden">
    <div id="modalEditSaleCryptClose" class="absolute inset-0"></div>
    <div class="bg-white rounded-xl shadow-xl p-6 relative z-10 w-full max-w-md">
        <h3 class="text-2xl font-semibold mb-4">
            Редактировать продажу <span id="saleCryptModalId" class="text-blue-600"></span>
        </h3>
        <form id="editSaleCryptForm" class="space-y-4">
            @csrf
            <input type="hidden" id="edit_salecrypt_id">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Платформа</label>
                <select id="edit_salecrypt_exchanger" class="w-full border-gray-300 rounded px-3 py-2">
                    <option disabled>— Выберите платформу —</option>
                    @if(isset($exchangers))
                        @foreach($exchangers as $e)
                            <option value="{{ $e->id }}">{{ $e->title }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Заявка</label>
                <select id="edit_salecrypt_application" class="w-full border-gray-300 rounded px-3 py-2">
                    <option disabled>— Выберите заявку —</option>
                    @if(isset($applications))
                        @foreach($applications as $app)
                            <option value="{{ $app->id }}">Заявка #{{ $app->id }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Продано −</label>
                    <input
                        type="number"
                        step="0.00000001"
                        id="edit_salecrypt_sale_amount"
                        class="w-full border-gray-300 rounded px-3 py-2"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Валюта продажи</label>
                    <select id="edit_salecrypt_sale_currency" class="w-full border-gray-300 rounded px-3 py-2">
                        <option disabled>— Выберите —</option>
                        @if(isset($currenciesForEdit))
                            @foreach($currenciesForEdit as $c)
                                <option value="{{ $c->id }}">{{ $c->code }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Получено +</label>
                    <input
                        type="number"
                        step="0.00000001"
                        id="edit_salecrypt_fixed_amount"
                        class="w-full border-gray-300 rounded px-3 py-2"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Валюта получения</label>
                    <select id="edit_salecrypt_fixed_currency" class="w-full border-gray-300 rounded px-3 py-2">
                        <option disabled>— Выберите —</option>
                        @if(isset($currenciesForEdit))
                            @foreach($currenciesForEdit as $c)
                                <option value="{{ $c->id }}">{{ $c->code }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <button type="button" id="cancelEditSaleCrypt" class="px-4 py-2 bg-gray-200 rounded">Отмена</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Сохранить</button>
            </div>
        </form>
    </div>
</div>

{{-- Модалка удаления --}}
<div id="modalDeleteSaleCryptBackdrop" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden">
    <div id="modalDeleteSaleCryptClose" class="absolute inset-0"></div>
    <div class="bg-white rounded-xl shadow-xl p-6 relative z-10 w-full max-w-xs">
        <h3 class="text-xl font-semibold mb-4">
            Удалить продажу <span id="deleteSaleCryptId" class="text-red-600"></span>?
        </h3>
        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
            <button id="cancelDeleteSaleCrypt" class="px-4 py-2 bg-gray-200 rounded">Отмена</button>
            <button id="confirmDeleteSaleCrypt" class="px-4 py-2 bg-red-600 text-white rounded">Удалить</button>
        </div>
    </div>
</div>
