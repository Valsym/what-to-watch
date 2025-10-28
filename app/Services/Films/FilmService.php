<?php
// app/Services/FilmService.php

namespace App\Services\Films;

//use App\Repositories\FilmRepository;
use App\DTOs\CreateFilmData;
use App\DTOs\FilmListQueryParams;
use App\DTOs\Films\SimilarFilmDto;
use App\DTOs\UpdateFilmData;
use App\Jobs\FetchFilmDataFromOmdbJob;
use App\Repositories\Films\FilmRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Http\Resources\FilmListResource;
use App\Http\Resources\FilmResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FilmService
{
    public function __construct(
        private FilmRepository $filmRepository,
        private OmdbService $omdbService
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

    public function createFilm(CreateFilmData $data): array
    {
        // Проверяем уникальность imdb_id
        if ($this->filmRepository->filmExistsByImdbId($data->imdbId)) {
            throw new \InvalidArgumentException('Фильм с таким IMDB ID уже существует');
        }

        $film = $this->filmRepository->createFilm($data);

        // Запускаем фоновую задачу для получения данных из OMDB
        FetchFilmDataFromOmdbJob::dispatch($film->id);

        return [
            'data' => new \App\Http\Resources\FilmResource($film)
        ];
    }

    public function updateFilm(int $filmId, UpdateFilmData $data): array
    {
        try {
            // Проверяем уникальность imdb_id (исключая текущий фильм)
            if ($data->imdbId && $this->filmRepository->filmExistsByImdbId($data->imdbId, $filmId)) {
                throw new \InvalidArgumentException('Фильм с таким IMDB ID уже существует');
            }

            $film = $this->filmRepository->updateFilm($filmId, $data);

            return [
                'data' => new \App\Http\Resources\FilmResource($film)
            ];
        } catch (ModelNotFoundException $e) {
            throw new \InvalidArgumentException('Фильм не найден', 404);
        }
    }

    public function fetchAndUpdateFromOmdb(int $filmId): void
    {
        $film = $this->filmRepository->findByIdOrFail($filmId);

        $omdbData = $this->omdbService->getFilmData($film->imdb_id);

        if ($omdbData) {
            $this->filmRepository->updateFilmFromOmdb($filmId, $omdbData);
        } else {
            // Логируем ошибку, но не прерываем выполнение
            \Log::error('Failed to fetch data from OMDB for film', ['film_id' => $filmId]);
        }
    }

    public function getSimilarFilms(int $filmId): array
    {
        $films = $this->filmRepository->getSimilarFilms($filmId);

        return $films->map(function ($film) {
            return new SimilarFilmDto(
                id: $film->id,
                name: $film->name,
                poster_image: $film->poster_image,
                preview_image: $film->preview_image,
                background_image: $film->background_image,
                background_color: $film->background_color,
                video_link: $film->video_link,
                preview_video_link: $film->preview_video_link,
                description: $film->description,
                rating: $film->rating,
//                scores_count: $film->scores_count,
                director: $film->director,
                starring: $film->starring ?? [],
                run_time: $film->run_time,
                genre: $film->genres->pluck('name')->toArray(),
                released: $film->released,
                is_favorite: $film->is_favorite ?? false,
            );
        })->toArray();
    }
}
