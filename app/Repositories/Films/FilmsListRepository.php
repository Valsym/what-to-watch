<?php

namespace App\Repositories\Films;

use App\Models\Film;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Репозиторий получения списка фильмов
 */
final class FilmsListRepository
{
    /**
     * Получение списка фильмов с фильтрацией и пагинацией.
     *
     * @template TModel of Model
     * @extends  Collection<TModel>
     */
    public function getFilms(array $filters = [], int $perPage = 8): LengthAwarePaginator
    {
        $film =  Film::query()
//            ->with(['genres', 'actors', 'directors'])
            ->when(
                isset($filters['genre']),
                fn ($query) => $query->whereHas(
                    'genres',
                    fn ($query) => $query->where('name', $filters['genre'])
                )
            )
            ->when(
                isset($filters['status']),
                fn ($query) => $query->where('status', $filters['status'])
            )
            ->orderBy($filters['order_by'] ?? 'released', $filters['order_to'] ?? 'desc')
            ->paginate($perPage);

        return $film;
    }
}
