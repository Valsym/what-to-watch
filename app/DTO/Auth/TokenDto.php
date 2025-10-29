<?php

namespace App\DTO\Auth;

class TokenDto
{
    public function __construct(
        public string $token,
//        public string $type = 'Bearer'
    ) {}
}
