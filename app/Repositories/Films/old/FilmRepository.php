<?php

namespace App\Repositories\Films\old;

use App\Models\Film;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Репозиторий для фильмов
 *
 * @template TModel of Model
 */
final class FilmRepository
{
    /**
     * Создает фильм
     *
     * @param  array $data
     * @return Film
     */
    public function create(array $data): Film
    {
        /**
         * @var Film $film
         */
        $film = Film::create($data);
        return $film;
    }

    /**
     * Обновляет данные фильма
     *
     * @param Film  $film
     * @param array $data
     *
     * @return void
     */
    public function update(Film $film, array $data): void
    {
        $film->update($data);
    }

    /**
     * Привязывает жанры к фильму
     *
     * @param  Film  $film
     * @param  array $genreIds
     * @return void
     */
    public function syncGenres(Film $film, array $genreIds): void
    {
        $film->genres()->sync($genreIds);
    }

    /**
     * Привязывает актёров к фильму
     *
     * @param  Film  $film
     * @param  array $actorIds
     * @return void
     */
    public function syncActors(Film $film, array $actorIds): void
    {
        $film->actors()->sync($actorIds);
    }

    /**
     * Загружает связи
     *
     * @param  Film $film
     * @return Film
     */
    public function loadRelations(Film $film): Film
    {
        /**
         * @var Film $film
         */
        $film = $film->load('genres');//, 'starring', 'director');
        return $film;
    }

    /**
     * Ищет фильм по ID
     *
     * @param int $id
     *
     * @return Film
     */
    public function findOrFail(int $id): Film
    {
        return Film::findOrFail($id);
    }

    /**
     * Получить похожие фильмы по жанрам фильма с заданным id
     *
     * @param  int $filmId
     * @param  int $limit
     * @return Collection
     *
     * @psalm-return Collection<int, Film>
     */
    public function getSimilarFilmsByGenres(int $filmId, int $limit = 4): Collection
    {
        $film = $this->findOrFail($filmId);

        /**
         * @var Builder<Film> $query
         */
        /**
         * @psalm-suppress UndefinedMagicMethod
         */
        $query = Film::with(['genres'])
            ->whereHas(
                'genres', function ($query) use ($film) {
                $query->whereIn('genres.id', $film->genres->pluck('id'));
            }
            )
            ->where('id', '!==', $film->id)
            ->orderBy('released', 'desc')
            ->limit($limit);

        return $query->get();
    }

    /**
     * Получить текущий промо-фильм
     *
     * @return Film
     */
    public function getPromoFilm(): Film
    {
        /**
         * @var Film $film
         */
        $film = Film::where('promo', true)
            ->with(['genres', 'actors', 'directors'])
            ->firstOrFail();

        return $film;
    }

    /**
     * Сбросить флаг promo у всех фильмов
     */
    public function resetPromoFlags(): void
    {
        Film::where('promo', true)->update(['promo' => false]);
    }

    /**
     * Установить флаг promo для фильма по ID
     *
     * @param int $filmId
     * Количество обновленных записей (должно быть 1)
     */
    public function setPromoFlag(int $filmId): void
    {
        Film::where('id', $filmId)->update(['promo' => true]);
    }

}
