<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\DTO\Auth\LoginDto;
use App\DTO\Auth\RegisterDto;
use App\Http\Responses\Success;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function register(RegisterRequest $request): Success
    {
        $registerDto = new RegisterDto(
            name: $request->input('name'),
            email: $request->input('email'),
            password: $request->input('password'),
            avatar: $request->file('file')
        );

        $result = $this->authService->register($registerDto);

        return $this->success($result, 201);
    }

    public function login(LoginRequest $request): Success
    {
        $loginDto = new LoginDto(
            email: $request->input('email'),
            password: $request->input('password')
        );

        $token = $this->authService->login($loginDto);

        return $this->success(['token' => $token->token]);
    }

    public function logout(): Success
    {
        $this->authService->logout(auth()->id());

        return $this->success(null, 204);
    }
}
