<?php

namespace App\Http\Controllers\Api;

use App\DTOs\CreateFilmData;
use App\DTOs\FilmListQueryParams;
use App\DTOs\UpdateFilmData;
use App\Http\Requests\Films\FilmsListRequest;
use App\Http\Requests\Films\StoreFilmRequest;
use App\Http\Requests\Films\UpdateFilmRequest;
use App\Http\Resources\FilmListResource;
use App\Http\Resources\FilmResource;
use App\Http\Responses\Success;
use App\Models\Film;
//use App\Repositories\FilmRepository;
use App\Repositories\Films\FilmRepository;
use App\Services\Films\FilmCreateService;
use App\Services\Films\FilmListService;
use App\Services\Films\FilmService;
use App\Services\Films\FilmUpdateService;
//use App\Services\FilmService;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class FilmController extends Controller
{
    public function __construct(
        private FilmService $filmService,
        protected FilmListService $filmListService,
        protected FilmCreateService $filmCreateService,
        protected FilmUpdateService $filmUpdateService,
        protected FilmRepository $filmRepository,
//        protected RequestFactoryInterface $httpFactory,
//        protected ClientInterface $httpClient
//        private \Psr\Http\Client\ClientInterface $httpClient
//        protected FilmsRepository $repository
    ) {
//        $this->repository = $repository;
    }

    /**
     * Получение списка фильмов.
     */
    public function index(FilmsListRequest $request)
    {
        $queryParams = FilmListQueryParams::fromRequest($request);
        $result = $this->filmService->getFilmsList($queryParams);

        return $this->success($result);
    }

    /**
     * Создание фильма (только для модераторов)
     */
    public function store(StoreFilmRequest $request)
    {
        try {
            $filmData = CreateFilmData::fromRequest($request);
            $result = $this->filmService->createFilm($filmData);

            return $this->success($result, 201);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), [], 422);
        } catch (\Exception $e) {
            return $this->error('Ошибка при создании фильма', [], 500);
        }
    }

    /**
     * Обновление фильма (только для модераторов)
     */
    public function update(UpdateFilmRequest $request, int $id)
    {
        try {
            $filmData = UpdateFilmData::fromRequest($request);
            $result = $this->filmService->updateFilm($id, $filmData);

            return $this->success($result);
        } catch (\InvalidArgumentException $e) {
            $statusCode = $e->getCode() ?: 422;
            return $this->error($e->getMessage(), [], $statusCode);
        } catch (\Exception $e) {
            return $this->error('Ошибка при обновлении фильма', [], 500);
        }
    }

    /**
     * Получение детальной информации о фильме.
     */
    public function show(int $id)
    {
        $filmDetails = $this->filmService->getFilmDetails($id);

        if (!$filmDetails) {
            return $this->notFound(); // или $this->notFound('Фильм не найден')
//            return $this->error('Фильм не найден', 404);
//            return $this->error('Запрашиваемая страница не существует', [], 404);
        }

        return $this->success($filmDetails);
    }

    /**
     * Получение списка похожих фильмов.
     *
     * @param Film $film
     * @return \App\Http\Responses\Success
     */
    public function similar0(int $id, FilmService $service)
    {
        $film = Film::findOrFail($id);
        $films = $service->getSimilarFor($film, Film::LIST_FIELDS);

        return $this->success(FilmListResource::collection($films));
//        return $this->success($service->getSimilarFor($film, Film::LIST_FIELDS));
    }

    /**
     * @OA\Get(
     *     path="/api/films/{id}/similar",
     *     summary="Get similar films",
     *     tags={"Films"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Film ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of similar films",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Film"))
     *         )
     *     ),
     *     @OA\Response(response=404, description="Film not found")
     * )
     */
    public function similar(int $id): JsonResponse
    {
        try {
            $similarFilms = $this->filmService->getSimilarFilms($id);

            return response()->json([
                'data' => $similarFilms
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->notFound();
//            return response()->json([
//                'message' => 'Film not found'
//            ], 404);
        }
    }

    /**
     * Показ промо
     *
     * @return Success
     */
    public function showPromo(): Success
    {
        $film = Film::where('promo', true)
            ->with(['genres'])//, 'actors', 'directors'])
            ->firstOrFail();

//        $this->setFavoriteFlag($film);

        return $this->success(new FilmResource($film));
    }

    /**
     * Создание промо
     *
     * @param $filmId
     *
     * @return Success
     * @throws Throwable
     */
    public function createPromo($filmId): Success
    {
        DB::transaction(
            function () use ($filmId) {
                $this->filmRepository->resetPromoFlags();
                $this->filmRepository->setPromoFlag($filmId);
            }
        );

        $film = $this->filmRepository->findOrFail($filmId);

        return $this->success(new FilmResource($film));
    }
}
