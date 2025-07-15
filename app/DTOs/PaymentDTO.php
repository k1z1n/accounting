<?php

namespace App\DTOs;

class PaymentDTO extends BaseDTO
{
    public ?int $id = null;
    public ?float $sum = null;
    public ?int $currency_id = null;
    public ?string $comment = null;
    public ?string $to_whom = null;
    public ?string $created_at = null;

    public function validate(): bool
    {
        return !empty($this->sum) &&
               !empty($this->currency_id) &&
               !empty($this->to_whom) &&
               $this->sum > 0;
    }

    public function getModelData(): array
    {
        return array_filter([
            'sum' => $this->sum,
            'currency_id' => $this->currency_id,
            'comment' => $this->comment,
            'to_whom' => $this->to_whom,
        ], fn($value) => $value !== null);
    }
}
