@extends('template.app')

@section('title','Вход')

@section('content')
    <div class="flex items-center justify-center min-h-screen">
        <div class="w-full max-w-md bg-[#191919] rounded-xl shadow-md border border-[#2d2d2d] p-8">
            <h2 class="text-2xl font-semibold text-white mb-6 text-center">Войти</h2>

            <form method="POST" action="{{ route('login.perform') }}" class="space-y-6">
                @csrf

                {{-- Логин --}}
                <div>
                    <label for="login" class="block text-sm font-medium text-gray-300 mb-1">Логин</label>
                    <input
                        id="login"
                        name="login"
                        type="text"
                        value="{{ old('login') }}"
                        placeholder="Введите логин"
                        class="w-full bg-[#1F1F1F] border border-[#2d2d2d] text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                    >
                    @error('login')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Пароль --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Пароль</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        placeholder="●●●●●●●●"
                        class="w-full bg-[#1F1F1F] border border-[#2d2d2d] text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                    >
                    @error('password')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Запомнить --}}
                <div class="flex items-center">
                    <input
                        id="remember"
                        name="remember"
                        type="checkbox"
                        class="h-4 w-4 rounded border-gray-600 bg-[#1F1F1F] text-cyan-500 focus:ring-cyan-400"
                    >
                    <label for="remember" class="ml-2 text-sm text-gray-300">Запомнить меня</label>
                </div>

                {{-- Кнопка --}}
                <button
                    type="submit"
                    class="w-full bg-cyan-600 hover:bg-cyan-700 text-white font-semibold py-3 rounded-lg transition focus:outline-none focus:ring-2 focus:ring-cyan-500"
                >
                    Войти
                </button>
            </form>
        </div>
    </div>
@endsection
