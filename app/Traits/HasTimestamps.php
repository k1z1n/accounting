<?php

namespace App\Traits;

use Carbon\Carbon;

trait HasTimestamps
{
    /**
     * Форматировать дату создания
     */
    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at ? $this->created_at->format('d.m.Y H:i') : '';
    }

    /**
     * Форматировать дату обновления
     */
    public function getFormattedUpdatedAtAttribute(): string
    {
        return $this->updated_at ? $this->updated_at->format('d.m.Y H:i') : '';
    }

    /**
     * Получить время прошедшее с создания
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at ? $this->created_at->diffForHumans() : '';
    }

    /**
     * Проверить, создано ли сегодня
     */
    public function isCreatedToday(): bool
    {
        return $this->created_at && $this->created_at->isToday();
    }

    /**
     * Проверить, обновлено ли сегодня
     */
    public function isUpdatedToday(): bool
    {
        return $this->updated_at && $this->updated_at->isToday();
    }

    /**
     * Получить дату в конкретном формате
     */
    public function getCreatedAtFormatted(string $format = 'd.m.Y'): string
    {
        return $this->created_at ? $this->created_at->format($format) : '';
    }

    /**
     * Проверить, создано ли в течение последних N дней
     */
    public function isCreatedWithinDays(int $days): bool
    {
        return $this->created_at && $this->created_at->greaterThan(Carbon::now()->subDays($days));
    }
}
