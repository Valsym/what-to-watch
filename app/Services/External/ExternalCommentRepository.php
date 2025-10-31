<?php

namespace App\Services\External;

use App\Contracts\ExternalCommentRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class ExternalCommentRepository implements ExternalCommentRepositoryInterface
{
    private string $baseUrl;
    private string $apiKey;
    private PendingRequest $client;

    public function __construct()
    {
        $this->baseUrl = 'http://www.omdbapi.com/';//config('services.external_comments.base_url');
        $this->apiKey = config('services.external_comments.api_key');

        $this->client = Http::timeout(30)
            ->retry(3, 1000)
            ->withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
            ]);
    }

    public function getRecentComments(Carbon $since): Collection
    {
        // Лимитируем запросы
        $executed = RateLimiter::attempt(
            'external-comments-api',
            $this->getRateLimit(),
            function() use ($since) {
                return $this->makeRequest($since);
            },
            60 // 1 минута
        );

        if (!$executed) {
            Log::warning('External comments API rate limit exceeded');
            return collect();
        }

        return $executed;
    }

    public function isAvailable(): bool
    {
        if (empty($this->apiKey)) {
            Log::warning('ExternalCommentRepository: apiKey is empty');
            return false;
        }

        try {
//            Log::info('try get response from '.$this->baseUrl);
            $response = $this->client->get($this->baseUrl);// . '/health');

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('External comments API availability check failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function getRateLimit(): int
    {
        return config('services.external_comments.rate_limit', 10);
    }

    private function makeRequest(Carbon $since): Collection
    {
        try {
            $response = $this->client->get($this->baseUrl . '/comments', [
                'since' => $since->toISOString(),
            ]);

            if ($response->successful()) {
                return $this->transformData($response->json());
            }

            Log::error('External comments API error', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return collect();
        } catch (\Exception $e) {
            Log::error('External comments API exception', [
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }

    private function transformData(array $data): Collection
    {
        return collect($data)->map(function ($item) {
            return [
                'imdb_id' => $item['imdb_id'] ?? null,
                'text' => $item['text'] ?? '',
                'rating' => $item['rating'] ?? null,
                'author' => $item['author'] ?? 'Гость',
                'created_at' => isset($item['created_at'])
                    ? Carbon::parse($item['created_at'])
                    : now(),
            ];
        })->filter(function ($item) {
            // Фильтруем некорректные данные
            return !empty($item['imdb_id']) && !empty($item['text']);
        });
    }
}
