<?php

namespace App\Repositories\Favorites;

use App\Models\FavoriteFilm;
use App\Models\Film;
use App\Models\User;

class FavoriteRepository
{
    public function getUserFavorites(int $userId)
    {
        return Film::whereHas('favoritedBy', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->with(['genres'])
            ->join('favorite_films', 'films.id', '=', 'favorite_films.film_id')
            ->where('favorite_films.user_id', $userId)
            ->orderBy('favorite_films.created_at', 'desc')
            ->select('films.*') // важно выбрать только поля films
            ->get();
//        return Film::whereHas('favoritedBy', function ($query) use ($userId) {
//            $query->where('user_id', $userId);
//        })
//            ->with(['genres'])
//            ->orderBy('favorite_films.created_at', 'desc')
////            ->orderBy('favorite_films.id', 'desc')
//            ->get();
    }

    public function addToFavorites(int $userId, int $filmId): void
    {
        $user = User::findOrFail($userId);
        $film = Film::findOrFail($filmId);
//        dump("addToFavorites: begin userId=$userId, filmId=$filmId");

        if ($user->favoriteFilms()->where('film_id', $filmId)->exists()) {
//            dump("addToFavorites: Exception Фильм уже в избранном");
            throw new \Exception('Фильм уже в избранном');
        }
//        dump("addToFavorites: check Фильм уже в избранном");

//        $favorite = new FavoriteFilm();
//        $favorite->user_id = $userId;
//        $favorite->film_id = $filmId;
//        $favorite->save();
//        dump("addToFavorites: end");
        $user->favoriteFilms()->attach($filmId);
    }

    public function removeFromFavorites(int $userId, int $filmId): void
    {
        $user = User::findOrFail($userId);
        $film = Film::findOrFail($filmId);

        if (!$user->favoriteFilms()->where('film_id', $filmId)->exists()) {
            throw new \Exception('Film not in favorites');
        }

        $user->favoriteFilms()->detach($filmId);
    }

    public function isFilmInFavorites(int $userId, int $filmId): bool
    {
        return Film::where('id', $filmId)
            ->whereHas('favoritedBy', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->exists();
    }
}
