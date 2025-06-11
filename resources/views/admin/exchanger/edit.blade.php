@extends('template.app')
@section('title','Редактировать платформу')

@section('content')
    <h1 class="text-2xl font-semibold mb-4">Редактировать платформу</h1>
    <form action="{{ route('platforms.update',$platform) }}" method="POST" class="bg-white p-6 rounded shadow">
        @csrf @method('PUT')
        <div class="mb-4">
            <label class="block mb-1">Название</label>
            <input name="title" class="w-full border px-3 py-2 rounded" value="{{ old('title',$platform->title) }}">
            @error('title')<p class="text-red-600">{{ $message }}</p>@enderror
        </div>
        <button class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Обновить</button>
    </form>
@endsection
