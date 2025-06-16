@extends('template.app')

@section('title', 'Список валют')

@section('content')
    <div class="container mx-auto px-4 py-6 space-y-6">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif
        {{-- Заголовок + кнопка --}}
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-semibold text-gray-800">Валюты</h1>
        </div>

        {{-- Карточка с таблицей --}}
        <div class="bg-white rounded-2xl shadow-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 table-auto">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-600 uppercase">Код</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-600 uppercase">Название</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-600 uppercase">Цвет</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-600 uppercase">Действия</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($currencies as $c)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $c->code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $c->name }}</td>
                        @if($c->color)
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $c->color }}</td>
                        @else
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900">-</td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('currencies.edit', $c) }}"
                               class="text-blue-600 hover:underline mr-4">Редактировать</a>
                            <button data-url="{{ route('currencies.destroy', $c) }}"
                                    class="btn-delete text-red-600 hover:underline">
                                Удалить
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                            Нет доступных валют
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{-- ... внутри <body>, после основного контента ... --}}
        <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-80">
                <h3 class="text-lg font-semibold mb-4">Подтверждение удаления</h3>
                <p class="mb-6 text-gray-700">Вы действительно хотите удалить?</p>
                <div class="flex justify-end space-x-3">
                    <button id="cancelDelete"
                            class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                        Отмена
                    </button>
                    <form id="deleteForm" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                            Удалить
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Открыть модал
                document.querySelectorAll('.btn-delete').forEach(btn => {
                    btn.addEventListener('click', e => {
                        e.preventDefault();
                        const form = document.getElementById('deleteForm');
                        form.action = btn.dataset.url;
                        document.getElementById('deleteModal').classList.remove('hidden');
                    });
                });
                // Отмена
                document.getElementById('cancelDelete')
                    .addEventListener('click', () => document.getElementById('deleteModal').classList.add('hidden'));
            });
        </script>
    </div>

@endsection
