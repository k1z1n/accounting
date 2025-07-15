// AG-Grid базовая конфигурация для проекта
import { createGrid, ModuleRegistry, AllCommunityModule } from 'ag-grid-community';
// Убираем дублирование CSS - он уже подключен в template
// import 'ag-grid-community/styles/ag-grid.css';
// import 'ag-grid-community/styles/ag-theme-alpine.css';

// Регистрируем все модули Community версии
ModuleRegistry.registerModules([AllCommunityModule]);

// Глобальные настройки AG-Grid
export const defaultGridOptions = {
    // Указываем использование legacy тем
    theme: 'legacy',

    // Основные настройки
    animateRows: true,
    enableRangeSelection: false, // Отключаем, так как требует Enterprise
    enableCellChangeFlash: true,
    suppressMenuHide: true,

    // Мобильная поддержка
    suppressTouch: false,
    enableBrowserTooltips: true,

    // Производительность
    rowBuffer: 10,
    debounceVerticalScrollbar: false,
    suppressRowHoverHighlight: false,

    // Локализация на русский
    localeText: {
        // Общие
        noRowsToShow: 'Нет данных для отображения',
        loadingOoo: 'Загрузка...',

        // Фильтры
        searchOoo: 'Поиск...',
        filterOoo: 'Фильтр...',
        applyFilter: 'Применить',
        resetFilter: 'Сбросить',
        clearFilter: 'Очистить',

        // Меню
        pinColumn: 'Закрепить колонку',
        pinLeft: 'Закрепить слева',
        pinRight: 'Закрепить справа',
        noPin: 'Не закреплять',
        valueColumns: 'Колонки значений',
        rowGroupColumns: 'Группировка строк',
        pivotColumns: 'Сводная таблица',
        autosizeThiscolumn: 'Автоширина этой колонки',
        autosizeAllColumns: 'Автоширина всех колонок',
        groupBy: 'Группировать по',
        ungroupBy: 'Разгруппировать по',
        resetColumns: 'Сбросить колонки',

        // Пагинация
        page: 'Страница',
        more: 'Ещё',
        to: 'до',
        of: 'из',
        next: 'Следующая',
        last: 'Последняя',
        first: 'Первая',
        previous: 'Предыдущая',

        // Сортировка
        sortAscending: 'Сортировать по возрастанию',
        sortDescending: 'Сортировать по убыванию',
        sortUnSort: 'Сбросить сортировку',

        // Экспорт
        export: 'Экспорт',
        csvExport: 'Экспорт CSV',
        excelExport: 'Экспорт Excel',

        // Выбор строк
        selectAll: 'Выбрать все',
        selectAllFiltered: 'Выбрать отфильтрованные',
        deselectAll: 'Сбросить выбор'
    },

    // Стандартные настройки колонок
    defaultColDef: {
        resizable: true,
        sortable: true,
        filter: 'agTextColumnFilter', // Используем стандартный текстовый фильтр
        editable: false,
        cellStyle: {
            display: 'flex',
            alignItems: 'center',
            fontSize: '14px',
            whiteSpace: 'nowrap' // Не переносить слова
        },
        headerClass: 'ag-header-cell-auto-width',
        tooltipField: null, // Отключаем стандартные tooltips
        headerTooltip: null
    },

    // Стили темной темы
    getRowStyle: (params) => {
        if (params.node.rowIndex % 2 === 0) {
            return { background: '#191919' };
        }
        return { background: '#1f1f1f' };
    },

    // Настройки пагинации
    pagination: true,
    paginationPageSize: 20,
    paginationPageSizeSelector: [10, 20, 50, 100],

    // Настройки для мобильных устройств
    suppressHorizontalScroll: false,
    alwaysShowHorizontalScroll: false,
    suppressContextMenu: false
};

// Кастомные компоненты
export const customComponents = {
    // Компонент для отображения валют с иконками
    currencyRenderer: (params) => {
        if (!params.value) return '—';

        const { amount, currency } = params.value;
        if (!amount || !currency) return '—';

        const isPositive = amount >= 0;
        const sign = isPositive ? '+' : '';
        const colorClass = isPositive ? 'text-green-400' : 'text-red-400';

        const container = document.createElement('div');
        container.className = 'inline-flex items-center space-x-1';
        container.innerHTML = `
            <span class="${colorClass} font-bold">${sign}${Math.abs(amount)}</span>
            <img src="/images/coins/${currency}.svg" alt="${currency}" class="w-4 h-4" onerror="this.style.display='none'">
        `;
        return container;
    },

    // Компонент для статуса с цветовой индикацией
    statusRenderer: (params) => {
        if (!params.value) return '—';

        let statusClass = 'status-badge';
        let statusText = params.value;

        switch (params.value) {
            case 'выполненная заявка':
                statusClass += ' completed';
                statusText = 'Выполнена';
                break;
            case 'оплаченная заявка':
                statusClass += ' paid';
                statusText = 'Оплачена';
                break;
            case 'возврат':
                statusClass += ' return';
                statusText = 'Возврат';
                break;
            default:
                statusClass += ' completed';
                statusText = params.value;
        }

        const container = document.createElement('span');
        container.className = statusClass;
        container.textContent = statusText;
        return container;
    },

    // Компонент для кнопок действий
    actionButtonRenderer: (params) => {
        const container = document.createElement('div');
        container.className = 'flex space-x-2';

        if (params.data && params.colDef.cellRendererParams) {
            const { buttons } = params.colDef.cellRendererParams;

            buttons.forEach(button => {
                const btn = document.createElement('button');
                btn.className = `action-button ${button.className || 'edit'}`;
                btn.innerHTML = button.icon ? `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">${button.icon}</svg>` : button.text;
                btn.onclick = () => button.onClick(params.data);
                container.appendChild(btn);
            });
        }

        return container;
    },

    // Компонент для дат
    dateRenderer: (params) => {
        if (!params.value) return '—';

        const date = new Date(params.value);
        return date.toLocaleString('ru-RU', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }
};

// Утилитные функции
export const gridUtils = {
    // Создание стандартной конфигурации грида
    createStandardGrid: (container, columnDefs, rowData, options = {}) => {
        const gridOptions = {
            ...defaultGridOptions,
            ...options,
            columnDefs,
            rowData,
            components: customComponents
        };

        return createGrid(container, gridOptions);
    },

    // Обновление данных грида
    updateGridData: (gridApi, newData) => {
        if (gridApi) {
            gridApi.setGridOption('rowData', newData);
        }
    },

    // Применение фильтров
    applyQuickFilter: (gridApi, searchText) => {
        if (gridApi) {
            gridApi.setGridOption('quickFilterText', searchText);
        }
    },

    // Экспорт в CSV
    exportToCsv: (gridApi, filename = 'export.csv') => {
        if (gridApi) {
            gridApi.exportDataAsCsv({
                fileName: filename,
                processCellCallback: (params) => {
                    // Обработка специальных типов данных для экспорта
                    if (params.value && typeof params.value === 'object') {
                        if (params.value.amount && params.value.currency) {
                            return `${params.value.amount} ${params.value.currency}`;
                        }
                    }
                    return params.value;
                }
            });
        }
    },

    // Автоматическое изменение размера колонок
    autoSizeColumns: (gridApi) => {
        if (gridApi) {
            gridApi.autoSizeAllColumns();
        }
    },

    // Настройка мобильного отображения
    setupMobileView: (gridApi) => {
        if (gridApi && window.innerWidth < 768) {
            // Скрываем менее важные колонки на мобильных
            const columnsToHide = ['created_by', 'updated_at'];
            columnsToHide.forEach(field => {
                gridApi.setColumnsVisible([field], false);
            });

            // Уменьшаем размер пагинации
            gridApi.setGridOption('paginationPageSize', 10);
        }
    }
};

// CSS стили для интеграции с темной темой проекта
export const agGridStyles = `
    .ag-theme-alpine.ag-theme-dark {
        --ag-background-color: #0f0f0f;
        --ag-header-background-color: #1a1a1a;
        --ag-odd-row-background-color: #141414;
        --ag-row-hover-color: #1e1e1e;
        --ag-border-color: #2a2a2a;
        --ag-header-foreground-color: #ffffff;
        --ag-foreground-color: #e5e5e5;
        --ag-secondary-foreground-color: #a0a0a0;
        --ag-input-focus-border-color: #3b82f6;
        --ag-range-selection-background-color: rgba(59, 130, 246, 0.1);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .ag-theme-alpine.ag-theme-dark .ag-header {
        background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
        border-bottom: 2px solid #2a2a2a;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .ag-theme-alpine.ag-theme-dark .ag-header-cell {
        border-right: 1px solid #2a2a2a;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #ffffff;
        padding: 16px 12px;
        background: transparent;
        transition: all 0.2s ease;
        white-space: nowrap !important;
        overflow: visible !important;
        text-overflow: clip !important;
    }

    .ag-theme-alpine.ag-theme-dark .ag-header-cell:hover {
        background: rgba(59, 130, 246, 0.1);
        color: #60a5fa;
    }

    .ag-theme-alpine.ag-theme-dark .ag-cell {
        border-right: 1px solid #2a2a2a;
        border-bottom: 1px solid #2a2a2a;
        line-height: 1.5;
        white-space: nowrap !important;
        overflow: visible !important;
        text-overflow: clip !important;
        padding: 12px 16px;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .ag-theme-alpine.ag-theme-dark .ag-row {
        border: none;
        transition: all 0.2s ease;
    }

    .ag-theme-alpine.ag-theme-dark .ag-row:hover {
        background: #1e1e1e !important;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .ag-theme-alpine.ag-theme-dark .ag-row.ag-row-selected {
        background: rgba(59, 130, 246, 0.15) !important;
    }

    .ag-theme-alpine.ag-theme-dark .ag-paging-panel {
        border-top: 2px solid #2a2a2a;
        background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
        color: var(--ag-foreground-color);
        padding: 16px;
        font-size: 14px;
    }

    .ag-theme-alpine.ag-theme-dark .ag-paging-button {
        color: var(--ag-foreground-color);
        border: 1px solid #2a2a2a;
        background: #1a1a1a;
        border-radius: 6px;
        padding: 8px 12px;
        margin: 0 4px;
        transition: all 0.2s ease;
        font-weight: 500;
    }

    .ag-theme-alpine.ag-theme-dark .ag-paging-button:hover {
        background: #2a2a2a;
        border-color: #3b82f6;
        color: #60a5fa;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .ag-theme-alpine.ag-theme-dark .ag-paging-button.ag-paging-button-disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .ag-theme-alpine.ag-theme-dark .ag-icon {
        color: var(--ag-foreground-color);
    }

    /* Стили для фильтров */
    .ag-theme-alpine.ag-theme-dark .ag-filter-toolpanel-header {
        background: #1a1a1a;
        border-bottom: 1px solid #2a2a2a;
    }

    .ag-theme-alpine.ag-theme-dark .ag-filter-toolpanel-header,
    .ag-theme-alpine.ag-theme-dark .ag-filter-toolpanel-header-wrapper {
        background: #1a1a1a;
    }

    /* Стили для меню колонок */
    .ag-theme-alpine.ag-theme-dark .ag-menu {
        background: #1a1a1a;
        border: 1px solid #2a2a2a;
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .ag-theme-alpine.ag-theme-dark .ag-menu-option {
        padding: 8px 16px;
        transition: all 0.2s ease;
    }

    .ag-theme-alpine.ag-theme-dark .ag-menu-option:hover {
        background: #2a2a2a;
        color: #60a5fa;
    }

    /* Стили для скроллбаров */
    .ag-theme-alpine.ag-theme-dark .ag-body-vertical-scroll-viewport {
        background: #1a1a1a;
    }

    .ag-theme-alpine.ag-theme-dark .ag-body-horizontal-scroll-viewport {
        background: #1a1a1a;
    }

    /* Автоматическая ширина заголовков */
    .ag-header-cell-auto-width {
        white-space: nowrap !important;
        overflow: visible !important;
        text-overflow: clip !important;
    }

    /* Включаем горизонтальную прокрутку */
    .ag-theme-alpine .ag-body-horizontal-scroll,
    .ag-theme-alpine.ag-theme-dark .ag-body-horizontal-scroll {
        display: block !important;
    }

    /* Позволяем колонкам автоматически подстраиваться под содержимое */
    .ag-theme-alpine .ag-header-cell,
    .ag-theme-alpine.ag-theme-dark .ag-header-cell,
    .ag-theme-alpine .ag-cell,
    .ag-theme-alpine.ag-theme-dark .ag-cell {
        padding: 12px !important;
        font-size: 14px !important;
        white-space: nowrap !important;
        overflow: visible !important;
        text-overflow: clip !important;
        border-right: 1px solid #2a2a2a;
        box-sizing: border-box !important;
    }

    .ag-theme-alpine.ag-theme-dark .ag-header-cell {
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #ffffff;
        background: transparent;
        transition: all 0.2s ease;
    }

    .ag-theme-alpine.ag-theme-dark .ag-header-cell:hover {
        background: rgba(59, 130, 246, 0.1);
        color: #60a5fa;
    }

    .ag-theme-alpine.ag-theme-dark .ag-cell {
        border-bottom: 1px solid #2a2a2a;
        line-height: 1.5;
        transition: all 0.2s ease;
    }

    /* Отключаем сжатие текста в заголовках */
    .ag-theme-alpine .ag-header-cell-label,
    .ag-theme-alpine.ag-theme-dark .ag-header-cell-label {
        white-space: nowrap !important;
        overflow: visible !important;
        text-overflow: clip !important;
    }

    /* Отключаем сжатие текста в ячейках */
    .ag-theme-alpine .ag-cell-wrapper,
    .ag-theme-alpine.ag-theme-dark .ag-cell-wrapper {
        white-space: nowrap !important;
        overflow: visible !important;
        text-overflow: clip !important;
    }

    /* Мобильная адаптация */
    @media (max-width: 768px) {
        .ag-theme-alpine.ag-theme-dark {
            font-size: 12px;
            border-radius: 8px;
        }

        .ag-theme-alpine.ag-theme-dark .ag-header-cell {
            min-width: 80px;
            font-size: 11px;
            padding: 12px 8px;
            white-space: nowrap !important;
            overflow: visible !important;
            text-overflow: clip !important;
        }

        .ag-theme-alpine.ag-theme-dark .ag-cell {
            min-width: 80px;
            padding: 8px 12px;
            font-size: 12px;
            white-space: nowrap !important;
            overflow: visible !important;
            text-overflow: clip !important;
        }

        .ag-theme-alpine.ag-theme-dark .ag-paging-panel {
            font-size: 12px;
            padding: 12px;
        }
    }

    /* Кастомные стили для валют */
    .currency-cell {
        display: flex !important;
        align-items: center;
        gap: 4px;
    }

    .currency-amount.positive {
        color: #10b981;
        font-weight: 600;
    }

    .currency-amount.negative {
        color: #ef4444;
        font-weight: 600;
    }

    /* Анимации */
    .ag-theme-alpine.ag-theme-dark .ag-row {
        transition: all 0.2s ease;
    }

    .ag-theme-alpine.ag-theme-dark .ag-cell-data-changed {
        background-color: rgba(59, 130, 246, 0.2) !important;
        transition: background-color 2s ease;
    }

    .ag-theme-alpine.ag-theme-dark .ag-cell-data-changed-animation {
        background-color: transparent !important;
    }

    /* Стили для статусов */
    .ag-theme-alpine.ag-theme-dark .ag-cell .status-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .status-badge.completed {
        background: rgba(16, 185, 129, 0.2);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .status-badge.paid {
        background: rgba(59, 130, 246, 0.2);
        color: #60a5fa;
        border: 1px solid rgba(59, 130, 246, 0.3);
    }

    .status-badge.return {
        background: rgba(239, 68, 68, 0.2);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    /* Стили для кнопок действий */
    .ag-theme-alpine.ag-theme-dark .ag-cell .action-button {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
    }

    .action-button.edit {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
    }

    .action-button.edit:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
    }

    .ag-header-right {
        text-align: right !important;
        justify-content: flex-end !important;
    }
    .ag-header-center {
        text-align: center !important;
        justify-content: center !important;
    }

    .ag-header-right .ag-header-cell-label,
    .ag-header-right .ag-header-cell-text {
        justify-content: flex-end !important;
        text-align: right !important;
        align-items: center !important;
        width: 100% !important;
        display: flex !important;
    }
    .ag-header-center .ag-header-cell-label,
    .ag-header-center .ag-header-cell-text {
        justify-content: center !important;
        text-align: center !important;
        align-items: center !important;
        width: 100% !important;
        display: flex !important;
    }

    /* Принудительно синхронизируем ширину header и cell */
    .ag-theme-alpine .ag-header-cell,
    .ag-theme-alpine.ag-theme-dark .ag-header-cell {
        width: auto !important;
        min-width: auto !important;
        max-width: none !important;
    }

    .ag-theme-alpine .ag-cell,
    .ag-theme-alpine.ag-theme-dark .ag-cell {
        width: auto !important;
        min-width: auto !important;
        max-width: none !important;
    }
`;

export default {
    defaultGridOptions,
    customComponents,
    gridUtils,
    agGridStyles
};
