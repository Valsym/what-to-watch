<?php

namespace App\Services\Auth;

use App\Models\User;
use App\DTO\Auth\LoginDto;
use App\DTO\Auth\RegisterDto;
use App\DTO\Auth\TokenDto;
use App\DTO\Auth\UserDto;
use App\Repositories\Auth\UserRepository;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function register(RegisterDto $registerDto): array
    {
        // Проверяем, не занят ли email
        if ($this->userRepository->findUserByEmail($registerDto->email)) {
            throw new \InvalidArgumentException('Email already exists');
        }

        $data = [
            'name' => $registerDto->name,
            'email' => $registerDto->email,
            'password' => $registerDto->password,
            'role' => \App\Models\User::ROLE_USER,
        ];

        // Сохраняем аватар если есть
        if ($registerDto->avatar) {
            $data['avatar'] = $this->userRepository->storeAvatar($registerDto->avatar);
        }

        $user = $this->userRepository->createUser($data);
        $token = $user->createToken('auth_token');

        return [
            'user' => $this->mapUserToDto($user),
            'token' => new TokenDto($token->plainTextToken),
        ];
    }

    public function login(LoginDto $loginDto): TokenDto
    {
        $user = $this->userRepository->findUserByEmail($loginDto->email);

        if (!$user || !$this->userRepository->validateCredentials($user, $loginDto->password)) {
            throw new AuthenticationException('Invalid credentials');
        }

        Auth::login($user);
        $token = $user->createToken('auth_token');

        return new TokenDto($token->plainTextToken);
    }

    public function logout(int $userId): void
    {
        $user = $this->userRepository->findUserById($userId);
        if ($user) {
            $user->tokens()->delete();
        }
    }

    public function getCurrentUser(int $userId): UserDto
    {
        $user = $this->userRepository->findUserById($userId);
        if (!$user) {
            throw new ModelNotFoundException('User not found');
        }

        return $this->mapUserToDto($user);
    }

    public function updateUser(int $userId, array $data, ?\Illuminate\Http\UploadedFile $avatar = null): UserDto
    {
        if (isset($data['email'])) {
            $existingUser = $this->userRepository->findUserByEmail($data['email']);
            if ($existingUser && $existingUser->id !== $userId) {
                throw new \InvalidArgumentException('Email already exists');
            }
        }

        if ($avatar) {
            $data['avatar'] = $this->userRepository->storeAvatar($avatar);
        }

        $this->userRepository->updateUser($userId, $data);
        $user = $this->userRepository->findUserById($userId);

        return $this->mapUserToDto($user);
    }

    private function mapUserToDto(User $user): UserDto
    {
        return new UserDto(
            name: $user->name,
            email: $user->email,
            avatar: $user->avatar,
            role: $user->role,
        );
    }
}
