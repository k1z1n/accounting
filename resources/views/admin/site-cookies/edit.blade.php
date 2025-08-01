@extends('template.app')

@section('title', 'Редактирование обменника')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-100">Редактирование обменника: {{ $siteCookie->name }}</h1>
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
        <form action="{{ route('admin.site-cookies.update', $siteCookie) }}" method="POST">
            @csrf
            @method('PUT')

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
                               value="{{ old('name', $siteCookie->name) }}"
                               class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
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
                               value="{{ old('url', $siteCookie->url) }}"
                               class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('url') border-red-500 @enderror"
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
                               value="{{ old('phpsessid', $siteCookie->phpsessid) }}"
                               class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('phpsessid') border-red-500 @enderror">
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
                               value="{{ old('premium_session_id', $siteCookie->premium_session_id) }}"
                               class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('premium_session_id') border-red-500 @enderror">
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
                                   value="{{ old('wordpress_logged_title', $siteCookie->wordpress_logged_title) }}"
                                   class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('wordpress_logged_title') border-red-500 @enderror"
                                   placeholder="wordpress_logged_in_...">
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
                                      placeholder="user%7Ctimestamp%7C...">{{ old('wordpress_logged_value', $siteCookie->wordpress_logged_value) }}</textarea>
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
                                   value="{{ old('wordpress_sec_title', $siteCookie->wordpress_sec_title) }}"
                                   class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('wordpress_sec_title') border-red-500 @enderror"
                                   placeholder="wordpress_sec_...">
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
                                      placeholder="user%7Ctimestamp%7C...">{{ old('wordpress_sec_value', $siteCookie->wordpress_sec_value) }}</textarea>
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
                        {{ $siteCookie->getCookiesString() ?: 'Cookies не настроены' }}
                    </div>
                </div>
            </div>

            <!-- Кнопки действий -->
            <div class="mt-8 flex justify-between items-center">
                <div class="flex space-x-4">
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                        💾 Сохранить изменения
                    </button>

                    <button type="button"
                            onclick="testConnection()"
                            class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                        🧪 Тестировать подключение
                    </button>
                </div>

                <a href="{{ route('admin.site-cookies.index') }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200">
                    ❌ Отмена
                </a>
            </div>

            <!-- Результат тестирования -->
            <div id="test-result" class="mt-4"></div>
        </form>
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

// Тестирование подключения
function testConnection() {
    const resultDiv = document.getElementById('test-result');
    resultDiv.innerHTML = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">⏳ Тестирование подключения...</div>';

    // Собираем данные формы
    const formData = new FormData(document.querySelector('form'));
    const data = Object.fromEntries(formData.entries());

    fetch(`/admin/site-cookies/{{ $siteCookie->id }}/test`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
                    if (data.success) {
                const result = data.data;
                let status = '';

                if (result.success) {
                    // Определяем цвет фона на основе авторизации
                    const bgColor = result.is_authorized ? 'bg-green-100 border-green-400 text-green-700' : 'bg-yellow-100 border-yellow-400 text-yellow-700';
                    const borderColor = result.is_authorized ? 'border-green-400' : 'border-yellow-400';

                    status = `<div class="${bgColor} border ${borderColor} px-4 py-3 rounded">`;

                    if (result.is_authorized) {
                        status += '<strong>✅ Подключение успешно! Авторизация подтверждена!</strong><br>';
                    } else {
                        status += '<strong>⚠️ Подключение работает, но авторизация не подтверждена!</strong><br>';
                    }

                    status += `HTTP Status: ${result.status}<br>`;
                    status += `Размер ответа: ${result.content_length} байт<br>`;
                    status += `Длина cookies: ${result.cookie_string_length} символов<br>`;
                    if (result.used_root_url) {
                        status += `<span class="text-blue-600">ℹ️ Тестировалась корневая страница (основная медленная)</span><br>`;
                    }
                    status += '<br>';

                    // Детальная информация
                    status += '<strong>Детальная проверка:</strong><br>';
                    status += `🔐 Страница логина: ${result.is_login_page ? 'ДА' : 'НЕТ'}<br>`;
                    status += `🏠 Панель админа: ${result.has_dashboard ? 'ДА' : 'НЕТ'}<br>`;
                    status += `👤 Меню пользователя: ${result.has_user_menu ? 'ДА' : 'НЕТ'}<br>`;
                    status += `🚪 Кнопка выхода: ${result.has_logout ? 'ДА' : 'НЕТ'}<br>`;
                    status += `📋 Элементы заявок: ${result.has_applications ? 'ДА' : 'НЕТ'}<br>`;
                    status += `❌ Ошибки авторизации: ${result.has_auth_error ? 'ДА' : 'НЕТ'}<br>`;

                    if (!result.is_authorized) {
                        status += '<br><span class="text-red-600 font-semibold">⚠️ Рекомендация: Проверьте правильность cookies!</span>';
                    }

                    status += '</div>';
                } else {
                    status = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <strong>❌ Ошибка подключения!</strong><br>
                        HTTP Status: ${result.status}
                    </div>`;
                }

                resultDiv.innerHTML = status;
        } else {
            resultDiv.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <strong>❌ Ошибка!</strong><br>
                ${data.error}
            </div>`;
        }
    })
    .catch(error => {
        resultDiv.innerHTML = `<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <strong>❌ Ошибка!</strong><br>
            ${error.message}
        </div>`;
    });
}
</script>
@endsection
