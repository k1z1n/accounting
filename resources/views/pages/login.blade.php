@extends('template.app')

@section('title','Вход')

@section('content')
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="w-full max-w-md">
            <!-- Заголовок -->
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-white mb-2">Вход в систему</h1>
                <p class="text-gray-400">Введите свои данные для входа</p>
            </div>

            <!-- Форма входа -->
            <div class="bg-[#191919] rounded-xl shadow-md border border-[#2d2d2d] p-8">

            <form method="POST" action="{{ route('login.perform') }}" class="space-y-6">
                @csrf

                    <!-- Логин -->
                <div>
                        <label for="login" class="block text-sm font-medium text-gray-300 mb-2">
                            Логин
                        </label>
                    <input
                        id="login"
                        name="login"
                        type="text"
                        value="{{ old('login') }}"
                            placeholder="Введите ваш логин"
                            class="w-full px-4 py-3 bg-[#0f0f0f] border border-[#2d2d2d] rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('login') border-red-500 @enderror"
                            autocomplete="username"
                    >
                    @error('login')
                            <div class="text-red-400 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                    <!-- Пароль -->
                <div>
                        <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                            Пароль
                        </label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                            placeholder="Введите ваш пароль"
                            class="w-full px-4 py-3 bg-[#0f0f0f] border border-[#2d2d2d] rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('password') border-red-500 @enderror"
                            autocomplete="current-password"
                    >
                    @error('password')
                            <div class="text-red-400 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                    <!-- Запомнить меня -->
                <div class="flex items-center">
                    <input
                        id="remember"
                        name="remember"
                        type="checkbox"
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 rounded bg-gray-700"
                    >
                        <label for="remember" class="ml-2 text-sm text-gray-300">
                            Запомнить меня
                        </label>
                </div>

                    <!-- Кнопка входа -->
                <button
                    type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-800"
                >
                        Войти в систему
                </button>
            </form>
            </div>

            <!-- Футер -->
            <div class="text-center mt-6">
                <p class="text-sm text-gray-400">
                    Нужна помощь?
                    <a href="#" class="text-blue-400 hover:text-blue-300 transition-colors" onclick="alert('Обратитесь к администратору для создания аккаунта')">
                        Связаться с поддержкой
                    </a>
                </p>
            </div>
        </div>
    </div>
@endsection

