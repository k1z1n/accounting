// AG-Grid таблицы для админских страниц
import { gridUtils, customComponents } from './ag-grid-config';

class AdminGridManager {
    constructor() {
        this.grids = new Map();
        this.init();
    }

    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.initCurrenciesGrid();
            this.initExchangersGrid();
            this.initUpdateLogsGrid();
            this.initUserLogsGrid();
        });
    }

    initCurrenciesGrid() {
        const container = this.setupGrid('currencies-table', 'currenciesGrid');
        if (!container) return;

        const columnDefs = [
            {
                headerName: 'Действие',
                field: 'actions',
                cellRenderer: 'actionButtonRenderer',
                cellRendererParams: {
                    buttons: [
                        {
                            text: 'Редактировать',
                            className: 'bg-blue-600 hover:bg-blue-700 text-white',
                            onClick: (data) => this.editCurrency(data)
                        },
                        {
                            text: 'Удалить',
                            className: 'bg-red-600 hover:bg-red-700 text-white ml-2',
                            onClick: (data) => this.deleteCurrency(data)
                        }
                    ]
                },
                width: 150,
                pinned: 'left',
                filter: false,
                sortable: false
            },
            {
                headerName: 'Код',
                field: 'code',
                width: 100,
                filter: 'agTextColumnFilter'
            },
            {
                headerName: 'Название',
                field: 'name',
                width: 200,
                filter: 'agTextColumnFilter'
            },
            {
                headerName: 'Цвет',
                field: 'color',
                cellRenderer: (params) => {
                    if (!params.value) return '—';
                    const container = document.createElement('div');
                    container.className = 'flex items-center space-x-2';
                    container.innerHTML = `
                        <div class="w-4 h-4 rounded" style="background-color: ${params.value}"></div>
                        <span>${params.value}</span>
                    `;
                    return container;
                },
                width: 120,
                filter: false
            }
        ];

        this.createGrid(container, columnDefs, 'currencies');
    }

    initExchangersGrid() {
        const container = this.setupGrid('exchangers-table', 'exchangersGrid');
        if (!container) return;

        const columnDefs = [
            {
                headerName: 'Действие',
                field: 'actions',
                cellRenderer: 'actionButtonRenderer',
                cellRendererParams: {
                    buttons: [
                        {
                            text: 'Редактировать',
                            className: 'bg-blue-600 hover:bg-blue-700 text-white',
                            onClick: (data) => this.editExchanger(data)
                        },
                        {
                            text: 'Удалить',
                            className: 'bg-red-600 hover:bg-red-700 text-white ml-2',
                            onClick: (data) => this.deleteExchanger(data)
                        }
                    ]
                },
                width: 150,
                pinned: 'left',
                filter: false,
                sortable: false
            },
            {
                headerName: 'Название',
                field: 'title',
                width: 200,
                filter: 'agTextColumnFilter'
            },
            {
                headerName: 'API ключ',
                field: 'api_key',
                cellRenderer: (params) => {
                    if (!params.value) return '—';
                    return params.value.substring(0, 10) + '...';
                },
                width: 150,
                filter: false
            }
        ];

        this.createGrid(container, columnDefs, 'exchangers');
    }

    initUpdateLogsGrid() {
        const container = this.setupGrid('update-logs-table', 'updateLogsGrid');
        if (!container) return;

        const columnDefs = [
            {
                headerName: 'Пользователь',
                field: 'user_login',
                width: 150,
                filter: 'agTextColumnFilter'
            },
            {
                headerName: 'Таблица',
                field: 'table_name',
                width: 150,
                filter: 'agTextColumnFilter'
            },
            {
                headerName: 'Запись ID',
                field: 'record_id',
                width: 100,
                filter: 'agNumberColumnFilter'
            },
            {
                headerName: 'Поле',
                field: 'field_name',
                width: 150,
                filter: 'agTextColumnFilter'
            },
            {
                headerName: 'Старое значение',
                field: 'old_value',
                width: 200,
                filter: 'agTextColumnFilter'
            },
            {
                headerName: 'Новое значение',
                field: 'new_value',
                width: 200,
                filter: 'agTextColumnFilter'
            },
            {
                headerName: 'Дата',
                field: 'created_at',
                cellRenderer: 'dateRenderer',
                width: 160,
                filter: 'agDateColumnFilter'
            }
        ];

        this.createGrid(container, columnDefs, 'updateLogs', {
            pagination: true,
            paginationPageSize: 20
        });
    }

    initUserLogsGrid() {
        const container = this.setupGrid('user-logs-table', 'userLogsGrid');
        if (!container) return;

        const columnDefs = [
            {
                headerName: 'Пользователь',
                field: 'user_login',
                width: 150,
                filter: 'agTextColumnFilter'
            },
            {
                headerName: 'Действие',
                field: 'action',
                width: 150,
                filter: 'agTextColumnFilter'
            },
            {
                headerName: 'IP адрес',
                field: 'ip_address',
                width: 120,
                filter: 'agTextColumnFilter'
            },
            {
                headerName: 'User Agent',
                field: 'user_agent',
                width: 300,
                filter: 'agTextColumnFilter'
            },
            {
                headerName: 'Дата',
                field: 'created_at',
                cellRenderer: 'dateRenderer',
                width: 160,
                filter: 'agDateColumnFilter'
            }
        ];

        this.createGrid(container, columnDefs, 'userLogs', {
            pagination: true,
            paginationPageSize: 20
        });
    }

    setupGrid(tableId, gridId) {
        const oldTable = document.getElementById(tableId);
        if (!oldTable) return null;

        const container = document.createElement('div');
        container.id = gridId;
        container.className = 'ag-theme-alpine ag-theme-dark w-full';
        container.style.height = '500px';

        oldTable.parentNode.replaceChild(container, oldTable);
        return container;
    }

    createGrid(container, columnDefs, name, options = {}) {
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
            paginationPageSize: 15,
            paginationPageSizeSelector: [10, 15, 25, 50],

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
                noRowsToShow: 'Нет данных для отображения',
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
                this.grids.set(name, params.api);
                this.loadGridData(name, params.api);
            },

            ...options
        };

        const grid = gridUtils.createStandardGrid(container, columnDefs, [], gridOptions);
        return grid;
    }

    loadGridData(name, gridApi) {
        // Загружаем данные из существующих таблиц
        const oldData = this.extractExistingData(name);
        if (oldData.length > 0) {
            gridApi.setGridOption('rowData', oldData);
        }
    }

    extractExistingData(name) {
        // Извлекаем данные из скрытых старых таблиц или делаем AJAX запрос
        const data = [];

        // Здесь можно добавить логику для извлечения данных
        // из существующих HTML таблиц или API endpoints

        return data;
    }

    // Методы для действий с валютами
    editCurrency(data) {
        // Открываем форму редактирования валюты
        window.location.href = `/admin/currencies/${data.id}/edit`;
    }

    async deleteCurrency(data) {
        if (!confirm('Вы уверены, что хотите удалить эту валюту?')) return;

        try {
            const response = await fetch(`/admin/currencies/${data.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const gridApi = this.grids.get('currencies');
                if (gridApi) {
                    gridApi.applyTransaction({ remove: [data] });
                }
                window.notifications?.success('Валюта успешно удалена');
            } else {
                throw new Error(`HTTP ${response.status}`);
            }
        } catch (error) {
            console.error('Ошибка удаления валюты:', error);
            window.notifications?.error('Не удалось удалить валюту');
        }
    }

    // Методы для действий с обменниками
    editExchanger(data) {
        window.location.href = `/admin/exchangers/${data.id}/edit`;
    }

    async deleteExchanger(data) {
        if (!confirm('Вы уверены, что хотите удалить этот обменник?')) return;

        try {
            const response = await fetch(`/admin/exchangers/${data.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const gridApi = this.grids.get('exchangers');
                if (gridApi) {
                    gridApi.applyTransaction({ remove: [data] });
                }
                window.notifications?.success('Обменник успешно удален');
            } else {
                throw new Error(`HTTP ${response.status}`);
            }
        } catch (error) {
            console.error('Ошибка удаления обменника:', error);
            window.notifications?.error('Не удалось удалить обменник');
        }
    }

    // Экспорт данных для любой админской таблицы
    exportGrid(gridName, filename) {
        const gridApi = this.grids.get(gridName);
        if (gridApi) {
            gridUtils.exportToCsv(gridApi, filename || `${gridName}.csv`);
        }
    }

    // Обновление данных
    async refreshGrid(gridName) {
        const gridApi = this.grids.get(gridName);
        if (gridApi) {
            // Здесь можно добавить логику для обновления данных через API
            this.loadGridData(gridName, gridApi);
            window.notifications?.success(`Данные ${gridName} обновлены`);
        }
    }
}

// Инициализация
window.adminGridManager = new AdminGridManager();

export default AdminGridManager;
