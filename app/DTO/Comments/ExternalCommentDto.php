<?php

namespace App\DTO\Comments;

use Carbon\Carbon;

class ExternalCommentDto
{
    public function __construct(
        public string $imdb_id,
        public string $text,
        public ?int $rating,
        public string $author,
        public Carbon $created_at,
    ) {}
}
