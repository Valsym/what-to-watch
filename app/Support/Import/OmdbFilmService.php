<?php
namespace App\Support\Import;

class OmdbFilmService
{
    public function __construct(private FilmsRepository $repository)
    {
    }

    public function requestFilm(string $imdbId)
    {
        return $this->repository->getFilm($imdbId);
    }
}
