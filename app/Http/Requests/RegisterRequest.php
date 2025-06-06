<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Определяет, разрешено ли выполнять этот запрос.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->guest();
    }

    /**
     * Правила валидации для регистрации.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login'    => 'required|string|unique:users,login',
            'password' => 'required|string|min:6',
        ];
    }

    /**
     * Сообщения об ошибках валидации на русском языке.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'login.required'    => 'Поле «Логин» обязательно для заполнения.',
            'login.string'      => 'Поле «Логин» должно быть строкой.',
            'login.unique'      => 'Пользователь с таким логином уже зарегистрирован.',
            'password.required' => 'Поле «Пароль» обязательно для заполнения.',
            'password.string'   => 'Поле «Пароль» должно быть строкой.',
            'password.min'      => 'Пароль должен содержать не менее 6 символов.',
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
        ];
    }
}
