<div
    id="modalAppDetailsBackdrop"
    class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-start justify-center z-50 hidden pt-20"
>
    <div id="modalAppDetailsClose" class="absolute inset-0"></div>
    <div
        class="bg-[#1F1F1F] text-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[80vh] overflow-y-auto p-6 relative z-10 animate-fadeIn"
    >
        <header class="flex justify-between items-center mb-4 border-b border-gray-700 pb-3">
            <h3 class="text-2xl font-semibold">
                Заявка&nbsp;<span id="detailsAppId" class="text-cyan-400"></span>
            </h3>
            <button
                id="btnCloseAppDetails"
                class="text-gray-400 hover:text-white p-1 rounded"
                aria-label="Закрыть"
            >
                <!-- простой крестик -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </header>

        <div id="detailsContent" class="grid grid-cols-2 gap-4 text-sm text-gray-200 mb-6">
            <div><span class="font-medium">Номер заявки:</span> <span id="detailsAppNumber">—</span></div>
            <div><span class="font-medium">Дата создания:</span> <span id="detailsCreatedAt">—</span></div>
            <div><span class="font-medium">Обменник:</span> <span id="detailsExchanger">—</span></div>
            <div><span class="font-medium">Статус:</span> <span id="detailsStatus">—</span></div>
            <div><span class="font-medium">Приход+:</span> <span id="detailsIncoming">—</span></div>
            <div><span class="font-medium">Продажа−:</span> <span id="detailsSell">—</span></div>
            <div><span class="font-medium">Купля+:</span> <span id="detailsBuy">—</span></div>
            <div><span class="font-medium">Расход−:</span> <span id="detailsExpense">—</span></div>
            <div class="col-span-2"><span class="font-medium">Мерчант:</span> <span id="detailsMerchant">—</span></div>
        </div>

        <div id="detailsLists" class="space-y-4">
            <div>
                <h4 class="text-lg font-medium text-white mb-1">Сопутствующие покупки</h4>
                <ul id="detailsPurchaseList" class="list-disc ml-6 text-gray-200 space-y-1"></ul>
            </div>
            <div>
                <h4 class="text-lg font-medium text-white mb-1">Продажи крипты</h4>
                <ul id="detailsSaleList" class="list-disc ml-6 text-gray-200 space-y-1"></ul>
            </div>
        </div>
    </div>
</div>
