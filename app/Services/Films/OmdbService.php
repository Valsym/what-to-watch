<?php

namespace App\Services\Films;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OmdbService
{
    private string $apiKey;
    private string $baseUrl = 'http://www.omdbapi.com/';
//    $private string $baseUrl = trim(config('services.omdb.films.url'), '/');

    public function __construct()
    {
//        if (!$this->apiKey) {
//            throw new Exception('Не найден OMDB_API_KEY');
//        }

//        $this->apiKey = $_ENV['OMDB_API_KEY'] ?? null;
        $this->apiKey = config('services.omdb.films.api_key');
    }

    public function getFilmData(string $imdbId): ?array
    {
        try {
            $response = Http::timeout(30)->get($this->baseUrl, [
                'i' => $imdbId,
                'apikey' => $this->apiKey,
                'plot' => 'full'
            ]);

            if ($response->successful() && $response->json('Response') === 'True') {
                return $this->transformOmdbData($response->json());
            }

            Log::error('OMDB API error', [
                'imdb_id' => $imdbId,
                'response' => $response->json()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('OMDB API exception', [
                'imdb_id' => $imdbId,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    private function transformOmdbData(array $data): array
    {
        return [
            'name' => $data['Title'] ?? null,
            'released' => isset($data['Year']) ? (int) filter_var($data['Year'], FILTER_SANITIZE_NUMBER_INT) : null,
            'description' => $data['Plot'] ?? null,
            'run_time' => isset($data['Runtime']) ? (int) filter_var($data['Runtime'], FILTER_SANITIZE_NUMBER_INT) : null,
            'director' => $data['Director'] ?? null,
            'starring' => isset($data['Actors']) ? array_map('trim', explode(',', $data['Actors'])) : [],
            'poster_image' => $data['Poster'] ?? null,
            'imdb_rating' => isset($data['imdbRating']) ? (float) $data['imdbRating'] : null,
            'imdb_votes' => isset($data['imdbVotes']) ? (int) str_replace(',', '', $data['imdbVotes']) : null,
            'genre' => isset($data['Genre']) ? array_map('trim', explode(',', $data['Genre'])) : [],
        ];
    }
}
