@extends('template.app')

@section('title', 'Добавить новую валюту')

@section('content')
    <div class="container mx-auto p-6 max-w-lg">
        {{-- Покажем флеш-сообщение об успехе, если есть --}}
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow-lg rounded-lg p-6">
            <h2 class="text-2xl font-semibold mb-4">Добавить новую валюту</h2>

            <form action="{{ route('currencies.store') }}" method="POST">
                @csrf

                {{-- Код валюты --}}
                <div class="mb-4">
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                        Код валюты <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="code"
                        id="code"
                        value="{{ old('code') }}"
                        placeholder="Например: USD"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                    >
                    @error('code')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Название валюты --}}
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Название валюты <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        placeholder="Например: US Dollar"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                    >
                    @error('name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
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

                {{-- Кнопка «Сохранить» --}}
                <div class="flex justify-end">
                    <button
                        type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
                    >
                        Добавить
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
