<header class="bg-[#0a0a0a]/80 backdrop-blur-md shadow-xl transition-all">
    <div class="container mx-auto flex items-center justify-between h-20 px-4 md:px-7">
        <a href="{{ route('view.main') }}" class="text-3xl font-extrabold tracking-tight text-white drop-shadow-lg select-none">
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
                            <ul class="hidden group-hover:block absolute left-1/2 top-[110%] -translate-x-1/2 w-52 bg-white/95 border border-gray-200 rounded-xl shadow-2xl z-10 mt-2 overflow-hidden">
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
                            <ul class="hidden group-hover:block absolute left-1/2 top-[110%] -translate-x-1/2 w-52 bg-white/95 border border-gray-200 rounded-xl shadow-2xl z-10 mt-2 overflow-hidden">
                                <li>
                                    <a href="{{ route('view.user.logs') }}" class="block px-5 py-3 text-base text-gray-700 hover:bg-cyan-50 transition">Логи авторизаций пользователей</a>
                                </li>
                                <li>
                                    <a href="{{ route('view.update.logs') }}" class="block px-5 py-3 text-base text-gray-700 hover:bg-cyan-50 transition">Логи изменений заявок</a>
                                </li>
                            </ul>
                        </li>
                    @endif
                    <li class="flex items-center gap-3">
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('wallets.history') }}" class="hover:text-cyan-400 transition-colors">{{ auth()->user()->login }}</a>
                        @endif
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
