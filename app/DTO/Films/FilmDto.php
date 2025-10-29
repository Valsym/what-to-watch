<?php

namespace App\DTO\Films;

use Illuminate\Contracts\Support\Arrayable;

class FilmDto implements Arrayable
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
        public bool    $promo,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'poster_image' => $this->poster_image,
            'preview_image' => $this->preview_image,
            'background_image' => $this->background_image,
            'background_color' => $this->background_color,
            'video_link' => $this->video_link,
            'preview_video_link' => $this->preview_video_link,
            'description' => $this->description,
            'rating' => $this->rating,
//        public int     $scores_count,
            'director' => $this->director,
            'starring' => $this->starring,
            'run_time' => $this->run_time,
            'genre' => $this->genre,
            'released' => $this->released,
            'is_favorite' => $this->is_favorite,
            'promo' => $this->promo,
        ];
    }
}
