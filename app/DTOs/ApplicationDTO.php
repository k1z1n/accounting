<?php

namespace App\DTOs;

class ApplicationDTO extends BaseDTO
{
    public ?int $id = null;
    public ?string $app_id = null;
    public ?string $exchanger = null;
    public ?string $status = null;
    public ?string $merchant = null;
    public ?string $order_id = null;
    public ?float $sell_amount = null;
    public ?string $sell_currency = null;
    public ?int $sell_currency_id = null;
    public ?float $buy_amount = null;
    public ?string $buy_currency = null;
    public ?int $buy_currency_id = null;
    public ?float $expense_amount = null;
    public ?string $expense_currency = null;
    public ?int $expense_currency_id = null;
    public ?string $sale_text = null;
    public ?string $app_created_at = null;
    public ?int $user_id = null;

    /**
     * Валидация данных заявки
     */
    public function validate(): bool
    {
        if (empty($this->app_id) || empty($this->exchanger)) {
            return false;
        }

        if ($this->sell_amount !== null && $this->sell_amount < 0) {
            return false;
        }

        if ($this->buy_amount !== null && $this->buy_amount < 0) {
            return false;
        }

        if ($this->expense_amount !== null && $this->expense_amount < 0) {
            return false;
        }

        return true;
    }

    /**
     * Получить данные для создания/обновления модели
     */
    public function getModelData(): array
    {
        return array_filter($this->toArray(), function ($value) {
            return $value !== null;
        });
    }

    /**
     * Проверить, есть ли валютные данные
     */
    public function hasCurrencyData(): bool
    {
        return !empty($this->sell_currency) ||
               !empty($this->buy_currency) ||
               !empty($this->expense_currency);
    }
}
