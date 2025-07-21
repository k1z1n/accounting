class SaleCryptPage {
    constructor() {
        console.log('SaleCryptPage: конструктор вызван');
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
        console.log('SaleCryptPage: инициализация');
        this.setupColumnDefs();
        this.setupGridOptions();
        this.createGrid();
        this.setupEventListeners();
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
                headerName: 'Сумма продажи',
                field: 'sale_amount',
                width: 120,
                sortable: true,
                filter: true,
                cellRenderer: (params) => {
                    const data = params.data;
                    if (!data.sale_amount || !data.sale_currency) return '—';
                    const amount = parseFloat(data.sale_amount).toFixed(2);
                    const currency = data.sale_currency.code;
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
                headerName: 'Фиксированная сумма',
                field: 'fixed_amount',
                width: 120,
                sortable: true,
                filter: true,
                cellRenderer: (params) => {
                    const data = params.data;
                    if (!data.fixed_amount || !data.fixed_currency) return '—';
                    const amount = parseFloat(data.fixed_amount).toFixed(2);
                    const currency = data.fixed_currency.code;
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
            onGridReady: (params) => {
                console.log('SaleCryptPage: AG-Grid готов');
                this.gridApi = params.api;
                this.gridApi.sizeColumnsToFit();
            },
            onFirstDataRendered: (params) => {
                console.log('SaleCryptPage: данные отрендерены');
                params.api.sizeColumnsToFit();
            }
        };
    }

    createGrid() {
        const gridDiv = document.getElementById('saleCryptGrid');
        if (!gridDiv) {
            console.error('SaleCryptPage: элемент saleCryptGrid не найден');
            return;
        }

        new agGrid.Grid(gridDiv, this.gridOptions);
        console.log('SaleCryptPage: AG-Grid создан');
    }

    setupEventListeners() {
        console.log('SaleCryptPage: setupEventListeners вызван');

        // Фильтр по статусу
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                console.log('SaleCryptPage: изменен фильтр статуса:', e.target.value);
                this.filters.status = e.target.value;
                this.applyFilters();
            });
        }

        // Фильтр по обменнику
        const exchangerFilter = document.getElementById('exchangerFilter');
        if (exchangerFilter) {
            exchangerFilter.addEventListener('change', (e) => {
                console.log('SaleCryptPage: изменен фильтр обменника:', e.target.value);
                this.filters.exchanger = e.target.value;
                this.applyFilters();
            });
        }

        // Кнопка обновления
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                console.log('SaleCryptPage: нажата кнопка обновления');
                this.loadInitialData();
            });
        }
    }

    async loadInitialData() {
        try {
            console.log('SaleCryptPage: загружаем данные...');

            const params = {
                page: 1,
                perPage: 50,
                statusFilter: this.filters.status || '',
                exchangerFilter: this.filters.exchanger || ''
            };

            const url = '/test-sale-crypts/data?' + new URLSearchParams(params);
            console.log('SaleCryptPage: URL запроса:', url);

            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            console.log('SaleCryptPage: получены данные:', result);

            this.allData = result.data || [];
            this.currentPage = result.currentPage || 1;
            this.hasMorePages = result.hasMorePages || false;

            this.updateGrid();
            this.updateStatistics();
            this.updateLoadMoreButton();

            if (this.gridApi) {
                this.gridApi.hideOverlay();
            }
        } catch (error) {
            console.error('SaleCryptPage: ошибка загрузки данных:', error);
            this.showError('Ошибка загрузки данных: ' + error.message);
        }
    }

    async applyFilters() {
        console.log('SaleCryptPage: applyFilters вызван');
        this.currentPage = 1;
        this.hasMorePages = true;
        this.allData = [];
        await this.loadInitialData();
    }

    async loadMore() {
        if (this.isLoading || !this.hasMorePages) return;

        console.log('SaleCryptPage: загружаем еще данные...');
        this.isLoading = true;
        this.showLoadMoreSpinner();

        try {
            const nextPage = this.currentPage + 1;
            const url = '/test-sale-crypts/data?' + new URLSearchParams({
                page: nextPage,
                perPage: this.perPage || 50,
                statusFilter: this.filters.status || '',
                exchangerFilter: this.filters.exchanger || ''
            });

            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            this.allData = [...this.allData, ...result.data];
            this.currentPage = result.currentPage;
            this.hasMorePages = result.hasMorePages;

            this.updateGrid();
            this.updateStatistics();
            this.updateLoadMoreButton();
        } catch (error) {
            console.error('SaleCryptPage: ошибка загрузки дополнительных данных:', error);
            this.showError('Ошибка загрузки данных: ' + error.message);
        } finally {
            this.isLoading = false;
            this.hideLoadMoreSpinner();
        }
    }

    updateGrid() {
        if (this.gridApi) {
            this.gridApi.setRowData(this.allData);
            this.gridApi.sizeColumnsToFit();
        }
    }

    updateStatistics() {
        const total = this.allData.length;
        // Статистика по статусам отключена, так как поля status нет в миграции
        const completed = 0;
        const paid = 0;
        const returned = 0;

        document.getElementById('totalSaleCrypts').textContent = total;
        document.getElementById('completedSaleCrypts').textContent = completed;
        document.getElementById('paidSaleCrypts').textContent = paid;
        document.getElementById('returnSaleCrypts').textContent = returned;
    }

    showLoadMoreSpinner() {
        const spinner = document.getElementById('loadMoreSpinner');
        if (spinner) spinner.classList.remove('hidden');
    }

    hideLoadMoreSpinner() {
        const spinner = document.getElementById('loadMoreSpinner');
        if (spinner) spinner.classList.add('hidden');
    }

    updateLoadMoreButton() {
        const button = document.getElementById('loadMoreBtn');
        if (button) {
            if (this.hasMorePages && !this.isLoading) {
                button.classList.remove('hidden');
            } else {
                button.classList.add('hidden');
            }
        }
    }

    showError(message) {
        console.error('SaleCryptPage: ошибка:', message);
    }

    static stripZeros(value) {
        if (value === null || value === undefined) return '';
        return parseFloat(value).toString();
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    console.log('SaleCryptPage: DOM загружен, инициализируем страницу');
    window.saleCryptPage = new SaleCryptPage();
});

// Проверка AG-Grid
const checkAGGrid = () => {
    if (typeof agGrid === 'undefined') {
        console.error('SaleCryptPage: AG-Grid не загружен!');
        setTimeout(checkAGGrid, 100);
    } else {
        console.log('SaleCryptPage: AG-Grid загружен');
    }
};

checkAGGrid();
