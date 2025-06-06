@extends('template.app')

@section('title','Регистрация')

@section('content')
    <div class="flex items-center justify-center min-h-screen py-6">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-3xl font-extrabold text-gray-800 mb-8 text-center">Регистрация</h2>

            <form method="POST" action="{{ route('register.perform') }}" class="space-y-6">
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

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Пароль</label>
                    <div class="flex items-center space-x-2">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-gray-900
                                   placeholder-gray-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-200
                                   @error('password') border-red-500 focus:ring-red-200 @enderror"
                            placeholder="●●●●●●●●"
                        >
                        <button type="button"
                                onclick="generatePassword()"
                                class="mt-1 rounded-lg bg-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 shadow hover:bg-blue-100 transition-colors"
                        >
                            Сгенерировать
                        </button>
                    </div>
                    @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="w-full rounded-lg bg-blue-600 py-3 text-center text-lg font-semibold text-white
                           shadow-md transition-colors duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300"
                >
                    Зарегистрироваться
                </button>
            </form>
        </div>
    </div>

    <script>
        function generatePassword(length = 12) {
            const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-+=';
            let password = '';
            for (let i = 0; i < length; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.getElementById('password').value = password;
        }
    </script>
@endsection
