<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'amount' => 'required|numeric|min:0.01',
            'currency_id' => 'required|exists:currencies,id',
            'comment' => 'nullable|string|max:1000',
            'to_address' => 'required|string|max:255',
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['id'] = 'required|exists:transfers,id';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Поле "Сумма" обязательно для заполнения',
            'amount.numeric' => 'Поле "Сумма" должно быть числом',
            'amount.min' => 'Сумма должна быть больше 0',
            'currency_id.required' => 'Поле "Валюта" обязательно для заполнения',
            'currency_id.exists' => 'Выбранная валюта не существует',
            'comment.max' => 'Комментарий не должен превышать 1000 символов',
            'to_address.required' => 'Поле "Адрес получателя" обязательно для заполнения',
            'to_address.max' => 'Поле "Адрес получателя" не должно превышать 255 символов',
        ];
    }

    public function attributes(): array
    {
        return [
            'amount' => 'сумма',
            'currency_id' => 'валюта',
            'comment' => 'комментарий',
            'to_address' => 'адрес получателя',
        ];
    }
}
