<?php

namespace App\DTOs;

use App\Http\Requests\Films\StoreFilmRequest;

/**
 * DTO для создания фильмов
 */
class CreateFilmData
{
    public function __construct(
        public string $imdbId,
        public string $status = 'pending'
    )
    {
    }

    public static function fromRequest(StoreFilmRequest $request): self
    {
        return new self(
            imdbId: $request->input('imdb_id'),
            status: 'pending' // По ТЗ всегда 'pending' при создании
        );
    }
}
