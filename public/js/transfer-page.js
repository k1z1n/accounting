(function() {
class TransferPage {
    constructor() {
        console.log('TransferPage: конструктор вызван');
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
        console.log('TransferPage: инициализация');
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

    actionRenderer(params) {
        return `
            <div class="flex gap-2 justify-center">
                <button class="edit-transfer-btn" title="Редактировать" data-id="${params.data.id}">
                    <svg class="w-5 h-5 text-cyan-400 hover:text-cyan-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a4 4 0 01-2.828 1.172H7v-2a4 4 0 011.172-2.828z"/></svg>
                </button>
                <button class="delete-transfer-btn" title="Удалить" data-id="${params.data.id}">
                    <svg class="w-5 h-5 text-red-400 hover:text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        `;
    }

    setupColumnDefs() {
        this.columnDefs = [
            {
                headerName: 'ДЕЙСТВИЕ',
                field: 'actions',
                width: 100,
                cellRenderer: this.actionRenderer,
                pinned: 'left',
                suppressMenu: true,
                sortable: false,
                filter: false
            },
            {
                headerName: 'ОТКУДА',
                field: 'exchanger_from.title',
                width: 150,
                sortable: true,
                filter: true
            },
            {
                headerName: 'КУДА',
                field: 'exchanger_to.title',
                width: 150,
                sortable: true,
                filter: true
            },
            {
                headerName: 'СУММА',
                field: 'amount',
                width: 120,
                sortable: true,
                filter: true,
                cellRenderer: (params) => {
                    const data = params.data;
                    if (!data.amount || !data.amount_currency) return '—';
                    const amount = parseFloat(data.amount).toFixed(2);
                    const currency = data.amount_currency.code;
                    return `<span>${amount} <img src="/images/coins/${currency}.svg" alt="${currency}" class="w-4 h-4 inline-block align-middle ml-1" onerror="this.style.display='none'"> <span class='font-mono text-cyan-300'>${currency}</span></span>`;
                }
            },
            {
                headerName: 'КОМИССИЯ',
                field: 'commission',
                width: 120,
                sortable: true,
                filter: true,
                cellRenderer: (params) => {
                    const data = params.data;
                    if (!data.commission || !data.commission_currency) return '—';
                    const amount = parseFloat(data.commission).toFixed(2);
                    const currency = data.commission_currency.code;
                    return `<span>${amount} <img src="/images/coins/${currency}.svg" alt="${currency}" class="w-4 h-4 inline-block align-middle ml-1" onerror="this.style.display='none'"> <span class='font-mono text-pink-300'>${currency}</span></span>`;
                }
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
            onGridReady: (params) => {
                console.log('TransferPage: AG-Grid готов');
                this.gridApi = params.api;
                this.gridApi.sizeColumnsToFit();
            },
            onFirstDataRendered: (params) => {
                console.log('TransferPage: данные отрендерены');
                params.api.sizeColumnsToFit();
            },
            onCellClicked: (params) => {
                if (params.colDef.field === 'actions') {
                    const target = params.event.target.closest('button');
                    if (target && target.classList.contains('edit-transfer-btn')) {
                        alert('Редактировать перевод ID: ' + params.data.id);
                    } else if (target && target.classList.contains('delete-transfer-btn')) {
                        if (confirm('Удалить перевод ID: ' + params.data.id + '?')) {
                            alert('Удаление перевода ID: ' + params.data.id);
                        }
                    }
                }
            }
        };
    }

    createGrid() {
        const gridDiv = document.getElementById('transferGrid');
        if (!gridDiv) {
            console.error('TransferPage: элемент transferGrid не найден');
            return;
        }

        new agGrid.Grid(gridDiv, this.gridOptions);
        console.log('TransferPage: AG-Grid создан');
    }

    setupEventListeners() {
        console.log('TransferPage: setupEventListeners вызван');

        // Фильтр по статусу
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                console.log('TransferPage: изменен фильтр статуса:', e.target.value);
                this.filters.status = e.target.value;
                this.applyFilters();
            });
        }

        // Фильтр по обменнику
        const exchangerFilter = document.getElementById('exchangerFilter');
        if (exchangerFilter) {
            exchangerFilter.addEventListener('change', (e) => {
                console.log('TransferPage: изменен фильтр обменника:', e.target.value);
                this.filters.exchanger = e.target.value;
                this.applyFilters();
            });
        }

        // Кнопка обновления
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                console.log('TransferPage: нажата кнопка обновления');
                this.loadInitialData();
            });
        }
    }

    async loadInitialData() {
        try {
            console.log('TransferPage: загружаем данные...');

            const params = {
                page: 1,
                perPage: 50,
                statusFilter: this.filters.status || '',
                exchangerFilter: this.filters.exchanger || ''
            };

            const url = '/test-transfers/data?' + new URLSearchParams(params);
            console.log('TransferPage: URL запроса:', url);

            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            console.log('TransferPage: получены данные:', result);

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
            console.error('TransferPage: ошибка загрузки данных:', error);
            this.showError('Ошибка загрузки данных: ' + error.message);
        }
    }

    async applyFilters() {
        console.log('TransferPage: applyFilters вызван');
        this.currentPage = 1;
        this.hasMorePages = true;
        this.allData = [];
        await this.loadInitialData();
    }

    async loadMore() {
        if (this.isLoading || !this.hasMorePages) return;

        console.log('TransferPage: загружаем еще данные...');
        this.isLoading = true;
        this.showLoadMoreSpinner();

        try {
            const nextPage = this.currentPage + 1;
            const url = '/test-transfers/data?' + new URLSearchParams({
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
            console.error('TransferPage: ошибка загрузки дополнительных данных:', error);
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
        const elTotal = document.getElementById('totalTransfers');
        if (elTotal) elTotal.textContent = total;
        const elCompleted = document.getElementById('completedTransfers');
        if (elCompleted) elCompleted.textContent = completed;
        const elPaid = document.getElementById('paidTransfers');
        if (elPaid) elPaid.textContent = paid;
        const elReturned = document.getElementById('returnTransfers');
        if (elReturned) elReturned.textContent = returned;
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
        console.error('TransferPage: ошибка:', message);
    }

    static stripZeros(value) {
        if (value === null || value === undefined) return '';
        return parseFloat(value).toString();
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    console.log('TransferPage: DOM загружен, инициализируем страницу');
    window.transferPage = new TransferPage();
});
// Проверка AG-Grid
const checkAGGrid = () => {
    if (typeof agGrid === 'undefined') {
        console.error('TransferPage: AG-Grid не загружен!');
        setTimeout(checkAGGrid, 100);
    } else {
        console.log('TransferPage: AG-Grid загружен');
    }
};
checkAGGrid();
})();
