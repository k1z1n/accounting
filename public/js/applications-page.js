// Управление таблицей заявок через AG-Grid на отдельной странице
console.log('ApplicationsPage: файл загружен');

class ApplicationsPage {
    constructor() {
        console.log('ApplicationsPage: конструктор вызван');
        this.gridApi = null;
        this.isAdmin = window.isAdmin || false;
        this.editModal = null;
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
        this.setupColumnDefs();
        this.setupGridOptions();
        this.createGrid();
        this.setupEventListeners();
        this.setupModal();
        this.loadInitialData();
        this.updateStatistics();
    }

    // Компоненты для AG-Grid
    dateRenderer(params) {
        if (!params.value) return '—';
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
        const status = params.value;
        const statusConfig = {
            'выполненная заявка': { color: 'text-green-400', bg: 'bg-green-900/20', text: 'Выполнена' },
            'оплаченная заявка': { color: 'text-blue-400', bg: 'bg-blue-900/20', text: 'Оплачена' },
            'возврат': { color: 'text-red-400', bg: 'bg-red-900/20', text: 'Возврат' }
        };

        const config = statusConfig[status] || { color: 'text-gray-400', bg: 'bg-gray-900/20', text: status };

        const container = document.createElement('div');
        container.className = `inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${config.bg} ${config.color}`;
        container.textContent = config.text;
        return container;
    }

    actionButtonRenderer(params) {
        const container = document.createElement('div');
        container.className = 'flex space-x-1';

        params.buttons.forEach(button => {
            const btn = document.createElement('button');
            btn.className = `px-2 py-1 text-xs rounded ${button.className}`;
            btn.textContent = button.text;
            btn.addEventListener('click', () => button.onClick(params.data));
            container.appendChild(btn);
        });

        return container;
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
                pinned: 'left',
                resizable: true,
                sortable: false,
                filter: false
            }, {
                headerName: 'Кто изменил',
                field: 'user.login',
                valueGetter: params => params.data?.user?.login || '—'
            });
        }

        // Основные колонки
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
                    const amount = ApplicationsPage.stripZeros(data.sell_amount);
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
                    return `-${ApplicationsPage.stripZeros(d.sell_amount)} ${d.sell_currency.code}`;
                }
            },
            {
                headerName: 'Купля+',
                field: 'buy_data',
                cellRenderer: (params) => {
                    const data = params.data;
                    if (!data.buy_amount || !data.buy_currency) return '—';
                    const amount = ApplicationsPage.stripZeros(data.buy_amount);
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
                    return `${sign}${ApplicationsPage.stripZeros(d.buy_amount)} ${d.buy_currency.code}`;
                }
            },
            {
                headerName: 'Расход−',
                field: 'expense_data',
                cellRenderer: (params) => {
                    const data = params.data;
                    if (!data.expense_amount || !data.expense_currency) return '—';
                    const amount = ApplicationsPage.stripZeros(data.expense_amount);
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
                    return `-${ApplicationsPage.stripZeros(d.expense_amount)} ${d.expense_currency.code}`;
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
            components: {
                dateRenderer: this.dateRenderer.bind(this),
                statusRenderer: this.statusRenderer.bind(this),
                actionButtonRenderer: this.actionButtonRenderer.bind(this)
            },
            defaultColDef: {
                sortable: true,
                filter: true,
                resizable: true,
                autoHeight: true,
                wrapText: true
            },
            pagination: true,
            paginationPageSize: 50,
            rowSelection: 'single',
            animateRows: true,
            suppressRowClickSelection: true,
            // Показываем загрузку пока нет данных
            overlayLoadingTemplate: `
                <div class="flex items-center justify-center h-full">
                    <div class="inline-flex items-center space-x-2">
                        <div class="animate-spin rounded-full h-8 w-8 border-2 border-gray-300 border-t-cyan-500"></div>
                        <span class="text-gray-400">Загрузка данных...</span>
                    </div>
                </div>
            `,
            overlayNoRowsTemplate: `
                <div class="flex items-center justify-center h-full">
                    <span class="text-gray-400">Нет данных для отображения</span>
                </div>
            `,
            onFirstDataRendered: (params) => {
                // Ждем немного перед подгонкой размеров
                setTimeout(() => {
                    try {
                        console.log('ApplicationsPage: подгоняем размеры колонок под контент...');
                        // Подгоняем размеры колонок под контент
                        params.columnApi.autoSizeAllColumns();
                        console.log('ApplicationsPage: размеры колонок подогнаны под контент');
                    } catch (error) {
                        console.log('Не удалось подогнать размеры колонок:', error);
                    }
                }, 300);
            },
            onGridReady: (params) => {
                this.gridApi = params.api;
                this.columnApi = params.columnApi;
                console.log('ApplicationsPage: грид готов');

                // Показываем загрузку сразу после создания грида
                this.gridApi.showLoadingOverlay();
            }
        };
    }

    createGrid() {
        const gridDiv = document.getElementById('applicationsGrid');
        if (!gridDiv) {
            console.error('ApplicationsPage: не найден элемент applicationsGrid');
            return;
        }

        // Проверяем, что AG-Grid загружен
        if (typeof agGrid === 'undefined') {
            console.error('ApplicationsPage: AG-Grid не загружен');
            return;
        }

        // Проверяем, что Grid конструктор доступен
        if (typeof agGrid.Grid === 'undefined') {
            console.error('ApplicationsPage: agGrid.Grid не найден');
            console.log('Доступные свойства agGrid:', Object.keys(agGrid));
            return;
        }

        // Проверяем размеры контейнера
        const rect = gridDiv.getBoundingClientRect();
        console.log('ApplicationsPage: размеры контейнера:', rect.width, 'x', rect.height);

        try {
                        console.log('ApplicationsPage: создаем AG-Grid...');
            new agGrid.Grid(gridDiv, this.gridOptions);
            console.log('ApplicationsPage: AG-Grid создан успешно');
        } catch (error) {
            console.error('ApplicationsPage: ошибка создания грида:', error);
        }
    }

    setupEventListeners() {
        console.log('ApplicationsPage: setupEventListeners вызван');

        // Фильтр по статусу
        const statusFilter = document.getElementById('statusFilter');
        console.log('ApplicationsPage: элемент statusFilter найден:', !!statusFilter);
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                console.log('ApplicationsPage: изменен фильтр статуса:', e.target.value);
                this.filters.status = e.target.value;
                console.log('ApplicationsPage: фильтры после изменения статуса:', this.filters);
                this.applyFilters();
            });
        } else {
            console.error('ApplicationsPage: элемент statusFilter НЕ найден!');
        }

        // Фильтр по обменнику
        const exchangerFilter = document.getElementById('exchangerFilter');
        console.log('ApplicationsPage: элемент exchangerFilter найден:', !!exchangerFilter);
        if (exchangerFilter) {
            exchangerFilter.addEventListener('change', (e) => {
                console.log('ApplicationsPage: изменен фильтр обменника:', e.target.value);
                this.filters.exchanger = e.target.value;
                console.log('ApplicationsPage: фильтры после изменения обменника:', this.filters);
                this.applyFilters();
            });
        } else {
            console.error('ApplicationsPage: элемент exchangerFilter НЕ найден!');
        }

        // Кнопка обновления
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                console.log('ApplicationsPage: нажата кнопка обновления');
                // Просто перезагружаем текущие данные без сброса страницы
                this.loadInitialData();
            });
        }

        // Кнопка синхронизации
        const syncBtn = document.getElementById('syncBtn');
        if (syncBtn) {
            console.log('ApplicationsPage: кнопка синхронизации найдена');
            syncBtn.addEventListener('click', () => {
                console.log('ApplicationsPage: кнопка синхронизации нажата');
                this.syncData();
            });
        } else {
            console.error('ApplicationsPage: кнопка синхронизации не найдена');
        }
    }

            async loadInitialData() {
        // Не показываем загрузку, так как AG-Grid сам управляет отображением
        try {
            console.log('ApplicationsPage: загружаем данные...');
            console.log('ApplicationsPage: фильтры перед запросом:', this.filters);

            const params = {
                page: 1,
                perPage: 50,
                statusFilter: this.filters.status || '',
                exchangerFilter: this.filters.exchanger || ''
            };
            console.log('ApplicationsPage: параметры запроса:', params);

            const url = '/applications/data?' + new URLSearchParams(params);
            console.log('ApplicationsPage: URL запроса:', url);

            const response = await fetch(url);
            console.log('ApplicationsPage: статус ответа:', response.status);
            console.log('ApplicationsPage: заголовки ответа:', Object.fromEntries(response.headers.entries()));

            if (!response.ok) {
                const errorText = await response.text();
                console.error('ApplicationsPage: ошибка HTTP:', response.status, errorText);
                throw new Error(`HTTP ${response.status}: ${errorText}`);
            }

            const result = await response.json();
            console.log('ApplicationsPage: получены данные:', result);

            this.allData = result.data || [];
            this.currentPage = result.currentPage || 1;
            this.hasMorePages = result.hasMorePages || false;

            console.log('ApplicationsPage: обработано записей:', this.allData.length);
            console.log('ApplicationsPage: текущая страница:', this.currentPage);
            console.log('ApplicationsPage: есть еще страницы:', this.hasMorePages);

                                    this.updateGrid();
            this.updateStatistics();
            this.updateLoadMoreButton();

            // Скрываем загрузку в AG-Grid
            if (this.gridApi) {
                this.gridApi.hideOverlay();
            }

            console.log('ApplicationsPage: данные успешно загружены и отображены');

            // Синхронизация уже выполнена в первой загрузке
        } catch (error) {
            console.error('ApplicationsPage: ошибка загрузки данных:', error);
            // Показываем ошибку пользователю
            this.showError('Ошибка загрузки данных: ' + error.message);
        }
    }

    async applyFilters() {
        console.log('ApplicationsPage: applyFilters вызван');
        console.log('ApplicationsPage: текущие фильтры:', this.filters);
        console.log('ApplicationsPage: сбрасываем страницу на 1');

        this.currentPage = 1;
        this.hasMorePages = true;
        this.allData = [];

        console.log('ApplicationsPage: вызываем loadInitialData с фильтрами');
        await this.loadInitialData();

        console.log('ApplicationsPage: applyFilters завершен');
    }

    async refreshData() {
        console.log('ApplicationsPage: обновляем данные (refreshData)');
        // Сохраняем текущую страницу
        const currentPage = this.currentPage;
        this.currentPage = 1;
        this.allData = [];
        await this.loadInitialData();
        // Восстанавливаем страницу если нужно
        if (currentPage > 1) {
            console.log('ApplicationsPage: восстанавливаем страницу', currentPage);
            this.currentPage = currentPage;
        }
    }

        async refreshCurrentPage() {
        console.log('ApplicationsPage: обновляем только текущую страницу...');

        // Инициализируем фильтры если их нет
        if (!this.filters) {
            this.filters = {
                status: '',
                exchanger: ''
            };
        }

        // Сохраняем текущую страницу
        const currentPage = this.currentPage;

        // Очищаем данные только текущей страницы
        const startIndex = (currentPage - 1) * this.perPage;
        const endIndex = startIndex + this.perPage;

        // Удаляем данные текущей страницы из allData
        this.allData.splice(startIndex, endIndex - startIndex);

        // Загружаем данные только для текущей страницы
        await this.loadPageData(currentPage);

        console.log('ApplicationsPage: текущая страница обновлена');
    }

        async loadPageData(page) {
        console.log('ApplicationsPage: загружаем данные страницы', page);

        // Инициализируем фильтры если их нет
        if (!this.filters) {
            this.filters = {
                status: '',
                exchanger: ''
            };
        }

        // Инициализируем perPage если его нет
        if (!this.perPage) {
            this.perPage = 50;
        }

        const params = new URLSearchParams({
            page: page,
            perPage: this.perPage || 50,
            statusFilter: this.filters.status || '',
            exchangerFilter: this.filters.exchanger || ''
        });

        console.log('ApplicationsPage: URL запроса:', `/applications/data?${params}`);
        console.log('ApplicationsPage: фильтры:', this.filters);

        try {
            const response = await fetch(`/applications/data?${params}`);
            console.log('ApplicationsPage: статус ответа:', response.status);
            console.log('ApplicationsPage: заголовки ответа:', Object.fromEntries(response.headers.entries()));

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            console.log('ApplicationsPage: получены данные:', result);

            // Добавляем данные в allData на правильную позицию
            const startIndex = (page - 1) * this.perPage;
            result.data.forEach((item, index) => {
                this.allData[startIndex + index] = item;
            });

            console.log('ApplicationsPage: обработано записей:', result.data.length);
            console.log('ApplicationsPage: текущая страница:', result.currentPage);
            console.log('ApplicationsPage: есть еще страницы:', result.currentPage < result.lastPage);

            // Обновляем состояние
            this.hasMorePages = result.currentPage < result.lastPage;
            this.totalRecords = result.total;

            // Обновляем грид
            this.updateGrid();

            // Обновляем статистику
            this.updateStatistics();

            console.log('ApplicationsPage: данные успешно загружены и отображены');

        } catch (error) {
            console.error('ApplicationsPage: ошибка загрузки данных:', error);
            this.showError('Ошибка загрузки данных: ' + error.message);
        }
    }

    async syncData() {
        try {
            console.log('ApplicationsPage: начинаем синхронизацию...');

            // Показываем индикатор загрузки на кнопке
            const syncBtn = document.getElementById('syncBtn');
            if (syncBtn) {
                syncBtn.disabled = true;
                syncBtn.innerHTML = `
                    <div class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></div>
                    <span class="ml-2">Синхронизация...</span>
                `;
            }

            // Запускаем синхронизацию
            const response = await fetch('/applications/data?page=1&perPage=50&sync=true');

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            console.log('ApplicationsPage: результат синхронизации:', result);

            // Проверяем, что получили данные
            if (result.data && Array.isArray(result.data)) {
                console.log('ApplicationsPage: синхронизация завершена успешно');

                // При синхронизации получаем только первую страницу, но знаем общее количество
                this.allData = result.data;
                this.currentPage = result.currentPage || 1;
                this.hasMorePages = result.hasMorePages || false;
                this.totalRecords = result.total || result.data.length;

                console.log('ApplicationsPage: после синхронизации - текущая страница:', this.currentPage, 'записей на странице:', result.data.length, 'всего записей в БД:', this.totalRecords, 'есть еще страницы:', this.hasMorePages);

                this.updateGrid();
                this.updateStatistics();
                this.updateLoadMoreButton();

                // Если есть еще страницы, показываем уведомление
                if (this.hasMorePages) {
                    if (window.notificationManager) {
                        window.notificationManager.info(`Синхронизация завершена. Загружено ${result.data.length} из ${this.totalRecords} записей. Нажмите "Показать еще" для загрузки остальных.`);
                    }
                } else {
                    if (window.notificationManager) {
                        window.notificationManager.success('Синхронизация завершена успешно');
                    }
                }
            } else {
                throw new Error('Неверный формат ответа от сервера');
            }
        } catch (error) {
            console.error('ApplicationsPage: ошибка синхронизации:', error);
            if (window.notificationManager) {
                window.notificationManager.error('Ошибка синхронизации: ' + error.message);
            }
        } finally {
            // Восстанавливаем кнопку
            const syncBtn = document.getElementById('syncBtn');
            if (syncBtn) {
                syncBtn.disabled = false;
                syncBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                `;
            }
        }
    }

    async loadMore() {
        console.log('ApplicationsPage: loadMore вызван - isLoading:', this.isLoading, 'hasMorePages:', this.hasMorePages, 'currentPage:', this.currentPage, 'allData.length:', this.allData.length);

        if (this.isLoading || !this.hasMorePages) {
            console.log('ApplicationsPage: loadMore заблокирован - isLoading:', this.isLoading, 'hasMorePages:', this.hasMorePages);
            return;
        }

        this.isLoading = true;
        this.showLoadMoreSpinner();

                try {
            console.log('ApplicationsPage: загружаем еще данные с синхронизацией...');
            console.log('ApplicationsPage: текущее состояние - страница:', this.currentPage, 'всего записей:', this.allData.length, 'есть еще:', this.hasMorePages);

            const nextPage = this.currentPage + 1;
            const url = '/applications/data?' + new URLSearchParams({
                page: nextPage,
                perPage: this.perPage || 50,
                sync: 'true', // Синхронизируем при загрузке "еще" для получения новых данных с сайтов
                statusFilter: this.filters.status || '',
                exchangerFilter: this.filters.exchanger || ''
            });

            console.log('ApplicationsPage: URL запроса для "еще" с синхронизацией:', url);

            const response = await fetch(url);
            console.log('ApplicationsPage: статус ответа для "еще":', response.status);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('ApplicationsPage: ошибка HTTP для "еще":', response.status, errorText);
                throw new Error(`HTTP ${response.status}: ${errorText}`);
            }

            const result = await response.json();
            console.log('ApplicationsPage: получены дополнительные данные с синхронизацией:', result);

            const newData = result.data || [];
            this.currentPage = result.currentPage || nextPage;
            this.hasMorePages = result.hasMorePages || false;
            this.totalRecords = result.total || (this.allData.length + newData.length);

            console.log('ApplicationsPage: добавлено записей:', newData.length);
            console.log('ApplicationsPage: новое состояние - страница:', this.currentPage, 'всего записей:', this.allData.length + newData.length, 'есть еще:', this.hasMorePages);
            console.log('ApplicationsPage: детали ответа - currentPage:', result.currentPage, 'hasMorePages:', result.hasMorePages, 'total:', result.total, 'lastPage:', result.lastPage);

            // Добавляем новые данные к существующим
            this.allData = [...this.allData, ...newData];

            // Обновляем грид
            this.updateGrid();
            this.updateStatistics();

            console.log('ApplicationsPage: дополнительные данные с синхронизацией успешно загружены');
        } catch (error) {
            console.error('ApplicationsPage: ошибка загрузки дополнительных данных:', error);
            // Показываем уведомление об ошибке
            if (window.notificationManager) {
                window.notificationManager.error('Ошибка загрузки дополнительных данных');
            }
        } finally {
            this.isLoading = false;
            this.hideLoadMoreSpinner();
            this.updateLoadMoreButton();
        }
    }

    updateGrid() {
        if (this.gridApi) {
            this.gridApi.setRowData(this.allData);
            // Принудительно обновляем размеры после установки данных
            setTimeout(() => {
                try {
                    console.log('ApplicationsPage: обновляем размеры колонок после загрузки данных...');
                    this.columnApi.autoSizeAllColumns();
                    console.log('ApplicationsPage: размеры колонок обновлены');
                } catch (error) {
                    console.log('Не удалось обновить размеры после установки данных:', error);
                }
            }, 200);
        }
    }

    updateStatistics() {
        // Показываем общее количество из БД, если оно известно, иначе количество загруженных
        const totalInDB = this.totalRecords || this.allData.length;
        const loadedCount = this.allData.length;

        const stats = {
            total: totalInDB,
            completed: this.allData.filter(item => item.status === 'выполненная заявка').length,
            paid: this.allData.filter(item => item.status === 'оплаченная заявка').length,
            returned: this.allData.filter(item => item.status === 'возврат').length
        };

        // Показываем общее количество с индикатором загруженных
        const totalText = loadedCount < totalInDB ? `${loadedCount}/${totalInDB}` : totalInDB.toString();

        document.getElementById('totalApplications').textContent = totalText;
        document.getElementById('completedApplications').textContent = stats.completed;
        document.getElementById('paidApplications').textContent = stats.paid;
        document.getElementById('returnApplications').textContent = stats.returned;

        console.log('ApplicationsPage: статистика обновлена - загружено:', loadedCount, 'всего в БД:', totalInDB);
    }



    setupModal() {
        this.editModal = document.getElementById('editModalBackdrop');
        const closeBtn = document.getElementById('closeEditModalBtn');
        const backdropClose = document.getElementById('editModalBackdropClose');
        const editForm = document.getElementById('editForm');

        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.closeEditModal());
        }

        if (backdropClose) {
            backdropClose.addEventListener('click', () => this.closeEditModal());
        }

        if (editForm) {
            editForm.addEventListener('submit', (e) => this.handleEditSubmit(e));
        }
    }

    openEditModal(data) {
        if (!this.editModal) return;

        console.log('ApplicationsPage: открываем модальное окно редактирования', data);

        // Загружаем данные заявки
        fetch(`/applications/${data.id}/edit`)
            .then(res => {
                console.log('ApplicationsPage: ответ сервера для редактирования:', res.status, res.statusText);
                return res.json();
            })
            .then(applicationData => {
                console.log('ApplicationsPage: получены данные для редактирования:', applicationData);
                document.getElementById('edit_app_id').value = applicationData.id;
                document.getElementById('modalAppId').textContent = applicationData.app_id;
                document.getElementById('edit_sell_amount').value = applicationData.sell_amount || '';
                document.getElementById('edit_sell_currency').value = applicationData.sell_currency || '';
                document.getElementById('edit_buy_amount').value = applicationData.buy_amount || '';
                document.getElementById('edit_buy_currency').value = applicationData.buy_currency || '';
                document.getElementById('edit_expense_amount').value = applicationData.expense_amount || '';
                document.getElementById('edit_expense_currency').value = applicationData.expense_currency || '';
                document.getElementById('edit_merchant').value = applicationData.merchant || '';
                document.getElementById('edit_order_id').value = applicationData.order_id || '';

                this.editModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            })
            .catch(err => console.error('Ошибка загрузки заявки:', err));
    }

    closeEditModal() {
        if (!this.editModal) return;

        this.editModal.classList.add('hidden');
        document.body.style.overflow = '';

        // Очищаем ошибки
        ['sell_amount', 'sell_currency', 'buy_amount', 'buy_currency', 'expense_amount', 'expense_currency', 'merchant', 'order_id']
            .forEach(f => {
                const errEl = document.getElementById('err_' + f);
                if (errEl) errEl.textContent = '';
            });
    }

    async handleEditSubmit(e) {
        e.preventDefault();

        const formData = new FormData(e.target);
        const id = formData.get('id');

        // Собираем данные в объект
        const data = {
            sell_amount: formData.get('sell_amount') || null,
            sell_currency: formData.get('sell_currency') || null,
            buy_amount: formData.get('buy_amount') || null,
            buy_currency: formData.get('buy_currency') || null,
            expense_amount: formData.get('expense_amount') || null,
            expense_currency: formData.get('expense_currency') || null,
            merchant: formData.get('merchant') || null,
            order_id: formData.get('order_id') || null,
        };

                try {
            console.log('ApplicationsPage: отправляем запрос на обновление заявки', { id, data });

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (!csrfMeta) {
                throw new Error('CSRF токен не найден в meta теге');
            }

            const csrfToken = csrfMeta.getAttribute('content');
            if (!csrfToken) {
                throw new Error('CSRF токен пустой');
            }

            console.log('ApplicationsPage: CSRF токен:', csrfToken);
            console.log('ApplicationsPage: длина CSRF токена:', csrfToken.length);

            const url = `/applications/${id}`;
            console.log('ApplicationsPage: URL запроса:', url);

            // Добавляем _method для совместимости с Laravel
            const formData = new FormData();
            formData.append('_method', 'PUT');
            formData.append('_token', csrfToken);

            // Добавляем данные
            Object.keys(data).forEach(key => {
                if (data[key] !== null && data[key] !== undefined) {
                    formData.append(key, data[key]);
                }
            });

            console.log('ApplicationsPage: отправляем FormData:', Object.fromEntries(formData));

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                },
                body: formData
            });

            console.log('ApplicationsPage: ответ сервера:', response.status, response.statusText);

            if (!response.ok) {
                const errorText = await response.text();
                console.error('ApplicationsPage: ошибка HTTP:', response.status, errorText);
                throw new Error(`HTTP ${response.status}: ${errorText}`);
            }

            const result = await response.json();

            if (result.success) {
                this.closeEditModal();

                // Обновляем данные в таблице без перезагрузки
                await this.updateApplicationInGrid(id, data);

                // Показываем уведомление об успехе
                if (window.notificationManager) {
                    window.notificationManager.success('Заявка обновлена успешно');
                }
            } else {
                // Показываем ошибки валидации
                Object.keys(result.errors || {}).forEach(field => {
                    const errEl = document.getElementById('err_' + field);
                    if (errEl) errEl.textContent = result.errors[field][0];
                });
            }
        } catch (error) {
            console.error('Ошибка сохранения:', error);
            if (window.notificationManager) {
                window.notificationManager.error('Ошибка при сохранении заявки');
            }
        }
    }

    async updateApplicationInGrid(id, updatedData) {
        try {
            // Получаем обновленные данные с сервера
            const response = await fetch(`/applications/${id}/edit`);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const applicationData = await response.json();

            // Находим строку в таблице и обновляем её
            if (this.gridApi) {
                const rowNode = this.gridApi.getRowNode(String(applicationData.app_id));
                if (rowNode) {
                    // Обновляем данные в строке
                    const updatedRowData = {
                        ...rowNode.data,
                        sell_amount: applicationData.sell_amount,
                        sell_currency: applicationData.sell_currency ? { code: applicationData.sell_currency } : null,
                        buy_amount: applicationData.buy_amount,
                        buy_currency: applicationData.buy_currency ? { code: applicationData.buy_currency } : null,
                        expense_amount: applicationData.expense_amount,
                        expense_currency: applicationData.expense_currency ? { code: applicationData.expense_currency } : null,
                        merchant: applicationData.merchant,
                        order_id: applicationData.order_id,
                    };

                    // Обновляем строку в AG-Grid
                    rowNode.setData(updatedRowData);

                    console.log('ApplicationsPage: данные в таблице обновлены');

                    // Обновляем статистику
                    this.updateStatistics();
                } else {
                    console.log('ApplicationsPage: строка не найдена, обновляем всю таблицу');
                    await this.refreshData();
                }
            }
        } catch (error) {
            console.error('Ошибка обновления данных в таблице:', error);
            // В случае ошибки просто логируем, не обновляем всю таблицу
            console.log('ApplicationsPage: ошибка обновления строки, но редактирование прошло успешно');
        }
    }

    showLoading() {
        const gridDiv = document.getElementById('applicationsGrid');
        if (gridDiv) {
            gridDiv.innerHTML = `
                <div class="flex items-center justify-center h-full">
                    <div class="animate-spin rounded-full h-8 w-8 border-2 border-gray-300 border-t-cyan-500"></div>
                    <span class="ml-3 text-gray-400">Загрузка данных...</span>
                </div>
            `;
        }
    }

    hideLoading() {
        console.log('ApplicationsPage: скрываем загрузку');
        const gridDiv = document.getElementById('applicationsGrid');
        if (gridDiv) {
            // Убираем загрузочный индикатор, если он есть
            const loadingElements = gridDiv.querySelectorAll('.loading-spinner, .animate-spin');
            loadingElements.forEach(el => el.remove());

            // Убираем текст загрузки
            const loadingTexts = gridDiv.querySelectorAll('span:contains("Загрузка данных...")');
            loadingTexts.forEach(el => el.remove());

            console.log('ApplicationsPage: загрузка скрыта');
        }
    }

    showError(message) {
        const gridDiv = document.getElementById('applicationsGrid');
        if (gridDiv) {
            gridDiv.innerHTML = `
                <div class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <div class="text-red-400 text-lg mb-2">⚠️ Ошибка</div>
                        <div class="text-gray-400">${message}</div>
                        <button onclick="window.applicationsPage.refreshData()" class="mt-4 px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700">
                            Попробовать снова
                        </button>
                    </div>
                </div>
            `;
        }
    }

    showLoadMoreSpinner() {
        const spinner = document.getElementById('loadMoreSpinner');
        const button = document.getElementById('loadMoreBtn');
        if (spinner) spinner.classList.remove('hidden');
        if (button) button.classList.add('hidden');
    }

    hideLoadMoreSpinner() {
        const spinner = document.getElementById('loadMoreSpinner');
        if (spinner) spinner.classList.add('hidden');
    }

        updateLoadMoreButton() {
        const button = document.getElementById('loadMoreBtn');
        if (button) {
            console.log('ApplicationsPage: обновляем кнопку "Показать еще" - hasMorePages:', this.hasMorePages, 'isLoading:', this.isLoading, 'всего записей:', this.allData.length, 'totalRecords:', this.totalRecords, 'currentPage:', this.currentPage);

            // Проверяем, действительно ли есть еще страницы
            const shouldShowButton = this.hasMorePages && !this.isLoading;
            console.log('ApplicationsPage: shouldShowButton:', shouldShowButton);

            if (shouldShowButton) {
                button.classList.remove('hidden');
                console.log('ApplicationsPage: кнопка "Показать еще" показана');
            } else {
                button.classList.add('hidden');
                console.log('ApplicationsPage: кнопка "Показать еще" скрыта - hasMorePages:', this.hasMorePages, 'isLoading:', this.isLoading);
            }
        } else {
            console.log('ApplicationsPage: кнопка "Показать еще" не найдена в DOM');
        }
    }



    static stripZeros(value) {
        const s = String(value);
        if (!s.includes('.')) return s;
        return s.replace(/\.?0+$/, '');
    }
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    // Устанавливаем флаг админа
    window.isAdmin = document.body.dataset.isAdmin === 'true';

    // Ждем загрузки AG-Grid
    const checkAGGrid = () => {
        console.log('Проверяем AG-Grid...', typeof agGrid);
        if (typeof agGrid !== 'undefined' && agGrid.Grid) {
            console.log('AG-Grid загружен, создаем страницу');
            // Создаем экземпляр страницы
            window.applicationsPage = new ApplicationsPage();
        } else {
            console.log('AG-Grid еще не загружен, ждем...');
            setTimeout(checkAGGrid, 200);
        }
    };

    // Ждем немного перед первой проверкой
    setTimeout(checkAGGrid, 500);
});
