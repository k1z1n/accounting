// 1. JS: AJAX-подгрузка графика
import './bootstrap';
import Chart from 'chart.js/auto';

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
                        label: ctx => `${ctx.dataset.label}: ${ctx.formattedValue} USDT`
                    }
                },
                legend: { labels: { color: '#e5e7eb' } }
            },
            scales: {
                x: {
                    ticks: { color: '#d1d5db' },
                    grid: { color: 'rgba(255,255,255,0.05)' }
                },
                y: {
                    beginAtZero: true,
                    ticks: { color: '#d1d5db' },
                    grid: { color: 'rgba(255,255,255,0.05)' }
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
