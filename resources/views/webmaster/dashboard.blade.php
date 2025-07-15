@extends('template.app')

@section('title', '🔍 Яндекс.Вебмастер - SEO Аналитика')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-8 mb-8">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                        🔍 SEO Аналитика
                    </h1>
                    <p class="text-slate-600 mt-2 text-lg">Статистика Яндекс.Вебмастера</p>
                </div>
                <div class="flex gap-3">
                    <button onclick="location.reload()" class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        🔄 Обновить
                    </button>
                    <button onclick="exportData()" class="bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        📊 Экспорт
                    </button>
                </div>
            </div>
        </div>

        <!-- Site Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            @foreach($stats['sites'] as $siteName => $siteData)
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/30 p-6 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-slate-800">{{ ucfirst($siteName) }}</h3>
                        @if(empty($siteData['error']))
                            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">✅ Активен</span>
                        @else
                            <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">❌ Ошибка</span>
                        @endif
                    </div>

                    @if(empty($siteData['error']))
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-600">Индексация:</span>
                                <span class="font-semibold text-blue-600">{{ number_format($siteData['indexing']['indexed_pages'] ?? 0) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-600">Ошибки:</span>
                                <span class="font-semibold text-orange-600">{{ count($siteData['crawl_errors'] ?? []) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-600">Ссылки:</span>
                                <span class="font-semibold text-green-600">{{ count($siteData['external_links'] ?? []) }}</span>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="text-slate-400 mb-2">⚠️</div>
                            <p class="text-sm text-slate-600">{{ $siteData['error'] }}</p>
                            <a href="https://webmaster.yandex.ru/" target="_blank" class="text-blue-500 hover:text-blue-600 text-sm underline mt-2 inline-block">
                                Настроить в Вебмастере
                            </a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Main Stats Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Search Analytics Chart -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/30 p-6">
                <h3 class="text-xl font-semibold text-slate-800 mb-6 flex items-center">
                    📈 Поисковая аналитика
                    <span class="ml-auto text-sm text-slate-500">за месяц</span>
                </h3>
                <div class="h-80 flex items-center justify-center text-slate-500">
                    <div class="text-center">
                        <div class="text-6xl mb-4">📊</div>
                        <p>График поисковой аналитики</p>
                        <p class="text-sm">Данные будут доступны после настройки API</p>
                    </div>
                </div>
            </div>

            <!-- Top Queries -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/30 p-6">
                <h3 class="text-xl font-semibold text-slate-800 mb-6 flex items-center">
                    🔍 Топ запросы
                    <span class="ml-auto text-sm text-slate-500">CTR</span>
                </h3>
                <div class="space-y-4 max-h-80 overflow-y-auto">
                    @php
                        $allQueries = [];
                        foreach($stats['sites'] as $siteData) {
                            if(!empty($siteData['search_analytics'])) {
                                $allQueries = array_merge($allQueries, $siteData['search_analytics']);
                            }
                        }
                        $topQueries = array_slice(array_filter($allQueries, function($q) { return !empty($q['query']); }), 0, 10);
                    @endphp

                    @forelse($topQueries as $query)
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg hover:bg-slate-100 transition-colors">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-900 truncate">{{ $query['query'] ?: 'Скрытый запрос' }}</p>
                                <p class="text-xs text-slate-500">{{ number_format($query['impressions'] ?? 0) }} показов • {{ number_format($query['clicks'] ?? 0) }} кликов</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-semibold text-blue-600">{{ round($query['ctr'] ?? 0, 1) }}%</span>
                                <span class="text-xs text-slate-400">pos {{ round($query['position'] ?? 0, 1) }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <div class="text-slate-400 text-4xl mb-4">🔍</div>
                            <p class="text-slate-600">Нет данных о поисковых запросах</p>
                            <p class="text-sm text-slate-500 mt-2">Данные появятся после настройки API</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Indexing & Errors Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Indexing Stats -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/30 p-6">
                <h3 class="text-xl font-semibold text-slate-800 mb-6">📑 Индексация страниц</h3>
                <div class="h-64 flex items-center justify-center">
                    @php
                        $totalIndexed = 0;
                        $totalExcluded = 0;
                        foreach($stats['sites'] as $siteData) {
                            if (isset($siteData['indexing'])) {
                                $totalIndexed += $siteData['indexing']['indexed_pages'] ?? 0;
                                $totalExcluded += $siteData['indexing']['excluded_pages'] ?? 0;
                            }
                        }
                    @endphp

                    @if($totalIndexed > 0 || $totalExcluded > 0)
                        <div class="text-center">
                            <div class="text-4xl text-green-500 mb-4">{{ $totalIndexed }}</div>
                            <p class="text-sm text-slate-600 mb-2">Проиндексированных страниц</p>
                            <div class="text-2xl text-orange-500 mb-2">{{ $totalExcluded }}</div>
                            <p class="text-sm text-slate-600">Исключённых страниц</p>
                        </div>
                    @else
                        <div class="text-center text-slate-500">
                            <div class="text-6xl mb-4">📑</div>
                            <p>Статистика индексации</p>
                            <p class="text-sm">Данные будут доступны после настройки API</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Crawl Errors -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/30 p-6">
                <h3 class="text-xl font-semibold text-slate-800 mb-6">⚠️ Ошибки сканирования</h3>
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    @php
                        $allErrors = [];
                        foreach($stats['sites'] as $siteData) {
                            if(!empty($siteData['crawl_errors'])) {
                                $allErrors = array_merge($allErrors, $siteData['crawl_errors']);
                            }
                        }
                    @endphp

                    @forelse(array_slice($allErrors, 0, 8) as $error)
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-red-900 truncate">{{ $error['url'] }}</p>
                                <p class="text-xs text-red-600">{{ $error['error_type'] }} • Код {{ $error['error_code'] }}</p>
                            </div>
                            <span class="text-red-500 text-lg">❌</span>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <div class="text-green-400 text-4xl mb-4">✅</div>
                            <p class="text-slate-600">Ошибок сканирования не обнаружено</p>
                            <p class="text-sm text-slate-500 mt-2">Отличная работа!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Setup Instructions (shown when no data) -->
        @if(empty(array_filter($stats['sites'], function($site) { return empty($site['error']); })))
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl shadow-xl p-8 text-white">
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold mb-2">🛠️ Настройка API</h3>
                    <p class="text-blue-100">Для получения данных необходимо настроить подключение</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-center">
                        <div class="text-3xl mb-3">🌐</div>
                        <h4 class="font-semibold mb-2">1. Добавьте сайт</h4>
                        <p class="text-sm text-blue-100 mb-4">Добавьте свой сайт в Яндекс.Вебмастер и подтвердите права</p>
                        <a href="https://webmaster.yandex.ru/" target="_blank" class="bg-white text-blue-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-50 transition-colors">
                            Открыть Вебмастер
                        </a>
                    </div>

                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-center">
                        <div class="text-3xl mb-3">🔑</div>
                        <h4 class="font-semibold mb-2">2. Получите токен</h4>
                        <p class="text-sm text-blue-100 mb-4">Создайте OAuth приложение и получите токен доступа</p>
                        <a href="https://oauth.yandex.ru/" target="_blank" class="bg-white text-blue-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-50 transition-colors">
                            Создать приложение
                        </a>
                    </div>

                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 text-center">
                        <div class="text-3xl mb-3">⚙️</div>
                        <h4 class="font-semibold mb-2">3. Обновите .env</h4>
                        <p class="text-sm text-blue-100 mb-4">Добавьте токен и URL сайта в файл конфигурации</p>
                        <button onclick="copyEnvExample()" class="bg-white text-blue-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-50 transition-colors">
                            Копировать пример
                        </button>
                    </div>
                </div>

                <!-- Инструкция по устранению ошибки 404 -->
                <div class="mt-8 bg-white/10 backdrop-blur-sm rounded-xl p-6">
                    <h4 class="font-semibold mb-4 text-center">🚨 Устранение ошибки "Resource not found"</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                        <div>
                            <h5 class="font-semibold mb-2">Проверьте:</h5>
                            <ul class="space-y-1 text-blue-100">
                                <li>• Сайт добавлен в правильный аккаунт Яндекса</li>
                                <li>• Права собственности подтверждены</li>
                                <li>• Токен получен из того же аккаунта</li>
                                <li>• URL сайта указан корректно</li>
                            </ul>
                        </div>
                        <div>
                            <h5 class="font-semibold mb-2">Попробуйте:</h5>
                            <ul class="space-y-1 text-blue-100">
                                <li>• palma-forum.io (без протокола)</li>
                                <li>• http://palma-forum.io</li>
                                <li>• https://palma-forum.io</li>
                                <li>• Проверить список сайтов в Вебмастере</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Footer Stats -->
        <div class="mt-8 text-center">
            <div class="bg-white/60 backdrop-blur-sm rounded-xl p-4 inline-block">
                <p class="text-sm text-slate-600">
                    Период: <strong>{{ $stats['period']['start'] }} - {{ $stats['period']['end'] }}</strong> •
                    Обновлено: <strong>{{ now()->format('d.m.Y H:i') }}</strong>
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function exportData() {
    const statsData = <?php echo json_encode($stats); ?>;
    const blob = new Blob([JSON.stringify(statsData, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'webmaster-stats-' + new Date().toISOString().split('T')[0] + '.json';
    a.click();
    URL.revokeObjectURL(url);
}

function copyEnvExample() {
    const envExample = `# Яндекс.Вебмастер API
YANDEX_WEBMASTER_TOKEN=ваш_oauth_токен
YANDEX_WEBMASTER_SITE_1=https://palma-forum.io
YANDEX_WEBMASTER_SITE_2=https://второй-сайт.ru`;

    navigator.clipboard.writeText(envExample).then(() => {
        alert('Пример конфигурации скопирован в буфер обмена!');
    });
}
</script>
@endpush
