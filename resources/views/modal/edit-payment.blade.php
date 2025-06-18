{{-- Модалки для «Оплаты» --}}
<div id="modalEditPaymentBackdrop" class="fixed inset-0 flex items-center justify-center bg-black/40 backdrop-blur-sm z-50 hidden mb-0">
    <div id="modalEditPaymentClose" class="absolute inset-0"></div>
    <div class="bg-[#1F1F1F] text-white rounded-xl shadow-2xl p-6 relative z-10 w-full max-w-md animate-fadeIn">
        <h3 class="text-xl font-semibold mb-4">Редактировать оплату <span id="paymentModalId" class="text-blue-600"></span></h3>
        <form id="editPaymentForm" class="space-y-4">
            @csrf<input type="hidden" id="edit_payment_id">
            <div>
                <label class="block text-sm text-gray-700">Платформа</label>
                <select id="edit_payment_exchanger" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-3 py-2 pr-8
                               focus:outline-none focus:ring-2 focus:ring-cyan-500 transition">
                    <option disabled>— Выберите —</option>
                    @foreach($exchangers as $e)<option value="{{ $e->id }}">{{ $e->title }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-700">Сумма</label>
                <input type="number" step="0.00000001" id="edit_payment_amount" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-700">Валюта</label>
                <select id="edit_payment_currency" class="w-full border rounded px-3 py-2">
                    <option disabled>— Выберите —</option>
                    @foreach($currenciesForEdit as $c)<option value="{{ $c->id }}">{{ $c->code }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-700">Комментарий</label>
                <input type="text" id="edit_payment_comment" class="w-full border rounded px-3 py-2">
            </div>
            <div class="flex justify-end space-x-2 pt-4">
                <button type="button" id="cancelEditPayment" class="px-4 py-2 bg-gray-200 rounded">Отмена</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Сохранить</button>
            </div>
        </form>
    </div>
</div>

<div id="modalDeletePaymentBackdrop" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 hidden">
    <div id="modalDeletePaymentClose" class="absolute inset-0"></div>
    <div class="bg-white rounded-xl shadow-xl p-6 relative z-10 w-full max-w-sm">
        <h3 class="text-xl font-semibold mb-4">Удалить оплату <span id="deletePaymentId" class="text-red-600"></span>?</h3>
        <div class="flex justify-end space-x-2 pt-4">
            <button id="cancelDeletePayment" class="px-4 py-2 bg-gray-200 rounded">Отмена</button>
            <button id="confirmDeletePayment" class="px-4 py-2 bg-red-600 text-white rounded">Удалить</button>
        </div>
    </div>
</div>
