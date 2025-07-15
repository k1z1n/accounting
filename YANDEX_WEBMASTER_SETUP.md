# 🔍 Интеграция с Яндекс.Вебмастером

## ✨ Возможности

Полнофункциональная система для работы с **API Яндекс.Вебмастера** (Search Console):

### 🎯 SEO аналитика:
- **Поисковые запросы** с кликами, показами, CTR и позициями
- **Статистика индексации** страниц
- **Ошибки сканирования** и их анализ
- **Внешние ссылки** (бэклинки)
- **История обхода** сайта роботом
- **Скорость загрузки** страниц
- **Аналитика по поисковому трафику**

### 🖥️ Интерфейс:
- **SEO дашборд** с графиками и метриками
- **Сравнение сайтов** между собой
- **Интерактивные графики** с Chart.js
- **Мониторинг ошибок** в реальном времени
- **Фильтры по датам** и быстрый выбор периодов

## 🚀 Быстрая настройка

### 1. Получите OAuth токен:
1. Идите на https://oauth.yandex.ru/
2. Создайте приложение с правами "Яндекс.Вебмастер: чтение данных"
3. Получите токен по ссылке:
   ```
   https://oauth.yandex.ru/authorize?response_type=token&client_id=YOUR_CLIENT_ID
   ```

### 2. Добавьте переменные в .env:

```bash
# Яндекс.Вебмастер настройки
YANDEX_WEBMASTER_TOKEN=ваш_oauth_токен
YANDEX_WEBMASTER_SITE_1=https://palma-forum.io
YANDEX_WEBMASTER_SITE_2=https://ваш-второй-сайт.ru
YANDEX_WEBMASTER_SITE_3=https://ваш-третий-сайт.ru
```

### 3. Добавьте сайт в Яндекс.Вебмастер:

1. Войдите в https://webmaster.yandex.ru/
2. Добавьте ваш сайт
3. Подтвердите права на сайт (через HTML-файл, meta-тег или DNS)

## 📈 Использование

### Веб-интерфейс:
- **SEO дашборд**: `/webmaster/dashboard`
- **Сравнение сайтов**: `/webmaster/comparison`

### API эндпоинты:
```php
GET /webmaster/api/stats                    // Общая статистика
GET /webmaster/api/search-queries           // Поисковые запросы
GET /webmaster/api/indexing                 // Статистика индексации
GET /webmaster/api/crawl-errors             // Ошибки сканирования
GET /webmaster/api/external-links           // Внешние ссылки
GET /webmaster/api/sites                    // Список сайтов
GET /webmaster/api/test-connection          // Проверка подключения
```

## 🧪 Тестирование

Проверьте интеграцию командой:
```bash
php artisan webmaster:test
```

С конкретным сайтом:
```bash
php artisan webmaster:test --site-url=https://palma-forum.io
```

## 📁 Структура файлов

### Бэкенд:
- `app/Services/YandexWebmasterService.php` - Основной сервис
- `app/Http/Controllers/YandexWebmasterController.php` - Контроллер
- `app/DTOs/WebmasterStatsDTO.php` - DTO для данных
- `app/Contracts/Services/YandexWebmasterServiceInterface.php` - Интерфейс

### Фронтенд:
- `resources/views/webmaster/dashboard.blade.php` - SEO дашборд
- Интерактивные графики с Chart.js
- Адаптивная верстка на Bootstrap

### Консоль:
- `app/Console/Commands/TestYandexWebmasterCommand.php` - Команда тестирования

## 🎨 Особенности API

### 📊 Поисковые запросы:
```json
{
  "query": "продать биткоин",
  "impressions": 1500,
  "clicks": 75,
  "ctr": 5.0,
  "position": 3.2
}
```

### 🔍 Индексация:
```json
{
  "indexed_pages": 450,
  "excluded_pages": 23,
  "total_pages": 473,
  "last_update": "2024-01-15"
}
```

### 🚨 Ошибки сканирования:
```json
{
  "url": "https://site.com/error-page",
  "error_type": "HTTP_ERROR",
  "error_code": 404,
  "last_access": "2024-01-14"
}
```

### 🔗 Внешние ссылки:
```json
{
  "source_url": "https://external-site.com/article",
  "destination_url": "https://your-site.com/page",
  "discovery_date": "2024-01-10"
}
```

## 🔧 Дополнительная настройка

В `config/services.php` можно добавить больше сайтов:

```php
'yandex_webmaster' => [
    'oauth_token' => env('YANDEX_WEBMASTER_TOKEN'),
    'site_urls' => [
        'main_site' => 'https://palma-forum.io',
        'landing' => 'https://landing.palma-forum.io',
        'blog' => 'https://blog.palma-forum.io',
        'shop' => 'https://shop.palma-forum.io',
        'mobile' => 'https://m.palma-forum.io',
    ],
],
```

## 📊 Возможности анализа

### 🎯 SEO метрики:
- **CTR** - кликабельность в поиске
- **Средняя позиция** по запросам
- **Динамика показов** и кликов
- **Топ поисковых запросов**

### 🔍 Техническое SEO:
- **Мониторинг индексации** страниц
- **Выявление ошибок** 404, 500, etc.
- **Анализ скорости** загрузки
- **Проблемы сканирования**

### 📈 Link Building:
- **Анализ бэклинков**
- **Качество ссылочной массы**
- **Новые внешние ссылки**

## 🚀 Автоматизация

Система автоматически:
- **Кэширует данные** для быстродействия
- **Логирует ошибки** API
- **Валидирует запросы**
- **Форматирует данные** для отображения

## 📞 Поддержка

При возникновении проблем:

1. Проверьте логи Laravel: `storage/logs/laravel.log`
2. Используйте команду тестирования: `php artisan webmaster:test`
3. Убедитесь, что токен актуален
4. Проверьте права доступа к сайтам в Вебмастере

## 🔐 Безопасность

- OAuth 2.0 аутентификация
- Валидация всех входящих данных
- Защита от CSRF
- Ограничение доступа через middleware

## 📚 Примеры использования

### JavaScript API вызовы:
```javascript
// Получить поисковые запросы
fetch('/webmaster/api/search-queries?start_date=2024-01-01&end_date=2024-01-31')
    .then(response => response.json())
    .then(data => console.log(data));

// Получить ошибки сканирования
fetch('/webmaster/api/crawl-errors')
    .then(response => response.json())
    .then(data => console.log(data));
```

### PHP использование сервиса:
```php
use App\Services\YandexWebmasterService;

$webmaster = app(YandexWebmasterService::class);

// Получить статистику
$stats = $webmaster->getSiteStats('https://palma-forum.io', $startDate, $endDate);

// Получить поисковые запросы
$queries = $webmaster->getSearchQueries($hostId, $startDate, $endDate);
```

---

**✅ Интеграция готова к использованию!**

Теперь у вас есть полноценный **SEO дашборд** с данными из Яндекс.Вебмастера. Перейдите в раздел "🔍 Яндекс.Вебмастер" в навигационном меню для доступа к статистике вашего сайта `palma-forum.io`. 
