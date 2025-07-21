<!DOCTYPE html>
<html>
<head>
    <title>AG-Grid Test</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community@30.2.1/styles/ag-grid.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community@30.2.1/styles/ag-theme-alpine.css">
</head>
<body>
    <h1>AG-Grid Test</h1>
    <div id="myGrid" class="ag-theme-alpine" style="height: 500px; width: 100%;"></div>

    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community@30.2.1/dist/ag-grid-community.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('AG-Grid test page loaded');
            console.log('agGrid object:', agGrid);
            console.log('agGrid.Grid:', agGrid.Grid);

            if (typeof agGrid !== 'undefined' && agGrid.Grid) {
                const gridOptions = {
                    columnDefs: [
                        { field: 'make' },
                        { field: 'model' },
                        { field: 'price' }
                    ],
                    rowData: [
                        { make: 'Toyota', model: 'Celica', price: 35000 },
                        { make: 'Ford', model: 'Mondeo', price: 32000 },
                        { make: 'Porsche', model: 'Boxster', price: 72000 }
                    ]
                };

                const gridDiv = document.querySelector('#myGrid');
                new agGrid.Grid(gridDiv, gridOptions);
                console.log('Grid created successfully');
            } else {
                console.error('AG-Grid not available');
            }
        });
    </script>
</body>
</html>
