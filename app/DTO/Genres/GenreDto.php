<?php

namespace App\DTO\Genres;

use Illuminate\Contracts\Support\Arrayable;

class GenreDto implements Arrayable
{
    public function __construct(
        public int $id,
        public string $name,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
//class GenreDto
//{
//    public function __construct(
//        public int $id,
//        public string $name,
//    ) {}
//}
