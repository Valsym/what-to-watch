<?php

namespace App\Services\Films;

use App\Models\Film;

class FilmService
{
    /**
     * Получение похожих фильмов
     *
     * @param Film $film
     * @param array $fields
     * @return mixed
     */
    public function getSimilarFor(Film $film, array $fields = ['*'])
    {
        return Film::select($fields)
            ->whereHas('genres', function ($query) use ($film) {
                $query->whereIn('genres.id', $film->genres()->pluck('genres.id'));
            })
            ->where('id', '!=', $film->id)
            ->take(config('app.api.films.similar.limit', 4))
            ->get();
    }

    public function getPromo()
    {
        return Film::promo()->latest('updated_at')->first();
    }
}
