<?php

require_once 'vendor/autoload.php';

// Загружаем переменные окружения
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Проверяем наличие необходимых переменных
if (!isset($_ENV['TELEGRAM_BOT_TOKEN']) || !isset($_ENV['TELEGRAM_CHAT_ID'])) {
    echo "❌ Ошибка: не настроены TELEGRAM_BOT_TOKEN или TELEGRAM_CHAT_ID\n";
    echo "Добавьте их в файл .env\n";
    exit(1);
}

$botToken = $_ENV['TELEGRAM_BOT_TOKEN'];
$chatId = $_ENV['TELEGRAM_CHAT_ID'];

echo "🤖 Тестирование Telegram бота...\n";
echo "Bot Token: " . substr($botToken, 0, 10) . "...\n";
echo "Chat ID: {$chatId}\n\n";

// Отправляем тестовое сообщение
$url = "https://api.telegram.org/bot{$botToken}/sendMessage";
$data = [
    'chat_id' => $chatId,
    'text' => "🧪 Тестовое сообщение от бота\nВремя: " . date('d.m.Y H:i:s'),
    'parse_mode' => 'HTML'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $result = json_decode($response, true);
    if ($result['ok'] ?? false) {
        echo "✅ Тестовое сообщение отправлено успешно!\n";
        echo "Message ID: " . ($result['result']['message_id'] ?? 'N/A') . "\n";
    } else {
        echo "❌ Ошибка API Telegram: " . ($result['description'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ HTTP ошибка: {$httpCode}\n";
    echo "Ответ: {$response}\n";
}

echo "\n🔧 Для настройки бота следуйте инструкции в файле TELEGRAM_SETUP.md\n";
