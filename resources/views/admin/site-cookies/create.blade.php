@extends('template.app')

@section('title', 'Создание нового обменника')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-100">Создание нового обменника</h1>
        <a href="{{ route('admin.site-cookies.index') }}"
           class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
            ← Назад к списку
        </a>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-gray-800 rounded-lg shadow-lg p-6">
        <form action="{{ route('admin.site-cookies.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Основная информация -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-100 mb-4">Основная информация</h3>

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                            Название обменника *
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                               placeholder="OBAMA, URAL, NEW_EXCHANGER"
                               required>
                        @error('name')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="url" class="block text-sm font-medium text-gray-300 mb-2">
                            URL обменника *
                        </label>
                        <input type="url"
                               id="url"
                               name="url"
                               value="{{ old('url') }}"
                               class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('url') border-red-500 @enderror"
                               placeholder="https://example.com/wp-admin/admin.php?page=pn_bids"
                               required>
                        @error('url')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Cookies -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-100 mb-4">Cookies</h3>

                    <div>
                        <label for="phpsessid" class="block text-sm font-medium text-gray-300 mb-2">
                            PHPSESSID
                        </label>
                        <input type="text"
                               id="phpsessid"
                               name="phpsessid"
                               value="{{ old('phpsessid') }}"
                               class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('phpsessid') border-red-500 @enderror"
                               placeholder="53533259006bfccc44abd1b7dc373297">
                        @error('phpsessid')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="premium_session_id" class="block text-sm font-medium text-gray-300 mb-2">
                            Premium Session ID
                        </label>
                        <input type="text"
                               id="premium_session_id"
                               name="premium_session_id"
                               value="{{ old('premium_session_id') }}"
                               class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('premium_session_id') border-red-500 @enderror"
                               placeholder="YrikZAhubfQ0TvxPrDEg2PZ3yp2taQtMXYFSlsISy4l1vIcehugHXjrI5gFBn0nL">
                        @error('premium_session_id')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- WordPress Cookies -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-100 mb-4">WordPress Cookies</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label for="wordpress_logged_title" class="block text-sm font-medium text-gray-300 mb-2">
                                WordPress Logged Title
                            </label>
                            <input type="text"
                                   id="wordpress_logged_title"
                                   name="wordpress_logged_title"
                                   value="{{ old('wordpress_logged_title') }}"
                                   class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('wordpress_logged_title') border-red-500 @enderror"
                                   placeholder="wordpress_logged_in_000f37c7c9e29bc682c1113c4ab6ebfa">
                            @error('wordpress_logged_title')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="wordpress_logged_value" class="block text-sm font-medium text-gray-300 mb-2">
                                WordPress Logged Value
                            </label>
                            <textarea id="wordpress_logged_value"
                                      name="wordpress_logged_value"
                                      rows="3"
                                      class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('wordpress_logged_value') border-red-500 @enderror"
                                      placeholder="martyn%7C1754395239%7C2cKPrjkrx7SLvIsMCHqB35FtLssVWUbW3bPr9hvk30P%7Ca2bb4c3661e372555a38fdc96f908689ef0c6a90a2ec0d38566ece7248a60f7a">{{ old('wordpress_logged_value') }}</textarea>
                            @error('wordpress_logged_value')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label for="wordpress_sec_title" class="block text-sm font-medium text-gray-300 mb-2">
                                WordPress Sec Title
                            </label>
                            <input type="text"
                                   id="wordpress_sec_title"
                                   name="wordpress_sec_title"
                                   value="{{ old('wordpress_sec_title') }}"
                                   class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('wordpress_sec_title') border-red-500 @enderror"
                                   placeholder="wordpress_sec_000f37c7c9e29bc682c1113c4ab6ebfa">
                            @error('wordpress_sec_title')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="wordpress_sec_value" class="block text-sm font-medium text-gray-300 mb-2">
                                WordPress Sec Value
                            </label>
                            <textarea id="wordpress_sec_value"
                                      name="wordpress_sec_value"
                                      rows="3"
                                      class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('wordpress_sec_value') border-red-500 @enderror"
                                      placeholder="martyn%7C1754395239%7C2cKPrjkrx7SLvIsMCHqB35FtLssVWUbW3bPr9hvk30P%7C54597b86096027ebcbc6e915d9aff97bc7bde6e97cf571d667decdc42cb18da8">{{ old('wordpress_sec_value') }}</textarea>
                            @error('wordpress_sec_value')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Предварительный просмотр cookies -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-100 mb-4">Предварительный просмотр cookies</h3>
                <div class="bg-gray-700 rounded-lg p-4">
                    <div class="text-sm text-gray-300 font-mono break-all" id="cookies-preview">
                        Cookies не настроены
                    </div>
                </div>
            </div>

            <!-- Кнопки действий -->
            <div class="mt-8 flex justify-between items-center">
                <div class="flex space-x-4">
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                        💾 Создать обменник
                    </button>
                </div>

                <a href="{{ route('admin.site-cookies.index') }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                    ❌ Отмена
                </a>
            </div>
        </form>
    </div>

    <!-- Инструкции -->
    <div class="mt-8 bg-gray-800 rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-100 mb-4">📋 Инструкции по заполнению</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-gray-300">
            <div>
                <h4 class="font-semibold text-gray-100 mb-2">Основная информация:</h4>
                <ul class="list-disc list-inside space-y-1">
                    <li><strong>Название:</strong> Уникальное имя обменника (например: OBAMA, URAL)</li>
                    <li><strong>URL:</strong> Полный URL страницы с заявками</li>
                </ul>
            </div>

            <div>
                <h4 class="font-semibold text-gray-100 mb-2">Cookies:</h4>
                <ul class="list-disc list-inside space-y-1">
                    <li><strong>PHPSESSID:</strong> Идентификатор сессии PHP</li>
                    <li><strong>Premium Session ID:</strong> Идентификатор премиум сессии</li>
                </ul>
            </div>

            <div>
                <h4 class="font-semibold text-gray-100 mb-2">WordPress Cookies:</h4>
                <ul class="list-disc list-inside space-y-1">
                    <li><strong>Logged Title:</strong> Название cookie для авторизованного пользователя</li>
                    <li><strong>Logged Value:</strong> Значение cookie авторизации</li>
                    <li><strong>Sec Title:</strong> Название cookie безопасности</li>
                    <li><strong>Sec Value:</strong> Значение cookie безопасности</li>
                </ul>
            </div>

            <div>
                <h4 class="font-semibold text-gray-100 mb-2">Как получить cookies:</h4>
                <ul class="list-disc list-inside space-y-1">
                    <li>Войдите в админку обменника в браузере</li>
                    <li>Откройте Developer Tools (F12)</li>
                    <li>Перейдите на вкладку Application/Storage → Cookies</li>
                    <li>Скопируйте нужные значения</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Обновление предварительного просмотра cookies при изменении полей
function updateCookiesPreview() {
    const fields = ['phpsessid', 'premium_session_id', 'wordpress_logged_title', 'wordpress_logged_value', 'wordpress_sec_title', 'wordpress_sec_value'];
    const cookies = [];

    fields.forEach(field => {
        const value = document.getElementById(field).value.trim();
        if (value) {
            if (field === 'wordpress_logged_title' && document.getElementById('wordpress_logged_value').value.trim()) {
                cookies.push(`${value}=${document.getElementById('wordpress_logged_value').value.trim()}`);
            } else if (field === 'wordpress_sec_title' && document.getElementById('wordpress_sec_value').value.trim()) {
                cookies.push(`${value}=${document.getElementById('wordpress_sec_value').value.trim()}`);
            } else if (field === 'phpsessid') {
                cookies.push(`PHPSESSID=${value}`);
            } else if (field === 'premium_session_id') {
                cookies.push(`premium_session_id=${value}`);
            }
        }
    });

    const preview = cookies.length > 0 ? cookies.join('; ') : 'Cookies не настроены';
    document.getElementById('cookies-preview').textContent = preview;
}

// Добавляем обработчики событий для обновления предварительного просмотра
document.addEventListener('DOMContentLoaded', function() {
    const fields = ['phpsessid', 'premium_session_id', 'wordpress_logged_title', 'wordpress_logged_value', 'wordpress_sec_title', 'wordpress_sec_value'];
    fields.forEach(field => {
        const element = document.getElementById(field);
        if (element) {
            element.addEventListener('input', updateCookiesPreview);
        }
    });
});
</script>
@endsection
