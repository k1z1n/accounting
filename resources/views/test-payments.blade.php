<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Тест AG-Grid - Оплаты</title>

    <!-- AG Grid CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-grid.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community/styles/ag-theme-alpine.css">
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .ag-theme-alpine {
            height: 500px;
            width: 100%;
        }
        .test-data {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Тест AG-Grid - Оплаты</h1>

        <div class="test-data">
            <h3>Тестовые данные:</h3>
            <div id="testData"></div>
        </div>

        <div id="paymentsGrid" class="ag-theme-alpine"></div>
    </div>

    <script>
        // Тестовые данные
        const testData = [
            {
                id: 1,
                user: { login: 'test_user_1' },
                sell_amount: 1000.50,
                sell_currency: { code: 'USDT' },
                status: 'выполненная заявка',
                exchanger: 'obama',
                comment: 'Тестовый платеж 1',
                created_at: '2025-07-20T10:00:00Z'
            },
            {
                id: 2,
                user: { login: 'test_user_2' },
                sell_amount: 2500.75,
                sell_currency: { code: 'RUB' },
                status: 'оплаченная заявка',
                exchanger: 'ural',
                comment: 'Тестовый платеж 2',
                created_at: '2025-07-20T11:00:00Z'
            },
            {
                id: 3,
                user: { login: 'test_user_3' },
                sell_amount: 500.25,
                sell_currency: { code: 'BTC' },
                status: 'возврат',
                exchanger: 'obama',
                comment: 'Тестовый платеж 3',
                created_at: '2025-07-20T12:00:00Z'
            }
        ];

        // Отображаем тестовые данные
        document.getElementById('testData').innerHTML = '<pre>' + JSON.stringify(testData, null, 2) + '</pre>';

        // Проверяем загрузку AG-Grid
        function checkAGGrid() {
            if (typeof agGrid === 'undefined') {
                console.error('AG-Grid не загружен!');
                setTimeout(checkAGGrid, 100);
                return;
            }
            console.log('AG-Grid загружен успешно!');
            initGrid();
        }

        function initGrid() {
            const columnDefs = [
                {
                    headerName: 'ID',
                    field: 'id',
                    width: 80,
                    sortable: true,
                    filter: true
                },
                {
                    headerName: 'Пользователь',
                    field: 'user.login',
                    width: 150,
                    sortable: true,
                    filter: true
                },
                {
                    headerName: 'Сумма',
                    field: 'sell_amount',
                    width: 120,
                    sortable: true,
                    filter: true,
                    valueFormatter: (params) => {
                        if (!params.value) return '';
                        return parseFloat(params.value).toString();
                    }
                },
                {
                    headerName: 'Статус',
                    field: 'status',
                    width: 150,
                    sortable: true,
                    filter: true,
                    cellRenderer: (params) => {
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
                },
                {
                    headerName: 'Обменник',
                    field: 'exchanger',
                    width: 120,
                    sortable: true,
                    filter: true
                },
                {
                    headerName: 'Комментарий',
                    field: 'comment',
                    width: 200,
                    sortable: true,
                    filter: true
                },
                {
                    headerName: 'Дата',
                    field: 'created_at',
                    width: 150,
                    sortable: true,
                    filter: true,
                    cellRenderer: (params) => {
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
                }
            ];

            const gridOptions = {
                columnDefs: columnDefs,
                rowData: testData,
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
                    console.log('AG-Grid готов!');
                    params.api.sizeColumnsToFit();
                },
                onFirstDataRendered: (params) => {
                    console.log('Данные отрендерены!');
                    params.api.sizeColumnsToFit();
                }
            };

            const gridDiv = document.getElementById('paymentsGrid');
            new agGrid.Grid(gridDiv, gridOptions);
            console.log('AG-Grid создан успешно!');
        }

        // Запускаем проверку
        checkAGGrid();
    </script>
</body>
</html>
