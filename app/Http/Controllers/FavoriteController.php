<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Responses\Success;
use App\Http\Responses\ErrorResponse;
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

        return $this->success([]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(int $filmId): Success|ErrorResponse
    {
        $show = $this->show($filmId);

        if($show->statusCode === 200) {
            return $this->success(['message' => 'Фильм уже в избранном'], 200);
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
    public function destroy($id)
    {
        return $this->success([]);
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
