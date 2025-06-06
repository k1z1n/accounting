<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {
    }

    public function viewRegister()
    {
        return view('pages.register');
    }

    public function register(RegisterRequest $request)
    {
        $this->authService->register($request);
        return redirect()->route('view.main');
    }

    public function viewLogin()
    {
        return view('pages.login');
    }

    public function login(LoginRequest $request)
    {
        $this->authService->login($request);
        return redirect()->intended(route('view.main'));
    }

    public function logout(\Illuminate\Http\Request $request)
    {
        $this->authService->logout($request);
        return redirect()->route('view.login');
    }
}
