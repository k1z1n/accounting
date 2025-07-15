<?php

namespace App\Contracts\Services;

interface StatisticsServiceInterface
{
    /**
     * Получить общую статистику системы
     */
    public function getGeneralStatistics(): array;

    /**
     * Получить статистику заявок
     */
    public function getApplicationStatistics(): array;

    /**
     * Получить статистику операций
     */
    public function getOperationStatistics(): array;

    /**
     * Получить статистику пользователей
     */
    public function getUserStatistics(): array;

    /**
     * Получить статистику валют
     */
    public function getCurrencyStatistics(): array;

    /**
     * Получить данные для графиков
     */
    public function getChartData(string $type, array $params = []): array;

    /**
     * Получить статистику за период
     */
    public function getPeriodStatistics(\Carbon\Carbon $from, \Carbon\Carbon $to): array;

    /**
     * Экспорт статистики
     */
    public function exportStatistics(string $format = 'json'): array;
}
