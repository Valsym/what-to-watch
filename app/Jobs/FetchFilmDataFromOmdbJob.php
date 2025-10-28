<?php

namespace App\Jobs;

use App\Services\Films\FilmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchFilmDataFromOmdbJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $filmId
    ) {}

    public function handle(FilmService $filmService): void
    {
        $filmService->fetchAndUpdateFromOmdb($this->filmId);
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('Failed to fetch OMDB data for film', [
            'film_id' => $this->filmId,
            'error' => $exception->getMessage()
        ]);
    }
}
