(function() {
class TransferPage {
    constructor() {
        this.allData = [];
        this.currentPage = 1;
        this.hasMorePages = false;
        this.isLoading = false;
        this.gridApi = null;
        this.filters = {
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
                filter: true,
                valueGetter: (params) => {
                    return params.data.exchanger_from ? params.data.exchanger_from.title : '';
                }
            },
            {
                headerName: 'КУДА',
                field: 'exchanger_to.title',
                width: 150,
                sortable: true,
                filter: true,
                valueGetter: (params) => {
                    return params.data.exchanger_to ? params.data.exchanger_to.title : '';
                }
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
                    return `<span>${amount} <img src="/images/coins/${currency}.svg" alt="${currency}" class="w-4 h-4 inline-block align-middle ml-1" onerror="this.style.display='none'"></span>`;
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
                    return `<span>${amount} <img src="/images/coins/${currency}.svg" alt="${currency}" class="w-4 h-4 inline-block align-middle ml-1" onerror="this.style.display='none'"></span>`;
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
            }
        };
    }

    createGrid() {
        // Проверяем, что DOM полностью загружен
        if (document.readyState !== 'complete') {
            console.log('TransferPage: DOM еще не загружен, ждем...');
            setTimeout(() => this.createGrid(), 100);
            return;
        }

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

        // Обработчики для кнопок редактирования и удаления
        document.addEventListener('click', (e) => {
            if (e.target.closest('.edit-transfer-btn')) {
                const id = e.target.closest('.edit-transfer-btn').dataset.id;
                this.openEditModal(id);
            }
            if (e.target.closest('.delete-transfer-btn')) {
                const id = e.target.closest('.delete-transfer-btn').dataset.id;
                this.openDeleteModal(id);
            }
        });

        // Обработчики для модальных окон
        const editForm = document.getElementById('editTransferForm');
        if (editForm) {
            editForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveEdit();
            });
        }

        const confirmDeleteBtn = document.getElementById('confirmDeleteTransferBtn');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', () => {
                this.confirmDelete();
            });
        }
    }

    async loadInitialData() {
        try {
            console.log('TransferPage: загружаем данные...');

            const params = {
                page: 1,
                perPage: 50,
                exchangerFilter: this.filters.exchanger || ''
            };

            const url = '/transfer/data?' + new URLSearchParams(params);
            console.log('TransferPage: URL запроса:', url);

            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            console.log('TransferPage: получены данные:', result);

            this.allData = result.data || [];
            this.currentPage = result.currentPage || 1;
            this.hasMorePages = Boolean(result.hasMorePages);

            this.updateGrid();
            this.updateStatistics();
            this.updateLoadMoreButton();

            // Показываем уведомление об успешной загрузке
            if (result.data && result.data.length > 0) {
                window.notifications.success(`Загружено ${result.data.length} записей переводов`);
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

        console.log('TransferPage: загружаем еще данные...');
        this.isLoading = true;
        this.showLoadMoreSpinner();

        try {
            const nextPage = this.currentPage + 1;
            const url = '/transfer/data?' + new URLSearchParams({
                page: nextPage,
                perPage: this.perPage || 50,
                exchangerFilter: this.filters.exchanger || ''
            });

            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            console.log('TransferPage: получен ответ от сервера:', result);
            this.allData = [...this.allData, ...result.data];
            this.currentPage = result.currentPage;
            this.hasMorePages = Boolean(result.hasMorePages);
            console.log('TransferPage: обновлены данные - currentPage:', this.currentPage, 'hasMorePages:', this.hasMorePages, 'totalRecords:', this.allData.length);

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
            console.error('TransferPage: ошибка загрузки дополнительных данных:', error);
            this.showError('Ошибка загрузки данных: ' + error.message);
        } finally {
            this.isLoading = false;
            this.hideLoadMoreSpinner();
            console.log('TransferPage: finally блок - обновляем кнопку после завершения загрузки');
            this.updateLoadMoreButton();
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
        const spinner = document.getElementById('loadMoreTransferSpinner');
        if (spinner) spinner.classList.remove('hidden');
    }

    hideLoadMoreSpinner() {
        const spinner = document.getElementById('loadMoreTransferSpinner');
        if (spinner) spinner.classList.add('hidden');
    }

    updateLoadMoreButton() {
        const button = document.getElementById('loadMoreTransferBtn');
        console.log('TransferPage: updateLoadMoreButton - ищем кнопку с ID loadMoreTransferBtn');
        if (button) {
            console.log('TransferPage: кнопка найдена, текущие классы:', button.className);
            console.log('TransferPage: updateLoadMoreButton - hasMorePages:', this.hasMorePages, 'isLoading:', this.isLoading, 'currentPage:', this.currentPage);
            if (this.hasMorePages && !this.isLoading) {
                button.classList.remove('hidden');
                console.log('TransferPage: кнопка показана, новые классы:', button.className);
            } else {
                button.classList.add('hidden');
                console.log('TransferPage: кнопка скрыта, новые классы:', button.className);
            }
        } else {
            console.error('TransferPage: кнопка с ID loadMoreTransferBtn не найдена!');
        }
    }

    showError(message) {
        console.error('TransferPage: ошибка:', message);
    }

                async openEditModal(id) {
        const transfer = this.allData.find(t => t.id == id);
        if (!transfer) return;

        document.getElementById('editTransferId').textContent = id;
        document.getElementById('edit_transfer_id').value = id;
        document.getElementById('edit_transfer_exchanger_from_id').value = transfer.exchanger_from_id || '';
        document.getElementById('edit_transfer_exchanger_to_id').value = transfer.exchanger_to_id || '';
        document.getElementById('edit_transfer_commission').value = transfer.commission;
        document.getElementById('edit_transfer_commission_id').value = transfer.commission_id || '1';
        document.getElementById('edit_transfer_amount').value = transfer.amount;
        document.getElementById('edit_transfer_amount_id').value = transfer.amount_id || '1';
        document.getElementById('editTransferModal').classList.remove('hidden');
    }

                async saveEdit() {
        const id = document.getElementById('edit_transfer_id').value;
        const exchangerFromId = document.getElementById('edit_transfer_exchanger_from_id').value;
        const exchangerToId = document.getElementById('edit_transfer_exchanger_to_id').value;
        const commission = document.getElementById('edit_transfer_commission').value;
        const commissionId = document.getElementById('edit_transfer_commission_id').value;
        const amount = document.getElementById('edit_transfer_amount').value;
        const amountId = document.getElementById('edit_transfer_amount_id').value;

        const url = `/transfer/${id}`;
        console.log('[TransferPage] PUT', url, {
            exchanger_from_id: exchangerFromId,
            exchanger_to_id: exchangerToId,
            commission: commission,
            commission_id: commissionId,
            amount: amount,
            amount_id: amountId
        });

        try {
            const resp = await fetch(url, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken },
                body: JSON.stringify({
                    exchanger_from_id: exchangerFromId,
                    exchanger_to_id: exchangerToId,
                    commission: commission,
                    commission_id: commissionId,
                    amount: amount,
                    amount_id: amountId
                })
            });

            console.log('[TransferPage] PUT response status:', resp.status);
            if (resp.ok) {
                // Мгновенно обновляем строку
                const idx = this.allData.findIndex(t => t.id == id);
                if (idx !== -1) {
                    // Обновляем основные поля
                    this.allData[idx].exchanger_from_id = exchangerFromId;
                    this.allData[idx].exchanger_to_id = exchangerToId;
                    this.allData[idx].commission = commission;
                    this.allData[idx].commission_id = commissionId;
                    this.allData[idx].amount = amount;
                    this.allData[idx].amount_id = amountId;

                    // Обновляем связанные объекты для корректного отображения
                    // Находим обменник "откуда" по ID
                    const exchangerFromSelect = document.getElementById('edit_transfer_exchanger_from_id');
                    if (exchangerFromSelect && exchangerFromSelect.options) {
                        const selectedExchangerFromOption = exchangerFromSelect.options[exchangerFromSelect.selectedIndex];
                        if (selectedExchangerFromOption) {
                            this.allData[idx].exchanger_from = {
                                id: exchangerFromId,
                                title: selectedExchangerFromOption.text
                            };
                            console.log('[TransferPage] Обновлен exchanger_from:', this.allData[idx].exchanger_from);
                        }
                    }

                    // Находим обменник "куда" по ID
                    const exchangerToSelect = document.getElementById('edit_transfer_exchanger_to_id');
                    if (exchangerToSelect && exchangerToSelect.options) {
                        const selectedExchangerToOption = exchangerToSelect.options[exchangerToSelect.selectedIndex];
                        if (selectedExchangerToOption) {
                            this.allData[idx].exchanger_to = {
                                id: exchangerToId,
                                title: selectedExchangerToOption.text
                            };
                            console.log('[TransferPage] Обновлен exchanger_to:', this.allData[idx].exchanger_to);
                        }
                    }

                    // Находим валюту комиссии по ID
                    const commissionCurrencySelect = document.getElementById('edit_transfer_commission_id');
                    if (commissionCurrencySelect && commissionCurrencySelect.options) {
                        const selectedCommissionCurrencyOption = commissionCurrencySelect.options[commissionCurrencySelect.selectedIndex];
                        if (selectedCommissionCurrencyOption) {
                            this.allData[idx].commission_currency = {
                                id: commissionId,
                                code: selectedCommissionCurrencyOption.text.split(' — ')[0] // Берем код валюты из текста опции
                            };
                            console.log('[TransferPage] Обновлен commission_currency:', this.allData[idx].commission_currency);
                        }
                    }

                    // Находим валюту суммы по ID
                    const amountCurrencySelect = document.getElementById('edit_transfer_amount_id');
                    if (amountCurrencySelect && amountCurrencySelect.options) {
                        const selectedAmountCurrencyOption = amountCurrencySelect.options[amountCurrencySelect.selectedIndex];
                        if (selectedAmountCurrencyOption) {
                            this.allData[idx].amount_currency = {
                                id: amountId,
                                code: selectedAmountCurrencyOption.text.split(' — ')[0] // Берем код валюты из текста опции
                            };
                            console.log('[TransferPage] Обновлен amount_currency:', this.allData[idx].amount_currency);
                        }
                    }

                    this.updateGrid();
                }
                document.getElementById('editTransferModal').classList.add('hidden');
                window.notifications.success('Запись успешно обновлена');
            } else {
                const errText = await resp.text();
                console.error('[TransferPage] PUT error:', resp.status, errText);
                window.notifications.error('Ошибка при сохранении изменений: ' + errText);
            }
        } catch (error) {
            console.error('[TransferPage] PUT error:', error);
            window.notifications.error('Ошибка при сохранении изменений: ' + error.message);
        }
    }

    async openDeleteModal(id) {
        document.getElementById('deleteTransferId').textContent = id;
        document.getElementById('deleteTransferModal').dataset.id = id;
        document.getElementById('deleteTransferModal').classList.remove('hidden');
    }

    async confirmDelete() {
        const id = document.getElementById('deleteTransferModal').dataset.id;
        const url = `/transfer/${id}`;
        console.log('[TransferPage] DELETE', url);

        try {
            const resp = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            console.log('[TransferPage] DELETE response status:', resp.status);
            if (resp.ok) {
                // Мгновенно удаляем строку
                this.allData = this.allData.filter(t => t.id != id);
                this.updateGrid();
                document.getElementById('deleteTransferModal').classList.add('hidden');
                window.notifications.success('Перевод успешно удален');
            } else {
                const errText = await resp.text();
                console.error('[TransferPage] DELETE error:', resp.status, errText);
                window.notifications.error('Ошибка при удалении перевода: ' + errText);
            }
        } catch (error) {
            console.error('[TransferPage] DELETE error:', error);
            window.notifications.error('Ошибка при удалении перевода: ' + error.message);
        }
    }

    static stripZeros(value) {
        if (value === null || value === undefined) return '';
        return parseFloat(value).toString();
    }
}

// Экспортируем класс для использования на странице
window.TransferPage = TransferPage;
})();
