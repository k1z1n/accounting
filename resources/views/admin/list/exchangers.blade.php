@extends('template.app')

@section('title', 'Список платформ')

@section('content')
    <div class="container mx-auto px-4 py-6 space-y-6">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif
        {{-- Заголовок + кнопка --}}
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-semibold text-gray-800">Платформы</h1>
        </div>

        {{-- Карточка с таблицей --}}
        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 table-auto">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-600 uppercase">Название</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-600 uppercase">Действия</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($exchangers as $p)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $p->title }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('platforms.edit', $p) }}"
                               class="text-blue-600 hover:underline mr-4">Редактировать</a>
                            <button data-url="{{ route('platforms.destroy', $p) }}"
                                    class="btn-delete text-red-600 hover:underline">
                                Удалить
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-center text-gray-500">
                            Нет доступных платформ
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{-- Удаление: общий модал --}}
        <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
            <div class="bg-white rounded-lg p-6 w-80">
                <h3 class="text-lg font-semibold mb-4">Подтвердите удаление</h3>
                <p class="mb-6">Вы действительно хотите удалить эту запись?</p>
                <div class="flex justify-end space-x-3">
                    <button id="cancelDelete" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Отмена</button>
                    <form id="deleteForm" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Удалить</button>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded',()=>{
                // Открыть модал
                document.querySelectorAll('.btn-delete').forEach(btn=>{
                    btn.addEventListener('click', e=>{
                        e.preventDefault();
                        const form = document.getElementById('deleteForm');
                        form.action = btn.dataset.url;
                        document.getElementById('deleteModal').classList.remove('hidden');
                    });
                });
                // Отмена
                document.getElementById('cancelDelete')
                    .addEventListener('click', ()=>document.getElementById('deleteModal').classList.add('hidden'));
            });
        </script>
    </div>

@endsection
