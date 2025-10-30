<?php

namespace App\Services\External;

use App\Contracts\ExternalFilmRepositoryInterface;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class OmdbFilmRepository implements ExternalFilmRepositoryInterface
{
    private string $apiKey;
    private string $baseUrl = 'http://www.omdbapi.com/';
    private PendingRequest $client;

    public function __construct()
    {
        $this->apiKey = config('services.omdb.api_key');
        $this->client = Http::timeout(30)
            ->retry(3, 1000) // 3 попытки с задержкой 1 секунда
            ->withHeaders([
                'Accept' => 'application/json',
            ]);
    }

    public function getFilmData(string $imdbId): ?array
    {
        // Лимитируем запросы: максимум 10 в минуту
        $executed = RateLimiter::attempt(
            'omdb-api:' . $imdbId,
            10,
            function() use ($imdbId) {
                return $this->makeRequest($imdbId);
            },
            60 // 1 минута
        );

        if (!$executed) {
            Log::warning('OMDB API rate limit exceeded', ['imdb_id' => $imdbId]);
            return null;
        }

        return $executed;
    }

    public function isAvailable(): bool
    {
        try {
            // Простой тестовый запрос для проверки доступности
            $response = $this->client->get($this->baseUrl, [
                'i' => 'tt0133093', // The Matrix
                'apikey' => $this->apiKey,
            ]);

            return $response->successful() && $response->json('Response') === 'True';
        } catch (Exception $e) {
            Log::error('OMDB API availability check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function makeRequest(string $imdbId): ?array
    {
        try {
            $response = $this->client->get($this->baseUrl, [
                'i' => $imdbId,
                'apikey' => $this->apiKey,
                'plot' => 'full'
            ]);

            if ($response->successful() && $response->json('Response') === 'True') {
                return $this->transformData($response->json());
            }

            Log::error('OMDB API error', [
                'imdb_id' => $imdbId,
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('OMDB API exception', [
                'imdb_id' => $imdbId,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    private function transformData(array $data): array
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
