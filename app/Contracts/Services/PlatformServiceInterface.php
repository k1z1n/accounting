<?php

namespace App\Contracts\Services;

use App\Models\User;

interface PlatformServiceInterface
{
    /**
     * Получить доступные платформы для пользователя
     */
    public function getAvailablePlatforms(User $user): array;

    /**
     * Выбрать платформу для пользователя
     */
    public function selectPlatform(User $user, string $platform): bool;

    /**
     * Проверить доступ к платформе
     */
    public function hasAccessToPlatform(User $user, string $platform): bool;

    /**
     * Получить текущую платформу из сессии
     */
    public function getCurrentPlatform(): ?string;

    /**
     * Сбросить выбор платформы
     */
    public function resetPlatformSelection(): void;

    /**
     * Получить информацию о платформе
     */
    public function getPlatformInfo(string $platform): ?array;

    /**
     * Получить все доступные платформы
     */
    public function getAllPlatforms(): array;
}
