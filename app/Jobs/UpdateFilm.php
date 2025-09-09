<?php

namespace App\Jobs;

use App\Exceptions\FilmsRepositoryException;
use App\Models\Film;
use App\Models\Genre;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use App\Support\Import\FilmsRepository;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Exception;
use Throwable;
use GuzzleHttp\Client;
use App\Support\Import\OmdbFilmRepository;
use App\Support\Import\OmdbFilmService;

class UpdateFilm implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Film $film)
    {
    }

    private string $check_file = "files/check-all.txt";
    // C:\Users\Ler2\projects\what-to-watch-12\public\files

    /**
     * @return void
     * @throws FilmsRepositoryException
     */
    public function handle(): void
    {
        file_put_contents($this->check_file, "\r\ndata: ".date("Y-m-d H:i:s"),
            FILE_APPEND | LOCK_EX);
//        return;

        // Получение информации
        $client = new Client();
        $repository = new OmdbFilmRepository($client);
        $service = new OmdbFilmService($repository);

        $data = $service->requestFilm($this->film->imdb_id);//'tt0031382');

        if(empty($data)) {
            file_put_contents($this->check_file, "\r\nОтсутствуют данные для обновления",
                FILE_APPEND | LOCK_EX);
//            return;
            throw new FilmsRepositoryException('Отсутствуют данные для обновления');
        }

        file_put_contents($this->check_file, print_r($data, 1),
            FILE_APPEND | LOCK_EX);

        $this->film = Film::updateOrCreate(
            ['imdb_id' => $data['imdbID'] ?? null],
            [
                'name' => $data['Title'] ?? '',
                'description' => $data['Plot'] ?? '',
                'run_time' => $this->parseRuntime($data['Runtime'] ?? ''),
                'released' => $data['Year'] ?? null,
                'rating' => ($data['imdbRating'] === 'N/A') ? null : $data['imdbRating'],
                'poster_image' => $data['Poster'] ?? '',
                'director' => $data['Director'] ?? '',
                'starring' => json_encode(explode(',', $data['Actors'])) ?? '',
                'status' => Film::STATUS_ON_MODERATION,
            ]
        );

        $genresIds = [];
        foreach (explode(',', $data['Genre']) as $genre) {
            $genresIds[] = Genre::firstOrCreate(['name' => $genre])->id;
        }

        $this->film->genres()->attach($genresIds);

    }

    /**
     * Обработать провал задания.
     */
    public function failed(?Throwable $exception): void
    {
        // Отправляем пользователю уведомление об ошибке и т.п.
        file_put_contents($this->check_file,
            "\r\nПровал задания: $exception",
            FILE_APPEND | LOCK_EX);
    }

    /**
     * Преобразует "142 min" в 142
     *
     * @param string $runtime
     *
     * @return int
     */
    private function parseRuntime(string $runtime): int
    {
        return (int)explode(' ', $runtime)[0] ?? 0;
//        if (preg_match('/(\d+)\s*min/', $runtime, $matches)) {
//            return (int)$matches[1];
//        }
//
//        return 0;
    }
}
