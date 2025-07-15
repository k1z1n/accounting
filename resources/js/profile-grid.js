// AG-Grid для профиля пользователя
import { gridUtils, customComponents } from './ag-grid-config';

class ProfileGrid {
    constructor() {
        this.gridApi = null;
        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.initHistoryGrid();
        });
    }

    initHistoryGrid() {
        const oldTable = document.querySelector('#profile-history-table, .profile-history-table');
        if (!oldTable) return;

        const container = document.createElement('div');
        container.id = 'profileHistoryGrid';
        container.className = 'ag-theme-alpine ag-theme-dark w-full';
        container.style.height = '400px';

        oldTable.parentNode.replaceChild(container, oldTable);

        const columnDefs = [
            {
                headerName: 'Дата',
                field: 'created_at',
                cellRenderer: 'dateRenderer',
                width: 160,
                filter: 'agDateColumnFilter',
                sort: 'desc' // Сортировка по убыванию по умолчанию
            },
            {
                headerName: 'Тип операции',
                field: 'operation_type',
                width: 150,
                filter: 'agTextColumnFilter',
                cellRenderer: (params) => {
                    if (!params.value) return '—';

                    let className = 'px-2 py-1 rounded-full text-xs font-medium';
                    switch (params.value) {
                        case 'Покупка крипты':
                            className += ' bg-green-100 text-green-800';
                            break;
                        case 'Продажа крипты':
                            className += ' bg-blue-100 text-blue-800';
                            break;
                        case 'Оплата':
                            className += ' bg-red-100 text-red-800';
                            break;
                        case 'Перевод':
                            className += ' bg-yellow-100 text-yellow-800';
                            break;
                        case 'Заявка':
                            className += ' bg-purple-100 text-purple-800';
                            break;
                        default:
                            className += ' bg-gray-100 text-gray-800';
                    }

                    const container = document.createElement('span');
                    container.className = className;
                    container.textContent = params.value;
                    return container;
                }
            },
            {
                headerName: 'Сумма',
                field: 'amount_data',
                cellRenderer: (params) => {
                    const data = params.data;
                    if (!data.amount || !data.currency) return '—';

                    const amount = ProfileGrid.stripZeros(Math.abs(data.amount));
                    const currency = data.currency;
                    const isPositive = data.amount > 0;
                    const sign = isPositive ? '+' : '-';
                    const colorClass = isPositive ? 'text-green-400' : 'text-red-400';

                    const container = document.createElement('div');
                    container.className = 'inline-flex items-center space-x-1';
                    container.innerHTML = `
                        <span class="${colorClass} font-bold">${sign}${amount}</span>
                        <img src="/images/coins/${currency}.svg" alt="${currency}" class="w-4 h-4" onerror="this.style.display='none'">
                    `;
                    return container;
                },
                width: 140,
                filter: false,
                sortable: true,
                comparator: (a, b, nodeA, nodeB) => {
                    const amountA = Math.abs(nodeA.data?.amount || 0);
                    const amountB = Math.abs(nodeB.data?.amount || 0);
                    return amountA - amountB;
                }
            },
            {
                headerName: 'Обменник/Платформа',
                field: 'platform',
                width: 150,
                filter: 'agTextColumnFilter'
            },
            {
                headerName: 'Описание',
                field: 'description',
                width: 200,
                filter: 'agTextColumnFilter',
                cellRenderer: (params) => {
                    if (!params.value) return '—';

                    // Обрезаем длинное описание
                    const maxLength = 50;
                    if (params.value.length > maxLength) {
                        const container = document.createElement('span');
                        container.textContent = params.value.substring(0, maxLength) + '...';
                        container.title = params.value; // Полный текст в tooltip
                        return container;
                    }

                    return params.value;
                }
            }
        ];

        const gridOptions = {
            columnDefs,
            rowData: [],
            components: customComponents,

            // Основные настройки
            animateRows: true,
            enableRangeSelection: false, // Отключаем, так как требует Enterprise
            enableCellChangeFlash: true,
            suppressMenuHide: true,

            // Мобильная поддержка
            suppressTouch: false,
            enableBrowserTooltips: true,

            // Пагинация
            pagination: true,
            paginationPageSize: 20,
            paginationPageSizeSelector: [10, 20, 50],

            // Стили темной темы
            getRowStyle: (params) => {
                if (params.node.rowIndex % 2 === 0) {
                    return { background: '#191919' };
                }
                return { background: '#1f1f1f' };
            },

            // Стандартные настройки колонок
            defaultColDef: {
                flex: 1,
                minWidth: 100,
                resizable: true,
                sortable: true,
                filter: true,
                editable: false,
                cellStyle: {
                    display: 'flex',
                    alignItems: 'center',
                    fontSize: '14px',
                    whiteSpace: 'nowrap'
                }
            },

            // Локализация
            localeText: {
                noRowsToShow: 'Нет операций для отображения',
                loadingOoo: 'Загрузка...',
                searchOoo: 'Поиск...',
                filterOoo: 'Фильтр...',
                applyFilter: 'Применить',
                resetFilter: 'Сбросить',
                clearFilter: 'Очистить',
                page: 'Страница',
                to: 'до',
                of: 'из',
                next: 'Следующая',
                last: 'Последняя',
                first: 'Первая',
                previous: 'Предыдущая'
            },

            // События
            onGridReady: (params) => {
                this.gridApi = params.api;
                this.loadInitialData();
                this.setupMobileView();
            }
        };

        this.grid = gridUtils.createStandardGrid(container, columnDefs, [], gridOptions);
    }

    async loadInitialData() {
        try {
            // Попытка загрузить данные из API профиля
            const response = await fetch('/profile/history', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.updateGrid(data.history || []);
            } else {
                // Fallback: извлекаем из существующих данных в DOM
                this.loadFromDOM();
            }
        } catch (error) {
            console.error('Ошибка загрузки истории профиля:', error);
            this.loadFromDOM();
        }
    }

    loadFromDOM() {
        // Извлекаем данные из paintTable функции если она есть
        if (window.paintTable && typeof window.paintTable === 'function') {
            // Используем данные которые уже загружены
            const tableRows = document.querySelectorAll('.profile-history-row');
            const data = [];

            tableRows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length >= 4) {
                    const rowData = {
                        created_at: cells[0]?.textContent.trim(),
                        operation_type: cells[1]?.textContent.trim(),
                        amount: this.parseAmount(cells[2]?.textContent),
                        currency: this.parseCurrency(cells[2]?.textContent),
                        platform: cells[3]?.textContent.trim(),
                        description: cells[4]?.textContent.trim() || cells[1]?.textContent.trim()
                    };
                    data.push(rowData);
                }
            });

            this.updateGrid(data);
        }
    }

    parseAmount(text) {
        if (!text) return 0;
        const match = text.match(/([-+]?[\d.,]+)/);
        return match ? parseFloat(match[1].replace(',', '.')) : 0;
    }

    parseCurrency(text) {
        if (!text) return 'USDT';
        const match = text.match(/([A-Z]{3,4})$/);
        return match ? match[1] : 'USDT';
    }

    updateGrid(data) {
        if (this.gridApi) {
            this.gridApi.setGridOption('rowData', data);
        }
    }

    setupMobileView() {
        if (this.gridApi && window.innerWidth < 768) {
            // Скрываем менее важные колонки на мобильных
            this.gridApi.setColumnsVisible(['description', 'platform'], false);
            this.gridApi.setGridOption('paginationPageSize', 10);

            // Автоматически подгоняем размер колонок
            this.gridApi.autoSizeAllColumns();
        }
    }

    static stripZeros(value) {
        const s = String(value);
        if (!s.includes('.')) return s;
        return s.replace(/\.?0+$/, '');
    }

    // Добавление фильтра по типу операции
    filterByOperationType(type) {
        if (this.gridApi) {
            if (type) {
                this.gridApi.setFilterModel({
                    operation_type: {
                        type: 'equals',
                        filter: type
                    }
                });
            } else {
                this.gridApi.setFilterModel({});
            }
        }
    }

    // Добавление фильтра по валюте
    filterByCurrency(currency) {
        if (this.gridApi) {
            if (currency) {
                this.gridApi.setFilterModel({
                    currency: {
                        type: 'equals',
                        filter: currency
                    }
                });
            } else {
                this.gridApi.setFilterModel({});
            }
        }
    }

    // Экспорт истории
    exportHistory() {
        if (this.gridApi) {
            gridUtils.exportToCsv(this.gridApi, 'profile_history.csv');
        }
    }

    // Обновление данных
    async refreshData() {
        await this.loadInitialData();
        window.notifications?.success('История операций обновлена');
    }
}

// Инициализация при загрузке DOM
document.addEventListener('DOMContentLoaded', () => {
    // Проверяем несколько возможных селекторов для таблицы профиля
    const profileTableSelectors = [
        '#profile-history-table',
        '.profile-history-table',
        '#historyTable',
        'table[data-profile-history]'
    ];

    const profileTable = profileTableSelectors
        .map(selector => document.querySelector(selector))
        .find(table => table !== null);

    if (profileTable) {
        window.profileGrid = new ProfileGrid();
    }
});

export default ProfileGrid;
