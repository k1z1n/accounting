    {{-- resources/views/wallets/dashboard.blade.php --}}
    @extends('template.app')

    @section('title', 'Мои кошельки')

    @section('content')
        <div class="container mx-auto px-4 py-6">

            {{-- Статичная шапка --}}
            <div class="bg-[#191919] rounded-2xl p-6 mb-6 flex flex-col md:flex-row md:justify-between items-start md:items-center text-white">
                <div>
                    <div class="text-gray-400">Общий баланс</div>
                    <div class="text-4xl font-bold">$0.00</div>
                    <div class="text-gray-500">Доступно: $0.00</div>
                </div>
                <div class="mt-4 md:mt-0 flex space-x-3">
                    <button class="px-4 py-2 bg-gray-800 rounded-full hover:bg-gray-700 transition">Получить</button>
                    <button class="px-4 py-2 bg-gray-800 rounded-full hover:bg-gray-700 transition">Отправить</button>
                    <button class="px-4 py-2 bg-gray-800 rounded-full hover:bg-gray-700 transition">Конверт</button>
                    <button class="px-4 py-2 bg-gray-800 rounded-full hover:bg-gray-700 transition">Перевести</button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Левая колонка: выбор провайдера и балансы --}}
                <div class="bg-[#191919] rounded-2xl shadow-md p-6 space-y-4 text-white">
                    <h3 class="text-xl font-semibold">Активы</h3>
                    <div class="flex space-x-4 mb-4">
                        <!-- Провайдер -->
                        <div class="flex-1">
                            <label for="provider" class="block text-sm font-medium text-gray-300 mb-1">Провайдер</label>
                            <div class="relative">
                                <select
                                        id="provider"
                                        class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg
                   px-4 py-2 pr-8
                   appearance-none
                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                   transition duration-150 ease-in-out"
                                >
                                    <option value="heleket">Heleket</option>
                                    <option value="rapira">Rapira</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Обменник -->
                        <div class="flex-1">
                            <label for="exchanger" class="block text-sm font-medium text-gray-300 mb-1">Обменник</label>
                            <div class="relative">
                                <select
                                        id="exchanger"
                                        class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg
                   px-4 py-2 pr-8
                   appearance-none
                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                   transition duration-150 ease-in-out"
                                >
                                    <option value="obama">Obama</option>
                                    <option value="ural">Ural</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <h4 class="font-semibold mb-2">Merchant</h4>
                            <ul id="balancesMerchant" class="divide-y divide-gray-700 text-sm max-h-[400px] overflow-auto">
                                <li class="py-4 text-center text-gray-500">— Нет данных —</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold mb-2">Пользователь</h4>
                            <ul id="balancesUser" class="divide-y divide-gray-700 text-sm max-h-[400px] overflow-auto">
                                <li class="py-4 text-center text-gray-500">— Нет данных —</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Правая колонка: история --}}
                <div class="bg-[#191919] rounded-2xl shadow-md p-6 space-y-4 text-white">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-semibold">История</h3>
                        <a href="#" class="text-sm text-gray-400 hover:underline">Все транзакции →</a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h4 class="font-semibold mb-2">Merchant</h4>
                            <ul id="historyMerchant" class="divide-y divide-gray-700 text-sm max-h-[400px] overflow-auto">
                                <li class="py-4 text-center text-gray-500">— Нет данных —</li>
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-semibold mb-2">Пользователь</h4>
                            <ul id="historyUser" class="divide-y divide-gray-700 text-sm max-h-[400px] overflow-auto">
                                <li class="py-4 text-center text-gray-500">— Нет данных —</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const providerEl   = document.getElementById('provider');
                const exchangerEl  = document.getElementById('exchanger');
                const balMerchUl   = document.getElementById('balancesMerchant');
                const balUserUl    = document.getElementById('balancesUser');
                const histMerchUl  = document.getElementById('historyMerchant');
                const histUserUl   = document.getElementById('historyUser');

                function splitMU(payload) {
                    console.log('splitMU payload:', payload);
                    if (!payload) return { merchant: [], user: [] };
                    // если пришёл объект с разделением
                    if (payload.merchant || payload.user) {
                        const m = Array.isArray(payload.merchant) ? payload.merchant : [];
                        const u = Array.isArray(payload.user)     ? payload.user     : [];
                        console.log('splitMU via keys → merchant:', m.length, 'user:', u.length);
                        return { merchant: m, user: u };
                    }
                    // если просто массив — делим пополам
                    if (Array.isArray(payload)) {
                        const half = Math.ceil(payload.length / 2);
                        const m = payload.slice(0, half);
                        const u = payload.slice(half);
                        console.log('splitMU flat array → merchant:', m.length, 'user:', u.length);
                        return { merchant: m, user: u };
                    }
                    return { merchant: [], user: [] };
                }

                async function loadBalances() {
                    balMerchUl.innerHTML = balUserUl.innerHTML =
                        '<li class="py-4 text-center text-gray-500">Загрузка…</li>';
                    try {
                        const url = `{{ route('api.wallets.balances') }}?provider=${providerEl.value}&exchanger=${exchangerEl.value}`;
                        console.log('Fetching balances from:', url);
                        const resp = await fetch(url, { headers:{ 'X-Requested-With':'XMLHttpRequest' } });
                        const json = await resp.json();
                        console.log('Balances API response:', json);
                        if (!resp.ok) throw new Error(json.error || 'Ошибка');

                        // теперь берем json.balances
                        const arr = json.balances || [];
                        console.log('Extracted balances array length:', arr.length);
                        const { merchant, user } = splitMU(arr);

                        // merchant
                        if (merchant.length === 0) {
                            balMerchUl.innerHTML = '<li class="py-4 text-center text-gray-500">Пусто</li>';
                        } else {
                            balMerchUl.innerHTML = merchant.map(b => `
              <li class="flex justify-between py-3">
                <div class="flex items-center space-x-2">
                  <img src="/images/coins/${b.code}.svg" alt="${b.code}" class="w-5 h-5">
                  <span>${b.code}</span>
                </div>
                <span class="font-medium">${Number(b.amount).toLocaleString('ru-RU',{ maximumFractionDigits:8 })}</span>
              </li>
            `).join('');
                        }

                        // user
                        if (user.length === 0) {
                            balUserUl.innerHTML = '<li class="py-4 text-center text-gray-500">Пусто</li>';
                        } else {
                            balUserUl.innerHTML = user.map(b => `
              <li class="flex justify-between py-3">
                <div class="flex items-center space-x-2">
                  <img src="/images/coins/${b.code}.svg" alt="${b.code}" class="w-5 h-5">
                  <span>${b.code}</span>
                </div>
                <span class="font-medium">${Number(b.amount).toLocaleString('ru-RU',{ maximumFractionDigits:8 })}</span>
              </li>
            `).join('');
                        }

                    } catch (err) {
                        console.error('loadBalances error:', err);
                        balMerchUl.innerHTML = balUserUl.innerHTML =
                            `<li class="py-4 text-center text-red-600">${err.message}</li>`;
                    }
                }

                async function loadHistory() {
                    histMerchUl.innerHTML = histUserUl.innerHTML =
                        '<li class="py-4 text-center text-gray-500">Загрузка…</li>';
                    try {
                        const url = `{{ route('api.wallets.history') }}`;
                        console.log('Fetching history from:', url);
                        const resp = await fetch(url, { headers:{ 'X-Requested-With':'XMLHttpRequest' } });
                        const json = await resp.json();
                        console.log('History API response:', json);
                        if (!resp.ok) throw new Error(json.error || 'Ошибка');

                        const arr = json.history || [];
                        console.log('Extracted history array length:', arr.length);
                        const { merchant, user } = splitMU(arr);

                        // merchant
                        if (merchant.length === 0) {
                            histMerchUl.innerHTML = '<li class="py-4 text-center text-gray-500">Нет записей</li>';
                        } else {
                            histMerchUl.innerHTML = merchant.map(tx => `
              <li class="flex justify-between py-3">
                <div>
                  <div class="font-medium">${tx.type}</div>
                  <div class="text-gray-400 text-xs">${tx.date}</div>
                </div>
                <div class="flex items-center space-x-1">
                  <span class="${tx.amount>0?'text-green-400':'text-red-400'}">
                    ${tx.amount>0?'+':''}${tx.amount}
                  </span>
                  <span class="text-gray-300">${tx.currency}</span>
                </div>
              </li>
            `).join('');
                        }

                        // user
                        if (user.length === 0) {
                            histUserUl.innerHTML = '<li class="py-4 text-center text-gray-500">Нет записей</li>';
                        } else {
                            histUserUl.innerHTML = user.map(tx => `
              <li class="flex justify-between py-3">
                <div>
                  <div class="font-medium">${tx.type}</div>
                  <div class="text-gray-400 text-xs">${tx.date}</div>
                </div>
                <div class="flex items-center space-x-1">
                  <span class="${tx.amount>0?'text-green-400':'text-red-400'}">
                    ${tx.amount>0?'+':''}${tx.amount}
                  </span>
                  <span class="text-gray-300">${tx.currency}</span>
                </div>
              </li>
            `).join('');
                        }

                    } catch (err) {
                        console.error('loadHistory error:', err);
                        histMerchUl.innerHTML = histUserUl.innerHTML =
                            `<li class="py-4 text-center text-red-600">${err.message}</li>`;
                    }
                }

                function refreshAll() {
                    loadBalances();
                    loadHistory();
                }

                providerEl.addEventListener('change', refreshAll);
                exchangerEl.addEventListener('change', refreshAll);

                // инициализация
                refreshAll();
            });
        </script>
    @endsection
