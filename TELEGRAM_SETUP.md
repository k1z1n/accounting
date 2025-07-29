# Настройка Telegram бота для отправки балансов

## 1. Создание бота

1. Найдите @BotFather в Telegram
2. Отправьте команду `/newbot`
3. Следуйте инструкциям для создания бота
4. Сохраните полученный токен бота

## 2. Получение Chat ID

### Для группы:
1. Добавьте бота в группу
2. Отправьте любое сообщение в группу
3. Перейдите по ссылке: `https://api.telegram.org/bot<BOT_TOKEN>/getUpdates`
4. Найдите `chat.id` в ответе (для групп это отрицательное число)

### Для личного чата:
1. Начните диалог с ботом
2. Отправьте любое сообщение
3. Перейдите по ссылке: `https://api.telegram.org/bot<BOT_TOKEN>/getUpdates`
4. Найдите `chat.id` в ответе

## 3. Настройка переменных окружения

Добавьте в файл `.env`:

```env
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_CHAT_ID=your_chat_id_here
```

## 4. Использование

### Ручная отправка:
- Перейдите на страницу `/admin/exchangers/balances`
- Нажмите кнопку "Отправить в Telegram"

### Автоматическая отправка:
```bash
# Отправить все балансы
php artisan telegram:send-balances

# Отправить балансы конкретного провайдера
php artisan telegram:send-balances --provider=heleket

# Отправить балансы конкретного обменника
php artisan telegram:send-balances --exchanger=obama

# Отправить балансы конкретного провайдера и обменника
php artisan telegram:send-balances --provider=heleket --exchanger=obama
```

### Настройка автоматической отправки (cron):

Добавьте в `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Отправка балансов каждый час
    $schedule->command('telegram:send-balances')->hourly();
    
    // Или каждый день в 9:00
    $schedule->command('telegram:send-balances')->dailyAt('09:00');
}
```

## 5. Формат сообщения

Сообщение будет содержать:
- Дату и время
- Балансы по провайдерам (Heleket, Rapira)
- Балансы по обменникам (Obama, Ural)
- Разделение на мерчант и пользователя (для Heleket)
- Эмодзи для визуального отображения статуса баланса

Пример:
```
💰 Балансы обменников (25.01.2025 15:30)

📊 Heleket
  └ Obama
    💼 Мерчант:
      🟢 USDT: 1,234.56789000
      🔴 BTC: -0.00123456
    👤 Пользователь:
      🟢 USDT: 567.89012300
      ⚪ ETH: 0.00000000

📊 Rapira
  └ Ural
    🟢 USDT: 2,345.67890100
    🟢 BTC: 0.12345678
``` 
