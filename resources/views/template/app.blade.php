<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite('resources/css/app.css')
{{--    @vite('resources/js/applications.js')--}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <title>@yield('title','Моё приложение')</title>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
@include('includes.header')

<main class="flex-1 container mx-auto p-4">
    @yield('content')
</main>
@stack('scripts')
</body>
</html>
