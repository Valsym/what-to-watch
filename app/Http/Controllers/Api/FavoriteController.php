<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\FilmListResource;
use App\Http\Resources\FilmResource;
use App\Http\Responses\ErrorResponse;
use App\Http\Responses\Success;
use App\Models\FavoriteFilm;
use App\Models\Film;
use App\Services\Films\FilmService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function __construct(private FilmService $filmService) {}

    /**
     * Display a listing of the resource.
     *
     * @return Success
     */
    public function index0()
    {
        $perPage = 8;

        $favorites = FavoriteFilm::where('user_id', auth()->id())->
            with('film')->latest()->paginate($perPage);

        $items = collect($favorites->items());
        $formatted = $items->map(
            function ($favorite) {
                $film = $favorite->film;
                $film->is_favorite = true;
                $film->added_at = $favorite->created_at->format('Y-m-d H:i:s');
                return new filmResource($film);
            }
        );

        return $this->success(FilmListResource::collection($formatted));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store0(int $filmId): Success|ErrorResponse
    {
        Film::findOrFail($filmId);

        $show = $this->show($filmId);

        if($show->statusCode === 200) {
//            return $this->success(['message' => 'Фильм уже в избранном'], 200);
            return $this->error('Фильм уже в избранном', [], 401);
        }

        $userId = auth()->id();

        $favorite = new FavoriteFilm();
        $favorite->user_id = $userId;
        $favorite->film_id = $filmId;
        $favorite->save();
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
    public function destroy0($filmId)
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

    //+++++++++++++++++++++++++++++++++++++++++++++++

    /**
     * @OA\Get(
     *     path="/api/favorite",
     *     summary="Get user's favorite films",
     *     tags={"Favorites"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of favorite films",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Film"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function index(): JsonResponse
    {
        $userId = auth()->id();
        $favorites = $this->filmService->getUserFavorites($userId);

        return response()->json([
            'data' => $favorites
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/films/{id}/favorite",
     *     summary="Add film to favorites",
     *     tags={"Favorites"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Film ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Film added to favorites",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Film added to favorites")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Film not found"),
     *     @OA\Response(response=422, description="Film already in favorites"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function store(int $id): JsonResponse
    {
        try {
            if(!Film::where('id', $id)->exists()){
                return response()->json([
                    'message' => 'Запрашиваемая страница не существует'
                ], 404);
            }

            $userId = auth()->id();

            $show = $this->show($id);
//            dump("show->statusCode=".$show->statusCode);
            if($show->statusCode === 200) {
                return response()->json([
                    'message' => 'Фильм уже в избранном'
                ], 422);
            }

            $this->filmService->addToFavorites($userId, $id);

            return response()->json([
                'message' => 'Фильм успешно добавлен в избранное!'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Film not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/films/{id}/favorite",
     *     summary="Remove film from favorites",
     *     tags={"Favorites"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Film ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Film removed from favorites",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Film removed from favorites")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Film not found"),
     *     @OA\Response(response=422, description="Film not in favorites"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $userId = auth()->id();
            $this->filmService->removeFromFavorites($userId, $id);

            return response()->json([
                'message' => 'Фильм успешно удален из избранного!'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Film not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

}
