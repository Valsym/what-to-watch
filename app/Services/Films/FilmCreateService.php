<?php

namespace App\Services\Films;

use App\Models\Film;
use App\Repositories\Films\FilmRepository;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Сервис создания нового фильма
 */
class FilmCreateService
{
    /**
     * @psalm-suppress PossiblyUnusedMethod
     * Laravel DI автоматически вызывает этот конструктор
     */
    public function __construct(protected FilmRepository $repository)
    {

    }

    /**
     * Создает новый фильм
     *
     * @throws Throwable
     */
    public function createFilm(array $data): Film
    {
        return DB::transaction(
            function () use ($data) {
                $film = $this->repository->create($data);

                if (!empty($data['genre_id'])) {
                    $this->repository->syncGenres($film, (array) $data['genre_id']);
                }

                if (!empty($data['actor_id'])) {
                    $this->repository->syncActors($film, (array) $data['actor_id']);
                }

                return $this->repository->loadRelations($film);
            }
        );
    }

}
