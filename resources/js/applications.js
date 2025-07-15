function attachLoadMore(tbodyId, btnId, apiUrl, render) {
    const tbody = document.getElementById(tbodyId);
    const btn   = document.getElementById(btnId);

    btn.addEventListener('click', () => {
        if (btn.dataset.hasMore !== 'true') return;

        const next = btn.dataset.nextPage;
        btn.disabled = true;
        btn.textContent = 'Загрузка…';

        fetch(`${apiUrl}?page=${next}`, {
            headers: {'X-Requested-With':'XMLHttpRequest'}
        })
            .then(r=>r.json())
            .then(json=>{
                json.data.forEach(item => {
                    tbody.insertAdjacentHTML('beforeend', render(item));
                });
                btn.dataset.nextPage = parseInt(next)+1;
                btn.dataset.hasMore  = json.has_more ? 'true':'false';
                btn.textContent      = json.has_more ? 'Загрузить ещё' : 'Больше нет';
                btn.disabled = false;
            })
            .catch(err=>{
                console.error(err);
                btn.disabled = false;
                btn.textContent = 'Загрузить ещё';
            });
    });
}

// 1) Transfers
attachLoadMore(
    'transfersTbody',
    'loadMoreTransfers',
    '/transfers',
    item => `
      <tr class="bg-[#191919] hover:bg-gray-700">
        ${ window.isAdmin ? `
          <td class="px-5 py-4 whitespace-nowrap space-x-2">
            <button class="edit-transfer-btn …" data-id="${item.id}" …>✏️</button>
            <button class="delete-transfer-btn …">🗑️</button>
          </td>` : '' }
        <td class="px-5 py-4 text-gray-200 whitespace-nowrap">${item.exchanger_from.title}</td>
        <td class="px-5 py-4 text-gray-200 whitespace-nowrap">${item.exchanger_to.title}</td>
        <td class="px-5 py-4 text-sm whitespace-nowrap">
          <div class="inline-flex items-center space-x-1">
            <span class="text-white">${item.amount ?? '—'}</span>
            ${item.amount_currency.code ? `<img src="/images/coins/${item.amount_currency.code}.svg" class="w-4 h-4">` : ''}
          </div>
        </td>
        <td class="px-5 py-4 text-sm whitespace-nowrap">
          <div class="inline-flex items-center space-x-1">
            <span class="text-red-400">${item.commission ?? '—'}</span>
            ${item.commission_currency.code ? `<img src="/images/coins/${item.commission_currency.code}.svg" class="w-4 h-4">` : ''}
          </div>
        </td>
      </tr>`
);

// 2) Payments
attachLoadMore(
    'paymentsTbody',
    'loadMorePayments',
    '/payments',
    item => `
      <tr class="bg-[#191919] hover:bg-gray-700">
        ${ window.isAdmin ? `
          <td class="px-5 py-4 whitespace-nowrap space-x-2">
            <button class="edit-payment-btn …" data-id="${item.id}" …>✏️</button>
            <button class="delete-payment-btn …">🗑️</button>
          </td>` : '' }
        <td class="px-5 py-4 text-white whitespace-nowrap">${item.exchanger.title}</td>
        <td class="px-5 py-4 text-sm whitespace-nowrap">
          <span class="text-red-400">${item.sell_amount}</span>
          <img src="/images/coins/${item.sell_currency.code}.svg" class="w-4 h-4">
        </td>
        <td class="px-5 py-4 text-white">${item.comment || '—'}</td>
      </tr>`
);

// 3) Purchases
attachLoadMore(
    'purchasesTbody',
    'loadMorePurchases',
    '/purchases',
    item => `
      <tr class="bg-[#191919] hover:bg-gray-700">
        ${ window.isAdmin ? `…` : '' }
        <td class="px-5 py-4 text-gray-200 whitespace-nowrap">${item.exchanger.title}</td>
        <td class="px-5 py-4 text-sm whitespace-nowrap">
          <span class="text-green-400">${item.received_amount}</span>
          <img src="/images/coins/${item.received_currency.code}.svg" class="w-4 h-4">
        </td>
        <td class="px-5 py-4 text-sm whitespace-nowrap">
          <span class="text-red-400">${item.sale_amount}</span>
          <img src="/images/coins/${item.sale_currency.code}.svg" class="w-4 h-4">
        </td>
      </tr>`
);

// 4) SaleCrypts
attachLoadMore(
    'saleCryptsTbody',
    'loadMoreSaleCrypts',
    '/sale-crypts',
    item => `
      <tr class="bg-[#191919] hover:bg-gray-700">
        ${ window.isAdmin ? `…` : '' }
        <td class="px-5 py-4 text-gray-200 whitespace-nowrap">${item.exchanger.title}</td>
        <td class="px-5 py-4 text-sm whitespace-nowrap">
          <span class="text-red-400">${item.sale_amount}</span>
          <img src="/images/coins/${item.sale_currency.code}.svg" class="w-4 h-4">
        </td>
        <td class="px-5 py-4 text-sm whitespace-nowrap">
          <span class="${item.fixed_amount>0?'text-green-400':'text-red-400'}">${item.fixed_amount>0?'+':''}${item.fixed_amount}</span>
          <img src="/images/coins/${item.fixed_currency.code}.svg" class="w-4 h-4">
        </td>
      </tr>`
);

// Хелпер для форматирования даты в dd.mm.yyyy HH:MM:SS
function formatDate(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    if (isNaN(d)) return dateStr;
    const pad = n => String(n).padStart(2, '0');
    return `${pad(d.getDate())}.${pad(d.getMonth()+1)}.${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
}

// 0) Applications (главная таблица)
attachLoadMore(
    'appsTbody',
    'loadMoreBtn',
    '/api/applications',
    d => {
        let rowHtml = `<tr class=\"bg-[#191919] hover:bg-gray-800 transition\">`;
        if (window.isAdmin) {
            rowHtml += `
                <td class=\"px-5 py-4 text-sm text-gray-200\">
                    <button class=\"editBtn px-3 py-1 bg-cyan-600 text-white rounded-md hover:bg-cyan-700 transition text-xs\"
                        data-id=\"${d.id}\"
                        data-app_id=\"${d.app_id}\"
                        data-sell_amount=\"${d.sell_amount ?? ''}\"
                        data-sell_currency=\"${d.sell_currency?.code ?? ''}\"
                        data-buy_amount=\"${d.buy_amount ?? ''}\"
                        data-buy_currency=\"${d.buy_currency?.code ?? ''}\"
                        data-expense_amount=\"${d.expense_amount ?? ''}\"
                        data-expense_currency=\"${d.expense_currency?.code ?? ''}\"
                        data-merchant=\"${d.merchant ?? ''}\"
                        data-order_id=\"${d.order_id ?? ''}\"
                    >Редактировать</button>
                </td>
                <td class=\"px-5 py-4 text-sm text-gray-200 text-center\">${d.user?.login ?? '-'}</td>`;
        }
        rowHtml += `
            <td class=\"px-5 py-4 text-base text-gray-200 text-center whitespace-nowrap font-bold\">${d.app_id}</td>
            <td class=\"px-5 py-4 text-base text-gray-200 text-center whitespace-nowrap\">${formatDate(d.app_created_at)}</td>
            <td class=\"px-5 py-4 text-base text-gray-200 text-center whitespace-nowrap\">${d.exchanger}</td>
            <td class=\"px-5 py-4 text-base text-gray-200 text-center whitespace-nowrap\">${d.status}</td>
            <td class=\"px-5 py-4 text-lg whitespace-nowrap\">`;
        if (d.sale_text) {
            const [amount, curCode] = d.sale_text.trim().split(' ');
            rowHtml += `<div class=\"inline-flex items-center space-x-1\"><span class=\"text-green-400 font-bold\">+${amount}</span>${curCode ? `<img src=\"/images/coins/${curCode}.svg\" alt=\"${curCode}\" class=\"w-5 h-5\">` : ''}</div>`;
        } else {
            rowHtml += '—';
        }
        rowHtml += `</td><td class=\"px-5 py-4 text-lg whitespace-nowrap\">`;
        if (d.sell_amount !== null && d.sell_currency) {
            const sell = String(d.sell_amount).replace(/\.?0+$/, '').replace(/^-/, '');
            rowHtml += `<div class=\"inline-flex items-center space-x-1\"><span class=\"text-red-400 font-bold\">-${sell}</span><img src=\"/images/coins/${d.sell_currency.code}.svg\" alt=\"${d.sell_currency.code}\" class=\"w-5 h-5\"></div>`;
        } else {
            rowHtml += '—';
        }
        rowHtml += `</td><td class=\"px-5 py-4 text-lg whitespace-nowrap\">`;
        if (d.buy_amount !== null && d.buy_currency) {
            const buy = String(d.buy_amount).replace(/\.?0+$/, '').replace(/^-/, '');
            const sign = d.buy_amount > 0 ? '+' : '-';
            const cls = d.buy_amount > 0 ? 'text-green-400' : 'text-red-400';
            rowHtml += `<div class=\"inline-flex items-center space-x-1\"><span class=\"${cls} font-bold\">${sign}${buy}</span><img src=\"/images/coins/${d.buy_currency.code}.svg\" alt=\"${d.buy_currency.code}\" class=\"w-5 h-5\"></div>`;
        } else {
            rowHtml += '—';
        }
        rowHtml += `</td><td class=\"px-5 py-4 text-lg whitespace-nowrap\">`;
        if (d.expense_amount !== null && d.expense_currency) {
            const exp = String(d.expense_amount).replace(/\.?0+$/, '').replace(/^-/, '');
            rowHtml += `<div class=\"inline-flex items-center space-x-1\"><span class=\"text-red-400 font-bold\">-${exp}</span><img src=\"/images/coins/${d.expense_currency.code}.svg\" alt=\"${d.expense_currency.code}\" class=\"w-5 h-5\"></div>`;
        } else {
            rowHtml += '—';
        }
        rowHtml += `</td><td class=\"px-5 py-4 text-base text-gray-200 text-center whitespace-nowrap\">${d.merchant ?? '—'}</td><td class=\"px-5 py-4 text-base text-gray-200 text-center whitespace-nowrap\">${d.order_id ?? '—'}</td></tr>`;
        return rowHtml;
    }
);

window.onerror = (msg, src, ln, col, err) => console.error(msg, src, ln, col, err);

// ===== УЛУЧШЕННАЯ ФУНКЦИОНАЛЬНОСТЬ ТАБЛИЦЫ =====
class EnhancedTableManager {
    constructor() {
        this.table = document.getElementById('applicationsTable');
        this.searchInput = document.getElementById('tableSearch');
        this.statusFilter = document.getElementById('statusFilter');
        this.exchangerFilter = document.getElementById('exchangerFilter');
        this.refreshBtn = document.getElementById('refreshTable');
        this.loader = document.getElementById('tableLoader');

        this.currentPage = 1;
        this.isLoading = false;

        this.init();
    }

    init() {
        if (!this.table) return;

        this.bindEvents();
        this.enhanceTable();
        this.setupInfiniteScroll();
        this.addRowAnimations();
    }

    bindEvents() {
        // Поиск с задержкой
        if (this.searchInput) {
            let searchTimeout;
            this.searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.handleSearch(e.target.value);
                }, 300);
            });
        }

        // Фильтры
        if (this.statusFilter) {
            this.statusFilter.addEventListener('change', (e) => {
                this.handleStatusFilter(e.target.value);
            });
        }

        if (this.exchangerFilter) {
            this.exchangerFilter.addEventListener('change', (e) => {
                this.handleExchangerFilter(e.target.value);
            });
        }

        // Кнопка обновления
        if (this.refreshBtn) {
            this.refreshBtn.addEventListener('click', () => {
                this.refreshTable();
            });
        }

        // Сортировка по заголовкам
        this.table.querySelectorAll('th[data-sortable]').forEach(header => {
            header.addEventListener('click', () => {
                this.handleSort(header.dataset.sortable, header);
            });
        });
    }

    enhanceTable() {
        // Добавляем атрибуты для лучшей доступности
        this.table.setAttribute('role', 'table');
        this.table.setAttribute('aria-label', 'Таблица заявок');

        // Улучшаем строки
        this.table.querySelectorAll('tbody tr').forEach((row, index) => {
            row.setAttribute('role', 'row');
            row.setAttribute('tabindex', '0');
            row.style.animationDelay = `${index * 50}ms`;

            // Добавляем hover эффекты
            row.addEventListener('mouseenter', () => {
                row.classList.add('scale-[1.01]', 'shadow-lg');
            });

            row.addEventListener('mouseleave', () => {
                row.classList.remove('scale-[1.01]', 'shadow-lg');
            });

            // Клик по строке для выделения
            row.addEventListener('click', (e) => {
                if (e.target.tagName !== 'BUTTON') {
                    this.selectRow(row);
                }
            });
        });
    }

    handleSearch(query) {
        const rows = this.table.querySelectorAll('tbody tr');
        const normalizedQuery = query.toLowerCase().trim();

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const matches = text.includes(normalizedQuery);

            row.style.display = matches ? '' : 'none';

            if (matches && normalizedQuery) {
                this.highlightSearchTerm(row, normalizedQuery);
            } else {
                this.removeHighlight(row);
            }
        });

        this.updateResultsCount();
    }

    handleStatusFilter(status) {
        const rows = this.table.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const statusCell = row.querySelector('[data-column="status"]');
            if (!statusCell) return;

            const cellText = statusCell.textContent.toLowerCase();
            const matches = !status || cellText.includes(status.toLowerCase());

            row.style.display = matches ? '' : 'none';
        });

        this.updateResultsCount();
    }

    handleExchangerFilter(exchanger) {
        const rows = this.table.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const exchangerCell = row.querySelector('[data-column="exchanger"]');
            if (!exchangerCell) return;

            const cellText = exchangerCell.textContent.toLowerCase();
            const matches = !exchanger || cellText.includes(exchanger.toLowerCase());

            row.style.display = matches ? '' : 'none';
        });

        this.updateResultsCount();
    }

    handleSort(column, header) {
        const tbody = this.table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const isAscending = !header.classList.contains('sort-asc');

        // Обновляем индикаторы сортировки
        this.table.querySelectorAll('th').forEach(th => {
            th.classList.remove('sort-asc', 'sort-desc');
            const icon = th.querySelector('svg');
            if (icon) {
                icon.classList.remove('text-primary-400', 'rotate-180');
                icon.classList.add('text-gray-400');
            }
        });

        header.classList.add(isAscending ? 'sort-asc' : 'sort-desc');
        const headerIcon = header.querySelector('svg');
        if (headerIcon) {
            headerIcon.classList.remove('text-gray-400');
            headerIcon.classList.add('text-primary-400');
            if (!isAscending) {
                headerIcon.classList.add('rotate-180');
            }
        }

        // Сортируем строки
        rows.sort((a, b) => {
            const aCell = a.querySelector(`[data-column="${column}"]`);
            const bCell = b.querySelector(`[data-column="${column}"]`);

            if (!aCell || !bCell) return 0;

            const aVal = aCell.textContent.trim();
            const bVal = bCell.textContent.trim();

            // Числовая сортировка для определенных колонок
            if (['app_id', 'income', 'sale', 'purchase', 'expense'].includes(column)) {
                const aNum = parseFloat(aVal.replace(/[^\d.-]/g, '')) || 0;
                const bNum = parseFloat(bVal.replace(/[^\d.-]/g, '')) || 0;
                return isAscending ? aNum - bNum : bNum - aNum;
            }

            // Сортировка по дате
            if (column === 'created_at') {
                const aDate = new Date(aVal);
                const bDate = new Date(bVal);
                return isAscending ? aDate - bDate : bDate - aDate;
            }

            // Текстовая сортировка
            return isAscending ?
                aVal.localeCompare(bVal, 'ru', { numeric: true }) :
                bVal.localeCompare(aVal, 'ru', { numeric: true });
        });

        // Анимация перестройки
        rows.forEach((row, index) => {
            row.style.animationDelay = `${index * 20}ms`;
            row.classList.add('animate-fadeIn');
            tbody.appendChild(row);
        });

        // Показываем уведомление
        if (window.notifications) {
            window.notifications.info(`Таблица отсортирована по ${header.textContent.trim()}`);
        }
    }

    selectRow(row) {
        // Убираем выделение с других строк
        this.table.querySelectorAll('tbody tr').forEach(r => {
            r.classList.remove('bg-primary-600/20', 'ring-2', 'ring-primary-500');
        });

        // Выделяем текущую строку
        row.classList.add('bg-primary-600/20', 'ring-2', 'ring-primary-500');

        // Показываем информацию о выделенной строке
        const appId = row.querySelector('[data-column="app_id"]')?.textContent.trim();
        if (appId && window.notifications) {
            window.notifications.info(`Выделена заявка ${appId}`);
        }
    }

    highlightSearchTerm(row, term) {
        const cells = row.querySelectorAll('td');
        cells.forEach(cell => {
            if (cell.querySelector('button')) return; // Пропускаем кнопки

            const text = cell.textContent;
            const regex = new RegExp(`(${term})`, 'gi');
            const highlighted = text.replace(regex, '<mark class="bg-yellow-200 text-yellow-800 px-1 rounded">$1</mark>');

            if (highlighted !== text) {
                cell.innerHTML = highlighted;
            }
        });
    }

    removeHighlight(row) {
        const marks = row.querySelectorAll('mark');
        marks.forEach(mark => {
            mark.outerHTML = mark.textContent;
        });
    }

    updateResultsCount() {
        const visibleRows = this.table.querySelectorAll('tbody tr:not([style*="display: none"])');
        const totalRows = this.table.querySelectorAll('tbody tr');

        const badge = document.querySelector('.badge-primary');
        if (badge) {
            badge.textContent = `${visibleRows.length} из ${totalRows.length}`;
        }
    }

    refreshTable() {
        if (this.isLoading) return;

        this.isLoading = true;
        this.showLoader();

        // Анимация обновления
        this.refreshBtn.classList.add('animate-spin');
        this.refreshBtn.disabled = true;

        // Имитация загрузки (в реальном приложении здесь был бы AJAX запрос)
        setTimeout(() => {
            this.hideLoader();
            this.refreshBtn.classList.remove('animate-spin');
            this.refreshBtn.disabled = false;
            this.isLoading = false;

            if (window.notifications) {
                window.notifications.success('Таблица обновлена');
            }
        }, 1500);
    }

    showLoader() {
        if (this.loader) {
            this.loader.classList.remove('hidden');
        }
    }

    hideLoader() {
        if (this.loader) {
            this.loader.classList.add('hidden');
        }
    }

    setupInfiniteScroll() {
        const tableContainer = this.table.closest('.overflow-x-auto');
        if (!tableContainer) return;

        let isNearBottom = false;

        tableContainer.addEventListener('scroll', () => {
            const { scrollTop, scrollHeight, clientHeight } = tableContainer;
            const isAtBottom = scrollTop + clientHeight >= scrollHeight - 100;

            if (isAtBottom && !isNearBottom && !this.isLoading) {
                isNearBottom = true;
                this.loadMoreData();
            } else if (!isAtBottom) {
                isNearBottom = false;
            }
        });
    }

    loadMoreData() {
        if (this.isLoading) return;

        this.isLoading = true;
        this.currentPage++;

        // Показываем индикатор загрузки в футере таблицы
        const loadingRow = document.createElement('tr');
        loadingRow.className = 'loading-row';
        loadingRow.innerHTML = `
            <td colspan="100%" class="text-center py-4">
                <div class="flex items-center justify-center space-x-2">
                    <div class="loading-spinner"></div>
                    <span class="text-gray-400">Загрузка дополнительных данных...</span>
                </div>
            </td>
        `;

        this.table.querySelector('tbody').appendChild(loadingRow);

        // Имитация загрузки
        setTimeout(() => {
            loadingRow.remove();
            this.isLoading = false;

            if (window.notifications) {
                window.notifications.info('Дополнительные данные загружены');
            }
        }, 1000);
    }

    addRowAnimations() {
        // Добавляем анимацию появления для новых строк
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1 && node.tagName === 'TR') {
                            node.classList.add('animate-fadeIn');
                        }
                    });
                }
            });
        });

        observer.observe(this.table.querySelector('tbody'), {
            childList: true
        });
    }
}

// Инициализация после загрузки DOM
document.addEventListener('DOMContentLoaded', () => {
    new EnhancedTableManager();
});
