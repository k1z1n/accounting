# Управление данными сайтов в БД

## Обзор

Система была модифицирована для хранения данных о сайтах и их cookies в базе данных вместо использования переменных окружения. Это позволяет более гибко управлять данными и обновлять их без изменения кода.

## Структура таблицы

Таблица `site_cookies` содержит следующие поля:

- `id` - уникальный идентификатор
- `name` - название сайта (OBAMA, URAL)
- `url` - URL сайта
- `phpsessid` - PHPSESSID cookie
- `premium_session_id` - premium_session_id cookie
- `wordpress_logged_title` - название WordPress logged cookie
- `wordpress_logged_value` - значение WordPress logged cookie
- `wordpress_sec_title` - название WordPress sec cookie
- `wordpress_sec_value` - значение WordPress sec cookie
- `created_at` - дата создания
- `updated_at` - дата обновления

## Команды управления

### Просмотр списка сайтов
```bash
php artisan site-cookies:manage list
```

### Просмотр данных конкретного сайта
```bash
php artisan site-cookies:manage show --name=OBAMA
```

### Обновление данных сайта
```bash
php artisan site-cookies:manage update --name=OBAMA --phpsessid=новое_значение
```

### Создание нового сайта
```bash
php artisan site-cookies:manage create --name=NEW_SITE --url=https://example.com --phpsessid=value
```

### Удаление сайта
```bash
php artisan site-cookies:manage delete --name=OBAMA
```

## Методы модели SiteCookie

### getCookiesString()
Возвращает полную строку cookies для использования в HTTP запросах:
```php
$site = SiteCookie::where('name', 'OBAMA')->first();
$cookiesString = $site->getCookiesString();
// Результат: "PHPSESSID=value; premium_session_id=value; ..."
```

### getCookiesArray()
Возвращает массив cookies для использования в HTTP клиенте:
```php
$site = SiteCookie::where('name', 'OBAMA')->first();
$cookiesArray = $site->getCookiesArray();
// Результат: ['PHPSESSID' => 'value', 'premium_session_id' => 'value', ...]
```

## Изменения в коде

### ApplicationService
Метод `syncFromExternalSources()` теперь получает данные из БД:
```php
// Получаем данные из БД вместо config
$siteCookies = SiteCookie::all()->keyBy('name');

$exchangers = [];

if ($siteCookies->has('OBAMA')) {
    $obama = $siteCookies['OBAMA'];
    $exchangers['obama'] = [
        'url' => $obama->url . '&page_num=' . $pageNum,
        'cookies' => $obama->getCookiesString(),
    ];
}
```

### CookieManagerService
Конструктор теперь использует данные из БД (пока только для URL, учетные данные остаются в config).

## Тестирование

Для тестирования работы с данными сайтов используйте команду:
```bash
php artisan test:site-cookies
```

Эта команда покажет:
1. Данные из БД
2. Симуляцию работы ApplicationService
3. Результаты методов getCookiesArray

## Миграция данных

Данные были автоматически перенесены из env файла в БД через сидер `SiteCookiesSeeder`. 

Текущие данные:
- **OBAMA**: https://obama.ru/wp-admin/admin.php?page=pn_bids
- **URAL**: https://ural-obmen.ru/wp-admin/admin.php?page=pn_bids

## Преимущества новой системы

1. **Гибкость**: Можно обновлять данные без изменения кода
2. **Безопасность**: Данные не хранятся в коде
3. **Управляемость**: Есть команды для управления данными
4. **Масштабируемость**: Легко добавлять новые сайты
5. **Отслеживание**: Есть timestamps для отслеживания изменений

## Примеры использования

### Обновление PHPSESSID для OBAMA
```bash
php artisan site-cookies:manage update --name=OBAMA --phpsessid=новый_phpsessid
```

### Добавление нового сайта
```bash
php artisan site-cookies:manage create \
  --name=NEW_SITE \
  --url=https://newsite.com/admin \
  --phpsessid=session_id \
  --premium-session-id=premium_id \
  --wordpress-logged-title=wp_logged_title \
  --wordpress-logged-value=wp_logged_value \
  --wordpress-sec-title=wp_sec_title \
  --wordpress-sec-value=wp_sec_value
```

### Проверка данных сайта
```bash
php artisan site-cookies:manage show --name=OBAMA
``` 
