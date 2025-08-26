<?php

namespace App\Services\Films;

use App\Models\FavoriteFilm;
use App\Repositories\Films\FavoriteFilmCheckRepository;

class FavoriteFilmCheckService
{
    /**
     * @psalm-suppress PossiblyUnusedMethod
     * Laravel DI автоматически вызывает этот конструктор
     */
    public function __construct(
        protected FavoriteFilmCheckRepository $repository
    ) {
    }

    /**
     * Проверяет, есть ли фильм в избранном у пользователя
     */
    public function isFavorite(int $filmId, int $userId): bool
    {
        return $this->repository->isFavorite($filmId, $userId);
    }
}
