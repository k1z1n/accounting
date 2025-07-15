@extends('template.app')

@section('title', 'Выбор платформы')

@section('content')
<div class="min-h-screen bg-gray-900 flex items-center justify-center px-4">
    <div class="max-w-4xl w-full">
        <!-- Заголовок -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-white mb-4">Выберите платформу</h1>
            <p class="text-gray-400 text-lg">Выберите приложение для работы</p>
            @if(isset($userRole))
            <div class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded-lg">
                <span class="text-sm font-medium">Ваша роль: {{ $userRole }}</span>
            </div>
            @endif
        </div>

        <!-- Карточки платформ -->
        <div class="grid md:grid-cols-2 gap-8">
            @foreach($platforms as $key => $platform)
            <div class="platform-card bg-gray-800 rounded-xl p-8 border border-gray-700 hover:border-blue-500 transition-all duration-300 cursor-pointer group"
                 data-platform="{{ $key }}">
                <!-- Иконка и заголовок -->
                <div class="text-center mb-6">
                    <div class="text-6xl mb-4 group-hover:scale-110 transition-transform duration-300">
                        {{ $platform['icon'] }}
                    </div>
                    <h2 class="text-2xl font-bold text-white mb-2">{{ $platform['name'] }}</h2>
                    <p class="text-gray-400">{{ $platform['description'] }}</p>
                </div>

                <!-- Возможности -->
                <div class="space-y-3 mb-8">
                    @foreach($platform['features'] as $feature)
                    <div class="flex items-center text-gray-300">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                        <span>{{ $feature }}</span>
                    </div>
                    @endforeach
                </div>

                <!-- Кнопка выбора -->
                <button class="platform-select-btn w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-300"
                        data-platform="{{ $key }}">
                    Выбрать платформу
                </button>
            </div>
            @endforeach
        </div>

        <!-- Дополнительная информация -->
        <div class="text-center mt-12">
            <p class="text-gray-500 text-sm">
                Вы можете изменить выбор платформы в любое время через меню профиля
            </p>
        </div>
    </div>
</div>

<!-- Модальное окно подтверждения -->
<div id="confirmModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-gray-800 rounded-xl p-6 max-w-md mx-4">
        <h3 class="text-xl font-bold text-white mb-4">Подтверждение выбора</h3>
        <p class="text-gray-300 mb-6">Вы уверены, что хотите выбрать платформу "<span id="selectedPlatformName"></span>"?</p>

        <div class="flex gap-4">
            <button id="confirmBtn" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                Подтвердить
            </button>
            <button id="cancelBtn" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                Отмена
            </button>
        </div>
    </div>
</div>

<!-- Форма для отправки выбора -->
<form id="platformForm" method="POST" action="{{ route('platform.choose') }}" style="display: none;">
    @csrf
    <input type="hidden" name="platform" id="platformInput">
</form>

<style>
.platform-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.platform-card.selected {
    border-color: #3b82f6;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.05));
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const platformCards = document.querySelectorAll('.platform-card');
    const selectButtons = document.querySelectorAll('.platform-select-btn');
    const confirmModal = document.getElementById('confirmModal');
    const confirmBtn = document.getElementById('confirmBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const platformForm = document.getElementById('platformForm');
    const platformInput = document.getElementById('platformInput');
    const selectedPlatformName = document.getElementById('selectedPlatformName');

    let selectedPlatform = null;
    const platforms = @json($platforms);

    // Обработка клика по карточке платформы
    platformCards.forEach(card => {
        card.addEventListener('click', function() {
            const platform = this.dataset.platform;
            selectPlatform(platform);
        });
    });

    // Обработка клика по кнопке выбора
    selectButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const platform = this.dataset.platform;
            selectPlatform(platform);
        });
    });

    function selectPlatform(platform) {
        selectedPlatform = platform;
        selectedPlatformName.textContent = platforms[platform].name;
        confirmModal.classList.remove('hidden');
        confirmModal.classList.add('flex');
    }

    // Подтверждение выбора
    confirmBtn.addEventListener('click', function() {
        if (selectedPlatform) {
            platformInput.value = selectedPlatform;
            platformForm.submit();
        }
    });

    // Отмена выбора
    cancelBtn.addEventListener('click', function() {
        confirmModal.classList.add('hidden');
        confirmModal.classList.remove('flex');
        selectedPlatform = null;
    });

    // Закрытие модального окна при клике вне его
    confirmModal.addEventListener('click', function(e) {
        if (e.target === this) {
            cancelBtn.click();
        }
    });
});
</script>
@endsection
