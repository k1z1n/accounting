<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отладка AG-Grid</title>

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
        .debug-info {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            font-family: monospace;
            white-space: pre-wrap;
        }
        .ag-theme-alpine {
            height: 400px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Отладка AG-Grid</h1>

        <div class="debug-info" id="debugInfo">
            Загрузка информации...
        </div>

        <div id="paymentsGrid" class="ag-theme-alpine"></div>
    </div>

    <script>
        function updateDebugInfo() {
            const debugInfo = document.getElementById('debugInfo');
            let info = '';

            info += '=== AG-GRID DEBUG INFO ===\n';
            info += `typeof agGrid: ${typeof agGrid}\n`;

            if (typeof agGrid !== 'undefined') {
                info += `agGrid keys: ${Object.keys(agGrid).join(', ')}\n`;

                if (agGrid.Grid) {
                    info += '✓ agGrid.Grid найден\n';
                } else {
                    info += '✗ agGrid.Grid НЕ найден\n';
                }

                if (agGrid.createGrid) {
                    info += '✓ agGrid.createGrid найден\n';
                } else {
                    info += '✗ agGrid.createGrid НЕ найден\n';
                }

                if (agGrid.GridOptions) {
                    info += '✓ agGrid.GridOptions найден\n';
                } else {
                    info += '✗ agGrid.GridOptions НЕ найден\n';
                }

                // Проверяем window.agGrid
                if (window.agGrid) {
                    info += '✓ window.agGrid найден\n';
                    info += `window.agGrid keys: ${Object.keys(window.agGrid).join(', ')}\n`;
                } else {
                    info += '✗ window.agGrid НЕ найден\n';
                }

                // Проверяем глобальные переменные
                if (window.Grid) {
                    info += '✓ window.Grid найден\n';
                } else {
                    info += '✗ window.Grid НЕ найден\n';
                }

                if (window.GridOptions) {
                    info += '✓ window.GridOptions найден\n';
                } else {
                    info += '✗ window.GridOptions НЕ найден\n';
                }

            } else {
                info += '✗ agGrid не определен\n';
            }

            debugInfo.textContent = info;
        }

        // Обновляем информацию каждые 2 секунды
        updateDebugInfo();
        setInterval(updateDebugInfo, 2000);

        // Пробуем создать грид
        function tryCreateGrid() {
            const gridDiv = document.getElementById('paymentsGrid');
            if (!gridDiv) return;

            const testData = [
                { id: 1, name: 'Test 1', value: 100 },
                { id: 2, name: 'Test 2', value: 200 },
                { id: 3, name: 'Test 3', value: 300 }
            ];

            const columnDefs = [
                { headerName: 'ID', field: 'id' },
                { headerName: 'Name', field: 'name' },
                { headerName: 'Value', field: 'value' }
            ];

            const gridOptions = {
                columnDefs: columnDefs,
                rowData: testData
            };

            let success = false;

            // Пробуем разные способы создания грида
            try {
                if (agGrid && agGrid.Grid) {
                    new agGrid.Grid(gridDiv, gridOptions);
                    console.log('✓ Успешно создан через agGrid.Grid');
                    success = true;
                }
            } catch (e) {
                console.log('✗ Ошибка через agGrid.Grid:', e.message);
            }

            try {
                if (agGrid && agGrid.createGrid && !success) {
                    agGrid.createGrid(gridDiv, gridOptions);
                    console.log('✓ Успешно создан через agGrid.createGrid');
                    success = true;
                }
            } catch (e) {
                console.log('✗ Ошибка через agGrid.createGrid:', e.message);
            }

            try {
                if (window.agGrid && window.agGrid.Grid && !success) {
                    new window.agGrid.Grid(gridDiv, gridOptions);
                    console.log('✓ Успешно создан через window.agGrid.Grid');
                    success = true;
                }
            } catch (e) {
                console.log('✗ Ошибка через window.agGrid.Grid:', e.message);
            }

            if (!success) {
                gridDiv.innerHTML = `
                    <div class="flex items-center justify-center h-full text-red-400">
                        <div class="text-center">
                            <p class="text-lg font-semibold">Не удалось создать AG-Grid</p>
                            <p class="text-sm text-gray-400 mt-2">Проверьте консоль для деталей</p>
                        </div>
                    </div>
                `;
            }
        }

        // Пробуем создать грид через 3 секунды
        setTimeout(tryCreateGrid, 3000);
    </script>
</body>
</html>
