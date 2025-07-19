#!/bin/bash

# Скрипт для запуска воркера очереди Laravel
echo "Запуск воркера очереди..."

# Запускаем воркер в фоновом режиме
php artisan queue:work --daemon --sleep=3 --tries=3 &

# Сохраняем PID процесса
echo $! > queue-worker.pid
echo "Воркер очереди запущен с PID: $(cat queue-worker.pid)"








