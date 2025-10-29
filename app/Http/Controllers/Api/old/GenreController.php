<?php

namespace App\Http\Controllers\Api\old;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\GenreResource;
use App\Http\Responses\Success;
use App\Models\Genre;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    /**
     * Список жанров
     *
     * @return Success
     */
    public function index(): Success
    {
        $genres = Genre::all();

        return $this->success(GenreResource::collection($genres));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function insert(Request $request, $id)
    {
        return $this->success([]);
    }

    /**
     * Обновление жанра
     *
     * @param Request $request
     * @param $id
     * @return Success
     */
    public function update(Request $request, int $id): Success
    {
        $genre = Genre::findOrFail($id);

        $genre->update($request->only('name'));

        return $this->success(new GenreResource($genre));
    }

}
