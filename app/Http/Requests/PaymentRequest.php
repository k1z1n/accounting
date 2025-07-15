<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'sum' => 'required|numeric|min:0.01',
            'currency_id' => 'required|exists:currencies,id',
            'comment' => 'nullable|string|max:1000',
            'to_whom' => 'required|string|max:255',
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['id'] = 'required|exists:payments,id';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'sum.required' => 'Поле "Сумма" обязательно для заполнения',
            'sum.numeric' => 'Поле "Сумма" должно быть числом',
            'sum.min' => 'Сумма должна быть больше 0',
            'currency_id.required' => 'Поле "Валюта" обязательно для заполнения',
            'currency_id.exists' => 'Выбранная валюта не существует',
            'comment.max' => 'Комментарий не должен превышать 1000 символов',
            'to_whom.required' => 'Поле "Получатель" обязательно для заполнения',
            'to_whom.max' => 'Поле "Получатель" не должно превышать 255 символов',
        ];
    }

    public function attributes(): array
    {
        return [
            'sum' => 'сумма',
            'currency_id' => 'валюта',
            'comment' => 'комментарий',
            'to_whom' => 'получатель',
        ];
    }
}
