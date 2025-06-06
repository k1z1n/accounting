<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Определяет, разрешено ли делать этот запрос.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Разрешаем просьбу, если пользователь не аутентифицирован
        return auth()->guest();
    }

    /**
     * Правила валидации для входа.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login'    => 'required',
            'password' => 'required|string',
            'remember' => 'boolean',
        ];
    }

    /**
     * Сообщения об ошибках валидации на русском.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'login.required'    => 'Поле «Логин» обязательно для заполнения.',
            'password.required' => 'Поле «Пароль» обязательно для заполнения.',
            'password.string'   => 'Поле «Пароль» должно быть строкой.',
            'remember.boolean'  => 'Поле «Запомнить меня» должно быть булевым значением.',
        ];
    }

    /**
     * Человеко-понятные названия полей.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'login'    => 'Логин',
            'password' => 'Пароль',
            'remember' => 'Запомнить меня',
        ];
    }
}
