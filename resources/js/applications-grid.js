// Управление таблицей заявок через AG-Grid
import { gridUtils, customComponents } from './ag-grid-config';

console.log('ApplicationsGrid: файл загружен');

class ApplicationsGrid {
    constructor() {
        console.log('ApplicationsGrid: конструктор вызван');
        this.gridApi = null;
        this.isAdmin = window.isAdmin || false;
        this.editModal = null;
        this.currentPage = 1;
        this.hasMorePages = true;
        this.allData = [];
        this.init();
    }

    init() {
        this.setupContainer();
        this.setupColumnDefs();
        this.setupGridOptions();
        this.createGrid();
        this.setupEventListeners();
        this.setupModal();
    }

    setupContainer() {
        // Заменяем старую таблицу на контейнер AG-Grid
        const oldTable = document.getElementById('applicationsTable');
        if (!oldTable) return;

        const container = document.createElement('div');
        container.id = 'applicationsGrid';
        // Фиксированная высота и скролл
        container.style.height = '500px';
        container.style.overflow = 'auto';
        oldTable.parentNode.replaceChild(container, oldTable);
        this.container = container;
    }

    setupColumnDefs() {
        this.columnDefs = [];

        // Колонки для админа
        if (this.isAdmin) {
            this.columnDefs.push({
                headerName: 'Действие',
                field: 'actions',
                cellRenderer: 'actionButtonRenderer',
                cellRendererParams: {
                    buttons: [
                        {
                            text: 'Редактировать',
                            className: 'bg-cyan-600 hover:bg-cyan-700 text-white',
                            onClick: (data) => this.openEditModal(data)
                        }
                    ]
                },
                minWidth: 120,
                maxWidth: 120,
                pinned: 'left',
                resizable: false,
                sortable: false,
                filter: false
            }, {
                headerName: 'Кто изменил',
                field: 'user.login',
                valueGetter: params => params.data?.user?.login || '—'
            });
        }

        // Основные колонки (без ограничений ширины)
        this.columnDefs.push(
            {
                headerName: 'Номер заявки',
                field: 'app_id',
                pinned: 'left',
                cellStyle: { fontWeight: 'bold', color: '#60a5fa' }
            },
            {
                headerName: 'Дата создания',
                field: 'app_created_at',
                cellRenderer: 'dateRenderer'
            },
            {
                headerName: 'Обменник',
                field: 'exchanger'
            },
            {
                headerName: 'Статус',
                field: 'status',
                cellRenderer: 'statusRenderer'
            },
            {
                headerName: 'Приход+',
                field: 'sale_text',
                cellRenderer: (params) => {
                    if (!params.value) return '—';
                    const parts = params.value.trim().split(' ');
                    if (parts.length < 2) return params.value;
                    const amount = parts[0];
                    const currency = parts.slice(1).join(' ');
                    const container = document.createElement('div');
                    container.className = 'inline-flex items-center space-x-1';
                    container.innerHTML = `
                        <span class="text-green-400 font-bold">+${amount}</span>
                        <img src="/images/coins/${currency}.svg" alt="${currency}" class="w-4 h-4" onerror="this.style.display='none'">
                    `;
                    return container;
                },
                getQuickFilterText: params => params.value ? params.value : ''
            },
            {
                headerName: 'Продажа−',
                field: 'sell_data',
                cellRenderer: (params) => {
                    const data = params.data;
                    if (!data.sell_amount || !data.sell_currency) return '—';
                    const amount = ApplicationsGrid.stripZeros(data.sell_amount);
                    const currency = data.sell_currency.code;
                    const container = document.createElement('div');
                    container.className = 'inline-flex items-center space-x-1';
                    container.innerHTML = `
                        <span class="text-red-400 font-bold">-${amount}</span>
                        <img src="/images/coins/${currency}.svg" alt="${currency}" class="w-4 h-4" onerror="this.style.display='none'">
                    `;
                    return container;
                },
                getQuickFilterText: params => {
                    const d = params.data;
                    if (!d.sell_amount || !d.sell_currency) return '';
                    return `-${ApplicationsGrid.stripZeros(d.sell_amount)} ${d.sell_currency.code}`;
                }
            },
            {
                headerName: 'Купля+',
                field: 'buy_data',
                cellRenderer: (params) => {
                    const data = params.data;
                    if (!data.buy_amount || !data.buy_currency) return '—';
                    const amount = ApplicationsGrid.stripZeros(data.buy_amount);
                    const currency = data.buy_currency.code;
                    const isPositive = data.buy_amount >= 0;
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
                getQuickFilterText: params => {
                    const d = params.data;
                    if (!d.buy_amount || !d.buy_currency) return '';
                    const sign = d.buy_amount >= 0 ? '+' : '-';
                    return `${sign}${ApplicationsGrid.stripZeros(d.buy_amount)} ${d.buy_currency.code}`;
                }
            },
            {
                headerName: 'Расход−',
                field: 'expense_data',
                cellRenderer: (params) => {
                    const data = params.data;
                    if (!data.expense_amount || !data.expense_currency) return '—';
                    const amount = ApplicationsGrid.stripZeros(data.expense_amount);
                    const currency = data.expense_currency.code;
                    const container = document.createElement('div');
                    container.className = 'inline-flex items-center space-x-1';
                    container.innerHTML = `
                        <span class="text-red-400 font-bold">-${amount}</span>
                        <img src="/images/coins/${currency}.svg" alt="${currency}" class="w-4 h-4" onerror="this.style.display='none'">
                    `;
                    return container;
                },
                getQuickFilterText: params => {
                    const d = params.data;
                    if (!d.expense_amount || !d.expense_currency) return '';
                    return `-${ApplicationsGrid.stripZeros(d.expense_amount)} ${d.expense_currency.code}`;
                }
            },
            {
                headerName: 'Мерчант',
                field: 'merchant',
                valueGetter: params => params.data?.merchant || '—'
            },
            {
                headerName: 'ID ордера',
                field: 'order_id',
                valueGetter: params => params.data?.order_id || '—'
            }
        );
    }

    setupGridOptions() {
        this.gridOptions = {
            columnDefs: this.columnDefs,
            rowData: [],
            components: customComponents,
            domLayout: 'normal', // Важно: фиксированная высота
            defaultColDef: {
                resizable: true,
                sortable: true,
                filter: true
            },
            onFirstDataRendered: params => {
                const allColumnIds = [];
                params.columnApi.getAllColumns().forEach(col => {
                    allColumnIds.push(col.getColId());
                });
                params.columnApi.autoSizeColumns(allColumnIds, false);

                let maxWidth = 0;
                params.columnApi.getAllColumns().forEach(col => {
                    const width = col.getActualWidth();
                    if (width > maxWidth) maxWidth = width;
                });

                params.columnApi.getAllColumns().forEach(col => {
                    params.columnApi.setColumnWidth(col, maxWidth, false);
                });
            }
        };
    }

    createGrid() {
        if (!this.container) return;

        console.log('ApplicationsGrid: создаем грид...');

        if (typeof agGrid !== 'undefined') {
            this.gridApi = agGrid.createGrid(this.container, this.gridOptions);
            console.log('ApplicationsGrid: грид создан', this.gridApi);
        } else {
            console.error('ApplicationsGrid: agGrid не найден!');
        }
    }

    setupEventListeners() {
        // Поиск
        const searchInput = document.getElementById('tableSearch');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.handleSearch(e.target.value);
                }, 300);
            });
        }

        // Фильтр по статусу
        const statusFilter = document.getElementById('statusFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                this.applyStatusFilter(e.target.value);
            });
        }

        // Фильтр по обменнику
        const exchangerFilter = document.getElementById('exchangerFilter');
        if (exchangerFilter) {
            exchangerFilter.addEventListener('change', (e) => {
                this.applyExchangerFilter(e.target.value);
            });
        }

        // Кнопка обновления
        const refreshBtn = document.getElementById('refreshTable');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.refreshData();
            });
        }

        // Кнопка "Загрузить ещё"
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', () => {
                this.loadMoreData();
            });
        }
    }

    // Функция для измерения ширины текста
    getTextWidth(text, font = '14px Inter') {
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        context.font = font;
        return context.measureText(text).width;
    }

    // Функция для расчета оптимальной ширины колонок
    calculateOptimalColumnWidths() {
        if (!this.gridApi || !this.allData.length) return;

        console.log('=== НАЧАЛО РАСЧЕТА ШИРИНЫ КОЛОНОК ===');
        console.log('Количество строк данных:', this.allData.length);

        const columns = this.gridApi.getColumns();
        console.log('Найдено колонок:', columns.length);

        // Сначала попробуем авторазмер
        try {
            console.log('🔄 Пробуем авторазмер колонок...');
            this.gridApi.autoSizeColumns();
            console.log('✅ Авторазмер применен');
        } catch (error) {
            console.warn('❌ Ошибка авторазмера:', error);
        }

        // Затем принудительно устанавливаем ширину через DOM
        setTimeout(() => {
            console.log('🔄 Принудительно устанавливаем ширину через DOM...');

            columns.forEach((col, index) => {
                const field = col.getColId();
                if (field === 'actions') return;

                const headerText = col.getColDef().headerName || '';
                let maxWidth = this.getTextWidth(headerText, '600 13px Inter') + 40;

                // Находим максимальную ширину содержимого
                this.allData.forEach(row => {
                    let cellText = '';

                    if (field === 'sale_text' && row.sale_text) {
                        const parts = row.sale_text.trim().split(' ');
                        cellText = `+${parts[0]} ${parts.slice(1).join(' ')}`;
                    } else if (field === 'sell_data' && row.sell_amount) {
                        cellText = `-${ApplicationsGrid.stripZeros(row.sell_amount)} ${row.sell_currency?.code || ''}`;
                    } else if (field === 'buy_data' && row.buy_amount) {
                        const sign = row.buy_amount >= 0 ? '+' : '-';
                        cellText = `${sign}${Math.abs(ApplicationsGrid.stripZeros(row.buy_amount))} ${row.buy_currency?.code || ''}`;
                    } else if (field === 'expense_data' && row.expense_amount) {
                        cellText = `-${ApplicationsGrid.stripZeros(row.expense_amount)} ${row.expense_currency?.code || ''}`;
                    } else if (field === 'user.login') {
                        cellText = row.user?.login || row['user.login'] || '';
                    } else if (row[field]) {
                        cellText = String(row[field]);
                    }

                    const cellWidth = this.getTextWidth(cellText, '14px Inter') + 60;
                    maxWidth = Math.max(maxWidth, cellWidth);
                });

                maxWidth = Math.max(maxWidth, 100);
                maxWidth = Math.min(maxWidth, 500);
                const finalWidth = Math.round(maxWidth);

                console.log(`Колонка "${headerText}": устанавливаем ширину ${finalWidth}px`);

                // Принудительно устанавливаем ширину через API
                try {
                    this.gridApi.setColumnWidth(field, finalWidth);
                } catch (error) {
                    console.warn(`Не удалось установить ширину для ${field}:`, error);
                }
            });

            // Принудительно обновляем отображение
            if (this.gridApi.redrawRows) {
                this.gridApi.redrawRows();
            }

            console.log('✅ Принудительная установка ширины завершена');
        }, 100);
    }

    async loadInitialData() {
        // Показываем анимацию загрузки
        this.showLoading();
        try {
            const response = await fetch('/api/applications?page=1', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            this.allData = data.data || [];
            this.currentPage = 1;
            this.hasMorePages = data.has_more || false;
            this.updateGrid();
            this.updateLoadMoreButton();
        } catch (error) {
            console.error('ApplicationsGrid: ошибка загрузки данных', error);
        } finally {
            // Скрываем анимацию загрузки
            this.hideLoading();
        }
    }

    async loadMoreData() {
        if (!this.hasMorePages || this.isLoading) return;

        this.isLoading = true;
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        if (loadMoreBtn) {
            loadMoreBtn.disabled = true;
            loadMoreBtn.textContent = 'Загрузка...';
        }

        try {
            const nextPage = this.currentPage + 1;
            const response = await fetch(`/api/applications?page=${nextPage}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('ApplicationsGrid: загружено ещё данных', data);

            this.allData = [...this.allData, ...(data.data || [])];
            this.currentPage = nextPage;
            this.hasMorePages = data.has_more || false;

            this.updateGrid();
            this.updateLoadMoreButton();

        } catch (error) {
            console.error('ApplicationsGrid: ошибка загрузки дополнительных данных', error);
        } finally {
            this.isLoading = false;
            if (loadMoreBtn) {
                loadMoreBtn.disabled = false;
                loadMoreBtn.textContent = this.hasMorePages ? 'Загрузить ещё' : 'Больше нет';
            }
        }
    }

    updateGrid() {
        if (this.gridApi) {
            this.gridApi.setGridOption('rowData', this.allData);
        }
    }

    updateLoadMoreButton() {
        const loadMoreBtn = document.getElementById('loadMoreBtn');
        if (loadMoreBtn) {
            loadMoreBtn.style.display = this.hasMorePages ? 'block' : 'none';
            loadMoreBtn.textContent = this.hasMorePages ? 'Загрузить ещё' : 'Больше нет';
        }
    }

    applyStatusFilter(status) {
        if (!this.gridApi) return;

        if (status) {
            this.gridApi.setFilterModel({
                status: {
                    type: 'equals',
                    filter: status
                }
            });
        } else {
            this.gridApi.setFilterModel(null);
        }
    }

    applyExchangerFilter(exchanger) {
        if (!this.gridApi) return;

        if (exchanger) {
            this.gridApi.setFilterModel({
                exchanger: {
                    type: 'equals',
                    filter: exchanger
                }
            });
        } else {
            this.gridApi.setFilterModel(null);
        }
    }

    handleSearch(query) {
        if (!this.gridApi) return;

        if (query.trim()) {
            this.gridApi.setQuickFilter(query);
        } else {
            this.gridApi.setQuickFilter(null);
        }
    }

    async refreshData() {
        console.log('ApplicationsGrid: обновляем данные...');

        this.currentPage = 1;
        this.hasMorePages = true;
        this.allData = [];

        await this.loadInitialData();
    }

    setupModal() {
        this.editModal = document.getElementById('editModalBackdrop');
        const backdropClose = document.getElementById('editModalBackdropClose');
        const closeBtn = document.querySelector('#editModalBackdrop .close-btn');

        if (backdropClose) {
            backdropClose.addEventListener('click', () => this.closeEditModal());
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.closeEditModal());
        }

        const form = document.getElementById('editForm');
        if (form) {
            form.addEventListener('submit', (e) => this.handleEditSubmit(e));
        }
    }

    openEditModal(data) {
        console.log('ApplicationsGrid: открываем модальное окно для', data);

        if (!this.editModal) return;

        // Заполняем форму данными
        document.getElementById('edit_app_id').value = data.id;
        document.getElementById('modalAppId').textContent = data.app_id;
        document.getElementById('edit_sell_amount').value = data.sell_amount || '';
        document.getElementById('edit_sell_currency').value = data.sell_currency?.code || '';
        document.getElementById('edit_buy_amount').value = data.buy_amount || '';
        document.getElementById('edit_buy_currency').value = data.buy_currency?.code || '';
        document.getElementById('edit_expense_amount').value = data.expense_amount || '';
        document.getElementById('edit_expense_currency').value = data.expense_currency?.code || '';
        document.getElementById('edit_merchant').value = data.merchant || '';
        document.getElementById('edit_order_id').value = data.order_id || '';

        // Показываем модальное окно
        this.editModal.classList.remove('hidden');
    }

    closeEditModal() {
        if (this.editModal) {
            this.editModal.classList.add('hidden');
        }
    }

    async handleEditSubmit(e) {
        e.preventDefault();
        console.log('ApplicationsGrid: отправляем форму редактирования...');

        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch(`/api/applications/${data.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            console.log('ApplicationsGrid: данные обновлены', result);

            // Обновляем данные в гриде
            await this.refreshData();

            // Закрываем модальное окно
            this.closeEditModal();

            // Показываем уведомление об успехе
            alert('Заявка успешно обновлена!');

        } catch (error) {
            console.error('ApplicationsGrid: ошибка обновления данных', error);
            alert('Ошибка при обновлении заявки: ' + error.message);
        }
    }

    static stripZeros(value) {
        return String(value).replace(/\.?0+$/, '').replace(/^-/, '');
    }

    showLoading() {
        if (!this.container) return;
        let loader = document.getElementById('agGridLoader');
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'agGridLoader';
            loader.style.position = 'absolute';
            loader.style.top = '0';
            loader.style.left = '0';
            loader.style.width = '100%';
            loader.style.height = '100%';
            loader.style.background = 'rgba(255,255,255,0.7)';
            loader.style.display = 'flex';
            loader.style.alignItems = 'center';
            loader.style.justifyContent = 'center';
            loader.style.zIndex = '10';
            loader.innerHTML = `<div class="ag-loading-spinner" style="width:48px;height:48px;border:6px solid #ccc;border-top:6px solid #3b82f6;border-radius:50%;animation:spin 1s linear infinite;"></div>`;
            // Добавляем анимацию через style
            const style = document.createElement('style');
            style.innerHTML = `@keyframes spin {0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}`;
            document.head.appendChild(style);
            this.container.style.position = 'relative';
            this.container.appendChild(loader);
        } else {
            loader.style.display = 'flex';
        }
    }

    hideLoading() {
        const loader = document.getElementById('agGridLoader');
        if (loader) loader.style.display = 'none';
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    console.log('ApplicationsGrid: DOM загружен, инициализируем...');

    const applicationsGrid = new ApplicationsGrid();

    // Загружаем данные после создания грида
    setTimeout(() => {
        applicationsGrid.loadInitialData();
    }, 100);
});

export default ApplicationsGrid;
