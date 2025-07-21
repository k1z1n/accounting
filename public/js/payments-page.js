class PaymentsPage {
    constructor() {
        console.log('PaymentsPage: конструктор вызван');
        this.gridApi = null;
        this.isAdmin = window.isAdmin || false;
        this.currentPage = 1;
        this.perPage = 50;
        this.hasMorePages = true;
        this.allData = [];
        this.isLoading = false;
        this.filters = {
            status: '',
            exchanger: ''
        };
        this.init();
    }

        init() {
        console.log('PaymentsPage: инициализация');
        this.setupColumnDefs();
        this.setupGridOptions();
        this.setupEventListeners();
        this.createGrid();
        this.loadInitialData();
    }

    dateRenderer(params) {
        if (!params.value) return '';
        const date = new Date(params.value);
        return date.toLocaleDateString('ru-RU', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    statusRenderer(params) {
        if (!params.value) return '';
        const status = params.value;
        let color = 'text-gray-400';
        let icon = '';

        switch (status) {
            case 'выполненная заявка':
                color = 'text-green-400';
                icon = '✅';
                break;
            case 'оплаченная заявка':
                color = 'text-blue-400';
                icon = '💰';
                break;
            case 'возврат':
                color = 'text-red-400';
                icon = '↩️';
                break;
        }

        return `<span class="${color}">${icon} ${status}</span>`;
    }

    setupColumnDefs() {
        this.columnDefs = [
            {
                headerName: 'ID',
                field: 'id',
                width: 80,
                sortable: true,
                filter: true
            },
            {
                headerName: 'Пользователь',
                field: 'user.login',
                width: 150,
                sortable: true,
                filter: true
            },
            {
                headerName: 'Сумма продажи',
                field: 'sell_amount',
                width: 120,
                sortable: true,
                filter: true,
                cellRenderer: (params) => {
                    const data = params.data;
                    if (!data.sell_amount || !data.sell_currency) return '—';
                    const amount = parseFloat(data.sell_amount).toFixed(2);
                    const currency = data.sell_currency.code;
                    const container = document.createElement('div');
                    container.className = 'inline-flex items-center space-x-1';
                    container.innerHTML = `
                        <span>${amount}</span>
                        <img src="/images/coins/${currency}.svg" alt="${currency}" class="w-4 h-4" onerror="this.style.display='none'">
                    `;
                    return container;
                }
            },
            {
                headerName: 'Обменник',
                field: 'exchanger.title',
                width: 120,
                sortable: true,
                filter: true
            },
            {
                headerName: 'Комментарий',
                field: 'comment',
                width: 200,
                sortable: true,
                filter: true
            },
            {
                headerName: 'Дата',
                field: 'created_at',
                width: 150,
                sortable: true,
                filter: true,
                cellRenderer: this.dateRenderer
            }
        ];
    }

    setupGridOptions() {
        this.gridOptions = {
            columnDefs: this.columnDefs,
            rowData: [],
            pagination: false,
            domLayout: 'normal',
            defaultColDef: {
                resizable: true,
                sortable: true,
                filter: true,
                floatingFilter: false
            },
            rowSelection: 'single',
            animateRows: true,
            suppressRowClickSelection: false,
            suppressCellFocus: true,
            suppressRowDeselection: false,
            suppressRowClickSelection: false,
            suppressRowTransform: true,
            suppressAnimationFrame: false,
            suppressBrowserResizeObserver: false,
            suppressColumnVirtualisation: false,
            suppressRowVirtualisation: false,
            suppressMenuHide: false,
            suppressMovableColumns: false,
            suppressFieldDotNotation: false,
            suppressPropertyNamesCheck: false,
            suppressParentsInRowNodes: false,
            suppressModelUpdateAfterUpdateTransaction: false,
            suppressLoadingOverlay: false,
            suppressNoRowsOverlay: false,
            suppressColumnMoveAnimation: false,
            suppressRowHoverHighlight: false,
            suppressColumnVirtualisation: false,
            suppressRowVirtualisation: false,
            suppressMenuHide: false,
            suppressMovableColumns: false,
            suppressFieldDotNotation: false,
            suppressPropertyNamesCheck: false,
            suppressParentsInRowNodes: false,
            suppressModelUpdateAfterUpdateTransaction: false,
            suppressLoadingOverlay: false,
            suppressNoRowsOverlay: false,
            suppressColumnMoveAnimation: false,
            suppressRowHoverHighlight: false,
            onGridReady: (params) => {
                console.log('PaymentsPage: AG-Grid готов');
                this.gridApi = params.api;
                this.gridApi.sizeColumnsToFit();
            },
            onFirstDataRendered: (params) => {
                console.log('PaymentsPage: данные отрендерены');
                params.api.sizeColumnsToFit();
            }
        };
    }

    createGrid() {
        const gridDiv = document.getElementById('paymentsGrid');
        if (!gridDiv) {
            console.error('PaymentsPage: элемент paymentsGrid не найден');
            return;
        }

        // Проверяем различные способы загрузки AG-Grid
        console.log('PaymentsPage: проверяем AG-Grid...');
        console.log('PaymentsPage: typeof agGrid:', typeof agGrid);
        console.log('PaymentsPage: agGrid keys:', agGrid ? Object.keys(agGrid) : 'undefined');

        let GridConstructor = null;

        // Пробуем разные варианты
        if (typeof agGrid !== 'undefined') {
            if (agGrid.Grid) {
                GridConstructor = agGrid.Grid;
                console.log('PaymentsPage: найден agGrid.Grid');
            } else if (agGrid.createGrid) {
                GridConstructor = agGrid.createGrid;
                console.log('PaymentsPage: найден agGrid.createGrid');
            } else if (window.agGrid && window.agGrid.Grid) {
                GridConstructor = window.agGrid.Grid;
                console.log('PaymentsPage: найден window.agGrid.Grid');
            }
        }

        if (GridConstructor) {
            try {
                if (GridConstructor === agGrid.createGrid) {
                    // Используем createGrid функцию
                    GridConstructor(gridDiv, this.gridOptions);
                } else {
                    // Используем конструктор Grid
                    new GridConstructor(gridDiv, this.gridOptions);
                }
                console.log('PaymentsPage: AG-Grid создан успешно');
            } catch (error) {
                console.error('PaymentsPage: ошибка создания AG-Grid:', error);
                this.showGridError(gridDiv, 'Ошибка создания таблицы: ' + error.message);
            }
        } else {
            console.error('PaymentsPage: AG-Grid не найден в ожидаемом формате');
            this.showGridError(gridDiv, 'AG-Grid не загружен корректно');
        }
    }

    showGridError(gridDiv, message) {
        gridDiv.innerHTML = `
            <div class="flex items-center justify-center h-full text-red-400">
                <div class="text-center">
                    <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <p class="text-lg font-semibold">Ошибка загрузки AG-Grid</p>
                    <p class="text-sm text-gray-400 mt-2">${message}</p>
                    <button onclick="location.reload()" class="mt-4 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                        Обновить страницу
                    </button>
                </div>
            </div>
        `;
    }

    setupEventListeners() {
        console.log('PaymentsPage: setupEventListeners вызван');

        // Фильтр по статусу
        const statusFilter = document.getElementById('statusFilter');
        console.log('PaymentsPage: элемент statusFilter найден:', !!statusFilter);
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                console.log('PaymentsPage: изменен фильтр статуса:', e.target.value);
                this.filters.status = e.target.value;
                console.log('PaymentsPage: фильтры после изменения статуса:', this.filters);
                this.applyFilters();
            });
        } else {
            console.error('PaymentsPage: элемент statusFilter НЕ найден!');
        }

        // Фильтр по обменнику
        const exchangerFilter = document.getElementById('exchangerFilter');
        console.log('PaymentsPage: элемент exchangerFilter найден:', !!exchangerFilter);
        if (exchangerFilter) {
            exchangerFilter.addEventListener('change', (e) => {
                console.log('PaymentsPage: изменен фильтр обменника:', e.target.value);
                this.filters.exchanger = e.target.value;
                console.log('PaymentsPage: фильтры после изменения обменника:', this.filters);
                this.applyFilters();
            });
        } else {
            console.error('PaymentsPage: элемент exchangerFilter НЕ найден!');
        }

        // Кнопка обновления
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                console.log('PaymentsPage: нажата кнопка обновления');
                this.loadInitialData();
            });
        }
    }

    async loadInitialData() {
        try {
            console.log('PaymentsPage: загружаем данные...');
            console.log('PaymentsPage: фильтры перед запросом:', this.filters);

            const params = {
                page: 1,
                perPage: 50,
                statusFilter: this.filters.status || '',
                exchangerFilter: this.filters.exchanger || ''
            };
            console.log('PaymentsPage: параметры запроса:', params);

            const url = '/test-payments/data?' + new URLSearchParams(params);
            console.log('PaymentsPage: URL запроса:', url);

            const response = await fetch(url);
            console.log('PaymentsPage: статус ответа:', response.status);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('PaymentsPage: ошибка HTTP:', response.status, errorText);
                throw new Error(`HTTP ${response.status}: ${errorText}`);
            }

            const result = await response.json();
            console.log('PaymentsPage: получены данные:', result);

            this.allData = result.data || [];
            this.currentPage = result.currentPage || 1;
            this.hasMorePages = result.hasMorePages || false;

            console.log('PaymentsPage: обработано записей:', this.allData.length);
            console.log('PaymentsPage: текущая страница:', this.currentPage);
            console.log('PaymentsPage: есть еще страницы:', this.hasMorePages);

            this.updateGrid();
            this.updateStatistics();
            this.updateLoadMoreButton();

            if (this.gridApi) {
                this.gridApi.hideOverlay();
            }

            console.log('PaymentsPage: данные успешно загружены и отображены');
        } catch (error) {
            console.error('PaymentsPage: ошибка загрузки данных:', error);
            this.showError('Ошибка загрузки данных: ' + error.message);
        }
    }

    async applyFilters() {
        console.log('PaymentsPage: applyFilters вызван');
        console.log('PaymentsPage: текущие фильтры:', this.filters);
        console.log('PaymentsPage: сбрасываем страницу на 1');

        this.currentPage = 1;
        this.hasMorePages = true;
        this.allData = [];

        console.log('PaymentsPage: вызываем loadInitialData с фильтрами');
        await this.loadInitialData();

        console.log('PaymentsPage: applyFilters завершен');
    }

    async loadMore() {
        if (this.isLoading || !this.hasMorePages) {
            console.log('PaymentsPage: загрузка невозможна - загрузка или нет страниц');
            return;
        }

        console.log('PaymentsPage: загружаем еще данные...');
        this.isLoading = true;
        this.showLoadMoreSpinner();

        try {
            const nextPage = this.currentPage + 1;
            console.log('PaymentsPage: следующая страница:', nextPage);

            const url = '/test-payments/data?' + new URLSearchParams({
                page: nextPage,
                perPage: this.perPage || 50,
                statusFilter: this.filters.status || '',
                exchangerFilter: this.filters.exchanger || ''
            });

            console.log('PaymentsPage: URL запроса:', url);

            const response = await fetch(url);
            console.log('PaymentsPage: статус ответа:', response.status);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            console.log('PaymentsPage: получены данные:', result);

            // Добавляем новые данные к существующим
            this.allData = [...this.allData, ...result.data];
            this.currentPage = result.currentPage;
            this.hasMorePages = result.hasMorePages;

            console.log('PaymentsPage: общее количество записей:', this.allData.length);
            console.log('PaymentsPage: текущая страница:', this.currentPage);
            console.log('PaymentsPage: есть еще страницы:', this.hasMorePages);

            this.updateGrid();
            this.updateStatistics();
            this.updateLoadMoreButton();

            console.log('PaymentsPage: данные успешно добавлены');
        } catch (error) {
            console.error('PaymentsPage: ошибка загрузки дополнительных данных:', error);
            this.showError('Ошибка загрузки данных: ' + error.message);
        } finally {
            this.isLoading = false;
            this.hideLoadMoreSpinner();
        }
    }

    updateGrid() {
        if (this.gridApi) {
            console.log('PaymentsPage: обновляем грид с', this.allData.length, 'записями');
            this.gridApi.setRowData(this.allData);
            this.gridApi.sizeColumnsToFit();
        }
    }

    updateStatistics() {
        console.log('PaymentsPage: обновляем статистику');

        const total = this.allData.length;
        // Статистика по статусам отключена, так как поля status нет в миграции
        const completed = 0;
        const paid = 0;
        const returned = 0;

        document.getElementById('totalPayments').textContent = total;
        document.getElementById('completedPayments').textContent = completed;
        document.getElementById('paidPayments').textContent = paid;
        document.getElementById('returnPayments').textContent = returned;

        console.log('PaymentsPage: статистика обновлена:', { total, completed, paid, returned });
    }

    showLoadMoreSpinner() {
        const spinner = document.getElementById('loadMoreSpinner');
        if (spinner) {
            spinner.classList.remove('hidden');
        }
    }

    hideLoadMoreSpinner() {
        const spinner = document.getElementById('loadMoreSpinner');
        if (spinner) {
            spinner.classList.add('hidden');
        }
    }

    updateLoadMoreButton() {
        const button = document.getElementById('loadMoreBtn');
        if (!button) {
            console.error('PaymentsPage: кнопка loadMoreBtn не найдена');
            return;
        }

        console.log('PaymentsPage: обновляем кнопку "Показать еще"');
        console.log('PaymentsPage: hasMorePages:', this.hasMorePages);
        console.log('PaymentsPage: isLoading:', this.isLoading);

        if (this.hasMorePages && !this.isLoading) {
            button.classList.remove('hidden');
            console.log('PaymentsPage: кнопка показана');
        } else {
            button.classList.add('hidden');
            console.log('PaymentsPage: кнопка скрыта');
        }
    }

    showError(message) {
        console.error('PaymentsPage: ошибка:', message);
        // Здесь можно добавить уведомление пользователю
    }

    static stripZeros(value) {
        if (value === null || value === undefined) return '';
        return parseFloat(value).toString();
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    console.log('PaymentsPage: DOM загружен, инициализируем страницу');
    window.paymentsPage = new PaymentsPage();
});

// Проверка AG-Grid при загрузке
document.addEventListener('DOMContentLoaded', function() {
    if (typeof agGrid === 'undefined') {
        console.error('PaymentsPage: AG-Grid не загружен при загрузке DOM!');
    } else {
        console.log('PaymentsPage: AG-Grid загружен при загрузке DOM');
    }
});
