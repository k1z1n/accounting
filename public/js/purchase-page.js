(function() {
class PurchasePage {
    constructor() {
        console.log('PurchasePage: конструктор вызван');
        this.gridApi = null;
        this.isAdmin = window.isAdmin || false;
        this.currentPage = 1;
        this.perPage = 50;
        this.hasMorePages = true;
        this.allData = [];
        this.isLoading = false;
        this.filters = {
            exchanger: ''
        };
        this.init();
    }

    init() {
        console.log('PurchasePage: инициализация');
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
                <button class="edit-purchase-btn" title="Редактировать" data-id="${params.data.id}">
                    <svg class="w-5 h-5 text-cyan-400 hover:text-cyan-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a4 4 0 01-2.828 1.172H7v-2a4 4 0 011.172-2.828z"/></svg>
                </button>
                <button class="delete-purchase-btn" title="Удалить" data-id="${params.data.id}">
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
                headerName: 'ПОЛУЧЕНО +',
                field: 'received_amount',
                width: 120,
                sortable: true,
                filter: true,
                cellRenderer: (params) => {
                    const data = params.data;
                    if (!data.received_amount || !data.received_currency) return '—';
                    const amount = parseFloat(data.received_amount).toFixed(2);
                    const currency = data.received_currency.code;
                    return `<span>${amount} <img src="/images/coins/${currency}.svg" alt="${currency}" class="w-4 h-4 inline-block align-middle ml-1" onerror="this.style.display='none'"></span>`;
                }
            },
            {
                headerName: 'ПРОДАНО −',
                field: 'sale_amount',
                width: 120,
                sortable: true,
                filter: true,
                cellRenderer: (params) => {
                    const data = params.data;
                    if (!data.sale_amount || !data.sale_currency) return '—';
                    const amount = parseFloat(data.sale_amount).toFixed(2);
                    const currency = data.sale_currency.code;
                    return `<span>${amount} <img src="/images/coins/${currency}.svg" alt="${currency}" class="w-4 h-4 inline-block align-middle ml-1" onerror="this.style.display='none'"></span>`;
                }
            },
            {
                headerName: 'ЗАЯВКА',
                field: 'application.app_id',
                width: 120,
                sortable: true,
                filter: true,
                valueGetter: (params) => {
                    return params.data.application ? params.data.application.app_id : '';
                },
                cellRenderer: (params) => {
                    const appId = params.data.application ? params.data.application.app_id : '';
                    if (appId) {
                        return `<span class="text-cyan-400 hover:text-cyan-300 cursor-pointer underline" onclick="showApplicationDetails(${params.data.application.id})">${appId}</span>`;
                    }
                    return '';
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
                console.log('PurchasePage: AG-Grid готов');
                this.gridApi = params.api;
                this.gridApi.sizeColumnsToFit();
            },
            onFirstDataRendered: (params) => {
                console.log('PurchasePage: данные отрендерены');
                params.api.sizeColumnsToFit();
            }
        };
    }

    createGrid() {
        // Проверяем, что DOM полностью загружен
        if (document.readyState !== 'complete') {
            console.log('PurchasePage: DOM еще не загружен, ждем...');
            setTimeout(() => this.createGrid(), 100);
            return;
        }

        const gridDiv = document.getElementById('purchaseGrid');
        if (!gridDiv) {
            console.error('PurchasePage: элемент purchaseGrid не найден');
            return;
        }

        new agGrid.Grid(gridDiv, this.gridOptions);
        console.log('PurchasePage: AG-Grid создан');
    }

    setupEventListeners() {
        console.log('PurchasePage: setupEventListeners вызван');

        // Фильтр по обменнику
        const exchangerFilter = document.getElementById('exchangerFilter');
        if (exchangerFilter) {
            exchangerFilter.addEventListener('change', (e) => {
                console.log('PurchasePage: изменен фильтр обменника:', e.target.value);
                this.filters.exchanger = e.target.value;
                this.applyFilters();
            });
        }

        // Кнопка обновления
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                console.log('PurchasePage: нажата кнопка обновления');
                window.notifications.info('Обновление данных...');
                this.loadInitialData();
            });
        }

        // Обработчики для кнопок редактирования и удаления
        document.addEventListener('click', (e) => {
            console.log('PurchasePage: клик по элементу:', e.target);
            if (e.target.closest('.edit-purchase-btn')) {
                const id = e.target.closest('.edit-purchase-btn').dataset.id;
                console.log('PurchasePage: нажата кнопка редактирования для ID:', id);
                this.openEditModal(id);
            }
            if (e.target.closest('.delete-purchase-btn')) {
                const id = e.target.closest('.delete-purchase-btn').dataset.id;
                console.log('PurchasePage: нажата кнопка удаления для ID:', id);
                this.openDeleteModal(id);
            }
        });

        // Обработчики для модальных окон
        const editForm = document.getElementById('editPurchaseForm');
        if (editForm) {
            editForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveEdit();
            });
        }

        const confirmDeleteBtn = document.getElementById('confirmDeletePurchaseBtn');
        if (confirmDeleteBtn) {
            console.log('PurchasePage: найден обработчик кнопки подтверждения удаления');
            confirmDeleteBtn.addEventListener('click', () => {
                console.log('PurchasePage: нажата кнопка подтверждения удаления');
                this.confirmDelete();
            });
        } else {
            console.error('PurchasePage: не найдена кнопка подтверждения удаления');
        }

        // Обработчики для закрытия модальных окон
        const modalEditPurchaseClose = document.getElementById('modalEditPurchaseClose');
        if (modalEditPurchaseClose) {
            modalEditPurchaseClose.addEventListener('click', () => {
                document.getElementById('modalEditPurchaseBackdrop').classList.add('hidden');
            });
        }

        const cancelEditPurchase = document.getElementById('cancelEditPurchase');
        if (cancelEditPurchase) {
            cancelEditPurchase.addEventListener('click', () => {
                document.getElementById('modalEditPurchaseBackdrop').classList.add('hidden');
            });
        }

        const modalDeletePurchaseClose = document.getElementById('modalDeletePurchaseClose');
        if (modalDeletePurchaseClose) {
            modalDeletePurchaseClose.addEventListener('click', () => {
                document.getElementById('modalDeletePurchaseBackdrop').classList.add('hidden');
            });
        }

        const cancelDeletePurchase = document.getElementById('cancelDeletePurchase');
        if (cancelDeletePurchase) {
            cancelDeletePurchase.addEventListener('click', () => {
                document.getElementById('modalDeletePurchaseBackdrop').classList.add('hidden');
            });
        }
    }

    async loadInitialData() {
        try {
            console.log('PurchasePage: загружаем данные...');

            const params = {
                page: 1,
                perPage: 50,
                exchangerFilter: this.filters.exchanger || ''
            };

            const url = '/purchase/data?' + new URLSearchParams(params);
            console.log('PurchasePage: URL запроса:', url);

            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            console.log('PurchasePage: получены данные:', result);

            this.allData = result.data || [];
            this.currentPage = result.currentPage || 1;
            this.hasMorePages = Boolean(result.hasMorePages);

            console.log('[PurchasePage] Загружены данные:', this.allData.length, 'записей');
            if (this.allData.length > 0) {
                console.log('[PurchasePage] Первая запись:', this.allData[0]);
                if (this.allData[0].application) {
                    console.log('[PurchasePage] Первая запись application:', this.allData[0].application);
                }
            }

            this.updateGrid();
            this.updateStatistics();
            this.updateLoadMoreButton();

            // Показываем уведомление об успешной загрузке
            if (result.data && result.data.length > 0) {
                window.notifications.success(`Загружено ${result.data.length} записей покупок`);
            }
        } catch (error) {
            console.error('PurchasePage: ошибка загрузки данных:', error);
            this.showError('Ошибка загрузки данных: ' + error.message);
        }
    }

    async applyFilters() {
        console.log('PurchasePage: applyFilters вызван');
        this.currentPage = 1;
        this.hasMorePages = true;
        this.allData = [];
        await this.loadInitialData();

        // Показываем уведомление о применении фильтров
        const activeFilters = [];
        if (this.filters.exchanger) activeFilters.push(`обменник: ${this.filters.exchanger}`);

        if (activeFilters.length > 0) {
            window.notifications.info(`Применены фильтры: ${activeFilters.join(', ')}`);
        } else {
            window.notifications.info('Фильтры сброшены');
        }
    }

    async loadMore() {
        if (this.isLoading || !this.hasMorePages) return;

        console.log('PurchasePage: загружаем еще данные...');
        this.isLoading = true;
        this.showLoadMoreSpinner();

        try {
            const nextPage = this.currentPage + 1;
            const url = '/purchase/data?' + new URLSearchParams({
                page: nextPage,
                perPage: this.perPage || 50,
                exchangerFilter: this.filters.exchanger || ''
            });

            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            this.allData = [...this.allData, ...result.data];
            this.currentPage = result.currentPage;
            this.hasMorePages = Boolean(result.hasMorePages);

            this.updateGrid();
            this.updateStatistics();
            this.updateLoadMoreButton();

            // Показываем уведомление о загруженных записях
            const loadedCount = result.data.length;
            const totalCount = this.allData.length;
            if (window.notifications) {
                window.notifications.success(`Загружено ${loadedCount} записей. Всего: ${totalCount}`);
            }
        } catch (error) {
            console.error('PurchasePage: ошибка загрузки дополнительных данных:', error);
            this.showError('Ошибка загрузки данных: ' + error.message);
        } finally {
            this.isLoading = false;
            this.hideLoadMoreSpinner();
            console.log('PurchasePage: finally блок - обновляем кнопку после завершения загрузки');
            this.updateLoadMoreButton();
        }
    }

    updateGrid() {
        if (this.gridApi) {
            console.log('[PurchasePage] Обновляем грид с данными:', this.allData.length, 'записей');
            this.gridApi.setRowData(this.allData);
            this.gridApi.sizeColumnsToFit();
            // Принудительно обновляем все ячейки
            this.gridApi.refreshCells({ force: true });
        }
    }

    updateStatistics() {
        const total = this.allData.length;
        // Статистика по статусам отключена, так как поля status нет в миграции
        const completed = 0;
        const paid = 0;
        const returned = 0;
        const elTotal = document.getElementById('totalPurchases');
        if (elTotal) elTotal.textContent = total;
        const elCompleted = document.getElementById('completedPurchases');
        if (elCompleted) elCompleted.textContent = completed;
        const elPaid = document.getElementById('paidPurchases');
        if (elPaid) elPaid.textContent = paid;
        const elReturned = document.getElementById('returnPurchases');
        if (elReturned) elReturned.textContent = returned;
    }

    showLoadMoreSpinner() {
        const spinner = document.getElementById('loadMorePurchaseSpinner');
        if (spinner) spinner.classList.remove('hidden');
    }

    hideLoadMoreSpinner() {
        const spinner = document.getElementById('loadMorePurchaseSpinner');
        if (spinner) spinner.classList.add('hidden');
    }

    updateLoadMoreButton() {
        const button = document.getElementById('loadMorePurchaseBtn');
        if (button) {
            if (this.hasMorePages && !this.isLoading) {
                button.classList.remove('hidden');
            } else {
                button.classList.add('hidden');
            }
        }
    }

    showError(message) {
        console.error('PurchasePage: ошибка:', message);
    }

        async loadApplicationsForSelect(selectedId = null) {
        const select = document.getElementById('edit_purchase_application_id');
        if (!select) return;
        select.innerHTML = '<option value="">Загрузка...</option>';

        try {
            const resp = await fetch('/api/applications/list-temp', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken
                },
                credentials: 'same-origin'
            });

            if (!resp.ok) {
                console.error('PurchasePage: ошибка загрузки заявок:', resp.status, resp.statusText);
                select.innerHTML = '<option value="">Ошибка загрузки</option>';
                return;
            }

            const apps = await resp.json();
            select.innerHTML = '<option value="">Выберите заявку</option>';
            apps.forEach(app => {
                const text = `${app.app_id}` + (app.order_id ? ` (${app.order_id})` : '');
                const option = document.createElement('option');
                option.value = app.id;
                option.textContent = text;
                if (selectedId && selectedId == app.id) option.selected = true;
                select.appendChild(option);
            });
        } catch (e) {
            console.error('PurchasePage: ошибка загрузки заявок:', e);
            select.innerHTML = '<option value="">Ошибка загрузки</option>';
        }
    }

    async openEditModal(id) {
        const purchase = this.allData.find(p => p.id == id);
        if (!purchase) return;
        await this.loadApplicationsForSelect(purchase.application_id);

        // Проверяем существование элементов перед их использованием
        const editPurchaseId = document.getElementById('editPurchaseId');
        const editPurchaseIdInput = document.getElementById('edit_purchase_id');
        const editPurchaseApplication = document.getElementById('edit_purchase_application_id');
        const editPurchaseExchanger = document.getElementById('edit_purchase_exchanger_id');
        const editSaleAmount = document.getElementById('edit_purchase_sale_amount');
        const editSaleCurrency = document.getElementById('edit_purchase_sale_currency_id');
        const editReceivedAmount = document.getElementById('edit_purchase_received_amount');
        const editReceivedCurrency = document.getElementById('edit_purchase_received_currency_id');
        const modalBackdrop = document.getElementById('editPurchaseModal');

        if (!editPurchaseId || !editPurchaseIdInput || !editPurchaseApplication || !editPurchaseExchanger ||
            !editSaleAmount || !editSaleCurrency || !editReceivedAmount || !editReceivedCurrency || !modalBackdrop) {
            console.error('PurchasePage: Не найдены элементы модального окна');
            return;
        }

        editPurchaseId.textContent = id;
        editPurchaseIdInput.value = id;
        editPurchaseApplication.value = purchase.application_id || '';
        editPurchaseExchanger.value = purchase.exchanger_id || '';
        editSaleAmount.value = purchase.sale_amount;
        editSaleCurrency.value = purchase.sale_currency_id || '1';
        editReceivedAmount.value = purchase.received_amount;
        editReceivedCurrency.value = purchase.received_currency_id || '1';
        modalBackdrop.classList.remove('hidden');
    }

    async saveEdit() {
        const id = document.getElementById('edit_purchase_id').value;
        const applicationId = document.getElementById('edit_purchase_application_id').value;
        const exchangerId = document.getElementById('edit_purchase_exchanger_id').value;
        const saleAmount = document.getElementById('edit_purchase_sale_amount').value;
        const saleCurrencyId = document.getElementById('edit_purchase_sale_currency_id').value;
        const receivedAmount = document.getElementById('edit_purchase_received_amount').value;
        const receivedCurrencyId = document.getElementById('edit_purchase_received_currency_id').value;

        const url = `/purchase/${id}`;
        console.log('[PurchasePage] PUT', url, {
            application_id: applicationId,
            exchanger_id: exchangerId,
            sale_amount: saleAmount,
            sale_currency_id: saleCurrencyId,
            received_amount: receivedAmount,
            received_currency_id: receivedCurrencyId
        });

        try {
            const resp = await fetch(url, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
                body: JSON.stringify({
                    application_id: applicationId,
                    exchanger_id: exchangerId,
                    sale_amount: saleAmount,
                    sale_currency_id: saleCurrencyId,
                    received_amount: receivedAmount,
                    received_currency_id: receivedCurrencyId
                })
            });

            console.log('[PurchasePage] PUT response status:', resp.status);
            if (resp.ok) {
                // Мгновенно обновляем строку
                const idx = this.allData.findIndex(p => p.id == id);
                if (idx !== -1) {
                    // Обновляем основные поля
                    this.allData[idx].application_id = applicationId;
                    this.allData[idx].exchanger_id = exchangerId;
                    this.allData[idx].sale_amount = saleAmount;
                    this.allData[idx].sale_currency_id = saleCurrencyId;
                    this.allData[idx].received_amount = receivedAmount;
                    this.allData[idx].received_currency_id = receivedCurrencyId;

                    // Обновляем связанные объекты для корректного отображения
                    // Находим обменник по ID
                    const exchangerSelect = document.getElementById('edit_purchase_exchanger_id');
                    if (exchangerSelect && exchangerSelect.options) {
                        const selectedExchangerOption = exchangerSelect.options[exchangerSelect.selectedIndex];
                        if (selectedExchangerOption) {
                            this.allData[idx].exchanger = {
                                id: exchangerId,
                                title: selectedExchangerOption.text
                            };
                            console.log('[PurchasePage] Обновлен exchanger:', this.allData[idx].exchanger);
                        }
                    }

                    // Находим валюту продажи по ID
                    const saleCurrencySelect = document.getElementById('edit_purchase_sale_currency_id');
                    if (saleCurrencySelect && saleCurrencySelect.options) {
                        const selectedSaleCurrencyOption = saleCurrencySelect.options[saleCurrencySelect.selectedIndex];
                        if (selectedSaleCurrencyOption) {
                            this.allData[idx].sale_currency = {
                                id: saleCurrencyId,
                                code: selectedSaleCurrencyOption.text.split(' — ')[0] // Берем код валюты из текста опции
                            };
                            console.log('[PurchasePage] Обновлен sale_currency:', this.allData[idx].sale_currency);
                        }
                    }

                    // Находим валюту получения по ID
                    const receivedCurrencySelect = document.getElementById('edit_purchase_received_currency_id');
                    if (receivedCurrencySelect && receivedCurrencySelect.options) {
                        const selectedReceivedCurrencyOption = receivedCurrencySelect.options[receivedCurrencySelect.selectedIndex];
                        if (selectedReceivedCurrencyOption) {
                            this.allData[idx].received_currency = {
                                id: receivedCurrencyId,
                                code: selectedReceivedCurrencyOption.text.split(' — ')[0] // Берем код валюты из текста опции
                            };
                            console.log('[PurchasePage] Обновлен received_currency:', this.allData[idx].received_currency);
                        }
                    }

                    // Обновляем информацию о заявке
                    const applicationSelect = document.getElementById('edit_purchase_application_id');
                    if (applicationSelect && applicationSelect.options) {
                        const selectedApplicationOption = applicationSelect.options[applicationSelect.selectedIndex];
                        if (selectedApplicationOption && applicationId) {
                            // Извлекаем app_id из текста опции (формат: "12345 (order_id)")
                            const text = selectedApplicationOption.text;
                            const appIdMatch = text.match(/^(\d+)/);
                            if (appIdMatch) {
                                this.allData[idx].application = {
                                    id: applicationId,
                                    app_id: appIdMatch[1]
                                };
                                console.log('[PurchasePage] Обновлен application:', this.allData[idx].application);
                            }
                        } else {
                            // Если заявка не выбрана, очищаем
                            this.allData[idx].application = null;
                            console.log('[PurchasePage] Очищен application');
                        }
                    }

                    console.log('[PurchasePage] Обновленные данные строки:', this.allData[idx]);
                    console.log('[PurchasePage] application объект после обновления:', this.allData[idx].application);
                    // Обновляем конкретную строку в гриде
                    if (this.gridApi) {
                        const rowNode = this.gridApi.getRowNode(id);
                        if (rowNode) {
                            rowNode.setData(this.allData[idx]);
                            console.log('[PurchasePage] Строка обновлена в гриде');
                            // Принудительно обновляем колонку с заявкой
                            this.gridApi.refreshCells({
                                rowNodes: [rowNode],
                                columns: ['application.app_id'],
                                force: true
                            });
                            console.log('[PurchasePage] Обновлена колонка application.app_id для строки', id);
                        }
                    }
                    this.updateGrid();
                }
                document.getElementById('editPurchaseModal').classList.add('hidden');
                window.notifications.success('Запись успешно обновлена');

                // Принудительно перезагружаем данные для этой страницы
                console.log('[PurchasePage] Начинаем принудительную перезагрузку данных');
                await this.loadInitialData();
            } else {
                const errText = await resp.text();
                console.error('[PurchasePage] PUT error:', resp.status, errText);
                window.notifications.error('Ошибка при сохранении изменений: ' + errText);
            }
        } catch (error) {
            console.error('[PurchasePage] PUT error:', error);
            window.notifications.error('Ошибка при сохранении изменений: ' + error.message);
        }
    }

    async openDeleteModal(id) {
        console.log('PurchasePage: openDeleteModal вызван с ID:', id);
        const modal = document.getElementById('deletePurchaseModal');
        const idSpan = document.getElementById('deletePurchaseId');

        if (modal && idSpan) {
            idSpan.textContent = id;
            modal.dataset.id = id;
            modal.classList.remove('hidden');
            console.log('PurchasePage: модальное окно удаления открыто');
        } else {
            console.error('PurchasePage: не найдены элементы модального окна удаления');
        }
    }

    async confirmDelete() {
        const modal = document.getElementById('deletePurchaseModal');
        const id = modal ? modal.dataset.id : null;
        console.log('PurchasePage: confirmDelete вызван с ID:', id);

        if (!id) {
            console.error('PurchasePage: ID не найден в модальном окне');
            return;
        }

        const url = `/purchase/${id}`;
        console.log('[PurchasePage] DELETE', url);

        try {
            const resp = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            console.log('[PurchasePage] DELETE response status:', resp.status);
            if (resp.ok) {
                // Мгновенно удаляем строку
                this.allData = this.allData.filter(p => p.id != id);
                this.updateGrid();
                document.getElementById('deletePurchaseModal').classList.add('hidden');
                window.notifications.success('Покупка крипты успешно удалена');
            } else {
                const errText = await resp.text();
                console.error('[PurchasePage] DELETE error:', resp.status, errText);
                window.notifications.error('Ошибка при удалении покупки крипты: ' + errText);
            }
        } catch (error) {
            console.error('[PurchasePage] DELETE error:', error);
            window.notifications.error('Ошибка при удалении покупки крипты: ' + error.message);
        }
    }

    static stripZeros(value) {
        if (value === null || value === undefined) return '';
        return parseFloat(value).toString();
    }
}

// Экспортируем класс для использования на странице
window.PurchasePage = PurchasePage;
})();
