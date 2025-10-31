<?php

namespace App\DTO\Auth;

use Illuminate\Http\UploadedFile;

class RegisterDto
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?UploadedFile $avatar = null,
    ) {}
}
