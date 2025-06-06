@extends('template.app')

@section('title','История авторизаций')

@section('content')
    <div class="container mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">История авторизаций</h1>
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100 sticky top-0">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">#</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Пользователь</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">IP</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">User Agent</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Дата/время</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach($loginLogs as $log)
                    <tr>
                        <td class="px-4 py-2">{{ $log->id }}</td>
                        <td class="px-4 py-2">
                            {{$log->user->login}}
                        </td>
                        <td class="px-4 py-2">{{ $log->ip }}</td>
                        <td class="px-4 py-2 truncate max-w-xs">{{ \Illuminate\Support\Str::limit($log->user_agent, 60) }}</td>
                        <td class="px-4 py-2">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('d.m.Y H:i') }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            {{ $loginLogs->links() }}
        </div>
    </div>
@endsection
