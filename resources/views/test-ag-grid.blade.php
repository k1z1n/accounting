@extends('template.app')

@section('title', 'Тест AG Grid')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold text-white mb-4">Тест AG Grid</h1>

    <div class="bg-gray-800 p-4 rounded">
        <h2 class="text-white mb-4">Простая таблица AG Grid:</h2>
        <div id="myGrid" class="ag-theme-alpine" style="height: 400px; width: 100%;"></div>
    </div>

    <div class="mt-4 bg-gray-800 p-4 rounded">
        <h2 class="text-white mb-4">Консоль браузера:</h2>
        <div id="console" class="bg-black text-green-400 p-4 rounded font-mono text-sm" style="height: 200px; overflow-y: auto;">
            <div>Откройте консоль браузера (F12) для просмотра логов...</div>
        </div>
    </div>
</div>

<script>
// Простой тест AG Grid
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM загружен');

    // Проверяем, загружен ли AG Grid
    if (typeof agGrid !== 'undefined') {
        console.log('AG Grid загружен:', agGrid);

        // Простые данные для теста
        const rowData = [
            { id: 1, name: 'Тест 1', value: 100 },
            { id: 2, name: 'Тест 2', value: 200 },
            { id: 3, name: 'Тест 3', value: 300 }
        ];

        const columnDefs = [
            { headerName: "ID", field: "id", width: 100 },
            { headerName: "Имя", field: "name", width: 200 },
            { headerName: "Значение", field: "value", width: 150 }
        ];

        const gridOptions = {
            columnDefs: columnDefs,
            rowData: rowData,
            pagination: true,
            paginationPageSize: 10
        };

        const gridDiv = document.querySelector('#myGrid');
        if (gridDiv) {
            console.log('Создаем грид...');
            const grid = agGrid.createGrid(gridDiv, gridOptions);
            console.log('Грид создан:', grid);
        } else {
            console.error('Контейнер грида не найден');
        }
    } else {
        console.error('AG Grid не загружен!');
    }
});

// Функция для добавления сообщений в консоль на странице
function addToConsole(message) {
    const consoleDiv = document.getElementById('console');
    if (consoleDiv) {
        const div = document.createElement('div');
        div.textContent = new Date().toLocaleTimeString() + ': ' + message;
        consoleDiv.appendChild(div);
        consoleDiv.scrollTop = consoleDiv.scrollHeight;
    }
}

// Перехватываем console.log для отображения на странице
const originalLog = console.log;
console.log = function(...args) {
    originalLog.apply(console, args);
    addToConsole(args.join(' '));
};

const originalError = console.error;
console.error = function(...args) {
    originalError.apply(console, args);
    addToConsole('ERROR: ' + args.join(' '));
};
</script>
@endsection
