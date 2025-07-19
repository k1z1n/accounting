#!/bin/bash

# Скрипт для остановки воркера очереди Laravel
if [ -f queue-worker.pid ]; then
    PID=$(cat queue-worker.pid)
    echo "Остановка воркера очереди с PID: $PID"

    # Останавливаем процесс
    kill $PID 2>/dev/null

    # Удаляем файл PID
    rm -f queue-worker.pid
    echo "Воркер очереди остановлен"
else
    echo "Файл PID не найден. Воркер может быть уже остановлен."
fi








