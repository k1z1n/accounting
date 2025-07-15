<?php

namespace App\DTOs;

class TransferDTO extends BaseDTO
{
    public ?int $id = null;
    public ?float $amount = null;
    public ?int $currency_id = null;
    public ?string $comment = null;
    public ?string $to_address = null;
    public ?string $created_at = null;

    public function validate(): bool
    {
        return !empty($this->amount) &&
               !empty($this->currency_id) &&
               !empty($this->to_address) &&
               $this->amount > 0;
    }

    public function getModelData(): array
    {
        return array_filter([
            'amount' => $this->amount,
            'currency_id' => $this->currency_id,
            'comment' => $this->comment,
            'to_address' => $this->to_address,
        ], fn($value) => $value !== null);
    }
}
