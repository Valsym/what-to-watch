<?php
// app/Services/FilmService.php

namespace App\Services\Films;

//use App\Repositories\FilmRepository;
use App\DTOs\FilmListQueryParams;
use App\Repositories\Films\FilmRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Http\Resources\FilmListResource;
use App\Http\Resources\FilmResource;

class FilmService
{
    public function __construct(
        private FilmRepository $filmRepository
    ) {}

    public function getFilmsList(FilmListQueryParams $params): array
    {
        $filmsPaginator = $this->filmRepository->getFilmsList($params);

        return [
            'data' => FilmListResource::collection($filmsPaginator->items()),
            'current_page' => $filmsPaginator->currentPage(),
            'first_page_url' => $filmsPaginator->url(1),
            'next_page_url' => $filmsPaginator->nextPageUrl(),
            'prev_page_url' => $filmsPaginator->previousPageUrl(),
            'per_page' => $filmsPaginator->perPage(),
            'total' => $filmsPaginator->total(),
        ];
    }

    // Дополнительные сервисные методы
    public function getFilmDetails(int $filmId): ?array
    {
        $film = $this->filmRepository->findById($filmId);

        if (!$film) {
            return null;
        }

        return [
            'data' => new FilmResource($film)
        ];
    }
}
