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
        $this->authService->register($request->validated());
        return redirect()->route('view.main');
    }

    public function viewLogin()
    {
        return view('pages.login');
    }

    public function login(LoginRequest $request)
    {
        $user = $this->authService->authenticate(
            $request->login,
            $request->password,
            $request->remember ?? false
        );

        $this->authService->logLogin($user, $request);

        return redirect()->intended(route('view.main'));
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request);
        return redirect()->route('view.login');
    }
}
