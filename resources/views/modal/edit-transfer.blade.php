{{-- Edit Transfer Modal --}}
<div id="modalEditTransferBackdrop" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden">
    <div id="modalEditTransferClose" class="absolute inset-0"></div>
    <div class="bg-[#1F1F1F] text-white rounded-xl p-6 relative z-10 w-full max-w-md">
        <h3 class="text-2xl mb-4">Редактировать перевод <span id="transferModalId" class="text-cyan-400"></span></h3>
        <form id="editTransferForm" class="space-y-4">
            @csrf @method('PUT')
            <input type="hidden" id="edit_transfer_id" name="id">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Откуда --}}
                <div>
                    <label class="block text-sm mb-1">Откуда</label>
                    <select id="edit_transfer_from" name="exchanger_from_id" class="w-full bg-gray-800">
                        <option value="" disabled>—</option>
                        @foreach($exchangers as $e)
                            <option value="{{ $e->id }}">{{ $e->title }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Куда --}}
                <div>
                    <label class="block text-sm mb-1">Куда</label>
                    <select id="edit_transfer_to" name="exchanger_to_id" class="w-full bg-gray-800">
                        <option value="" disabled>—</option>
                        @foreach($exchangers as $e)
                            <option value="{{ $e->id }}">{{ $e->title }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Сумма --}}
                <div>
                    <label class="block text-sm mb-1">Сумма</label>
                    <input id="edit_transfer_amount" name="amount" type="number" step="0.00000001" class="w-full bg-gray-800">
                </div>

                {{-- Валюта суммы --}}
                <div>
                    <label class="block text-sm mb-1">Валюта суммы</label>
                    <select id="edit_transfer_amount_currency" name="amount_currency_id" class="w-full bg-gray-800">
                        <option value="" disabled>—</option>
                        @foreach($currenciesForEdit as $c)
                            <option value="{{ $c->id }}">{{ $c->code }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Комиссия --}}
                <div>
                    <label class="block text-sm mb-1">Комиссия</label>
                    <input id="edit_transfer_commission" name="commission" type="number" step="0.00000001" class="w-full bg-gray-800">
                </div>

                {{-- Валюта комиссии --}}
                <div>
                    <label class="block text-sm mb-1">Валюта комиссии</label>
                    <select id="edit_transfer_commission_currency" name="commission_currency_id" class="w-full bg-gray-800">
                        <option value="" disabled>—</option>
                        @foreach($currenciesForEdit as $c)
                            <option value="{{ $c->id }}">{{ $c->code }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end space-x-2">
                <button type="button" id="cancelEditTransfer" class="px-4 py-2 bg-gray-700 rounded">Отмена</button>
                <button type="submit" class="px-4 py-2 bg-green-600 rounded">Сохранить</button>
            </div>
        </form>
    </div>
</div>

{{-- Delete --}}
<div id="modalDeleteTransferBackdrop" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden">
    <div id="modalDeleteTransferClose" class="absolute inset-0"></div>
    <div class="bg-white rounded-xl p-6 relative z-10 w-full max-w-sm">
        <h3 class="text-xl mb-4">Удалить перевод <span id="deleteTransferId" class="text-red-600"></span>?</h3>
        <div class="flex justify-end space-x-2">
            <button type="button" id="cancelDeleteTransfer" class="px-4 py-2 bg-gray-200 rounded">Отмена</button>
            <button id="confirmDeleteTransfer" class="px-4 py-2 bg-red-600 rounded">Удалить</button>
        </div>
    </div>
</div>
