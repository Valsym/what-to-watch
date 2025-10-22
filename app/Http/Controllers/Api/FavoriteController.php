<?php

namespace App\Http\Controllers\Api;

use App\Http\Responses\ErrorResponse;
use App\Http\Responses\Success;
use App\Models\FavoriteFilm;
use App\Models\Film;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userId = auth()->id();
        $perPage = 8;

        $ff = FavoriteFilm::where('user_id', $userId)->
            with('film'
//                ['film' => function ($query) {
//            $query->with(
//                [
//                    'genres:genres.id,genres.name',
////                    'actors:actors.id,actors.name',
////                    'directors:directors.id,directors.name',
//                ]
//            );
//        }]
        )->latest()->paginate($perPage);//->get();

//        $items = collect($ff->items());
//        $formatted = $items->map(
//            function ($favorite) {
//                $film = $favorite->film;
//                $film->is_favorite = true;
//                $film->added_at = $favorite->created_at->format('Y-m-d H:i:s');
//                return new filmResource($film);
//            }
//        );
//
//        return $this->success(FilmListResource::collection($formatted));

        return $this->success($ff);
//        dd($ff);//FilmListResource::collection($ff));
//        return $this->success(FilmListResource::collection($ff));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(int $filmId): Success|ErrorResponse
    {
        Film::findOrFail($filmId);

        $show = $this->show($filmId);

        if($show->statusCode === 200) {
//            return $this->success(['message' => 'Фильм уже в избранном'], 200);
            return $this->error('Фильм уже в избранном', [], 401);
        }

        $userId = auth()->id();

        $ff = new FavoriteFilm();
        $ff->user_id = $userId;
        $ff->film_id = $filmId;
        $ff->save();
        // or
//        FavoriteFilm::create(
//            [
//                'user_id' => $usrId,
//                'film_id' => $filmId,
//            ]
//        );

        return $this->success(['message' =>
            "Фильм успешно добавлен в избранное!"], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($filmId): Success|ErrorResponse
    {
        $userId = auth()->id();
        if(FavoriteFilm::where('user_id', $userId)->
            where('film_id', $filmId)->first()) {
            return $this->success(['message' => 'Фильм найден в избранном'], 200);
        }

//        return $this->ErrorResponse([], 400);
//        return $this->ErrorResponse('Фильм не найден', [], 404);
        return $this->error('Фильм не найден в избранном', [], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return $this->success([], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($filmId)
    {
//        dump($this->show($filmId));

        if($this->show($filmId)->statusCode !== 200) {
            return $this->success(['message' =>
                'Фильм уже отсутствует в избранном'], 201);
        }

        $favoriteFilm = FavoriteFilm::where('film_id', $filmId)->first();
        $favoriteFilm->delete();
//        FavoriteFilm::destroy($favoriteFilm->id);

        if($this->show($filmId)->statusCode !== 200) {
            return $this->success(['message' =>
                'Фильм успешно удален из избранного!'], 201);
        }

        return $this->error(
            'Не удалось удалить фильм из избранного',
            [], 404);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function status($id)
    {
        return $this->success([]);
    }
}
