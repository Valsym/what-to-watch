<?php

namespace App\DTO\Auth;

use Illuminate\Contracts\Support\Arrayable;

class TokenDto implements Arrayable
{
    public function __construct(
        public string $token
    ) {}

    public function toArray(): array
    {
        return [
            'token' => $this->token
        ];
    }
}
//class TokenDto
//{
//    public function __construct(
//        public string $token,
////        public string $type = 'Bearer'
//    ) {}
//}
