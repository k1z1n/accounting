@extends('template.app')

@section('title','Вход')

@section('content')
    <div class="flex items-center justify-center min-h-screen py-6">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-3xl font-extrabold text-gray-800 mb-8 text-center">Вход</h2>

            <form method="POST" action="{{ route('login.perform') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="login" class="block text-sm font-medium text-gray-700">Логин</label>
                    <input
                        id="login"
                        name="login"
                        type="text"
                        value="{{ old('login') }}"
                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-900
           placeholder-gray-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200
           @error('login') border-red-500 focus:ring-red-200 @enderror"
                        placeholder="Введите логин"
                    >
                    @error('login')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Пароль</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-900
                           placeholder-gray-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200
                           @error('password') border-red-500 focus:ring-red-200 @enderror"
                        placeholder="●●●●●●●●"
                    >
                    @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember --}}
                <div class="flex items-center">
                    <input
                        id="remember"
                        name="remember"
                        type="checkbox"
                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    >
                    <label for="remember" class="ml-2 block text-sm text-gray-700">Запомнить меня</label>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full rounded-lg bg-green-600 py-3 text-lg font-semibold text-white shadow-md
                       transition-colors duration-200 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300"
                >
                    Войти
                </button>

                {{-- Link to register --}}
{{--                <p class="text-center text-sm text-gray-600">--}}
{{--                    Нет аккаунта?--}}
{{--                    <a href="{{ route('view.register') }}" class="font-medium text-blue-600 hover:underline">Зарегистрироваться</a>--}}
{{--                </p>--}}
            </form>
        </div>
    </div>
@endsection
