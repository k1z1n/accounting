# 🔍 Яндекс.Вебмастер - Интеграция

## 🚀 Быстрый старт

### 1. Настройте токен
```bash
# Добавьте в .env файл
YANDEX_WEBMASTER_TOKEN=ваш_oauth_токен
YANDEX_WEBMASTER_SITE_1=https://palma-forum.io
```

### 2. Получите OAuth токен
1. Создайте приложение: https://oauth.yandex.ru/
2. Получите токен с правами "Яндекс.Вебмастер: чтение данных"

### 3. Добавьте сайт в Вебмастер
- Перейдите: https://webmaster.yandex.ru/
- Добавьте и подтвердите сайт

### 4. Протестируйте
```bash
php artisan webmaster:test
php artisan webmaster:test --site-url=https://palma-forum.io
```

## 📊 Возможности

### SEO данные:
- ✅ **Поисковые запросы** (клики, показы, CTR, позиции)
- ✅ **Индексация** страниц
- ✅ **Ошибки сканирования**
- ✅ **Внешние ссылки** (бэклинки)
- ✅ **Скорость загрузки**

### Интерфейс:
- 🖥️ **SEO дашборд**: `/webmaster/dashboard`
- 📈 **Интерактивные графики**
- 📱 **Адаптивный дизайн**

### API:
```
GET /webmaster/api/stats           - Общая статистика
GET /webmaster/api/search-queries  - Поисковые запросы
GET /webmaster/api/indexing        - Индексация
GET /webmaster/api/crawl-errors    - Ошибки сканирования
GET /webmaster/api/external-links  - Внешние ссылки
```

## 🎯 Используйте для:

1. **SEO мониторинга** сайта
2. **Анализа поискового трафика**
3. **Отслеживания технических ошибок**
4. **Анализа ссылочной массы**
5. **Контроля индексации**

## 📝 Пример использования

```php
use App\Services\YandexWebmasterService;

$webmaster = app(YandexWebmasterService::class);

// Получить статистику
$stats = $webmaster->getSiteStats('https://palma-forum.io', $startDate, $endDate);

// Получить поисковые запросы
$queries = $webmaster->getSearchQueries($hostId, $startDate, $endDate);

// Проверить ошибки
$errors = $webmaster->getCrawlErrors($hostId);
```

---

**✅ Готово!** Используйте навигационное меню "🔍 Яндекс.Вебмастер" для доступа к SEO дашборду. 
