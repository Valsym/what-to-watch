<?php

namespace App\DTO\Comments;

class StoreCommentDto
{
    public function __construct(
        public string $text,
        public int $rating,
        public int $film_id,
        public int $user_id,
        public ?int $parent_id = null,
    ) {}
}
