<header class="bg-[#0a0a0a]/80 backdrop-blur-md shadow-xl transition-all">
    <div class="container mx-auto flex items-center justify-between h-20 px-4 md:px-7">
        <a href="{{ route('applications.index') }}" class="text-3xl font-extrabold tracking-tight text-white drop-shadow-lg select-none">
            Бухгалтерия
        </a>
        <nav>
            <ul class="flex space-x-8 text-white items-center text-lg font-medium">
                @guest
                    <li><a href="#" class="hover:text-cyan-400 transition-colors">Войти</a></li>
                @endguest

                @auth
                    @if(auth()->user()->role === 'admin')
                        <li class="relative group">
                            <span class="cursor-pointer hover:text-cyan-400 transition-colors">Списки</span>
                            <ul class="hidden group-hover:block absolute left-1/2 top-[110%] -translate-x-1/2 w-52 bg-white/95 border border-gray-200 rounded-xl shadow-2xl z-10 mt-2 mt-[-10px] overflow-hidden">
                                <li>
                                    <a href="{{ route('view.exchangers') }}" class="block px-5 py-3 text-base text-gray-700 hover:bg-cyan-50 transition">Список платформ</a>
                                </li>
                                <li>
                                    <a href="{{ route('view.currencies') }}" class="block px-5 py-3 text-base text-gray-700 hover:bg-cyan-50 transition">Список валют</a>
                                </li>
                            </ul>
                        </li>
                        <li class="relative group">
                            <span class="cursor-pointer hover:text-cyan-400 transition-colors">Добавление</span>
                            <ul class="hidden group-hover:block absolute left-1/2 top-[110%] -translate-x-1/2 w-52 bg-white/95 border border-gray-200 rounded-xl shadow-2xl z-10 mt-2 mt-[-10px]overflow-hidden">
                                <li>
                                    <a href="{{ route('exchangers.create') }}" class="block px-5 py-3 text-base text-gray-700 hover:bg-cyan-50 transition">Добавить платформу</a>
                                </li>
                                <li>
                                    <a href="{{ route('view.currency.create') }}" class="block px-5 py-3 text-base text-gray-700 hover:bg-cyan-50 transition">Добавить валюту</a>
                                </li>
                                <li>
                                    <a href="{{ route('view.register.user') }}" class="block px-5 py-3 text-base text-gray-700 hover:bg-cyan-50 transition">Добавить пользователя</a>
                                </li>
                            </ul>
                        </li>
                        <li class="relative group">
                            <span class="cursor-pointer hover:text-cyan-400 transition-colors">Логи</span>
                            <ul class="hidden group-hover:block absolute left-1/2 top-[110%] -translate-x-1/2 w-52 bg-white/95 border border-gray-200 rounded-xl shadow-2xl z-10 mt-2 mt-[-10px]overflow-hidden">
                                <li>
                                    <a href="{{ route('view.user.logs') }}" class="block px-5 py-3 text-base text-gray-700 hover:bg-cyan-50 transition">Логи авторизаций пользователей</a>
                                </li>
                                <li>
                                    <a href="{{ route('view.update.logs') }}" class="block px-5 py-3 text-base text-gray-700 hover:bg-cyan-50 transition">Логи изменений заявок</a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="{{ route('wallets.history') }}" class="hover:text-cyan-400 transition-colors">История кошельков</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.exchanger.balances') }}" class="hover:text-cyan-400 transition-colors">Балансы кошельков</a>
                        </li>
                    @endif
                    <li>
                        <a href="{{ route('history.all') }}" class="hover:text-cyan-400 transition-colors">Вся история</a>
                    </li>
                    <li>
                        <button onclick="openChooseSectionModal()" class="hover:text-cyan-400 transition-colors flex items-center gap-1">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="4" stroke="currentColor" stroke-width="2" fill="none"/><path d="M8 12h8M8 16h8M8 8h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            <span>Раздел</span>
                        </button>
                    </li>
                    <li class="flex items-center gap-3">
                        <a href="{{ route('history.all') }}" class="hover:text-cyan-400 transition-colors">Балансы</a>
                        <div class="h-11 w-11 bg-cyan-600 rounded-full flex items-center justify-center text-xl font-bold uppercase shadow-md select-none">
                            {{ mb_substr(auth()->user()->login,0,2) }}
                        </div>
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="post">
                            @csrf
                            <input type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-5 rounded-xl transition shadow-md cursor-pointer" value="Выйти">
                        </form>
                    </li>
                @endauth
            </ul>
        </nav>
    </div>
</header>

@push('scripts')
    <script>
    function openChooseSectionModal() {
        const modal = document.getElementById('chooseSectionModal');
        modal.classList.remove('hidden');
        // Принудительно устанавливаем максимальный z-index
        modal.style.zIndex = '999999999';
        modal.style.position = 'fixed';
        // Также устанавливаем для всех дочерних элементов
        const modalContent = modal.querySelector('.bg-\\[\\#1F1F1F\\]');
        if (modalContent) {
            modalContent.style.zIndex = '999999999';
            modalContent.style.position = 'relative';
        }
    }
    </script>

    <!-- Модальное окно выбора раздела (в конце body) -->
    <div id="chooseSectionModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[99999999] flex items-center justify-center min-h-screen hidden" style="position: fixed !important; z-index: 99999999 !important;">
        <div class="absolute inset-0" onclick="document.getElementById('chooseSectionModal').classList.add('hidden')"></div>
        <div class="bg-[#1F1F1F] text-white rounded-xl shadow-2xl p-6 relative z-10 w-full max-w-lg" style="position: relative !important; z-index: 99999999 !important;">
            <button type="button" onclick="document.getElementById('chooseSectionModal').classList.add('hidden')" class="absolute top-3 right-3 text-gray-400 hover:text-white text-2xl">&times;</button>
            <h3 class="text-xl font-semibold mb-6 text-center">Выберите раздел</h3>
            <div class="flex flex-col gap-4 w-full">
                <form method="POST" action="{{ route('choose.section') }}" class="w-full">
                    @csrf
                    <input type="hidden" name="section" value="applications">
                    <button type="submit" class="w-full flex flex-col items-center justify-center gap-2 px-4 py-4 bg-[#232b3a] rounded-lg border border-gray-700 hover:bg-gray-800 transition disabled:opacity-60 disabled:cursor-not-allowed @if(auth()->check() && auth()->user()->role !== 'admin') opacity-60 cursor-not-allowed @endif" @if(auth()->check() && auth()->user()->role !== 'admin') disabled @endif>
                        <svg class="w-8 h-8 text-cyan-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="4" stroke="currentColor" stroke-width="2" fill="none"/><path d="M8 12h8M8 16h8M8 8h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        <span class="font-semibold">Заявки</span>
                        @if(auth()->check() && auth()->user()->role !== 'admin')
                            <span class="text-xs text-gray-400 mt-1">Только для администраторов</span>
                        @endif
                    </button>
                </form>
                <form method="POST" action="{{ route('choose.section') }}" class="w-full">
                    @csrf
                    <input type="hidden" name="section" value="dashboard">
                    <button type="submit" class="w-full flex flex-col items-center justify-center gap-2 px-4 py-4 bg-[#232b3a] rounded-lg border border-gray-700 hover:bg-gray-800 transition disabled:opacity-60 disabled:cursor-not-allowed @if(auth()->check() && auth()->user()->role !== 'admin') opacity-60 cursor-not-allowed @endif" @if(auth()->check() && auth()->user()->role !== 'admin') disabled @endif>
                        <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 13h2v-2H3v2zm4 0h2v-2H7v2zm4 0h2v-2h-2v2zm4 0h2v-2h-2v2zm4 0h2v-2h-2v2z"/><rect x="3" y="5" width="18" height="14" rx="3" stroke="currentColor" stroke-width="2" fill="none"/></svg>
                        <span class="font-semibold">Статистика</span>
                        @if(auth()->check() && auth()->user()->role !== 'admin')
                            <span class="text-xs text-gray-400 mt-1">Только для администраторов</span>
                        @endif
                    </button>
                </form>
            </div>
        </div>
    </div>
@endpush
