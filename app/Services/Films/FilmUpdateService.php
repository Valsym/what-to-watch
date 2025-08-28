<?php

namespace App\Services\Films;

use App\Models\Film;
//use App\Repositories\Films\FilmRepository;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Сервис обновления фильма
 */
class FilmUpdateService
{
    /**
     * @psalm-suppress PossiblyUnusedMethod
     * Laravel DI автоматически вызывает этот конструктор
     */
//    public function __construct(protected FilmRepository $filmRepository)
//    {
//
//    }

    /**
     * Обновляет данные фильма
     *
     * @throws Throwable
     */
    public function updateFilm(int $id, array $data): Film
    {
        return DB::transaction(
            function () use ($id, $data) {
                $film = Film::findOrFail($id);//$this->filmRepository->findOrFail($id);

                $film->update(//->filmRepository->update(
                    [
                        'name' => $data['name'] ?? $film->name,
                        'description' => $data['description'] ?? $film->description,
                        'rating' => $data['rating'] ?? $film->rating,
                        'released' => $data['released'] ?? $film->released,
                        'run_time' => $data['run_time'] ?? $film->run_time,
                        'background_color' => $data['background_color'] ?? $film->background_color,
                        'poster_image' => $data['poster_image'] ?? $film->poster_image,
                        'preview_image' => $data['preview_image'] ?? $film->preview_image,
                        'background_image' => $data['background_image'] ?? $film->background_image,
                        'video_link' => $data['video_link'] ?? $film->video_link,
                        'preview_video_link' => $data['preview_video_link'] ?? $film->preview_video_link,
                        'imdb_votes' => $data['imdb_votes'] ?? $film->imdb_votes,
                    ]
                );

                if (!empty($data['genre_id'])) {
//                    $this->filmRepository->syncGenres($film, $data['genre_id']);
                    $film->genres()->sync($data['genre_id']);
                }

                if (isset($data['actor_id'])) {
                    $film->actors()->sync($data['actor_id']);
                }

                return $film->load('genres');//, 'actors', 'directors');
            }
        );
    }
}
