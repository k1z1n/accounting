// Простая версия AG Grid для заявок
console.log('AG Grid Simple: загружен');

class SimpleApplicationsGrid {
    constructor() {
        console.log('SimpleApplicationsGrid: создается');
        this.gridApi = null;
        this.init();
    }

    init() {
        // Ждем полной загрузки страницы
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    setup() {
        console.log('SimpleApplicationsGrid: настройка');

        // Найдем существующую таблицу заявок
        const existingTable = document.getElementById('applicationsTable');
        console.log('Существующая таблица:', existingTable);

        if (!existingTable) {
            console.log('Таблица applicationsTable не найдена, создаем тестовый грид');
            this.createTestGrid();
            return;
        }

        // Создаем контейнер для AG Grid после существующей таблицы
        const container = document.createElement('div');
        container.id = 'agGridContainer';
        container.innerHTML = `
            <div class="mt-4 p-4 bg-gray-800 rounded">
                <h3 class="text-white mb-4">AG Grid версия таблицы заявок:</h3>
                <div id="myGrid" class="ag-theme-alpine" style="height: 400px; width: 100%;"></div>
            </div>
        `;

        // Вставляем после существующей таблицы
        existingTable.parentNode.insertBefore(container, existingTable.nextSibling);

        // Настраиваем AG Grid
        this.setupGrid();
    }

    createTestGrid() {
        console.log('Создаем тестовый грид');

        // Создаем контейнер в body если нет таблицы
        const container = document.createElement('div');
        container.id = 'testAgGridContainer';
        container.innerHTML = `
            <div class="mt-4 p-4 bg-gray-800 rounded">
                <h3 class="text-white mb-4">Тестовый AG Grid:</h3>
                <div id="myGrid" class="ag-theme-alpine" style="height: 400px; width: 100%;"></div>
            </div>
        `;

        // Вставляем в начало main
        const main = document.querySelector('main');
        if (main) {
            main.insertBefore(container, main.firstChild);
        } else {
            document.body.appendChild(container);
        }

        this.setupGrid();
    }

    setupGrid() {
        console.log('SimpleApplicationsGrid: настройка грида');

        // Проверяем, загружен ли AG Grid
        if (typeof agGrid === 'undefined') {
            console.error('AG Grid не загружен!');
            return;
        }

        console.log('AG Grid доступен:', agGrid);

        // Простые колонки
        const columnDefs = [
            { headerName: "ID", field: "id", width: 80 },
            { headerName: "App ID", field: "app_id", width: 100 },
            { headerName: "Статус", field: "status", width: 150 },
            { headerName: "Обменник", field: "exchanger", width: 120 },
            { headerName: "Создано", field: "created_at", width: 160 }
        ];

        // Тестовые данные
        const testData = [
            { id: 1, app_id: 123, status: "выполнена заявка", exchanger: "test1", created_at: "2025-01-01" },
            { id: 2, app_id: 456, status: "в обработке", exchanger: "test2", created_at: "2025-01-02" },
            { id: 3, app_id: 789, status: "завершена", exchanger: "test3", created_at: "2025-01-03" }
        ];

        // Опции грида
        const gridOptions = {
            columnDefs: columnDefs,
            rowData: testData,
            pagination: true,
            paginationPageSize: 10,
            onGridReady: (params) => {
                console.log('AG Grid готов');
                this.gridApi = params.api;
                console.log('Данные загружены:', testData.length, 'записей');
            }
        };

        // Создаем грид
        const gridDiv = document.querySelector('#myGrid');
        if (gridDiv) {
            console.log('Создаем AG Grid...');
            try {
                this.grid = agGrid.createGrid(gridDiv, gridOptions);
                console.log('AG Grid создан успешно');
            } catch (error) {
                console.error('Ошибка создания AG Grid:', error);
            }
        } else {
            console.error('Контейнер грида не найден');
        }
    }

    async loadData() {
        console.log('SimpleApplicationsGrid: загрузка данных');

        try {
            const response = await fetch('/api/applications?page=1', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                console.log('Данные получены:', data.data?.length || 0, 'записей');

                if (this.gridApi && data.data) {
                    this.gridApi.setGridOption('rowData', data.data);
                }
            } else {
                console.error('Ошибка HTTP:', response.status);
            }
        } catch (error) {
            console.error('Ошибка загрузки данных:', error);
        }
    }
}

// Инициализация
console.log('SimpleApplicationsGrid: инициализация');
window.simpleGrid = new SimpleApplicationsGrid();
