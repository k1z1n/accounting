// 1. JS: AJAX-подгрузка графика
import './bootstrap';
import Chart from 'chart.js/auto';
import { agGridStyles } from './ag-grid-config';
import './applications-grid';
import './transfers-grid';
import './universal-grid';
import './mobile-optimization';
import './admin-grids';
import './profile-grid';

// Добавляем стили AG-Grid в DOM
const style = document.createElement('style');
style.textContent = agGridStyles;
document.head.appendChild(style);

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
                            return `Моржа: ${sign}${stripZeros(delta)} USDT`;
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

// ===== СИСТЕМА УПРАВЛЕНИЯ ТЕМОЙ =====
class ThemeManager {
    constructor() {
        this.theme = localStorage.getItem('theme') || 'dark';
        this.init();
    }

    init() {
        this.applyTheme();
        this.createToggleButton();
    }

    applyTheme() {
        document.documentElement.setAttribute('data-theme', this.theme);
        document.body.classList.toggle('dark', this.theme === 'dark');
        localStorage.setItem('theme', this.theme);
    }

    toggle() {
        this.theme = this.theme === 'dark' ? 'light' : 'dark';
        this.applyTheme();
    }

    createToggleButton() {
        const button = document.createElement('button');
        button.className = 'fixed bottom-6 right-6 w-12 h-12 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-primary-500 z-50';
        button.innerHTML = `
            <svg class="w-6 h-6 mx-auto transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${this.theme === 'dark' ? 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z' : 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z'}"></path>
            </svg>
        `;
        button.addEventListener('click', () => this.toggle());
        document.body.appendChild(button);
    }
}

// ===== СИСТЕМА УВЕДОМЛЕНИЙ =====
class NotificationManager {
    constructor() {
        this.container = this.createContainer();
        this.notifications = [];
    }

    createContainer() {
        const container = document.createElement('div');
        container.className = 'fixed top-4 right-4 z-50 space-y-2';
        container.id = 'notification-container';
        document.body.appendChild(container);
        return container;
    }

    show(message, type = 'info', duration = 5000) {
        const notification = this.createNotification(message, type);
        this.container.appendChild(notification);
        this.notifications.push(notification);

        // Анимация появления
        requestAnimationFrame(() => {
            notification.classList.add('animate-slideIn');
        });

        // Автоудаление
        if (duration > 0) {
            setTimeout(() => this.remove(notification), duration);
        }

        return notification;
    }

    createNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `max-w-sm w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-4 transition-all duration-300 transform translate-x-full`;

        const icons = {
            success: `<svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>`,
            error: `<svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>`,
            warning: `<svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>`,
            info: `<svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>`
        };

        notification.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    ${icons[type] || icons.info}
                </div>
                <div class="ml-3 w-0 flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">${message}</p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button class="inline-flex text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500" onclick="this.closest('.max-w-sm').remove()">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        `;

        return notification;
    }

    remove(notification) {
        if (notification && notification.parentNode) {
            notification.classList.add('opacity-0', 'translate-x-full');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
                this.notifications = this.notifications.filter(n => n !== notification);
            }, 300);
        }
    }

    success(message, duration = 5000) {
        return this.show(message, 'success', duration);
    }

    error(message, duration = 7000) {
        return this.show(message, 'error', duration);
    }

    warning(message, duration = 6000) {
        return this.show(message, 'warning', duration);
    }

    info(message, duration = 5000) {
        return this.show(message, 'info', duration);
    }
}

// ===== СИСТЕМА ЗАГРУЗКИ =====
class LoadingManager {
    constructor() {
        this.overlay = this.createOverlay();
        this.activeLoaders = new Set();
    }

    createOverlay() {
        const overlay = document.createElement('div');
        overlay.className = 'fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300';
        overlay.innerHTML = `
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-2xl flex items-center space-x-4">
                <div class="animate-spin rounded-full h-8 w-8 border-2 border-gray-300 border-t-primary-600"></div>
                <span class="text-gray-700 dark:text-gray-300 font-medium">Загрузка...</span>
            </div>
        `;
        document.body.appendChild(overlay);
        return overlay;
    }

    show(id = 'default') {
        this.activeLoaders.add(id);
        if (this.activeLoaders.size === 1) {
            this.overlay.classList.remove('opacity-0', 'pointer-events-none');
            this.overlay.classList.add('opacity-100');
        }
    }

    hide(id = 'default') {
        this.activeLoaders.delete(id);
        if (this.activeLoaders.size === 0) {
            this.overlay.classList.remove('opacity-100');
            this.overlay.classList.add('opacity-0', 'pointer-events-none');
        }
    }

    hideAll() {
        this.activeLoaders.clear();
        this.overlay.classList.remove('opacity-100');
        this.overlay.classList.add('opacity-0', 'pointer-events-none');
    }
}

// ===== УЛУЧШЕННЫЕ МОДАЛЬНЫЕ ОКНА =====
class ModalManager {
    constructor() {
        this.activeModals = [];
        this.bindEvents();
    }

    bindEvents() {
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.activeModals.length > 0) {
                this.close(this.activeModals[this.activeModals.length - 1]);
            }
        });
    }

    open(modalElement) {
        if (!modalElement) return;

        modalElement.classList.remove('hidden');
        modalElement.classList.add('animate-fadeIn');
        this.activeModals.push(modalElement);

        // Блокируем скролл body
        document.body.style.overflow = 'hidden';

        // Фокус на первом интерактивном элементе
        const firstInput = modalElement.querySelector('input, select, textarea, button');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }

    close(modalElement) {
        if (!modalElement) return;

        modalElement.classList.add('opacity-0');
        setTimeout(() => {
            modalElement.classList.add('hidden');
            modalElement.classList.remove('animate-fadeIn', 'opacity-0');
            this.activeModals = this.activeModals.filter(m => m !== modalElement);

            // Восстанавливаем скролл если нет активных модалок
            if (this.activeModals.length === 0) {
                document.body.style.overflow = '';
            }
        }, 300);
    }
}

// ===== УЛУЧШЕННЫЕ ТАБЛИЦЫ =====
class TableManager {
    constructor() {
        this.tables = new Map();
        this.init();
    }

    init() {
        document.querySelectorAll('[data-table]').forEach(table => {
            this.enhanceTable(table);
        });
    }

    enhanceTable(table) {
        const tableId = table.dataset.table;

        // Добавляем функциональность сортировки
        this.addSorting(table);

        // Добавляем поиск если есть атрибут
        if (table.dataset.searchable) {
            this.addSearch(table);
        }

        // Добавляем пагинацию если есть атрибут
        if (table.dataset.paginated) {
            this.addPagination(table);
        }

        this.tables.set(tableId, table);
    }

    addSorting(table) {
        const headers = table.querySelectorAll('th[data-sortable]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.innerHTML += ' <span class="sort-indicator">↕️</span>';

            header.addEventListener('click', () => {
                this.sortTable(table, header.dataset.sortable, header);
            });
        });
    }

    sortTable(table, column, header) {
        // Реализация сортировки таблицы
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const isAscending = !header.classList.contains('sort-asc');

        rows.sort((a, b) => {
            const aVal = a.querySelector(`[data-column="${column}"]`)?.textContent || '';
            const bVal = b.querySelector(`[data-column="${column}"]`)?.textContent || '';

            if (isAscending) {
                return aVal.localeCompare(bVal, 'ru', { numeric: true });
            } else {
                return bVal.localeCompare(aVal, 'ru', { numeric: true });
            }
        });

        // Обновляем индикаторы сортировки
        table.querySelectorAll('th').forEach(th => {
            th.classList.remove('sort-asc', 'sort-desc');
        });
        header.classList.add(isAscending ? 'sort-asc' : 'sort-desc');

        // Перестраиваем таблицу
        rows.forEach(row => tbody.appendChild(row));
    }

    addSearch(table) {
        const searchContainer = document.createElement('div');
        searchContainer.className = 'mb-4';
        searchContainer.innerHTML = `
            <div class="relative">
                <input type="text" placeholder="Поиск..." class="form-input pl-10" data-table-search="${table.dataset.table}">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        `;

        table.parentNode.insertBefore(searchContainer, table);

        const searchInput = searchContainer.querySelector('input');
        searchInput.addEventListener('input', (e) => {
            this.filterTable(table, e.target.value);
        });
    }

    filterTable(table, searchTerm) {
        const rows = table.querySelectorAll('tbody tr');
        const term = searchTerm.toLowerCase();

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    }
}

// ===== ИНИЦИАЛИЗАЦИЯ =====
document.addEventListener('DOMContentLoaded', () => {
    // Создаем глобальные экземпляры
    window.themeManager = new ThemeManager();
    window.notifications = new NotificationManager();
    window.loading = new LoadingManager();
    window.modals = new ModalManager();
    window.tables = new TableManager();

    // Улучшенная обработка форм
    document.querySelectorAll('form[data-ajax]').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;

            try {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<div class="loading-spinner mr-2"></div>Загрузка...';

                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: form.method,
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();

                if (response.ok) {
                    notifications.success(result.message || 'Операция выполнена успешно');
                    if (result.redirect) {
                        window.location.href = result.redirect;
                    }
                } else {
                    notifications.error(result.message || 'Произошла ошибка');
                }
            } catch (error) {
                notifications.error('Ошибка сети. Попробуйте еще раз.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    });

    // Улучшенные тултипы
    document.querySelectorAll('[data-tooltip]').forEach(element => {
        element.addEventListener('mouseenter', (e) => {
            const tooltip = document.createElement('div');
            tooltip.className = 'fixed bg-gray-900 text-white text-xs rounded py-1 px-2 z-50 pointer-events-none';
            tooltip.textContent = e.target.dataset.tooltip;
            document.body.appendChild(tooltip);

            const rect = e.target.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';

            e.target._tooltip = tooltip;
        });

        element.addEventListener('mouseleave', (e) => {
            if (e.target._tooltip) {
                e.target._tooltip.remove();
                delete e.target._tooltip;
            }
        });
    });

    // Автоматическое обновление времени
    const updateTimeElements = () => {
        document.querySelectorAll('[data-time-auto]').forEach(element => {
            const time = new Date(element.dataset.timeAuto);
            element.textContent = time.toLocaleString('ru-RU');
        });
    };

    updateTimeElements();
    setInterval(updateTimeElements, 60000); // Обновляем каждую минуту

    console.log('🚀 UI System initialized successfully');
});
