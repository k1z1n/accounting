<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'client' => 'required|string|max:255',
            'wallet' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'balance' => 'required|numeric|min:0',
            'comment' => 'nullable|string|max:1000',
            'status' => 'sometimes|string|in:new,progress,completed,completed_paid,canceled',
        ];

        // Дополнительные правила для обновления
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['id'] = 'required|exists:applications,id';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'client.required' => 'Поле "Клиент" обязательно для заполнения',
            'client.max' => 'Поле "Клиент" не должно превышать 255 символов',
            'wallet.required' => 'Поле "Кошелек" обязательно для заполнения',
            'wallet.max' => 'Поле "Кошелек" не должно превышать 255 символов',
            'amount.required' => 'Поле "Сумма" обязательно для заполнения',
            'amount.numeric' => 'Поле "Сумма" должно быть числом',
            'amount.min' => 'Сумма не может быть отрицательной',
            'balance.required' => 'Поле "Баланс" обязательно для заполнения',
            'balance.numeric' => 'Поле "Баланс" должно быть числом',
            'balance.min' => 'Баланс не может быть отрицательным',
            'comment.max' => 'Комментарий не должен превышать 1000 символов',
            'status.in' => 'Неверный статус заявки',
        ];
    }

    public function attributes(): array
    {
        return [
            'client' => 'клиент',
            'wallet' => 'кошелек',
            'amount' => 'сумма',
            'balance' => 'баланс',
            'comment' => 'комментарий',
            'status' => 'статус',
        ];
    }
}
