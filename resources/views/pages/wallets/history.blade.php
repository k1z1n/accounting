@extends('template.app')
@section('title','История кошелька')
@section('content')
    <div class="container mx-auto px-4 py-6 space-y-6">

        {{-- Фильтры --}}
        <div class="bg-[#191919] rounded-2xl p-6 flex flex-col md:flex-row md:items-end gap-4">
            <div class="flex-1">
                <label class="block text-sm text-gray-300 mb-1">Провайдер</label>
                <select id="prov" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                    @foreach($providers as $k => $v)
                        <option value="{{ $k }}" @selected($currentProv==$k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-sm text-gray-300 mb-1">Обменник</label>
                <select id="exch" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                    @foreach($exchangers as $k => $v)
                        <option value="{{ $k }}" @selected($currentExch==$k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-1">С&nbsp;даты</label>
                <input id="date_from" type="date" value="{{ $defaultFrom }}"
                       class="bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-300 mb-1">По&nbsp;дату</label>
                <input id="date_to" type="date" value="{{ $defaultTo }}"
                       class="bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
            </div>
        </div>

        {{-- Краткая таблица --}}
        <div class="bg-[#191919] rounded-2xl shadow-md overflow-auto">
            <table class="min-w-full text-sm text-gray-200">
                <thead class="bg-[#1F1F1F]">
                <tr>
                    <th class="px-3 py-2">Дата</th>
                    <th class="px-3 py-2">Order ID / UUID</th>
                    <th class="px-3 py-2 text-right">Получено</th>
                    <th class="px-3 py-2">Статус</th>
                    <th class="px-3 py-2">Детали</th>
                </tr>
                </thead>
                <tbody id="tbody" class="divide-y divide-[#2d2d2d]"></tbody>
            </table>
        </div>

        {{-- Пагинация --}}
        <div id="pager" class="flex justify-center gap-2"></div>
    </div>

    {{-- Модальное окно --}}
    <div id="detailModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-[#191919] rounded-2xl p-6 w-11/12 max-w-2xl relative text-gray-200">
            <button id="modalClose" class="absolute top-3 right-3 text-2xl hover:text-white">&times;</button>
            <div id="modalContent" class="space-y-4 overflow-y-auto max-h-[80vh]"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', ()=> {
            const prov        = document.getElementById('prov');
            const exch        = document.getElementById('exch');
            const from        = document.getElementById('date_from');
            const to          = document.getElementById('date_to');
            const tbody       = document.getElementById('tbody');
            const pager       = document.getElementById('pager');
            const modal       = document.getElementById('detailModal');
            const modalContent= document.getElementById('modalContent');
            const modalClose  = document.getElementById('modalClose');

            let prevCur = null, nextCur = null, items = [];

            // формат числа
            const fmt4 = n => (+n.toFixed(4)).toLocaleString('ru-RU');

            // рендер строки краткой таблицы
            function renderRow(it, idx) {
                return `
<tr class="hover:bg-gray-800">
  <td class="px-3 py-2">${it.date}</td>
  <td class="px-3 py-2 font-mono text-xs">${it.order_id} / ${it.uuid}</td>
  <td class="px-3 py-2 text-right">${fmt4(it.amount)}</td>
  <td class="px-3 py-2">${it.status_text}</td>
  <td class="px-3 py-2">
    <button class="detail-btn px-2 py-1 bg-cyan-600 hover:bg-cyan-500 rounded text-xs"
            data-idx="${idx}">Подробнее</button>
  </td>
</tr>`;
            }

            // показываем модалку с детальной информацией
            function openDetail(idx) {
                const it = items[idx];
                modalContent.innerHTML = `
      <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
        <dt class="font-semibold">Дата создания</dt><dd>${it.date}</dd>
        <dt class="font-semibold">Order ID</dt><dd>${it.order_id}</dd>
        <dt class="font-semibold">UUID</dt><dd class="font-mono">${it.uuid}</dd>
        <dt class="font-semibold">Получено</dt><dd>${fmt4(it.payer_amount)} ${it.currency}</dd>
        <dt class="font-semibold">К оплате</dt><dd>${fmt4(it.payment_amount)} ${it.currency}</dd>
        <dt class="font-semibold">Скидка</dt><dd>${it.discount_percent}% (${fmt4(it.discount)})</dd>
        <dt class="font-semibold">К оплате (USDT)</dt><dd>${fmt4(it.payment_amount_usd)}</dd>
        <dt class="font-semibold">Выручка</dt><dd>${fmt4(it.merchant_amount)}</dd>
        <dt class="font-semibold">Статус</dt><dd>${it.status_text}</dd>
        <dt class="font-semibold">Сеть</dt><dd>${it.network}</dd>
        <dt class="font-semibold">Адрес</dt><dd class="font-mono">${it.address}</dd>
        <dt class="font-semibold">Откуда</dt><dd class="font-mono">${it.from_address}</dd>
        <dt class="font-semibold">QR код</dt>
          <dd>${it.address_qr_code
                    ? `<img src="${it.address_qr_code}" alt="QR" class="w-32 h-32"/>`
                    : '—'}</dd>
        <dt class="font-semibold">Tx ID</dt><dd class="font-mono">${it.txid}</dd>
        <dt class="font-semibold">Ссылка на платёж</dt>
          <dd>${it.url
                    ? `<a href="${it.url}" target="_blank" class="text-cyan-400 underline">Открыть</a>`
                    : '—'}</dd>
        <dt class="font-semibold">Истёк</dt><dd>${it.expired_at}</dd>
        <dt class="font-semibold">Финал</dt><dd>${it.is_final}</dd>
        <dt class="font-semibold">Комментарий</dt><dd>${it.additional_data}</dd>
        <dt class="font-semibold">Обновлён</dt><dd>${it.updated_at}</dd>
      </dl>`;
                modal.classList.remove('hidden');
            }

            modalClose.addEventListener('click', ()=> modal.classList.add('hidden'));

            async function load(cursor = null) {
                tbody.innerHTML = `<tr><td colspan="5" class="py-6 text-center text-gray-500">Загрузка…</td></tr>`;
                pager.innerHTML = '';

                const params = new URLSearchParams({
                    provider:  prov.value,
                    exchanger: exch.value,
                    date_from: `${from.value} 00:00:00`,
                    date_to:   `${to.value} 23:59:59`,
                });
                if (cursor) params.set('cursor', cursor);

                const res = await fetch(`{{ route('wallets.history.data') }}?${params}`, {
                    headers: {'X-Requested-With':'XMLHttpRequest'}
                });

                if (!res.ok) {
                    tbody.innerHTML = `<tr><td colspan="5" class="py-6 text-center text-red-500">Ошибка загрузки</td></tr>`;
                    return;
                }

                const json = await res.json();
                items   = json.data;
                prevCur = json.meta.prevCursor;
                nextCur = json.meta.nextCursor;

                tbody.innerHTML = items.length
                    ? items.map(renderRow).join('')
                    : `<tr><td colspan="5" class="py-6 text-center text-gray-500">Нет записей</td></tr>`;

                // навигация
                let html = '';
                if (prevCur) html += `<button id="bprev" class="px-3 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded mr-2">← Назад</button>`;
                if (nextCur) html += `<button id="bnext" class="px-3 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded">Вперёд →</button>`;
                pager.innerHTML = html;

                if (prevCur) document.getElementById('bprev').onclick = () => load(prevCur);
                if (nextCur) document.getElementById('bnext').onclick = () => load(nextCur);

                // вешаем детали
                document.querySelectorAll('.detail-btn')
                    .forEach(btn => btn.addEventListener('click', e => openDetail(e.target.dataset.idx)));
            }

            [prov, exch, from, to].forEach(el => el.addEventListener('change', ()=> load()));
            load();
        });
    </script>
@endsection
