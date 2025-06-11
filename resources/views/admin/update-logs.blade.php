@extends('template.app')

@section('title', 'История обновлений')

@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">История обновлений</h1>

        @php
            // Словарь русских названий сущностей
            $entityNames = [
              \App\Models\Purchase::class    => 'Покупка',
              \App\Models\SaleCrypt::class   => 'Продажа крипты',
              \App\Models\Transfer::class    => 'Перевод',
              \App\Models\Payment::class     => 'Платёж',
              \App\Models\Application::class => 'Заявка',
              // при необходимости добавьте другие
            ];
        @endphp

        <div class="overflow-x-auto bg-white rounded-2xl shadow-md">
            <table class="min-w-full divide-y divide-gray-200 table-auto">
                <thead class="bg-gray-100 sticky top-0">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">#</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Пользователь</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Сущность</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Что изменилось</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Дата/время</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach($logs as $log)
                    <tr class="hover:bg-gray-50">
                        {{-- ID --}}
                        <td class="px-4 py-2">{{ $log->id }}</td>
                        {{-- Пользователь --}}
                        <td class="px-4 py-2">{{ $log->user->login ?? '—' }}</td>
                        {{-- Сущность на русском --}}
                        <td class="px-4 py-2">
                            {{ $entityNames[$log->sourceable_type] ?? class_basename($log->sourceable_type) }}
                            #{{ $log->sourceable_id }}
                        </td>
                        {{-- Что изменилось --}}
                        <td class="px-4 py-2">
                            @php $changes = json_decode($log->update, true) ?: []; @endphp
                            @if(empty($changes))
                                —
                            @else
                                <ul class="list-disc ml-5 space-y-1">
                                    @foreach($changes as $field => $value)
                                        @php
                                            $name = __("fields.{$field}");
                                            if($name === "fields.{$field}") $name = $field;
                                        @endphp
                                        <li><strong>{{ $name }}:</strong> {{ $value }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                        {{-- Дата/время --}}
                        <td class="px-4 py-2">{{ $log->created_at->format('d.m.Y H:i') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
