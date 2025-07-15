<?php

namespace App\Services;

use App\Contracts\Services\PlatformServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class PlatformService implements PlatformServiceInterface
{
    /**
     * Список доступных платформ
     */
    private const PLATFORMS = [
        'accounting' => [
            'name' => 'Бухгалтерия',
            'description' => 'Управление финансами, заявками и операциями',
            'icon' => '📊',
            'route' => 'view.main',
            'features' => [
                'Учет заявок и операций',
                'Управление валютами',
                'История транзакций',
                'Интеграция с обменниками'
            ]
        ],
        'webmaster' => [
            'name' => 'SEO Аналитика',
            'description' => 'Анализ поискового трафика и SEO метрик',
            'icon' => '🔍',
            'route' => 'webmaster.dashboard',
            'features' => [
                'Поисковые запросы и позиции',
                'Анализ индексации страниц',
                'Мониторинг ошибок сканирования',
                'Статистика внешних ссылок'
            ]
        ]
    ];

    /**
     * Получить доступные платформы для пользователя
     */
    public function getAvailablePlatforms(User $user): array
    {
        $availablePlatformKeys = $user->getAvailablePlatforms();

        return array_intersect_key(
            self::PLATFORMS,
            array_flip($availablePlatformKeys)
        );
    }

    /**
     * Выбрать платформу для пользователя
     */
    public function selectPlatform(User $user, string $platform): bool
    {
        // Проверяем, есть ли доступ к платформе
        if (!$this->hasAccessToPlatform($user, $platform)) {
            return false;
        }

        // Проверяем, существует ли платформа
        if (!isset(self::PLATFORMS[$platform])) {
            return false;
        }

        // Сохраняем выбор в сессии
        Session::put('app', $platform);

        return true;
    }

    /**
     * Проверить доступ к платформе
     */
    public function hasAccessToPlatform(User $user, string $platform): bool
    {
        return $user->hasAccessToPlatform($platform);
    }

    /**
     * Получить текущую платформу из сессии
     */
    public function getCurrentPlatform(): ?string
    {
        return Session::get('app');
    }

    /**
     * Сбросить выбор платформы
     */
    public function resetPlatformSelection(): void
    {
        Session::forget('app');
    }

    /**
     * Получить информацию о платформе
     */
    public function getPlatformInfo(string $platform): ?array
    {
        return self::PLATFORMS[$platform] ?? null;
    }

    /**
     * Получить все доступные платформы
     */
    public function getAllPlatforms(): array
    {
        return self::PLATFORMS;
    }

    /**
     * Автоматический выбор платформы для пользователя
     * (если у него доступ только к одной платформе)
     */
    public function autoSelectPlatform(User $user): ?string
    {
        $availablePlatforms = $this->getAvailablePlatforms($user);

        // Если доступна только одна платформа - выбираем её автоматически
        if (count($availablePlatforms) === 1) {
            $platform = array_key_first($availablePlatforms);
            $this->selectPlatform($user, $platform);
            return $platform;
        }

        return null;
    }

    /**
     * Получить маршрут для платформы
     */
    public function getPlatformRoute(string $platform): ?string
    {
        $platformInfo = $this->getPlatformInfo($platform);
        return $platformInfo['route'] ?? null;
    }

    /**
     * Проверить, нужно ли показывать страницу выбора платформы
     */
    public function shouldShowPlatformSelection(User $user): bool
    {
        // Если платформа уже выбрана
        if ($this->getCurrentPlatform()) {
            return false;
        }

        // Если у пользователя доступ только к одной платформе
        $availablePlatforms = $this->getAvailablePlatforms($user);
        if (count($availablePlatforms) === 1) {
            return false;
        }

        return true;
    }
}
