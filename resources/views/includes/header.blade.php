<header class="bg-[#0a0a0a] shadow-sm px-4">
    <div class="container mx-auto flex items-center justify-between h-16 px-7">
        <a href="{{ route('view.main') }}" class="text-2xl font-semibold text-white">
            Бухгалтерия
        </a>
        <nav>
            <ul class="flex space-x-8 text-white items-center">
                @guest
                    <li><a href="#" class="hover:text-gray-800">Войти</a></li>
                @endguest

                @auth
                    @if(auth()->user()->role === 'admin')

                        <li class="relative group">
                            <span class="cursor-pointer hover:text-gray-800">Списки</span>
                            <ul
                                class="hidden group-hover:block absolute left-0 top-full w-48 bg-white
                                       border border-gray-200 rounded-md shadow-lg z-10 mt-0">
                                <li>
                                    <a href="{{ route('view.exchangers') }}"
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Список платформ
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('view.currencies') }}"
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Список валют
                                    </a>
                                </li>
                            </ul>
                        </li>

                        {{-- Пункт с выпадающим меню --}}
                        <li class="relative group">
                            <span class="cursor-pointer hover:text-gray-800">Добавление</span>
                            <ul
                                class="hidden group-hover:block absolute left-0 top-full w-48 bg-white
                                       border border-gray-200 rounded-md shadow-lg z-10 mt-0">
                                <li>
                                    <a href="{{ route('exchangers.create') }}"
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Добавить платформу
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('view.currency.create') }}"
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Добавить валюту
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('view.register.user') }}"
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Добавить пользователя
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="relative group">
                            <span class="cursor-pointer hover:text-gray-800">Логи</span>
                            <ul
                                class="hidden group-hover:block absolute left-0 top-full w-48 bg-white
                                       border border-gray-200 rounded-md shadow-lg z-10 mt-0">
                                <li>
                                    <a href="{{ route('view.user.logs') }}"
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Логи авторизаций пользователей
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('view.update.logs') }}"
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Логи изменений заявок
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    <li class="flex items-center gap-2">
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('wallets.history') }}">{{ auth()->user()->login }}</a>
                        @endif
                        <div class="h-9 w-9 bg-gray-400 rounded-full"></div>
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="post">
                            @csrf
                            <input type="submit"
                                   class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition"
                                   value="Выйти">
                        </form>
                    </li>
                @endauth
            </ul>
        </nav>
    </div>
</header>
