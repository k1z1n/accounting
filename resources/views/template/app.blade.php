<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/node_modules/flowbite/dist/flowbite.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.46.0/dist/apexcharts.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    <title>@yield('title','Моё приложение')</title>
</head>
<body class="bg-[#0a0a0a] min-h-screen flex flex-col">
@include('includes.header')

<main class="flex-1 container mx-auto p-4">
    @yield('content')
</main>
@stack('scripts')
</body>
</html>
