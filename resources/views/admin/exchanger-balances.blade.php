@extends('template.app')
@section('title','Балансы обменников')
@section('content')
<div class="container mx-auto px-4 py-6 space-y-6">
    <h1 class="text-2xl font-bold text-white mb-4">Балансы обменников (реальные, через API)</h1>
    <div class="bg-[#191919] rounded-2xl p-6 flex flex-col md:flex-row md:items-end gap-4">
        <div class="flex-1">
            <label class="block text-sm text-gray-300 mb-1">Провайдер</label>
            <select id="prov" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                @foreach($providers as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1">
            <label class="block text-sm text-gray-300 mb-1">Обменник</label>
            <select id="exch" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                @foreach($exchangers as $k => $v)
                    <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <button id="refreshBtn" class="mt-6 px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700">Обновить</button>
        </div>
    </div>
    <div class="bg-[#191919] rounded-2xl shadow-md overflow-auto">
        <table class="min-w-full text-sm text-gray-200">
            <thead class="bg-[#1F1F1F]">
            <tr>
                <th class="px-3 py-2">Валюта</th>
                <th class="px-3 py-2 text-right">Баланс</th>
                <th class="px-3 py-2">Иконка</th>
            </tr>
            </thead>
            <tbody id="balancesTbody" class="divide-y divide-[#2d2d2d]"></tbody>
        </table>
    </div>
    <div id="errorBlock" class="text-red-400 mt-4"></div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const provSel = document.getElementById('prov');
    const exchSel = document.getElementById('exch');
    const tbody   = document.getElementById('balancesTbody');
    const errorBlock = document.getElementById('errorBlock');
    const refreshBtn = document.getElementById('refreshBtn');

    function renderBalances(balances) {
        tbody.innerHTML = '';
        if (!balances.length) {
            tbody.innerHTML = '<tr><td colspan="3" class="py-6 text-center text-gray-500">Нет данных</td></tr>';
            return;
        }
        balances.forEach(b => {
            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="px-3 py-2 font-mono">${b.code}</td>
                    <td class="px-3 py-2 text-right">${(+b.amount).toLocaleString('ru-RU', {minimumFractionDigits: 2, maximumFractionDigits: 8})}</td>
                    <td class="px-3 py-2"><img src="${b.icon}" alt="${b.code}" class="w-6 h-6 inline"></td>
                </tr>
            `);
        });
    }

    async function loadBalances() {
        errorBlock.textContent = '';
        tbody.innerHTML = '<tr><td colspan="3" class="py-6 text-center text-gray-500">Загрузка…</td></tr>';
        const prov = provSel.value;
        const exch = exchSel.value;
        try {
            const resp = await fetch(`/api/wallets/balances?provider=${prov}&exchanger=${exch}`);
            const json = await resp.json();
            if (!resp.ok || json.error) {
                errorBlock.textContent = json.error || 'Ошибка загрузки';
                tbody.innerHTML = '';
                return;
            }
            renderBalances(json.balances || []);
        } catch (e) {
            errorBlock.textContent = 'Ошибка запроса: ' + e;
            tbody.innerHTML = '';
        }
    }

    provSel.addEventListener('change', loadBalances);
    exchSel.addEventListener('change', loadBalances);
    refreshBtn.addEventListener('click', loadBalances);
    loadBalances();
});
</script>
@endsection
