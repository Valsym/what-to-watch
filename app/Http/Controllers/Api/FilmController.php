<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Films\StoreFilmRequest;
use App\Http\Requests\Films\UpdateFilmRequest;
use App\Http\Resources\FilmListResource;
use App\Http\Resources\FilmResource;
use App\Http\Responses\Success;
use App\Models\Film;
use App\Repositories\Films\FilmRepository;
use App\Services\Films\FilmCreateService;
use App\Services\Films\FilmListService;
use App\Services\Films\FilmService;
use App\Services\Films\FilmUpdateService;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class FilmController extends Controller
{
    public function __construct(
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
     *
     * @return Success
     */
    public function index(Request $request)//(FilmsListRequest $request)
    {
        $perPage = $request['per_page'] ?? 8;

        $films = Film::query()
            ->when($request->has('genre'), function ($query) use ($request) {
                $query->whereRelation('genres', 'name', $request->get('genre'));
            })
            ->when($request->has('status') && $request->user()?->isModerator(),
                function ($query) use ($request) {
                    $query->whereStatus($request->get('status'));
                },
                function ($query) {
                    $query->whereStatus(Film::STATUS_READY);
                }
            )
            ->ordered($request->get('order_by'), $request->get('order_to'));
//            ->paginate($perPage);

        return $this->success(FilmListResource::collection($films->paginate($perPage)));
    }

    /**
     * Добавление фильма в бд
     *
     * @param StoreFilmRequest $request
     *
     * @return Success
     * @throws Throwable
     */
    public function store(StoreFilmRequest $request): Success
    {
        $film = $this->filmCreateService->createFilm($request->validated());

        return $this->success(new FilmResource($film), Response::HTTP_CREATED);
    }

     /**
     * Получение информации о фильме
     *
     * @param  \App\Models\Film  $film
     * @return Success
     */
    public function show(int $id): Success
    {
        $film = Film::findOrFail($id);

//        $data = $this->repository->getFilm('tt0031381');
//        UpdateFilms::dispatchSync();//$film);

        return $this->success(new FilmResource($film));
//        return $this->success($film->append('rating')->loadCount('scores'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateFilmRequest $request, int $id): Success
    {
        $film = $this->filmUpdateService->updateFilm($id, $request->validated());

        return $this->success(new FilmResource($film), Response::HTTP_OK);
    }

    /**
     * Получение списка похожих фильмов.
     *
     * @param Film $film
     * @return \App\Http\Responses\Success
     */
    public function similar(int $id, FilmService $service)
    {
        $film = Film::findOrFail($id);
        $films = $service->getSimilarFor($film, Film::LIST_FIELDS);

        return $this->success(FilmListResource::collection($films));
//        return $this->success($service->getSimilarFor($film, Film::LIST_FIELDS));
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
