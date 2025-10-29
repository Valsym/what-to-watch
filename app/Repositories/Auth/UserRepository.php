<?php

namespace App\Repositories\Auth;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;

class UserRepository
{
    public function __construct(private User $user) {}

    public function createUser(array $data): User
    {
        return $this->user->create($data);
    }

    public function findUserByEmail(string $email): ?User
    {
        return $this->user->where('email', $email)->first();
    }

    public function findUserById(int $userId): ?User
    {
        return $this->user->find($userId);
    }

    public function updateUser(int $userId, array $data): bool
    {
        $user = $this->findUserById($userId);
        if (!$user) {
            throw new ModelNotFoundException('User not found');
        }

        return $user->update($data);
    }

    public function storeAvatar(UploadedFile $file): string
    {
        return $file->store('avatars', 'public');
    }

    public function validateCredentials(User $user, string $password): bool
    {
        return Hash::check($password, $user->password);
    }
}
