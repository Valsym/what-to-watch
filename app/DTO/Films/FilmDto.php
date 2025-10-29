<?php

namespace App\DTO\Films;

class FilmDto
{
    public function __construct(
        public int     $id,
        public string  $name,
        public ?string $poster_image,
        public ?string $preview_image,
        public ?string $background_image,
        public ?string $background_color,
        public ?string $video_link,
        public ?string $preview_video_link,
        public ?string $description,
        public float   $rating,
//        public int     $scores_count,
        public ?string $director,
        public array   $starring,
        public int     $run_time,
        public array   $genre,
        public int     $released,
        public bool    $is_favorite,
    )
    {
    }
}
