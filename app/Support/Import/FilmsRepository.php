<?php

namespace App\Support\Import;

interface FilmsRepository
{
    /**
     * @param string $imdbId
     * @return array{show: \App\Models\Film, genres: array, links: array}|null
     */
    public function getFilm(string $imdbId): ?array;
}
