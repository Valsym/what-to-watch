<?php
namespace App\Support\Import;
//use App\Models\Episode;
use App\Models\Film;
//use App\Models\Show;
use Carbon\Carbon;
use GuzzleHttp\Psr7\HttpFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
class TvmazeRepository implements FilmsRepository
{
    private const STATUSES = [
        "Ended" => "pending",
        "Running" => "ready",
        "To Be Determined" => "moderate",
    ];
    public function getFilm(string $imdbId): ?array
    {
        $data = $this->api($imdbId);
        if ($data->clientError()) {
            return null;
        }

        $show = Film::firstOrNew(["imdb_id" => $imdbId]);
        $show->fill([
            "title" => $show["title"] ?? $data["name"],
            "title_original" => $data["name"],
            "description" => strip_tags($data["summary"]),
            "year" => date("Y", strtotime($data["premiered"])),
            "status" => self::STATUSES[$data["status"]] ??
                strtolower($data["status"]),
            "updated_at" => $data["updated"]
        ]);

        return [
            "film" => $show,
            "genres" => $data["genres"]
        ];
    }
//    public function getEpisodes(string $imdbId): ?Collection
//    {
//        $show = $this->api($imdbId);
//        if ($show->clientError()) {
//            return null;
//        }
//        $data =
//            $this->api("/films/{$show["id"]}/episodes")->collect();
//
//        return $data->map(function ($value) use ($show) {
//            $episode = Episode::firstOrNew([
//                "season" => $value["season"],
//                "episode_number" => $value["number"],
//            ]);
//            $episode->fill([
//                "title" => $value["name"],
//                "air_at" =>
//                    Carbon::parse($value["airstamp"])->toDateTimeString(),
//            ]);
//            return $episode;
//        });
//    }
    private function api(string $imdbId)
    {
        $apiKey = $_ENV['OMDB_API_KEY'] ?? null;

        if (!$apiKey) {
            throw new Exception('Не найден OMDB_API_KEY');
        }

        $url = trim(config('services.omdb.films.url'), '/');

        return (new HttpFactory())->createRequest('get',
            "$url?i=$imdbId&apikey=$apiKey");

//        return
//            Http::baseUrl(config("services.tvmaze.url"))->get($path, $params);
    }
}
