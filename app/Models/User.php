<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'login', 'password', 'save_password', 'role', 'blocked', 'registered_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    /**
     * Константы ролей пользователей
     */
    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_ACCOUNTANT = 'accountant';
    public const ROLE_STATISTICIAN = 'statistician';

    /**
     * Получить все доступные роли
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_USER => 'Пользователь',
            self::ROLE_ADMIN => 'Администратор',
            self::ROLE_ACCOUNTANT => 'Бухгалтер',
            self::ROLE_STATISTICIAN => 'Статистик',
        ];
    }

    /**
     * Получить название роли на русском
     */
    public function getRoleNameAttribute(): string
    {
        return self::getRoles()[$this->role] ?? 'Неизвестная роль';
    }

    /**
     * Проверить, является ли пользователь администратором
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Проверить, является ли пользователь бухгалтером
     */
    public function isAccountant(): bool
    {
        return $this->role === self::ROLE_ACCOUNTANT;
    }

    /**
     * Проверить, является ли пользователь статистиком
     */
    public function isStatistician(): bool
    {
        return $this->role === self::ROLE_STATISTICIAN;
    }

    /**
     * Получить доступные платформы для роли пользователя
     */
    public function getAvailablePlatforms(): array
    {
        switch ($this->role) {
            case self::ROLE_ADMIN:
                // Админ имеет доступ ко всем платформам
                return ['accounting', 'webmaster'];

            case self::ROLE_ACCOUNTANT:
                // Бухгалтер имеет доступ только к бухгалтерии
                return ['accounting'];

            case self::ROLE_STATISTICIAN:
                // Статистик имеет доступ к SEO аналитике
                return ['webmaster'];

            case self::ROLE_USER:
            default:
                // Обычный пользователь имеет доступ ко всем платформам
                return ['accounting', 'webmaster'];
        }
    }

    /**
     * Проверить, имеет ли пользователь доступ к платформе
     */
    public function hasAccessToPlatform(string $platform): bool
    {
        return in_array($platform, $this->getAvailablePlatforms());
    }
}
