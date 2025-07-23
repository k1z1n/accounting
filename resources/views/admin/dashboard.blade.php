@extends('template.app')
@section('title','Дашборд — Статистика сайта')
@section('content')
<div class="container mx-auto px-4 py-8 space-y-8">
    <h1 class="text-3xl font-bold text-white mb-6">Дашборд: Статистика сайта</h1>

    <!-- Фильтр периода -->
    <div class="flex flex-wrap items-end gap-4 mb-6">
        <div>
            <label class="block text-gray-400 text-sm mb-1">Период</label>
            <select id="intervalSel" class="bg-[#232b3a] text-white rounded-lg px-4 py-2">
                <option value="day">По дням</option>
                <option value="month">По месяцам</option>
                <option value="year">По годам</option>
            </select>
        </div>
        <div>
            <label class="block text-gray-400 text-sm mb-1">С</label>
            <input type="date" id="startDate" class="bg-[#232b3a] text-white rounded-lg px-4 py-2" />
        </div>
        <div>
            <label class="block text-gray-400 text-sm mb-1">По</label>
            <input type="date" id="endDate" class="bg-[#232b3a] text-white rounded-lg px-4 py-2" />
        </div>
        <button id="filterBtn" class="ml-2 px-5 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700">Показать</button>
    </div>

    <!-- Ключевые метрики -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-[#232b3a] rounded-2xl p-6 flex flex-col items-start shadow">
            <div class="text-gray-400 text-sm mb-1">Пользователей</div>
            <div class="text-2xl font-bold text-cyan-300" id="stat-users">—</div>
        </div>
        <div class="bg-[#232b3a] rounded-2xl p-6 flex flex-col items-start shadow">
            <div class="text-gray-400 text-sm mb-1">Заявок</div>
            <div class="text-2xl font-bold text-cyan-300" id="stat-apps">—</div>
        </div>
        <div class="bg-[#232b3a] rounded-2xl p-6 flex flex-col items-start shadow">
            <div class="text-gray-400 text-sm mb-1">Операций</div>
            <div class="text-2xl font-bold text-cyan-300" id="stat-ops">—</div>
        </div>
        <div class="bg-[#232b3a] rounded-2xl p-6 flex flex-col items-start shadow">
            <div class="text-gray-400 text-sm mb-1">Сумма USDT</div>
            <div class="text-2xl font-bold text-cyan-300" id="stat-usdt">—</div>
        </div>
    </div>

    <!-- Графики -->
    <div class="bg-[#232b3a] rounded-2xl p-8 shadow">
        <h2 class="text-xl font-bold text-white mb-4">Динамика заявок и оборота</h2>
        <div id="chartMainLoading" class="flex justify-center items-center h-[240px]">
            <svg class="animate-spin h-10 w-10 text-cyan-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
        </div>
        <canvas id="chart-main" height="80" style="display:none;"></canvas>
    </div>

    <div class="bg-[#232b3a] rounded-2xl p-8 shadow">
        <div class="flex flex-wrap items-end gap-4 mb-4">
            <h2 class="text-xl font-bold text-white mr-4">Динамика операций</h2>
            <label class="block text-gray-400 text-sm">Тип операции
                <select id="opsTypeSel" class="ml-2 bg-[#191f2b] text-white rounded-lg px-3 py-1">
                    <option value="all">Все</option>
                    <option value="payments">Оплаты</option>
                    <option value="transfers">Переводы</option>
                    <option value="purchases">Покупки</option>
                    <option value="salecrypts">Продажи</option>
                </select>
            </label>
        </div>
        <div id="chartOpsLoading" class="flex justify-center items-center h-[240px]">
            <svg class="animate-spin h-10 w-10 text-cyan-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
        </div>
        <canvas id="chart-ops" height="80" style="display:none;"></canvas>
    </div>

    <!-- Детализация по операциям -->
    <div class="bg-[#232b3a] rounded-2xl p-8 shadow">
        <h2 class="text-xl font-bold text-white mb-4">Операции (детализация)</h2>
        <div id="operationsBlock">
            <div class="text-gray-400">Загрузка…</div>
        </div>
    </div>

    <!-- Placeholder для других секций -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-[#232b3a] rounded-2xl p-6 shadow">
            <h3 class="text-lg font-bold text-white mb-2">ТОП валют</h3>
            <div id="top-currencies">—</div>
        </div>
        <div class="bg-[#232b3a] rounded-2xl p-6 shadow">
            <h3 class="text-lg font-bold text-white mb-2">ТОП обменников</h3>
            <div id="top-exchangers">—</div>
        </div>
    </div>

    <!-- График криптовалют (Bybit) -->
    <div class="bg-[#232b3a] rounded-2xl p-8 shadow">
        <div class="flex flex-wrap md:flex-nowrap items-end gap-4 mb-4 bg-[#1a2233] rounded-xl px-4 py-3 shadow-inner border border-[#232b3a]">
            <label class="flex items-center gap-2 text-gray-400 text-sm" title="Выберите криптовалютную пару">
                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/><path d="M8 12l2 2 4-4" stroke="currentColor" stroke-width="2" fill="none"/></svg>
                Пара
                <select id="bybitPair" class="ml-2 bg-[#191f2b] text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-cyan-400 transition" title="Криптовалютная пара">
                    <option value="">Загрузка...</option>
                </select>
            </label>
            <label class="flex items-center gap-2 text-gray-400 text-sm" title="Таймфрейм — размер одной свечи">
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/><path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" fill="none"/></svg>
                Таймфрейм
                <select id="bybitInterval" class="ml-2 bg-[#191f2b] text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-400 transition" title="Таймфрейм (шаг графика)">
                    <option value="1m">1 мин</option>
                    <option value="5m">5 мин</option>
                    <option value="15m">15 мин</option>
                    <option value="30m">30 мин</option>
                    <option value="1h">1 час</option>
                    <option value="4h">4 часа</option>
                    <option value="1d">1 день</option>
                    <option value="1w">1 неделя</option>
                    <option value="1M">1 месяц</option>
                </select>
            </label>
            <label class="flex items-center gap-2 text-gray-400 text-sm" title="Начальная дата диапазона">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2" fill="none"/><path d="M16 2v4M8 2v4M3 10h18" stroke="currentColor" stroke-width="2" fill="none"/></svg>
                От
                <input type="datetime-local" id="bybitStart" class="ml-2 bg-[#191f2b] text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-emerald-400 transition" placeholder="Начало" title="Начальная дата" />
            </label>
            <label class="flex items-center gap-2 text-gray-400 text-sm" title="Конечная дата диапазона">
                <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2" fill="none"/><path d="M16 2v4M8 2v4M3 10h18" stroke="currentColor" stroke-width="2" fill="none"/></svg>
                До
                <input type="datetime-local" id="bybitEnd" class="ml-2 bg-[#191f2b] text-white rounded-lg px-3 py-2 focus:ring-2 focus:ring-rose-400 transition" placeholder="Конец" title="Конечная дата" />
            </label>
            <button id="bybitRefresh" class="ml-2 px-5 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 flex items-center gap-2 transition focus:ring-2 focus:ring-cyan-400" title="Обновить график">
                <svg class="w-5 h-5 animate-spin hidden" id="bybitRefreshSpinner" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                <span>Показать</span>
            </button>
        </div>
        <div id="bybitChartLoading" class="flex justify-center items-center h-[400px]">
            <svg class="animate-spin h-10 w-10 text-cyan-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
        </div>
        <div id="bybitChartContainer" style="height:400px;width:100%;display:none;"></div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/lightweight-charts@4.1.2/dist/lightweight-charts.standalone.production.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let lwChart, lwSeries;
let lwChartMain, lwSeriesMainApps, lwSeriesMainUsdt;
let lwChartOps, lwSeriesOps = {};
console.log('DASHBOARD SCRIPT LOADED');
const intervalSel = document.getElementById('intervalSel');
const startDate = document.getElementById('startDate');
const endDate = document.getElementById('endDate');
const filterBtn = document.getElementById('filterBtn');
const opsTypeSel = document.getElementById('opsTypeSel');

// Установить значения по умолчанию (последние 30 дней)
const today = new Date();
const monthAgo = new Date();
monthAgo.setDate(today.getDate() - 30);
startDate.value = monthAgo.toISOString().slice(0,10);
endDate.value = today.toISOString().slice(0,10);

async function fetchStats() {
    const params = new URLSearchParams({
        interval: intervalSel.value,
        start_date: startDate.value,
        end_date: endDate.value
    });
    const resp = await fetch('/admin/dashboard/stats?' + params.toString());
    if (!resp.ok) throw new Error('Ошибка загрузки статистики');
    return await resp.json();
}

function renderOperations(ops) {
    return `<div class='grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6'>
            <div class='bg-[#191f2b] rounded-xl p-5 flex flex-col items-start'>
                <div class='text-gray-400 text-sm mb-1'>Оплаты</div>
                <div class='text-2xl font-bold text-cyan-300 mb-1'>${ops.payments.count.toLocaleString('ru-RU')}</div>
                <div class='text-xs text-gray-400'>Сумма: <span class='text-cyan-200 font-semibold'>${(+ops.payments.sum).toLocaleString('ru-RU', {minimumFractionDigits:2})}</span></div>
            </div>
            <div class='bg-[#191f2b] rounded-xl p-5 flex flex-col items-start'>
                <div class='text-gray-400 text-sm mb-1'>Переводы</div>
                <div class='text-2xl font-bold text-cyan-300 mb-1'>${ops.transfers.count.toLocaleString('ru-RU')}</div>
                <div class='text-xs text-gray-400'>Сумма: <span class='text-cyan-200 font-semibold'>${(+ops.transfers.sum).toLocaleString('ru-RU', {minimumFractionDigits:2})}</span></div>
            </div>
            <div class='bg-[#191f2b] rounded-xl p-5 flex flex-col items-start'>
                <div class='text-gray-400 text-sm mb-1'>Покупки</div>
                <div class='text-2xl font-bold text-cyan-300 mb-1'>${ops.purchases.count.toLocaleString('ru-RU')}</div>
                <div class='text-xs text-gray-400'>Сумма: <span class='text-cyan-200 font-semibold'>${(+ops.purchases.sum).toLocaleString('ru-RU', {minimumFractionDigits:2})}</span></div>
            </div>
            <div class='bg-[#191f2b] rounded-xl p-5 flex flex-col items-start'>
                <div class='text-gray-400 text-sm mb-1'>Продажи</div>
                <div class='text-2xl font-bold text-cyan-300 mb-1'>${ops.salecrypts.count.toLocaleString('ru-RU')}</div>
                <div class='text-xs text-gray-400'>Сумма: <span class='text-cyan-200 font-semibold'>${(+ops.salecrypts.sum).toLocaleString('ru-RU', {minimumFractionDigits:2})}</span></div>
            </div>
        </div>`;
}

function getTimeFromLabel(label, interval) {
    // label: 'дд.мм', 'Месяц ГГГГ', 'ГГГГ'
    if (interval === 'day') {
        const [d, m] = label.split('.');
        const year = new Date().getFullYear(); // не идеально, но лучше чем ничего
        return new Date(`${year}-${m}-${d}`).getTime() / 1000;
    }
    if (interval === 'month') {
        const [mon, year] = label.split(' ');
        const monthNum = {
            'Jan':1,'Feb':2,'Mar':3,'Apr':4,'May':5,'Jun':6,'Jul':7,'Aug':8,'Sep':9,'Oct':10,'Nov':11,'Dec':12,
            'Янв':1,'Фев':2,'Мар':3,'Апр':4,'Май':5,'Июн':6,'Июл':7,'Авг':8,'Сен':9,'Окт':10,'Ноя':11,'Дек':12
        }[mon] || 1;
        return new Date(`${year}-${monthNum}-01`).getTime() / 1000;
    }
    if (interval === 'year') {
        return new Date(`${label}-01-01`).getTime() / 1000;
    }
    return 0;
}
let chartMain, chartOps, lastOpsStats;
async function updateDashboard() {
    try {
        // Показываем спиннеры, скрываем графики
        document.getElementById('chartMainLoading').style.display = '';
        document.getElementById('chart-main').style.display = 'none';
        document.getElementById('chartOpsLoading').style.display = '';
        document.getElementById('chart-ops').style.display = 'none';
        const stats = await fetchStats();
        document.getElementById('stat-users').textContent = stats.users.toLocaleString('ru-RU');
        document.getElementById('stat-apps').textContent = stats.apps.toLocaleString('ru-RU');
        document.getElementById('stat-ops').textContent = stats.ops.toLocaleString('ru-RU');
        document.getElementById('stat-usdt').textContent = stats.usdt.toLocaleString('ru-RU', {minimumFractionDigits:2});
        // --- Chart.js: Динамика заявок и оборота ---
        if (chartMain) chartMain.destroy();
        const ctx = document.getElementById('chart-main').getContext('2d');
        chartMain = new Chart(ctx, {
            type: 'line',
            data: {
                labels: stats.chart.labels,
                datasets: [
                    {
                        label: 'Заявки',
                        data: stats.chart.apps,
                        borderColor: '#22d3ee',
                        backgroundColor: 'rgba(34,211,238,0.1)',
                        tension: 0.4,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Оборот USDT',
                        data: stats.chart.usdt,
                        borderColor: '#fbbf24',
                        backgroundColor: 'rgba(251,191,36,0.1)',
                        tension: 0.4,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { labels: { color: '#fff', font: { size: 14 } } },
                    tooltip: { mode: 'index', intersect: false },
                },
                interaction: { mode: 'nearest', axis: 'x', intersect: false },
                scales: {
                    x: { ticks: { color: '#a3a3a3' }, grid: { color: '#222' } },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        ticks: { color: '#a3e635' },
                        grid: { color: '#222' },
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { color: '#fbbf24' },
                    }
                }
            }
        });
        // --- Chart.js: Динамика операций ---
        lastOpsStats = stats.operations_chart;
        renderOpsChart();
        // ТОП валют
        document.getElementById('top-currencies').innerHTML = stats.topCurrencies.map(c =>
            `<div class='flex items-center gap-3 mb-2'>
                    <span class='font-mono text-cyan-300 text-lg'>${c.code}</span>
                    <span class='text-gray-400 text-sm'>${c.name}</span>
                    <span class='ml-auto font-bold text-cyan-200'>${c.amount.toLocaleString('ru-RU')}</span>
                </div>`
        ).join('');
        document.getElementById('top-exchangers').innerHTML = stats.topExchangers.map(e =>
            `<div class='flex items-center gap-3 mb-2'>
                    <span class='font-semibold text-emerald-300'>${e.name}</span>
                    <span class='ml-auto font-bold text-emerald-200'>${e.amount.toLocaleString('ru-RU')}</span>
                </div>`
        ).join('');
        document.getElementById('operationsBlock').innerHTML = renderOperations(stats.operations);
        // После успешной загрузки скрываем спиннеры, показываем графики
        document.getElementById('chartMainLoading').style.display = 'none';
        document.getElementById('chart-main').style.display = '';
        document.getElementById('chartOpsLoading').style.display = 'none';
        document.getElementById('chart-ops').style.display = '';
    } catch (e) {
        document.getElementById('stat-users').textContent = '—';
        document.getElementById('stat-apps').textContent = '—';
        document.getElementById('stat-ops').textContent = '—';
        document.getElementById('stat-usdt').textContent = '—';
        document.getElementById('top-currencies').textContent = 'Ошибка';
        document.getElementById('top-exchangers').textContent = 'Ошибка';
        document.getElementById('chart-main').parentNode.innerHTML = '<div class="text-red-400">Ошибка загрузки статистики</div>';
        document.getElementById('chart-ops').parentNode.innerHTML = '<div class="text-red-400">Ошибка загрузки статистики</div>';
        document.getElementById('operationsBlock').innerHTML = '<div class="text-red-400">Ошибка загрузки операций</div>';
        document.getElementById('chartMainLoading').style.display = 'none';
        document.getElementById('chart-main').style.display = '';
        document.getElementById('chartOpsLoading').style.display = 'none';
        document.getElementById('chart-ops').style.display = '';
    }
}
function renderOpsChart() {
    if (!lastOpsStats) return;
    if (chartOps) chartOps.destroy();
    const ctxOps = document.getElementById('chart-ops').getContext('2d');
    const type = opsTypeSel.value;
    const allDatasets = [
        {
            label: 'Оплаты',
            data: lastOpsStats.payments,
            borderColor: '#22d3ee',
            backgroundColor: 'rgba(34,211,238,0.08)',
            tension: 0.4,
            yAxisID: 'y',
            id: 'payments',
        },
        {
            label: 'Переводы',
            data: lastOpsStats.transfers,
            borderColor: '#a78bfa',
            backgroundColor: 'rgba(167,139,250,0.08)',
            tension: 0.4,
            yAxisID: 'y',
            id: 'transfers',
        },
        {
            label: 'Покупки',
            data: lastOpsStats.purchases,
            borderColor: '#34d399',
            backgroundColor: 'rgba(52,211,153,0.08)',
            tension: 0.4,
            yAxisID: 'y',
            id: 'purchases',
        },
        {
            label: 'Продажи',
            data: lastOpsStats.salecrypts,
            borderColor: '#f87171',
            backgroundColor: 'rgba(248,113,113,0.08)',
            tension: 0.4,
            yAxisID: 'y',
            id: 'salecrypts',
        },
    ];
    let datasets = allDatasets;
    if (type !== 'all') {
        datasets = allDatasets.filter(ds => ds.id === type);
    }
    chartOps = new Chart(ctxOps, {
        type: 'line',
        data: {
            labels: lastOpsStats.labels,
            datasets: datasets,
        },
        options: {
            responsive: true,
            plugins: {
                legend: { labels: { color: '#fff', font: { size: 14 } } },
                tooltip: { mode: 'index', intersect: false },
            },
            interaction: { mode: 'nearest', axis: 'x', intersect: false },
            scales: {
                x: { ticks: { color: '#a3a3a3' }, grid: { color: '#222' } },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    ticks: { color: '#fbbf24' },
                    grid: { color: '#222' },
                },
            }
        }
    });
}
window.addEventListener('resize', () => {
    if (lwChartMain) lwChartMain.resize(document.getElementById('chartMainContainer').offsetWidth, 300);
    if (lwChartOps) lwChartOps.resize(document.getElementById('chartOpsContainer').offsetWidth, 300);
});
filterBtn.addEventListener('click', updateDashboard);
updateDashboard();
opsTypeSel.addEventListener('change', renderOpsChart);

// Bybit график
const bybitPair = document.getElementById('bybitPair');
const bybitInterval = document.getElementById('bybitInterval');
const bybitRefresh = document.getElementById('bybitRefresh');
const bybitStart = document.getElementById('bybitStart');
const bybitEnd = document.getElementById('bybitEnd');
// По умолчанию: input'ы пустые, но график строится за вчера 00:00 — сегодня 23:59
const now = new Date();
const todayStr = now.toISOString().slice(0,10);
const yesterday = new Date(now.getTime() - 24*60*60*1000);
const yesterdayStr = yesterday.toISOString().slice(0,10);
bybitInterval.value = '1m';
bybitStart.value = '';
bybitEnd.value = '';

function getDefaultStart() {
    return yesterdayStr + 'T00:00';
}
function getDefaultEnd() {
    return todayStr + 'T23:59';
}

function updateBybitRefreshState() {
    bybitRefresh.disabled = !(bybitStart.value && bybitEnd.value);
    bybitRefresh.classList.toggle('opacity-50', bybitRefresh.disabled);
    bybitRefresh.classList.toggle('cursor-not-allowed', bybitRefresh.disabled);
}
bybitStart.addEventListener('input', updateBybitRefreshState);
bybitEnd.addEventListener('input', updateBybitRefreshState);
updateBybitRefreshState();

function getBybitCategoryAndInterval(pair, interval) {
    // Если пара заканчивается на USD (без T) — inverse
    if (/USD$/.test(pair) && !/USDT$/.test(pair)) {
        let intMap = { '1m': '1', '5m': '5', '15m': '15', '1h': '60', '4h': '240', '1d': 'D' };
        return { category: 'inverse', interval: intMap[interval] || '60' };
    } else {
        // spot
        return { category: 'spot', interval: interval };
    }
}

async function loadBybitChart(forceDefault = false) {
    // Если forceDefault=true или обе даты не выбраны — строим за вчера-сегодня
    let startVal = bybitStart.value;
    let endVal = bybitEnd.value;
    let useDefault = forceDefault || !(startVal && endVal);
    if (useDefault) {
        startVal = getDefaultStart();
        endVal = getDefaultEnd();
    }
    let startMs = new Date(startVal).getTime();
    let endMs = new Date(endVal).getTime();
    if (startMs > endMs) {
        [startVal, endVal] = [endVal, startVal];
        [startMs, endMs] = [endMs, startMs];
    }
    const pair = bybitPair.value;
    const interval = bybitInterval.value;
    const { category, interval: apiInterval } = getBybitCategoryAndInterval(pair, interval);
    let url = `/admin/bybit/candles?symbol=${pair}&interval=${apiInterval}&category=${category}&limit=200`;
    if (!useDefault) {
        url += `&start=${startMs}&end=${endMs}`;
    }
    console.log('[BybitChart] Итоговый URL:', url);
    const resp = await fetch(url);
    const json = await resp.json();
    console.log('[BybitChart] Ответ сервера:', json);
    const rawList = (json.result && json.result.list) ? json.result.list : (json.list || []);
    const candles = rawList.map(c => ({
        time: Math.floor(+c[0]/1000),
        open: +c[1],
        high: +c[2],
        low: +c[3],
        close: +c[4],
        volume: +c[5],
    }));
    if (!candles.length) {
        document.getElementById('bybitChartLoading').style.display = 'none';
        document.getElementById('bybitChartContainer').style.display = 'none';
        document.getElementById('bybitChartContainer').innerHTML = '<div class="text-red-400 mt-4">Нет данных для выбранной пары/таймфрейма (candles пустой)</div>';
        return;
    }
    // Сначала делаем контейнер видимым и пустым!
    document.getElementById('bybitChartLoading').style.display = 'none';
    document.getElementById('bybitChartContainer').style.display = '';
    document.getElementById('bybitChartContainer').innerHTML = '';
    // Теперь создаём график
    if (lwChart) { lwChart.remove(); lwChart = null; }
    lwChart = LightweightCharts.createChart(document.getElementById('bybitChartContainer'), {
        layout: { background: { color: '#232b3a' }, textColor: '#fff' },
        grid: { vertLines: { color: '#222' }, horzLines: { color: '#222' } },
        timeScale: { timeVisible: true, secondsVisible: false },
        rightPriceScale: { borderColor: '#888' },
        width: document.getElementById('bybitChartContainer').offsetWidth,
        height: 400,
    });
    lwSeries = lwChart.addCandlestickSeries({
        upColor: '#22d3ee', downColor: '#f87171', borderVisible: false, wickUpColor: '#22d3ee', wickDownColor: '#f87171',
    });
    lwSeries.setData(candles);
    if (candles.length) {
        lwChart.timeScale().setVisibleRange({
            from: candles[0].time,
            to: candles[candles.length - 1].time
        });
        lwChart.timeScale().scrollToRealTime();
        lwChart.timeScale().subscribeVisibleTimeRangeChange(() => {
            lwChart.timeScale().scrollToRealTime();
        });
    }
    // После отрисовки свечей — загружаем сделки
    const baseCurrency = pair.replace(/USDT$/, '');
    let tradesUrl = `/admin/crypto-trades?currency=${baseCurrency}`;
    if (!useDefault) {
        tradesUrl += `&from=${encodeURIComponent(new Date(startVal).toISOString())}`;
        tradesUrl += `&to=${encodeURIComponent(new Date(endVal).toISOString())}`;
    }
    try {
        const tradesResp = await fetch(tradesUrl);
        if (!tradesResp.ok) {
            console.error('Ошибка загрузки сделок для графика: HTTP', tradesResp.status);
            return;
        }
        const trades = await tradesResp.json();
        if (Array.isArray(trades) && lwChart) {
            // Добавляем точки на график
            const markers = trades.map(trade => ({
                time: Math.floor(new Date(trade.created_at).getTime() / 1000),
                position: 'aboveBar',
                color: trade.type === 'sale' ? '#f87171' : '#22d3ee',
                shape: trade.type === 'sale' ? 'arrowDown' : 'arrowUp',
                text: `${trade.type === 'sale' ? 'Продажа' : 'Покупка'}: ${trade.amount}`,
            }));
            lwSeries.setMarkers(markers);
        }
    } catch (e) {
        console.error('Ошибка загрузки сделок для графика:', e);
    }
}
// Кнопка активна только если обе даты выбраны
bybitRefresh.addEventListener('click', () => {
    if (bybitStart.value && bybitEnd.value) {
        loadBybitChart(false);
    }
});
bybitPair.addEventListener('change', loadBybitChart);
bybitInterval.addEventListener('change', loadBybitChart);
// Автообновление при загрузке
document.addEventListener('DOMContentLoaded', () => {
    loadBybitChart(true);
});

// Тестовый запрос к Bybit (testnet, inverse)
async function testBybitRequest() {
    const url = 'https://api-testnet.bybit.com/v5/market/kline?category=inverse&symbol=BTCUSD&interval=60&start=1670601600000&end=1670608800000';
    const resp = await fetch(url);
    const json = await resp.json();
    console.log('TESTNET BYBIT RESPONSE:', json);
}
testBybitRequest();

async function loadBybitPairs() {
    const pairSelect = document.getElementById('bybitPair');
    pairSelect.innerHTML = '<option value="">Загрузка...</option>';
    try {
        // Получаем spot пары
        const spotResp = await fetch('https://api.bybit.com/v5/market/instruments-info?category=spot&limit=1000');
        const spotJson = await spotResp.json();
        const spotPairs = (spotJson.result && spotJson.result.list) ? spotJson.result.list : [];
        // Получаем inverse пары
        const invResp = await fetch('https://api.bybit.com/v5/market/instruments-info?category=inverse&limit=1000');
        const invJson = await invResp.json();
        const invPairs = (invJson.result && invJson.result.list) ? invJson.result.list : [];
        // Формируем <option> для каждой пары
        let options = [];
        spotPairs.forEach(p => {
            options.push(`<option value="${p.symbol}">${p.symbol} (spot)</option>`);
        });
        invPairs.forEach(p => {
            options.push(`<option value="${p.symbol}">${p.symbol} (inverse)</option>`);
        });
        // Сортируем по алфавиту
        options.sort((a,b) => a.localeCompare(b));
        pairSelect.innerHTML = options.join('');
        // По умолчанию BTCUSDT если есть
        if (pairSelect.querySelector('option[value="BTCUSDT"]')) {
            pairSelect.value = 'BTCUSDT';
        } else if (pairSelect.options.length > 0) {
            pairSelect.selectedIndex = 0;
        }
    } catch (e) {
        // Fallback к старым 6 вариантам
        pairSelect.innerHTML = `
            <option value="BTCUSDT">BTC/USDT (spot)</option>
            <option value="ETHUSDT">ETH/USDT (spot)</option>
            <option value="SOLUSDT">SOL/USDT (spot)</option>
            <option value="BNBUSDT">BNB/USDT (spot)</option>
            <option value="XRPUSDT">XRP/USDT (spot)</option>
            <option value="BTCUSD">BTC/USD (inverse)</option>
        `;
        pairSelect.value = 'BTCUSDT';
    }
}
document.addEventListener('DOMContentLoaded', loadBybitPairs);

</script>
@endsection
