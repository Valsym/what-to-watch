<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Http\Responses\Success;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Resources\GenreResource;

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

//        return $this->success([
//            'id' => $this->id,
//            'name' => $this->name,
//        ]);
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return $this->success([]);
    }

}
