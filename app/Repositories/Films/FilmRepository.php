<?php
// app/Repositories/FilmRepository.php

namespace App\Repositories\Films;

use App\DTOs\CreateFilmData;
use App\DTOs\UpdateFilmData;
use App\Models\Film;
use App\DTOs\FilmListQueryParams;
use App\Models\Genre;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FilmRepository
{
    public function __construct(
        private Film $film,
        private Genre $genre
    ) {}

    public function getFilmsList(FilmListQueryParams $params): LengthAwarePaginator
    {
        $query = $this->film->newQuery()->with('genres');

        // Фильтрация по жанру
        if ($params->genre) {
            $query->whereHas('genres', function (Builder $q) use ($params) {
                $q->where('name', $params->genre);
            });
        }

        // Фильтрация по статусу
        if ($params->status && $params->isModerator) {
            $query->where('status', $params->status);
        } else {
            $query->where('status', Film::STATUS_READY);
        }

        // Поиск (если нужно)
        if ($params->search) {
            $query->where('name', 'like', "%{$params->search}%");
        }

        // Сортировка
        $query->ordered($params->orderBy, $params->orderTo);

        return $query->paginate($params->perPage, ['*'], 'page', $params->page);
    }

    // Дополнительные методы репозитория
    public function findById(int $id): ?Film
    {
        return $this->film->with(['genres', 'comments.user'])->find($id);
    }

    public function getPromoFilms(): Collection
    {
        return $this->film->where('promo', true)
            ->where('status', Film::STATUS_READY)
            ->ordered('released', 'desc')
            ->limit(8)
            ->get();
    }

    public function createFilm(CreateFilmData $data): Film
    {
        return DB::transaction(function () use ($data) {
            $film = $this->film->create([
                'imdb_id' => $data->imdbId,
                'status' => Film::STATUS_PENDING,
            ]);

            return $film;
        });
    }

    public function updateFilm(int $filmId, UpdateFilmData $data): Film
    {
        return DB::transaction(function () use ($filmId, $data) {
            $film = $this->film->findOrFail($filmId);

            $updateData = array_filter([
                'name' => $data->name,
                'poster_image' => $data->posterImage,
                'preview_image' => $data->previewImage,
                'background_image' => $data->backgroundImage,
                'background_color' => $data->backgroundColor,
                'video_link' => $data->videoLink,
                'preview_video_link' => $data->previewVideoLink,
                'description' => $data->description,
                'director' => $data->director,
                'starring' => $data->starring,
                'run_time' => $data->runTime,
                'released' => $data->released,
                'imdb_id' => $data->imdbId,
                'status' => $data->status,
            ], fn($value) => !is_null($value));

            $film->update($updateData);

            // Обновляем жанры если переданы
            if (!is_null($data->genres)) {
                $this->syncGenres($film, $data->genres);
            }

            return $film->fresh(['genres']);
        });
    }

    public function updateFilmFromOmdb(int $filmId, array $omdbData): Film
    {
        return DB::transaction(function () use ($filmId, $omdbData) {
            $film = $this->film->findOrFail($filmId);

            $film->update([
                'name' => $omdbData['name'],
                'released' => $omdbData['released'],
                'description' => $omdbData['description'],
                'run_time' => $omdbData['run_time'],
                'director' => $omdbData['director'],
                'starring' => $omdbData['starring'],
                'poster_image' => $omdbData['poster_image'],
                'status' => Film::STATUS_ON_MODERATION,
            ]);

            // Синхронизируем жанры из OMDB
            if (!empty($omdbData['genre'])) {
                $this->syncGenresFromNames($film, $omdbData['genre']);
            }

            return $film->fresh(['genres']);
        });
    }

    /**
     * Привязывает жанры к фильму
     *
     * @param Film $film
     * @param array $genreIds
     * @return void
     */
    private function syncGenres(Film $film, array $genreIds): void
    {
        $film->genres()->sync($genreIds);
    }

    private function syncGenresFromNames(Film $film, array $genreNames): void
    {
        $genreIds = [];

        foreach ($genreNames as $genreName) {
            $genre = $this->genre->firstOrCreate(['name' => $genreName]);
            $genreIds[] = $genre->id;
        }

        $film->genres()->sync($genreIds);
    }

    /**
     * Ищет фильм по ID
     *
     * @param string $imdbId
     * @return bool
     */
    public function findByIdOrFail(int $filmId): Film
    {
        return $this->film->findOrFail($filmId);
    }

    // Метод для проверки уникальности с исключением
    public function filmExistsByImdbId(string $imdbId, ?int $excludeFilmId = null): bool
    {
        $query = $this->film->where('imdb_id', $imdbId);

        if ($excludeFilmId) {
            $query->where('id', '!=', $excludeFilmId);
        }

        return $query->exists();
    }

    public function getSimilarFilms(int $filmId, int $limit = 4): Collection
    {
        // Получаем текущий фильм и его жанры
        $film = $this->film->with('genres')->findOrFail($filmId);
        $genreIds = $film->genres->pluck('id')->toArray();

        if (empty($genreIds)) {
            return collect();
        }

        // Ищем фильмы с такими же жанрами, исключая текущий
        return $this->film
            ->where('id', '!=', $filmId)
            ->whereHas('genres', function ($query) use ($genreIds) {
                $query->whereIn('genres.id', $genreIds);
            })
            ->with(['genres'])
            ->limit($limit)
            ->get();
    }
}
