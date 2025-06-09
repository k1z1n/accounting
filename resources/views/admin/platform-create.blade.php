@extends('template.app')

@section('title', 'Добавить новую платформу')

@section('content')
    <div class="container mx-auto p-6 max-w-lg">
        {{-- Флеш-сообщение об успехе --}}
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow-lg rounded-lg p-6">
            <h2 class="text-2xl font-semibold mb-4">Добавить новую платформу</h2>

            <form action="{{ route('exchangers.store') }}" method="POST">
                @csrf

                {{-- Название платформы --}}
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                        Название платформы <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="title"
                        id="title"
                        value="{{ old('title') }}"
                        placeholder="Например: Ural-Obmen"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400"
                    >
                    @error('title')
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
