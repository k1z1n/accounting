@extends('template.app')

@section('title', 'Управление обменниками')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-100">Управление обменниками</h1>
        <a href="{{ route('admin.site-cookies.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
            ➕ Добавить обменник
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            Название
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            URL
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            PHPSESSID
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            Premium Session
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            WordPress
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            Статус
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                            Действия
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                    @forelse($siteCookies as $siteCookie)
                        <tr class="hover:bg-gray-700 transition duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-100">{{ $siteCookie->name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-300 max-w-xs truncate" title="{{ $siteCookie->url }}">
                                    {{ $siteCookie->url }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-300">
                                    @if($siteCookie->phpsessid)
                                        <span class="text-green-400">✓</span>
                                        <span class="text-xs text-gray-400">{{ Str::limit($siteCookie->phpsessid, 10) }}</span>
                                    @else
                                        <span class="text-red-400">✗</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-300">
                                    @if($siteCookie->premium_session_id)
                                        <span class="text-green-400">✓</span>
                                        <span class="text-xs text-gray-400">{{ Str::limit($siteCookie->premium_session_id, 10) }}</span>
                                    @else
                                        <span class="text-red-400">✗</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-300">
                                    @if($siteCookie->wordpress_logged_title && $siteCookie->wordpress_logged_value)
                                        <span class="text-green-400">✓</span>
                                        <span class="text-xs text-gray-400">Logged</span>
                                    @else
                                        <span class="text-red-400">✗</span>
                                    @endif
                                    @if($siteCookie->wordpress_sec_title && $siteCookie->wordpress_sec_value)
                                        <span class="text-green-400 ml-1">✓</span>
                                        <span class="text-xs text-gray-400">Sec</span>
                                    @else
                                        <span class="text-red-400 ml-1">✗</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    <button onclick="testConnection({{ $siteCookie->id }})"
                                            class="bg-yellow-600 hover:bg-yellow-700 text-white text-xs px-2 py-1 rounded transition duration-200">
                                        🧪 Тест
                                    </button>
                                    <div id="status-{{ $siteCookie->id }}" class="text-xs"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.site-cookies.show', $siteCookie) }}"
                                       class="text-blue-400 hover:text-blue-300 transition duration-200">
                                        👁️ Просмотр
                                    </a>
                                    <a href="{{ route('admin.site-cookies.edit', $siteCookie) }}"
                                       class="text-green-400 hover:text-green-300 transition duration-200">
                                        ✏️ Редактировать
                                    </a>
                                    <form action="{{ route('admin.site-cookies.destroy', $siteCookie) }}"
                                          method="POST"
                                          class="inline"
                                          onsubmit="return confirm('Вы уверены, что хотите удалить этот обменник?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-red-400 hover:text-red-300 transition duration-200">
                                            🗑️ Удалить
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-400">
                                Обменники не найдены
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 text-sm text-gray-400">
        <p><strong>Статус:</strong></p>
        <ul class="list-disc list-inside mt-2 space-y-1">
            <li><span class="text-green-400">✓</span> - Данные заполнены</li>
            <li><span class="text-red-400">✗</span> - Данные отсутствуют</li>
            <li><span class="text-yellow-400">🧪</span> - Кнопка тестирования подключения</li>
        </ul>
    </div>
</div>

<script>
function testConnection(siteCookieId) {
    const statusDiv = document.getElementById(`status-${siteCookieId}`);
    statusDiv.innerHTML = '<span class="text-yellow-400">⏳ Тестирование...</span>';

    fetch(`/admin/site-cookies/${siteCookieId}/test`, {
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
                status += '<span class="text-green-400">✅ Подключение успешно</span><br>';
                status += `<span class="text-xs text-gray-400">HTTP: ${result.status}</span><br>`;
                status += `<span class="text-xs text-gray-400">Размер: ${result.content_length} байт</span><br>`;

                if (result.is_login_page) {
                    status += '<span class="text-red-400 text-xs">⚠️ Страница логина</span><br>';
                } else {
                    status += '<span class="text-green-400 text-xs">✅ Не страница логина</span><br>';
                }

                if (result.has_applications) {
                    status += '<span class="text-green-400 text-xs">✅ Элементы заявок найдены</span>';
                } else {
                    status += '<span class="text-yellow-400 text-xs">⚠️ Элементы заявок не найдены</span>';
                }
            } else {
                status = `<span class="text-red-400">❌ HTTP ${result.status}</span>`;
            }

            statusDiv.innerHTML = status;
        } else {
            statusDiv.innerHTML = `<span class="text-red-400">❌ Ошибка: ${data.error}</span>`;
        }
    })
    .catch(error => {
        statusDiv.innerHTML = `<span class="text-red-400">❌ Ошибка: ${error.message}</span>`;
    });
}
</script>
@endsection
