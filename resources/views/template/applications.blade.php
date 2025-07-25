<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- Стили и скрипты -->
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')

    <!-- Meta токены и библиотеки -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.46.0/dist/apexcharts.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- AG Grid CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community@30.2.1/styles/ag-grid.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community@30.2.1/styles/ag-theme-alpine.css">
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community@30.2.1/dist/ag-grid-community.min.js"></script>

    @stack('styles')
</head>
<body class="bg-[#0a0a0a] min-h-screen flex flex-col" data-is-admin="{{ auth()->user()->role === 'admin' ? 'true' : 'false' }}">
@include('includes.header')

<main class="flex-1 container mx-auto p-4">
    @yield('content')
</main>

@stack('scripts')
</body>
</html>
