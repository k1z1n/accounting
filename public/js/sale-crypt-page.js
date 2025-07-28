(function() {
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
                <button class="edit-salecrypt-btn" title="Редактировать" data-id="${params.data.id}">
                    <svg class="w-5 h-5 text-cyan-400 hover:text-cyan-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828a4 4 0 01-2.828 1.172H7v-2a4 4 0 011.172-2.828z"/></svg>
                </button>
                <button class="delete-salecrypt-btn" title="Удалить" data-id="${params.data.id}">
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
                headerName: 'ПРОДАЖА −',
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
                headerName: 'ПОЛУЧЕНО +',
                field: 'fixed_amount',
                width: 120,
                sortable: true,
                filter: true,
                cellRenderer: (params) => {
                    const data = params.data;
                    if (!data.fixed_amount || !data.fixed_currency) return '—';
                    const amount = parseFloat(data.fixed_amount).toFixed(2);
                    const currency = data.fixed_currency.code;
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
        // Проверяем, что DOM полностью загружен
        if (document.readyState !== 'complete') {
            console.log('SaleCryptPage: DOM еще не загружен, ждем...');
            setTimeout(() => this.createGrid(), 100);
            return;
        }

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

        // Обработчики для кнопок редактирования и удаления
        document.addEventListener('click', (e) => {
            console.log('SaleCryptPage: клик по элементу:', e.target);
            if (e.target.closest('.edit-salecrypt-btn')) {
                const id = e.target.closest('.edit-salecrypt-btn').dataset.id;
                console.log('SaleCryptPage: нажата кнопка редактирования для ID:', id);
                this.openEditModal(id);
            }
            if (e.target.closest('.delete-salecrypt-btn')) {
                const id = e.target.closest('.delete-salecrypt-btn').dataset.id;
                console.log('SaleCryptPage: нажата кнопка удаления для ID:', id);
                this.openDeleteModal(id);
            }
        });

        // Обработчики для модальных окон
        const editForm = document.getElementById('editSaleCryptForm');
        if (editForm) {
            editForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveEdit();
            });
        }

        const confirmDeleteBtn = document.getElementById('confirmDeleteSaleCryptBtn');
        if (confirmDeleteBtn) {
            console.log('SaleCryptPage: найден обработчик кнопки подтверждения удаления');
            confirmDeleteBtn.addEventListener('click', () => {
                console.log('SaleCryptPage: нажата кнопка подтверждения удаления');
                this.confirmDelete();
            });
        } else {
            console.error('SaleCryptPage: не найдена кнопка подтверждения удаления');
        }

        // Обработчики для закрытия модальных окон
        const modalEditSaleCryptClose = document.getElementById('modalEditSaleCryptClose');
        if (modalEditSaleCryptClose) {
            modalEditSaleCryptClose.addEventListener('click', () => {
                document.getElementById('modalEditSaleCryptBackdrop').classList.add('hidden');
            });
        }

        const cancelEditSaleCrypt = document.getElementById('cancelEditSaleCrypt');
        if (cancelEditSaleCrypt) {
            cancelEditSaleCrypt.addEventListener('click', () => {
                document.getElementById('modalEditSaleCryptBackdrop').classList.add('hidden');
            });
        }

        const modalDeleteSaleCryptClose = document.getElementById('modalDeleteSaleCryptClose');
        if (modalDeleteSaleCryptClose) {
            modalDeleteSaleCryptClose.addEventListener('click', () => {
                document.getElementById('modalDeleteSaleCryptBackdrop').classList.add('hidden');
            });
        }

        const cancelDeleteSaleCrypt = document.getElementById('cancelDeleteSaleCrypt');
        if (cancelDeleteSaleCrypt) {
            cancelDeleteSaleCrypt.addEventListener('click', () => {
                document.getElementById('modalDeleteSaleCryptBackdrop').classList.add('hidden');
            });
        }
    }

    async loadInitialData() {
        try {
            console.log('SaleCryptPage: загружаем данные...');

            const params = {
                page: 1,
                perPage: 50,
                exchangerFilter: this.filters.exchanger || ''
            };

            const url = '/sale-crypt/data?' + new URLSearchParams(params);
            console.log('SaleCryptPage: URL запроса:', url);

            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            console.log('SaleCryptPage: получены данные:', result);

            this.allData = result.data || [];
            this.currentPage = result.currentPage || 1;
            this.hasMorePages = Boolean(result.hasMorePages);

            console.log('[SaleCryptPage] Загружены данные:', this.allData.length, 'записей');
            if (this.allData.length > 0) {
                console.log('[SaleCryptPage] Первая запись:', this.allData[0]);
                if (this.allData[0].application) {
                    console.log('[SaleCryptPage] Первая запись application:', this.allData[0].application);
                }
            }

            this.updateGrid();
            this.updateLoadMoreButton();

            // Показываем уведомление об успешной загрузке
            if (result.data && result.data.length > 0) {
                window.notifications.success(`Загружено ${result.data.length} записей продаж`);
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

        console.log('SaleCryptPage: загружаем еще данные...');
        this.isLoading = true;
        this.showLoadMoreSpinner();

        try {
            const nextPage = this.currentPage + 1;
            const url = '/sale-crypt/data?' + new URLSearchParams({
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
            this.updateLoadMoreButton();

            // Показываем уведомление о загруженных записях
            const loadedCount = result.data.length;
            const totalCount = this.allData.length;
            if (window.notifications) {
                window.notifications.success(`Загружено ${loadedCount} записей. Всего: ${totalCount}`);
            }
        } catch (error) {
            console.error('SaleCryptPage: ошибка загрузки дополнительных данных:', error);
            this.showError('Ошибка загрузки данных: ' + error.message);
        } finally {
            this.isLoading = false;
            this.hideLoadMoreSpinner();
            console.log('SaleCryptPage: finally блок - обновляем кнопку после завершения загрузки');
            this.updateLoadMoreButton();
        }
    }

    updateGrid() {
        if (this.gridApi) {
            console.log('[SaleCryptPage] Обновляем грид с данными:', this.allData.length, 'записей');
            this.gridApi.setRowData(this.allData);
            this.gridApi.sizeColumnsToFit();
            // Принудительно обновляем все ячейки
            this.gridApi.refreshCells({ force: true });
        }
    }

    async refreshData() {
        console.log('SaleCryptPage: refreshData вызван');

        // Сбрасываем состояние
        this.currentPage = 1;
        this.hasMorePages = true;
        this.allData = [];

        // Загружаем все данные заново
        await this.loadInitialData();

        console.log('SaleCryptPage: refreshData завершен');
    }

    showLoadMoreSpinner() {
        const spinner = document.getElementById('loadMoreSaleCryptSpinner');
        if (spinner) spinner.classList.remove('hidden');
    }

    hideLoadMoreSpinner() {
        const spinner = document.getElementById('loadMoreSaleCryptSpinner');
        if (spinner) spinner.classList.add('hidden');
    }

    updateLoadMoreButton() {
        const button = document.getElementById('loadMoreSaleCryptBtn');
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

        async loadApplicationsForSelect(selectedId = null) {
        const select = document.getElementById('edit_salecrypt_application_id');
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
                console.error('SaleCryptPage: ошибка загрузки заявок:', resp.status, resp.statusText);
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
            console.error('SaleCryptPage: ошибка загрузки заявок:', e);
            select.innerHTML = '<option value="">Ошибка загрузки</option>';
        }
    }

    async openEditModal(id) {
        const saleCrypt = this.allData.find(s => s.id == id);
        if (!saleCrypt) return;
        await this.loadApplicationsForSelect(saleCrypt.application_id);

        // Проверяем существование элементов перед их использованием
        const editSaleCryptId = document.getElementById('edit_salecrypt_id');
        const editSaleCryptApplication = document.getElementById('edit_salecrypt_application_id');
        const editSaleCryptExchanger = document.getElementById('edit_salecrypt_exchanger_id');
        const editSaleCryptSaleAmount = document.getElementById('edit_salecrypt_sale_amount');
        const editSaleCryptSaleCurrency = document.getElementById('edit_salecrypt_sale_currency_id');
        const editSaleCryptFixedAmount = document.getElementById('edit_salecrypt_fixed_amount');
        const editSaleCryptFixedCurrency = document.getElementById('edit_salecrypt_fixed_currency_id');
        const modal = document.getElementById('editSaleCryptModal');

        if (!editSaleCryptId || !editSaleCryptApplication || !editSaleCryptExchanger ||
            !editSaleCryptSaleAmount || !editSaleCryptSaleCurrency || !editSaleCryptFixedAmount ||
            !editSaleCryptFixedCurrency || !modal) {
            console.error('SaleCryptPage: Не найдены элементы модального окна');
            return;
        }

        editSaleCryptId.value = id;
        editSaleCryptApplication.value = saleCrypt.application_id || '';
        editSaleCryptExchanger.value = saleCrypt.exchanger_id || '';
        editSaleCryptSaleAmount.value = saleCrypt.sale_amount;
        editSaleCryptSaleCurrency.value = saleCrypt.sale_currency_id || '1';
        editSaleCryptFixedAmount.value = saleCrypt.fixed_amount;
        editSaleCryptFixedCurrency.value = saleCrypt.fixed_currency_id || '1';
        modal.classList.remove('hidden');
    }

                async saveEdit() {
        const id = document.getElementById('edit_salecrypt_id').value;
        const applicationId = document.getElementById('edit_salecrypt_application_id').value;
        const exchangerId = document.getElementById('edit_salecrypt_exchanger_id').value;
        const saleAmount = document.getElementById('edit_salecrypt_sale_amount').value;
        const saleCurrencyId = document.getElementById('edit_salecrypt_sale_currency_id').value;
        const fixedAmount = document.getElementById('edit_salecrypt_fixed_amount').value;
        const fixedCurrencyId = document.getElementById('edit_salecrypt_fixed_currency_id').value;


        const url = `/sale-crypt/${id}`;
        console.log('[SaleCryptPage] PUT', url, {
            application_id: applicationId,
            exchanger_id: exchangerId,
            sale_amount: saleAmount,
            sale_currency_id: saleCurrencyId,
            fixed_amount: fixedAmount,
            fixed_currency_id: fixedCurrencyId
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
                    fixed_amount: fixedAmount,
                    fixed_currency_id: fixedCurrencyId
                })
            });

            console.log('[SaleCryptPage] PUT response status:', resp.status);
            if (resp.ok) {
                // Мгновенно обновляем строку
                const idx = this.allData.findIndex(s => s.id == id);
                if (idx !== -1) {
                    // Обновляем основные поля
                    this.allData[idx].application_id = applicationId;
                    this.allData[idx].exchanger_id = exchangerId;
                    this.allData[idx].sale_amount = saleAmount;
                    this.allData[idx].sale_currency_id = saleCurrencyId;
                    this.allData[idx].fixed_amount = fixedAmount;
                    this.allData[idx].fixed_currency_id = fixedCurrencyId;

                    // Обновляем связанные объекты для корректного отображения
                    // Находим обменник по ID
                    const exchangerSelect = document.getElementById('edit_salecrypt_exchanger_id');
                    if (exchangerSelect && exchangerSelect.options) {
                        const selectedExchangerOption = exchangerSelect.options[exchangerSelect.selectedIndex];
                        if (selectedExchangerOption) {
                            this.allData[idx].exchanger = {
                                id: exchangerId,
                                title: selectedExchangerOption.text
                            };
                            console.log('[SaleCryptPage] Обновлен exchanger:', this.allData[idx].exchanger);
                        }
                    }

                    // Находим валюту продажи по ID
                    const saleCurrencySelect = document.getElementById('edit_salecrypt_sale_currency_id');
                    if (saleCurrencySelect && saleCurrencySelect.options) {
                        const selectedSaleCurrencyOption = saleCurrencySelect.options[saleCurrencySelect.selectedIndex];
                        if (selectedSaleCurrencyOption) {
                            this.allData[idx].sale_currency = {
                                id: saleCurrencyId,
                                code: selectedSaleCurrencyOption.text
                            };
                            console.log('[SaleCryptPage] Обновлен sale_currency:', this.allData[idx].sale_currency);
                        }
                    }

                    // Находим фиксированную валюту по ID
                    const fixedCurrencySelect = document.getElementById('edit_salecrypt_fixed_currency_id');
                    if (fixedCurrencySelect && fixedCurrencySelect.options) {
                        const selectedFixedCurrencyOption = fixedCurrencySelect.options[fixedCurrencySelect.selectedIndex];
                        if (selectedFixedCurrencyOption) {
                            this.allData[idx].fixed_currency = {
                                id: fixedCurrencyId,
                                code: selectedFixedCurrencyOption.text.split(' — ')[0] // Берем код валюты из текста опции
                            };
                            console.log('[SaleCryptPage] Обновлен fixed_currency:', this.allData[idx].fixed_currency);
                        }
                    }

                    // Обновляем информацию о заявке
                    const applicationSelect = document.getElementById('edit_salecrypt_application_id');
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
                                console.log('[SaleCryptPage] Обновлен application:', this.allData[idx].application);
                            }
                        } else {
                            // Если заявка не выбрана, очищаем
                            this.allData[idx].application = null;
                            console.log('[SaleCryptPage] Очищен application');
                        }
                    }

                    console.log('[SaleCryptPage] Обновленные данные строки:', this.allData[idx]);
                    console.log('[SaleCryptPage] application объект после обновления:', this.allData[idx].application);
                    // Обновляем конкретную строку в гриде
                    if (this.gridApi) {
                        const rowNode = this.gridApi.getRowNode(id);
                        if (rowNode) {
                            rowNode.setData(this.allData[idx]);
                            console.log('[SaleCryptPage] Строка обновлена в гриде');
                            // Принудительно обновляем колонку с заявкой
                            this.gridApi.refreshCells({
                                rowNodes: [rowNode],
                                columns: ['application.app_id'],
                                force: true
                            });
                            console.log('[SaleCryptPage] Обновлена колонка application.app_id для строки', id);
                        }
                    }
                    this.updateGrid();
                }
                document.getElementById('editSaleCryptModal').classList.add('hidden');
                window.notifications.success('Запись успешно обновлена');

                // Принудительно перезагружаем данные для этой страницы
                console.log('[SaleCryptPage] Начинаем принудительную перезагрузку данных');
                await this.loadInitialData();
            } else {
                const errText = await resp.text();
                console.error('[SaleCryptPage] PUT error:', resp.status, errText);
                window.notifications.error('Ошибка при сохранении изменений: ' + errText);
            }
        } catch (error) {
            console.error('[SaleCryptPage] PUT error:', error);
            window.notifications.error('Ошибка при сохранении изменений: ' + error.message);
        }
    }

    async openDeleteModal(id) {
        console.log('SaleCryptPage: openDeleteModal вызван с ID:', id);
        const modal = document.getElementById('deleteSaleCryptModal');
        const idSpan = document.getElementById('deleteSaleCryptId');

        if (modal && idSpan) {
            idSpan.textContent = id;
            modal.dataset.id = id;
            modal.classList.remove('hidden');
            console.log('SaleCryptPage: модальное окно удаления открыто');
        } else {
            console.error('SaleCryptPage: не найдены элементы модального окна удаления');
        }
    }

    async confirmDelete() {
        const modal = document.getElementById('deleteSaleCryptModal');
        const id = modal ? modal.dataset.id : null;
        console.log('SaleCryptPage: confirmDelete вызван с ID:', id);

        if (!id) {
            console.error('SaleCryptPage: ID не найден в модальном окне');
            return;
        }

        const url = `/sale-crypt/${id}`;
        console.log('[SaleCryptPage] DELETE', url);

        try {
            const resp = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            console.log('[SaleCryptPage] DELETE response status:', resp.status);
            if (resp.ok) {
                // Мгновенно удаляем строку
                this.allData = this.allData.filter(s => s.id != id);
                this.updateGrid();
                document.getElementById('deleteSaleCryptModal').classList.add('hidden');
                window.notifications.success('Продажа крипты успешно удалена');
            } else {
                const errText = await resp.text();
                console.error('[SaleCryptPage] DELETE error:', resp.status, errText);
                window.notifications.error('Ошибка при удалении продажи крипты: ' + errText);
            }
        } catch (error) {
            console.error('[SaleCryptPage] DELETE error:', error);
            window.notifications.error('Ошибка при удалении продажи крипты: ' + error.message);
        }
    }

    static stripZeros(value) {
        if (value === null || value === undefined) return '';
        return parseFloat(value).toString();
    }
}

// Экспортируем класс для использования на странице
window.SaleCryptPage = SaleCryptPage;
})();
