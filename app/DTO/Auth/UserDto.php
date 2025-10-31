<?php

namespace App\DTO\Auth;

use Illuminate\Contracts\Support\Arrayable;

class UserDto implements Arrayable
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $avatar,
        public int $role,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'role' => $this->role,
        ];
    }
}
