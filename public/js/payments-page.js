(function() {
class PaymentsPage {
    constructor() {
        this.allData = [];
        this.currentPage = 1;
        this.hasMorePages = false;
        this.isLoading = false;
        this.isDeleting = false; // Флаг для защиты от повторного удаления
        this.perPage = 50;
        this.gridApi = null;
        this.columnApi = null;
        this.filters = {
            exchanger: ''
        };
        this.isAdmin = window.currentUser && window.currentUser.role === 'admin';
        console.log('PaymentsPage: конструктор вызван');
        this.init();
    }

        init() {
        console.log('PaymentsPage: инициализация');

        // Проверяем существование контейнера
        const gridDiv = document.getElementById('paymentsGrid');
        if (!gridDiv) {
            console.error('PaymentsPage: контейнер paymentsGrid не найден на странице');
            return;
        }

        this.setupColumnDefs();
        this.setupGridOptions();
        this.setupEventListeners();
        this.createGrid();
        // Загружаем данные после создания грида с небольшой задержкой
        setTimeout(() => {
            this.loadInitialData();
        }, 100);
    }

    dateRenderer(params) {
        if (!params.value) return '';
        const date = new Date(params.value);
        return date.toLocaleDateString('ru-RU', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    actionRenderer(params) {
        return `
            <div class="flex gap-2 justify-center">
                <button class="edit-payment-btn" title="Редактировать" data-id="${params.data.id}">
                    <svg class="w-5 h-5 text-cyan-400 hover:text-cyan-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a4 4 0 01-2.828 1.172H7v-2a4 4 0 011.172-2.828z"/></svg>
                </button>
                <button class="delete-payment-btn" title="Удалить" data-id="${params.data.id}">
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
                headerName: 'ПЛАТФОРМА',
                field: 'exchanger.title',
                width: 150,
                sortable: true,
                filter: true,
                valueGetter: (params) => {
                    return params.data.exchanger ? params.data.exchanger.title : '';
                }
            },
            {
                headerName: 'СУММА',
                field: 'sell_amount',
                width: 120,
                sortable: true,
                filter: true,
                cellRenderer: (params) => {
                    const data = params.data;
                    if (!data.sell_amount || !data.sell_currency) return '—';
                    const amount = parseFloat(data.sell_amount).toFixed(2);
                    const currency = data.sell_currency.code;
                    return `<span>${amount} <img src="/images/coins/${currency}.svg" alt="${currency}" class="w-4 h-4 inline-block align-middle ml-1" onerror="this.style.display='none'"></span>`;
                }
            },
            {
                headerName: 'КОММЕНТАРИЙ',
                field: 'comment',
                width: 200,
                sortable: true,
                filter: true
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
                console.log('PaymentsPage: AG-Grid готов');
                this.gridApi = params.api;
                console.log('PaymentsPage: gridApi установлен:', !!this.gridApi);
                this.gridApi.sizeColumnsToFit();

                // Если данные уже загружены, обновляем грид
                if (this.allData && this.allData.length > 0) {
                    console.log('PaymentsPage: данные уже загружены, обновляем грид');
                    this.updateGrid();
                }
            },
            onFirstDataRendered: (params) => {
                console.log('PaymentsPage: данные отрендерены');
                params.api.sizeColumnsToFit();
            }
        };
    }

    createGrid() {
        // Проверяем, что DOM полностью загружен
        if (document.readyState !== 'complete') {
            console.log('PaymentsPage: DOM еще не загружен, ждем...');
            setTimeout(() => this.createGrid(), 100);
            return;
        }

        const gridDiv = document.getElementById('paymentsGrid');
        if (!gridDiv) {
            console.error('PaymentsPage: элемент paymentsGrid не найден');
            return;
        }

        // Проверяем загрузку AG-Grid
        console.log('PaymentsPage: проверяем AG-Grid...');
        console.log('PaymentsPage: typeof agGrid:', typeof agGrid);
        console.log('PaymentsPage: agGrid keys:', agGrid ? Object.keys(agGrid) : 'undefined');

        // Ждем загрузки AG-Grid если он еще не готов
        if (typeof agGrid === 'undefined' || !agGrid) {
            console.log('PaymentsPage: AG-Grid еще не загружен, ждем...');
            setTimeout(() => this.createGrid(), 100);
            return;
        }

        let GridConstructor = null;

        // Пробуем разные варианты получения конструктора
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

        if (!GridConstructor) {
            console.error('PaymentsPage: AG-Grid не найден в ожидаемом формате');
            this.showGridError(gridDiv, 'AG-Grid не загружен корректно');
            return;
        }

        try {
            let gridInstance;

            // Создаем экземпляр грида
            if (GridConstructor === agGrid.createGrid) {
                // Используем createGrid функцию
                gridInstance = GridConstructor(gridDiv, this.gridOptions);
            } else {
                // Используем конструктор Grid
                gridInstance = new GridConstructor(gridDiv, this.gridOptions);
            }

            // Сохраняем gridApi
            if (gridInstance && gridInstance.api) {
                this.gridApi = gridInstance.api;
                console.log('PaymentsPage: gridApi сохранен:', !!this.gridApi);
            } else if (gridInstance && typeof gridInstance.getApi === 'function') {
                this.gridApi = gridInstance.getApi();
                console.log('PaymentsPage: gridApi получен через getApi():', !!this.gridApi);
            } else if (gridInstance && gridInstance.gridOptions && gridInstance.gridOptions.api) {
                this.gridApi = gridInstance.gridOptions.api;
                console.log('PaymentsPage: gridApi получен через gridOptions.api:', !!this.gridApi);
            } else {
                console.error('PaymentsPage: не удалось получить gridApi из экземпляра грида');
                console.log('PaymentsPage: gridInstance:', gridInstance);
                this.showGridError(gridDiv, 'Не удалось получить API таблицы');
                return;
            }

            console.log('PaymentsPage: AG-Grid создан успешно');
        } catch (error) {
            console.error('PaymentsPage: ошибка создания AG-Grid:', error);
            this.showGridError(gridDiv, 'Ошибка создания таблицы: ' + error.message);
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

        // Фильтр по обменнику
        const exchangerFilter = document.getElementById('exchangerFilter');
        if (exchangerFilter) {
            console.log('PaymentsPage: элемент exchangerFilter найден:', !!exchangerFilter);
            exchangerFilter.addEventListener('change', (e) => {
                console.log('PaymentsPage: изменен фильтр обменника:', e.target.value);
                this.filters.exchanger = e.target.value;
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
                window.notifications.info('Обновление данных...');
                this.loadInitialData();
            });
        }

        // Единый обработчик для кнопок редактирования и удаления через делегирование
        document.addEventListener('click', (e) => {
            if (e.target.closest('.edit-payment-btn')) {
                const id = e.target.closest('.edit-payment-btn').dataset.id;
                this.openEditModal(id);
            }
            if (e.target.closest('.delete-payment-btn')) {
                const id = e.target.closest('.delete-payment-btn').dataset.id;
                this.openDeleteModal(id);
            }
        });

        // Обработчики для модальных окон
        const editForm = document.getElementById('editPaymentForm');
        if (editForm) {
            editForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveEdit();
            });
        }

        const confirmDeleteBtn = document.getElementById('confirmDeletePaymentBtn');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', () => {
                this.confirmDelete();
            });
        }
    }

    async loadInitialData() {
        try {
            console.log('[PaymentsPage] Загружаем данные...');
            console.log('[PaymentsPage] gridApi готов:', !!this.gridApi);

            // Если AG-Grid еще не готов, ждем
            if (!this.gridApi) {
                console.log('[PaymentsPage] AG-Grid не готов, ждем...');
                let attempts = 0;
                while (!this.gridApi && attempts < 50) {
                    await new Promise(resolve => setTimeout(resolve, 100));
                    attempts++;
                }
                if (!this.gridApi) {
                    console.error('[PaymentsPage] AG-Grid не готов после ожидания');
                    return;
                }
            }

            console.log('[PaymentsPage] Фильтры перед запросом:', this.filters);
            console.log('[PaymentsPage] Текущий пользователь:', window.currentUser || 'не определен');
            console.log('[PaymentsPage] Время запроса:', new Date().toISOString());

            const params = {
                page: 1,
                perPage: 50,
                exchangerFilter: this.filters.exchanger || ''
            };
            const url = '/payments/data?' + new URLSearchParams(params);
            console.log('[PaymentsPage] URL запроса:', url);

            // Добавляем заголовки для отладки
            const requestOptions = {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            };

            const response = await fetch(url, requestOptions);
            console.log('[PaymentsPage] Статус ответа:', response.status);
            console.log('[PaymentsPage] Заголовки ответа:', Object.fromEntries(response.headers.entries()));

            if (!response.ok) {
                const errorText = await response.text();
                console.error('[PaymentsPage] Ошибка HTTP:', response.status, errorText);
                throw new Error(`HTTP ${response.status}: ${errorText}`);
            }
            const result = await response.json();
            console.log('[PaymentsPage] Получены данные:', result);
            console.log('[PaymentsPage] Количество записей:', result.data ? result.data.length : 0);
            console.log('[PaymentsPage] Debug информация:', result.debug);

            this.allData = result.data || [];
            this.currentPage = result.currentPage || 1;
            this.hasMorePages = Boolean(result.hasMorePages);
            this.updateGrid();
            this.updateStatistics();
            this.updateLoadMoreButton();

            // Показываем уведомление об успешной загрузке
            if (result.data && result.data.length > 0) {
                window.notifications.success(`Загружено ${result.data.length} записей платежей`);
            }
            if (result.data && result.data.length === 0) {
                window.notifications.info('Записи платежей не найдены');
            }
            if (this.gridApi) {
                this.gridApi.hideOverlay();
            }
            console.log('[PaymentsPage] Данные успешно загружены и отображены');
        } catch (error) {
            console.error('[PaymentsPage] Ошибка загрузки данных:', error);
            console.error('[PaymentsPage] Стек ошибки:', error.stack);
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

        // Показываем уведомление о применении фильтров
        const activeFilters = [];
        if (this.filters.exchanger) activeFilters.push(`обменник: ${this.filters.exchanger}`);

        if (activeFilters.length > 0) {
            window.notifications.info(`Применены фильтры: ${activeFilters.join(', ')}`);
        } else {
            window.notifications.info('Фильтры сброшены');
        }

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

            const url = '/payments/data?' + new URLSearchParams({
                page: nextPage,
                perPage: this.perPage || 50,
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
            console.log('PaymentsPage: hasMorePages из ответа:', result.hasMorePages);

            // Добавляем новые данные к существующим
            this.allData = [...this.allData, ...result.data];
            this.currentPage = result.currentPage;
            this.hasMorePages = Boolean(result.hasMorePages);
            console.log('PaymentsPage: обновлены hasMorePages:', this.hasMorePages, 'currentPage:', this.currentPage);

            console.log('PaymentsPage: общее количество записей:', this.allData.length);
            console.log('PaymentsPage: текущая страница:', this.currentPage);
            console.log('PaymentsPage: есть еще страницы:', this.hasMorePages);

            this.updateGrid();
            this.updateStatistics();
            this.updateLoadMoreButton();

            // Показываем уведомление о загруженных записях
            const loadedCount = result.data.length;
            const totalCount = this.allData.length;
            if (window.notifications) {
                window.notifications.success(`Загружено ${loadedCount} записей. Всего: ${totalCount}`);
            }

            console.log('PaymentsPage: данные успешно добавлены');
        } catch (error) {
            console.error('PaymentsPage: ошибка загрузки дополнительных данных:', error);
            this.showError('Ошибка загрузки данных: ' + error.message);
        } finally {
            this.isLoading = false;
            this.hideLoadMoreSpinner();
            console.log('PaymentsPage: finally блок - обновляем кнопку после завершения загрузки');
            this.updateLoadMoreButton();
        }
    }

    updateGrid() {
        if (this.gridApi) {
            console.log('PaymentsPage: обновляем грид с', this.allData.length, 'записями');
            console.log('PaymentsPage: данные для грида:', this.allData);
            console.log('PaymentsPage: gridApi готов:', !!this.gridApi);
            console.log('PaymentsPage: gridApi методы:', Object.keys(this.gridApi));

            // Проверяем, что данные не пустые
            if (!this.allData || this.allData.length === 0) {
                console.warn('PaymentsPage: нет данных для отображения');
                this.gridApi.setRowData([]);
                return;
            }

            this.gridApi.setRowData(this.allData);

            // Проверяем, что данные установлены
            setTimeout(() => {
                const rowCount = this.gridApi.getDisplayedRowCount();
                console.log('PaymentsPage: количество строк в гриде после обновления:', rowCount);
                if (rowCount === 0 && this.allData.length > 0) {
                    console.error('PaymentsPage: данные не отображаются в гриде!');
                    // Попробуем принудительно обновить
                    this.gridApi.refreshCells();
                    this.gridApi.redrawRows();
                    // Попробуем еще раз установить данные
                    setTimeout(() => {
                        this.gridApi.setRowData([...this.allData]);
                        console.log('PaymentsPage: повторная попытка установки данных');
                    }, 50);
                }
            }, 100);

            this.gridApi.sizeColumnsToFit();
        } else {
            console.error('PaymentsPage: gridApi не готов!');
        }
    }

    updateStatistics() {
        console.log('PaymentsPage: обновляем статистику');
        const total = this.allData.length;
        // Статистика по статусам отключена, так как поля status нет в миграции
        const completed = 0;
        const paid = 0;
        const returned = 0;
        const elTotal = document.getElementById('totalPayments');
        if (elTotal) elTotal.textContent = total;
        const elCompleted = document.getElementById('completedPayments');
        if (elCompleted) elCompleted.textContent = completed;
        const elPaid = document.getElementById('paidPayments');
        if (elPaid) elPaid.textContent = paid;
        const elReturned = document.getElementById('returnPayments');
        if (elReturned) elReturned.textContent = returned;
        console.log('PaymentsPage: статистика обновлена:', { total, completed, paid, returned });
    }

    showLoadMoreSpinner() {
        const spinner = document.getElementById('loadMorePaymentsSpinner');
        if (spinner) {
            spinner.classList.remove('hidden');
        }
    }

    hideLoadMoreSpinner() {
        const spinner = document.getElementById('loadMorePaymentsSpinner');
        if (spinner) {
            spinner.classList.add('hidden');
        }
    }

    updateLoadMoreButton() {
        const button = document.getElementById('loadMorePaymentsBtn');
        if (!button) {
            console.error('PaymentsPage: кнопка loadMorePaymentsBtn не найдена');
            return;
        }

        console.log('PaymentsPage: обновляем кнопку "Показать еще"');
        console.log('PaymentsPage: hasMorePages:', this.hasMorePages);
        console.log('PaymentsPage: isLoading:', this.isLoading);
        console.log('PaymentsPage: currentPage:', this.currentPage);

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

    async openEditModal(id) {
        const payment = this.allData.find(p => p.id == id);
        if (!payment) return;
        document.getElementById('editPaymentId').textContent = id;
        document.getElementById('edit_payment_id').value = id;
        document.getElementById('edit_payment_exchanger_id').value = payment.exchanger_id || '';
        document.getElementById('edit_payment_sell_amount').value = payment.sell_amount;
        document.getElementById('edit_payment_sell_currency_id').value = payment.sell_currency_id || '1';
        document.getElementById('edit_payment_comment').value = payment.comment || '';
        document.getElementById('editPaymentModal').classList.remove('hidden');
    }
        async saveEdit() {
        const id = document.getElementById('edit_payment_id').value;
        const exchangerId = document.getElementById('edit_payment_exchanger_id').value;
        const sellAmount = document.getElementById('edit_payment_sell_amount').value;
        const sellCurrencyId = document.getElementById('edit_payment_sell_currency_id').value;
        const comment = document.getElementById('edit_payment_comment').value;

        const url = `/payments/${id}`;
        console.log('[PaymentsPage] PUT', url, {
            exchanger_id: exchangerId,
            sell_amount: sellAmount,
            sell_currency_id: sellCurrencyId,
            comment: comment
        });

        try {
            const resp = await fetch(url, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
                body: JSON.stringify({
                    exchanger_id: exchangerId,
                    sell_amount: sellAmount,
                    sell_currency_id: sellCurrencyId,
                    comment: comment
                })
            });

            console.log('[PaymentsPage] PUT response status:', resp.status);
            if (resp.ok) {
                // Мгновенно обновляем строку
                const idx = this.allData.findIndex(p => p.id == id);
                if (idx !== -1) {
                    // Обновляем основные поля
                    this.allData[idx].exchanger_id = exchangerId;
                    this.allData[idx].sell_amount = sellAmount;
                    this.allData[idx].sell_currency_id = sellCurrencyId;
                    this.allData[idx].comment = comment;

                    // Обновляем связанные объекты для корректного отображения
                    // Находим обменник по ID
                    const exchangerSelect = document.getElementById('edit_payment_exchanger_id');
                    const selectedExchangerOption = exchangerSelect.options[exchangerSelect.selectedIndex];
                    if (selectedExchangerOption) {
                        this.allData[idx].exchanger = {
                            id: exchangerId,
                            title: selectedExchangerOption.text
                        };
                        console.log('[PaymentsPage] Обновлен exchanger:', this.allData[idx].exchanger);
                    }

                    // Находим валюту по ID
                    const currencySelect = document.getElementById('edit_payment_sell_currency_id');
                    const selectedCurrencyOption = currencySelect.options[currencySelect.selectedIndex];
                    if (selectedCurrencyOption) {
                        this.allData[idx].sell_currency = {
                            id: sellCurrencyId,
                            code: selectedCurrencyOption.text.split(' — ')[0] // Берем код валюты из текста опции
                        };
                        console.log('[PaymentsPage] Обновлен sell_currency:', this.allData[idx].sell_currency);
                    }

                    this.updateGrid();
                }
                document.getElementById('editPaymentModal').classList.add('hidden');
                window.notifications.success('Запись успешно обновлена');
            } else {
                const errText = await resp.text();
                console.error('[PaymentsPage] PUT error:', resp.status, errText);
                window.notifications.error('Ошибка при сохранении изменений: ' + errText);
            }
        } catch (error) {
            console.error('[PaymentsPage] PUT error:', error);
            window.notifications.error('Ошибка при сохранении изменений: ' + error.message);
        }
    }
    async openDeleteModal(id) {
        document.getElementById('deletePaymentId').textContent = id;
        document.getElementById('deletePaymentModal').dataset.id = id;
        document.getElementById('deletePaymentModal').classList.remove('hidden');
    }
    async confirmDelete() {
        // Защита от повторного выполнения
        if (this.isDeleting) {
            console.log('[PaymentsPage] DELETE уже выполняется, игнорируем повторный вызов');
            return;
        }

        this.isDeleting = true;

        const id = document.getElementById('deletePaymentModal').dataset.id;
        const url = `/payments/${id}`;
        console.log('[PaymentsPage] DELETE', url);

        // Защита от двойного клика
        const deleteBtn = document.querySelector('#deletePaymentModal .btn-delete');
        if (deleteBtn && deleteBtn.disabled) {
            console.log('[PaymentsPage] DELETE уже выполняется, игнорируем повторный клик');
            this.isDeleting = false;
            return;
        }

        if (deleteBtn) {
            deleteBtn.disabled = true;
            deleteBtn.textContent = 'Удаление...';
        }

        try {
            const resp = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            console.log('[PaymentsPage] DELETE response status:', resp.status);

            const result = await resp.json();
            console.log('[PaymentsPage] DELETE response data:', result);

            if (resp.ok) {
                // Мгновенно удаляем строку
                this.allData = this.allData.filter(p => p.id != id);
                this.updateGrid();
                document.getElementById('deletePaymentModal').classList.add('hidden');
                window.notifications.success(result.message || 'Запись успешно удалена');
            } else {
                console.error('[PaymentsPage] DELETE error:', resp.status, result);
                window.notifications.error(result.message || 'Ошибка при удалении');
            }
        } catch (error) {
            console.error('[PaymentsPage] DELETE network error:', error);
            window.notifications.error('Ошибка сети при удалении: ' + error.message);
        } finally {
            // Восстанавливаем кнопку и флаг
            if (deleteBtn) {
                deleteBtn.disabled = false;
                deleteBtn.textContent = 'Удалить';
            }
            this.isDeleting = false;
        }
    }

    static stripZeros(value) {
        if (value === null || value === undefined) return '';
        return parseFloat(value).toString();
    }
}

// Экспортируем класс для использования на странице
window.PaymentsPage = PaymentsPage;
})();
