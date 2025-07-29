@extends('template.app')
@section('title','Балансы обменников')
@section('content')
<style>
.balance-section {
    margin-bottom: 2.5rem;
}
.balance-title {
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.balance-card {
    background: #232b3a;
    border-radius: 1rem;
    padding: 1.1rem 1.3rem 1.1rem 1.1rem;
    width: 100%;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 8px #0002;
    transition: box-shadow .2s, transform .2s;
    margin-bottom: 1rem;
}
.balance-card:hover {
    box-shadow: 0 4px 16px #0004;
    transform: translateY(-2px) scale(1.03);
}
.balance-icon {
    width: 2.2rem;
    height: 2.2rem;
    border-radius: 0.5rem;
    background: #191919;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 1px 4px #0002;
}
.balance-code {
    font-family: monospace;
    font-size: 1.1rem;
    font-weight: 600;
    color: #c3e3ff;
    margin-bottom: 0.1rem;
}
.balance-name {
    font-size: 0.95rem;
    color: #8ecae6;
    margin-bottom: 0.2rem;
}
.balance-amount {
    font-size: 1.15rem;
    font-weight: 700;
    color: #fff;
    letter-spacing: 0.01em;
}
.balance-amount.zero {
    color: #888;
}
.balance-amount.positive {
    color: #22d3ee;
}
.balance-amount.negative {
    color: #f87171;
}
</style>

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
        <div>
            <button id="sendAllSequentialBtn" class="mt-6 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 font-bold text-lg shadow-lg">
                🚀 Отправить все сообщения
            </button>
        </div>
    </div>
    <div class="bg-[#191919] rounded-2xl shadow-md overflow-auto p-6 md:p-10">
        <div id="balancesBlock"></div>
    </div>
    <div id="errorBlock" class="text-red-400 mt-4"></div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const provSel = document.getElementById('prov');
    const exchSel = document.getElementById('exch');
    const balancesBlock = document.getElementById('balancesBlock');
    const errorBlock = document.getElementById('errorBlock');
    const refreshBtn = document.getElementById('refreshBtn');
    const sendAllSequentialBtn = document.getElementById('sendAllSequentialBtn');

    function renderCard(b) {
        let amount = +b.amount;
        let amountClass = amount > 0 ? 'text-cyan-400' : (amount < 0 ? 'text-red-400' : 'text-gray-400');
        return `<div class="flex items-center gap-4 bg-[#232b3a] rounded-2xl px-6 py-5 shadow hover:shadow-lg transition w-full mb-4">
            <span class="flex-shrink-0 bg-[#191919] rounded-xl p-2 flex items-center justify-center"><img src="${b.icon}" alt="${b.code}" class="w-8 h-8"></span>
            <div class="flex-1 min-w-0">
                <div class="font-mono text-lg font-bold text-cyan-100 flex items-center gap-2 leading-tight">${b.code}${b.name ? `<span class='text-xs text-gray-400 font-normal ml-2'>${b.name}</span>` : ''}</div>
                <div class="${amountClass} font-extrabold text-2xl tabular-nums leading-snug mt-1">${amount.toLocaleString('ru-RU', {minimumFractionDigits: 2, maximumFractionDigits: 8})}</div>
            </div>
        </div>`;
    }

    function renderSection(title, arr, color, icon) {
        if (!arr || !arr.length) return '';
        return `<div class="mb-10">
            <div class="flex items-center gap-2 mb-4 text-lg font-bold ${color} tracking-tight pl-1">${icon ? `<span class='text-2xl'>${icon}</span>` : ''}${title}</div>
            <div class="space-y-4">${arr.map(renderCard).join('')}</div>
        </div>`;
    }

    function renderBalances(balances) {
        balancesBlock.innerHTML = '';
        // Heleket: merchant/user
        if (balances && typeof balances === 'object' && (balances.merchant || balances.user)) {
            let html = '';
            html += renderSection('Баланс мерчанта', balances.merchant, 'text-cyan-400', '💼');
            html += renderSection('Баланс пользователя', balances.user, 'text-emerald-400', '👤');
            if (!html) html = '<div class="text-gray-500 py-8 text-center">Нет данных</div>';
            balancesBlock.innerHTML = html;
            return;
        }
        // Остальные провайдеры: обычный массив
        if (!balances || !balances.length) {
            balancesBlock.innerHTML = '<div class="text-gray-500 py-8 text-center">Нет данных</div>';
            return;
        }
        balancesBlock.innerHTML = `<div class="space-y-4">${balances.map(renderCard).join('')}</div>`;
    }

    async function loadBalances() {
        errorBlock.textContent = '';
        balancesBlock.innerHTML = '<div class="text-gray-500 py-8 text-center">Загрузка…</div>';
        const prov = provSel.value;
        const exch = exchSel.value;
        try {
            const resp = await fetch(`/api/wallets/balances?provider=${prov}&exchanger=${exch}`);
            const json = await resp.json();
            if (!resp.ok || json.error) {
                errorBlock.textContent = json.error || 'Ошибка загрузки';
                balancesBlock.innerHTML = '';
                return;
            }
            renderBalances(json.balances || []);
        } catch (e) {
            errorBlock.textContent = 'Ошибка запроса: ' + e;
            balancesBlock.innerHTML = '';
        }
    }

    provSel.addEventListener('change', loadBalances);
    exchSel.addEventListener('change', loadBalances);
    refreshBtn.addEventListener('click', loadBalances);

    // Обработчик отправки всех сообщений последовательно
    sendAllSequentialBtn.addEventListener('click', async function() {
        sendAllSequentialBtn.disabled = true;
        sendAllSequentialBtn.textContent = 'Отправляем все...';

        try {
            const response = await fetch(`/admin/exchangers/send-all-sequential`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            });

            const result = await response.json();

            if (response.ok && result.success) {
                alert('Все сообщения успешно отправлены!');
            } else {
                alert('Ошибка отправки: ' + (result.message || 'Неизвестная ошибка'));
            }
        } catch (error) {
            alert('Ошибка отправки: ' + error.message);
        } finally {
            sendAllSequentialBtn.disabled = false;
            sendAllSequentialBtn.textContent = '🚀 Отправить все сообщения';
        }
    });

    loadBalances();
});
</script>
@endsection
