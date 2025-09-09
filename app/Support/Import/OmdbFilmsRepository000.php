<?php


namespace App\Support\Import;

use App\Models\Film;
use Illuminate\Support\Facades\Http;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Throwable;
use Exception;

class OmdbFilmsRepository000 implements FilmsRepository
{
    /**
     * @param RequestFactoryInterface $httpFactory
     * @param ClientInterface $httpClient
     */
    public function __construct(
        RequestFactoryInterface $httpFactory,
        ClientInterface $httpClient
    ) {
        $this->httpFactory = $httpFactory;
        $this->httpClient = $httpClient;
    }

    /**
     * Получает информацию о фильме по его IMDb ID
     *
     * @param string $imdbId
     * @return array|null
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function getFilm(string $imdbId): ?array
    {
//        try {
            $response = $this->httpClient->sendRequest($this->createRequest($imdbId));
            $data = json_decode($response->getBody()->getContents(), true);
            //$response->getBody()->getContents();
//        } catch (Throwable $e) {
//            $this->error = 'Ошибка при запросе информации с удаленного сервера';
//            return null;
//        }
        if ($data->clientError()) {
            return null;
        }

        return $data;

        $film = Film::firstOrNew(['imdb_id' => $imdbId]);

        $film->fill([
            'name' => $data['name'],
            'description' => $data['desc'],
            'director' => $data['director'],
            'starring' => $data['actors'],
            'run_time' => $data['run_time'],
            'released' => $data['released'],
        ]);

        $links = [
            'poster_image' => $data['poster'],
            'preview_image' => $data['icon'],
            'background_image' => $data['background'],
            'video_link' => $data['video'],
            'preview_video_link' => $data['preview'],
        ];

        return [
            'film' => $film,
            'genres' => $data['genres'],
            'links' => $links,
        ];
    }

    /**
     * Создаёт HTTP-запрос для получения информации о фильме по IMDb ID
     *
     * @param string $imdbId - IMDb ID фильма
     * @return RequestInterface
     */
    private function createRequest(string $imdbId): RequestInterface
    {
        $api = "http://www.omdbapi.com/?apikey=7e723ed9";

        return ($this->httpFactory->createRequest('get', "$api&i=$imdbId"));

        $apiKey = $_ENV['OMDB_API_KEY'] ?? null;

        if (!$apiKey) {
            throw new Exception('Не найден OMDB_API_KEY');
        }

        $url = trim(config('services.omdb.films.url'), '/');

        return $this->httpFactory->createRequest('get',
            "$url?i=$imdbId&apikey=$apiKey");
    }
}
