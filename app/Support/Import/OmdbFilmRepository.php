<?php
namespace App\Support\Import;

use \GuzzleHttp\Psr7\HttpFactory;
use Exception;
use Illuminate\Support\Facades\Http;

class OmdbFilmRepository implements FilmsRepository
{
    public function __construct(private \Psr\Http\Client\ClientInterface $httpClient)
    {
    }

    public function getFilm(string $imdbId): ?array
    {
        $response = $this->httpClient->sendRequest($this->createRequest($imdbId));

        return json_decode($response->getBody()->getContents(), true);
    }

    private function createRequest(string $imdbId)
    {
        $apiKey = $_ENV['OMDB_API_KEY'] ?? null;

        if (!$apiKey) {
            throw new Exception('Не найден OMDB_API_KEY');
        }

        $url = trim(config('services.omdb.films.url'), '/');

        return (new HttpFactory())->createRequest('get',
            "$url?i=$imdbId&apikey=$apiKey");

    }

//    private function api(string $path, array $params = [])
//    {
//        return
//            Http::baseUrl(config("services.tvmaze.url"))->get($path, $params);
//    }

}
