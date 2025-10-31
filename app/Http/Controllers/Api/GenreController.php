<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Genre\UpdateGenreRequest;
use App\Http\Resources\GenreResource;
use App\Http\Responses\Success;
use App\Services\Genres\GenreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function __construct(private GenreService $genreService) {}

    /**
     * Получение списка жанров
     */
    public function index(): Success
    {
        $genres = $this->genreService->getAllGenres();

//        return $this->success($genres);
        return $this->success(GenreResource::collection($genres));

    }

    /**
     * Обновление жанра
     */
    public function update(UpdateGenreRequest $request, int $id): Success
    {
        $genre = $this->genreService->updateGenre($id, $request->input('name'));

//        return $this->success($genre);
        return $this->success(new GenreResource($genre));
    }

    /**
     * @deprecated Этот метод больше не используется
     */
    public function insert(Request $request, $id): Success
    {
        return $this->success([]);
    }
}
