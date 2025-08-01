@extends('template.app')

@section('title', 'Просмотр обменника')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-100">Просмотр обменника: {{ $siteCookie->name }}</h1>
        <div class="flex space-x-4">
            <a href="{{ route('admin.site-cookies.edit', $siteCookie) }}"
               class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                ✏️ Редактировать
            </a>
            <a href="{{ route('admin.site-cookies.index') }}"
               class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                ← Назад к списку
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Основная информация -->
        <div class="bg-gray-800 rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-100 mb-4">📋 Основная информация</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Название обменника</label>
                    <div class="text-lg font-semibold text-gray-100">{{ $siteCookie->name }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">URL обменника</label>
                    <div class="text-sm text-gray-300 break-all">
                        <a href="{{ $siteCookie->url }}" target="_blank" class="text-blue-400 hover:text-blue-300">
                            {{ $siteCookie->url }}
                        </a>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Дата создания</label>
                    <div class="text-sm text-gray-300">{{ $siteCookie->created_at->format('d.m.Y H:i:s') }}</div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Последнее обновление</label>
                    <div class="text-sm text-gray-300">{{ $siteCookie->updated_at->format('d.m.Y H:i:s') }}</div>
                </div>
            </div>
        </div>

        <!-- Cookies -->
        <div class="bg-gray-800 rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-100 mb-4">🍪 Cookies</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">PHPSESSID</label>
                    <div class="text-sm text-gray-300 font-mono break-all">
                        @if($siteCookie->phpsessid)
                            <span class="text-green-400">✓</span> {{ $siteCookie->phpsessid }}
                        @else
                            <span class="text-red-400">✗</span> Не установлен
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Premium Session ID</label>
                    <div class="text-sm text-gray-300 font-mono break-all">
                        @if($siteCookie->premium_session_id)
                            <span class="text-green-400">✓</span> {{ $siteCookie->premium_session_id }}
                        @else
                            <span class="text-red-400">✗</span> Не установлен
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- WordPress Cookies -->
        <div class="bg-gray-800 rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-100 mb-4">🔐 WordPress Cookies</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">WordPress Logged Title</label>
                    <div class="text-sm text-gray-300 font-mono break-all">
                        @if($siteCookie->wordpress_logged_title)
                            <span class="text-green-400">✓</span> {{ $siteCookie->wordpress_logged_title }}
                        @else
                            <span class="text-red-400">✗</span> Не установлен
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">WordPress Logged Value</label>
                    <div class="text-sm text-gray-300 font-mono break-all">
                        @if($siteCookie->wordpress_logged_value)
                            <span class="text-green-400">✓</span> {{ $siteCookie->wordpress_logged_value }}
                        @else
                            <span class="text-red-400">✗</span> Не установлен
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">WordPress Sec Title</label>
                    <div class="text-sm text-gray-300 font-mono break-all">
                        @if($siteCookie->wordpress_sec_title)
                            <span class="text-green-400">✓</span> {{ $siteCookie->wordpress_sec_title }}
                        @else
                            <span class="text-red-400">✗</span> Не установлен
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">WordPress Sec Value</label>
                    <div class="text-sm text-gray-300 font-mono break-all">
                        @if($siteCookie->wordpress_sec_value)
                            <span class="text-green-400">✓</span> {{ $siteCookie->wordpress_sec_value }}
                        @else
                            <span class="text-red-400">✗</span> Не установлен
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Полная строка cookies -->
        <div class="bg-gray-800 rounded-lg shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-100 mb-4">🔗 Полная строка cookies</h2>

            <div class="bg-gray-700 rounded-lg p-4">
                <div class="text-sm text-gray-300 font-mono break-all">
                    @if($siteCookie->getCookiesString())
                        {{ $siteCookie->getCookiesString() }}
                    @else
                        <span class="text-red-400">Cookies не настроены</span>
                    @endif
                </div>
            </div>

            <div class="mt-4">
                <button onclick="testConnection()"
                        class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    🧪 Тестировать подключение
                </button>

                <div id="test-result" class="mt-4"></div>
            </div>
        </div>
    </div>

    <!-- Статистика -->
    <div class="mt-8 bg-gray-800 rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-semibold text-gray-100 mb-4">📊 Статистика</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-gray-700 rounded-lg p-4">
                <div class="text-2xl font-bold text-blue-400">
                    @if($siteCookie->phpsessid) 1 @else 0 @endif
                </div>
                <div class="text-sm text-gray-300">PHPSESSID</div>
            </div>

            <div class="bg-gray-700 rounded-lg p-4">
                <div class="text-2xl font-bold text-green-400">
                    @if($siteCookie->premium_session_id) 1 @else 0 @endif
                </div>
                <div class="text-sm text-gray-300">Premium Session</div>
            </div>

            <div class="bg-gray-700 rounded-lg p-4">
                <div class="text-2xl font-bold text-purple-400">
                    @if($siteCookie->wordpress_logged_title && $siteCookie->wordpress_logged_value) 1 @else 0 @endif
                </div>
                <div class="text-sm text-gray-300">WordPress Logged</div>
            </div>

            <div class="bg-gray-700 rounded-lg p-4">
                <div class="text-2xl font-bold text-orange-400">
                    @if($siteCookie->wordpress_sec_title && $siteCookie->wordpress_sec_value) 1 @else 0 @endif
                </div>
                <div class="text-sm text-gray-300">WordPress Sec</div>
            </div>

            <div class="bg-gray-700 rounded-lg p-4">
                <div class="text-2xl font-bold text-yellow-400">
                    {{ strlen($siteCookie->getCookiesString()) }}
                </div>
                <div class="text-sm text-gray-300">Длина cookies</div>
            </div>

            <div class="bg-gray-700 rounded-lg p-4">
                <div class="text-2xl font-bold text-red-400">
                    {{ $siteCookie->updated_at->diffForHumans() }}
                </div>
                <div class="text-sm text-gray-300">Обновлен</div>
            </div>
        </div>
    </div>
</div>

<script>
function testConnection() {
    const resultDiv = document.getElementById('test-result');
    resultDiv.innerHTML = '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">⏳ Тестирование подключения...</div>';

    fetch(`/admin/site-cookies/{{ $siteCookie->id }}/test`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const result = data.data;
            let status = '';

            if (result.success) {
                status = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">';
                status += '<strong>✅ Подключение успешно!</strong><br>';
                status += `HTTP Status: ${result.status}<br>`;
                status += `Размер ответа: ${result.content_length} байт<br>`;

                if (result.is_login_page) {
                    status += '<span class="text-red-600">⚠️ Обнаружена страница логина</span><br>';
                } else {
                    status += '<span class="text-green-600">✅ Не страница логина</span><br>';
                }

                if (result.has_applications) {
                    status += '<span class="text-green-600">✅ Элементы заявок найдены</span>';
                } else {
                    status += '<span class="text-yellow-600">⚠️ Элементы заявок не найдены</span>';
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
