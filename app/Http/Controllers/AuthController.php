<?php

namespace App\Http\Controllers;

use App\Contracts\Services\AuthServiceInterface;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthServiceInterface $authService
    ) {}

    public function viewRegister()
    {
        return view('pages.register');
    }

    public function register(RegisterRequest $request)
    {
        try {
            $this->authService->register($request->validated());
            return redirect()->route('applications.index')->with('success', 'Регистрация прошла успешно!');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors([
                'login' => $e->getMessage()
            ])->withInput($request->except('password'));
        } catch (\Exception $e) {
            return back()->withErrors([
                'login' => 'Произошла ошибка при регистрации. Попробуйте еще раз.'
            ])->withInput($request->except('password'));
        }
    }

    public function viewLogin()
    {
        return view('pages.login');
    }

    public function login(LoginRequest $request)
    {
        try {
            $user = $this->authService->authenticate(
                $request->login,
                $request->password,
                $request->remember ?? false
            );

            $this->authService->logLogin($user, $request);

            return redirect()->intended(route('applications.index'));
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            return back()->withErrors([
                'login' => 'Неверные учётные данные. Проверьте логин и пароль.'
            ])->withInput($request->only('login'));
        } catch (\Exception $e) {
            return back()->withErrors([
                'login' => 'Произошла ошибка при входе в систему. Попробуйте еще раз.'
            ])->withInput($request->only('login'));
        }
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request);
        return redirect()->route('view.login');
    }
}
