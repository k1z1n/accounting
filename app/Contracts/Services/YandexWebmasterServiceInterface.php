<?php

namespace App\Contracts\Services;

use Carbon\Carbon;

interface YandexWebmasterServiceInterface
{
    /**
     * Получить список всех сайтов пользователя
     */
    public function getUserSites(): array;

    /**
     * Получить общую статистику по всем сайтам
     */
    public function getGeneralStats(Carbon $startDate, Carbon $endDate): array;

    /**
     * Получить статистику по конкретному сайту
     */
    public function getSiteStats(string $siteUrl, Carbon $startDate, Carbon $endDate): array;

    /**
     * Получить поисковые запросы
     */
    public function getSearchQueries(string $hostId, Carbon $startDate, Carbon $endDate, int $limit = 100): array;

    /**
     * Получить статистику индексации
     */
    public function getIndexingStats(string $hostId): array;

    /**
     * Получить ошибки сканирования
     */
    public function getCrawlErrors(string $hostId): array;

    /**
     * Получить внешние ссылки
     */
    public function getExternalLinks(string $hostId): array;

    /**
     * Получить аналитику поиска
     */
    public function getSearchAnalytics(string $hostId, Carbon $startDate, Carbon $endDate): array;

    /**
     * Получить информацию о сайте
     */
    public function getSiteInfo(string $siteUrl): array;

    /**
     * Проверить настройки API
     */
    public function testConnection(): array;
}
