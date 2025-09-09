<?php

namespace App\Services\Films;

use App\Models\Film;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

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

    /*
     * Сохранение файла указанного типа
     */
    public function saveFile(string $url, string $type, string $name): string
    {
        // Рекомендации:
        // Хранить файл с hash суффиксом, или иначе контролировать кеширование (при использовании hash в имени - нужно удалять старые версии)
        // Ограничивать к-во файлов в одной папке

        $file = Http::get($url)->body();
        $ext = pathinfo($url, PATHINFO_EXTENSION);
        $path = $type . DIRECTORY_SEPARATOR . $name . ".$ext";

        Storage::disk('public')->put($path, $file);

        return Storage::disk('public')->url($path);
    }
}
