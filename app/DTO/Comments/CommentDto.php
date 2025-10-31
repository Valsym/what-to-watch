<?php

namespace App\DTO\Comments;

use Illuminate\Contracts\Support\Arrayable;

class CommentDto implements Arrayable
{
    public function __construct(
        public int $id,
        public string $text,
        public ?int $rating,
        public ?int $parent_id,
        public ?int $user_id,
        public int $film_id,
        public string $author, // Добавляем author
        public string $created_at,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'rating' => $this->rating,
            'film_id' => $this->film_id,
            'user_id' => $this->user_id,
            'parent_id' => $this->parent_id,
            'author' => $this->author,
            'created_at' => $this->created_at,
        ];
    }
}
