<?php

namespace App\DTO\Auth;

class UserDto
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $avatar,
        public int $role,
    ) {}
}
