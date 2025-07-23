<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Сервис для работы с аутентификацией (регистрация, вход, токены и т.д.).
 */
class AuthService
{
    /**
     * Регистрирует нового пользователя и создаёт токен.
     *
     * @param array $params
     * @return array{user: User, token: string}
     */
    public function registerUser(array $params): array
    {
        $user = User::create([
            'name' => $params['name'],
            'email' => $params['email'],
            'password' => Hash::make($params['password']),
        ]);

        $token = $user->createToken('auth_token');

        return [
            'user' => $user,
            'token' => $token->plainTextToken,
        ];
    }

    /**
     * Аутентифицирует пользователя и возвращает токен.
     *
     * @param array $credentials
     * @return string
     */
    public function loginUser(array $credentials): string
    {
        if (!Auth::attempt($credentials)) {
            throw new UnauthorizedHttpException('', 'Неверный email или пароль.');
        }

        return Auth::user()->createToken('auth_token')->plainTextToken;
    }

    /**
     * Выход пользователя — удаляет все его токены.
     *
     * @param User $user
     * @return void
     */
    public function logoutUser(User $user): void
    {
        $user->tokens()->delete();
    }
}
