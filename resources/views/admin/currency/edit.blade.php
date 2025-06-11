@extends('template.app')
@section('title','Редактировать валюту - ' . $currency->title)

@section('content')
    <h1 class="text-2xl font-semibold mb-4">Редактировать валюту</h1>
    <form action="{{ route('currencies.update',$currency) }}" method="POST" class="bg-white p-6 rounded shadow">
        @csrf @method('PUT')
        <div class="mb-4">
            <label class="block mb-1">Код</label>
            <input name="code" class="w-full border px-3 py-2 rounded" value="{{ old('code',$currency->code) }}">
            @error('code')<p class="text-red-600">{{ $message }}</p>@enderror
        </div>
        {{-- Цвет валюты --}}
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                Цвет валюты <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                name="color"
                id="color"
                value="{{ old('color') }}"
                placeholder="000000"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
            >
            @error('color')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-4">
            <label class="block mb-1">Название</label>
            <input name="name" class="w-full border px-3 py-2 rounded" value="{{ old('name',$currency->name) }}">
            @error('name')<p class="text-red-600">{{ $message }}</p>@enderror
        </div>
        <button class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Обновить</button>
    </form>
@endsection
