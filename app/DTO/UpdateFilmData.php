<?php

namespace App\DTO;

use App\Http\Requests\Films\UpdateFilmRequest;

/**
 * DTO для обновления фильмов
 */
class UpdateFilmData
{
    public function __construct(
        public ?string $name = null,
        public ?string $posterImage = null,
        public ?string $previewImage = null,
        public ?string $backgroundImage = null,
        public ?string $backgroundColor = null,
        public ?string $videoLink = null,
        public ?string $previewVideoLink = null,
        public ?string $description = null,
        public ?string $director = null,
        public ?array  $starring = null,
        public ?array  $genres = null,
        public ?int    $runTime = null,
        public ?int    $released = null,
        public ?string $imdbId = null,
        public ?string $status = null
    )
    {
    }

    public static function fromRequest(UpdateFilmRequest $request): self
    {
        return new self(
            name: $request->input('name'),
            posterImage: $request->input('poster_image'),
            previewImage: $request->input('preview_image'),
            backgroundImage: $request->input('background_image'),
            backgroundColor: $request->input('background_color'),
            videoLink: $request->input('video_link'),
            previewVideoLink: $request->input('preview_video_link'),
            description: $request->input('description'),
            director: $request->input('director'),
            starring: $request->input('starring'),
            genres: $request->input('genre'),
            runTime: $request->input('run_time'),
            released: $request->input('released'),
            imdbId: $request->input('imdb_id'),
            status: $request->input('status')
        );
    }
}
