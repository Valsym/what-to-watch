<?php

namespace App\DTO\Comments;

class UpdateCommentDto
{
    public function __construct(
        public string $text,
        public ?int $rating = null,
    ) {}
}
