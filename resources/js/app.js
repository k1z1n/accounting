// 1. JS: AJAX-подгрузка графика
import './bootstrap';
import Chart from 'chart.js/auto';

// Утилита для обрезки лишних нулей
function stripZeros(value) {
    const s = String(value);
    if (!s.includes('.')) return s;
    return s.replace(/\.?0+$/, '');
}

let chartInstance;

function renderChart(labels, datasets) {
    const ctx = document.getElementById('lineChart');
    if (!ctx) return;
    if (chartInstance) chartInstance.destroy();

    chartInstance = new Chart(ctx, {
        type: 'line',
        data: { labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        // показываем дату
                        title: items => items[0].label,
                        // показываем основную строку: лейбл и значение
                        label: ctx => `${ctx.dataset.label}: ${stripZeros(ctx.parsed.y)} USDT`,
                        // затем показываем delta из ctx.dataset.deltas
                        afterLabel: ctx => {
                            const deltas = ctx.dataset.deltas;
                            if (!deltas) return;
                            const delta = deltas[ctx.dataIndex];
                            if (delta == null) return;
                            const sign = delta >= 0 ? '+' : '';
                            return `Vjh;f: ${sign}${stripZeros(delta)} USDT`;
                        }
                    }
                },
                legend: { labels: { color: '#e5e7eb' } }
            },
            scales: {
                x: {
                    ticks: { color: '#d1d5db' },
                    grid:  { color: 'rgba(255,255,255,0.05)' }
                },
                y: {
                    beginAtZero: true,
                    ticks: { color: '#d1d5db' },
                    grid:  { color: 'rgba(255,255,255,0.05)' }
                }
            }
        }
    });
}

function loadChart(start, end) {
    fetch(`/chart/usdt?start=${start}&end=${end}`)
        .then(res => res.json())
        .then(({ labels, datasets }) => renderChart(labels, datasets));
}

// Инициализация
const startInput = document.getElementById('start_date');
const endInput = document.getElementById('end_date');

if (startInput && endInput) {
    const update = () => loadChart(startInput.value, endInput.value);
    startInput.addEventListener('change', update);
    endInput.addEventListener('change', update);
    update();
}
