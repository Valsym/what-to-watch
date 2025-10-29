<?php

namespace App\DTO\Auth;

class LoginDto
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}
}
