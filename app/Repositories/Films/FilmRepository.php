<?php
// app/Repositories/FilmRepository.php

namespace App\Repositories\Films;

use App\Models\Film;
use App\DTOs\FilmListQueryParams;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class FilmRepository
{
    public function __construct(
        private Film $film
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
}
