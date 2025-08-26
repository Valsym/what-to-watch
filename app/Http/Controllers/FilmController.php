<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Http\Requests\Films\FilmsListRequest;
use App\Services\Films\FilmListService;
use App\Services\Films\FilmCreateService;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Resources\FilmResource;
use App\Http\Responses\Success;
use App\Http\Requests\Films\StoreFilmRequest;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class FilmController extends Controller
{
    public function __construct(
//        protected FavoriteFilmCheckService $favoriteFilmCheckService,
        protected FilmListService $filmListService,
//        protected FilmDetailsService $filmDetailsService,
        protected FilmCreateService $filmCreateService,
//        protected FilmUpdateService $filmUpdateService,
//        protected SimilarFilmService $similarFilmService,
//        protected PromoFilmService $promoFilmService,
    ) {
    }

    /**
     * Список фильмов
     *
     * @param FilmsListRequest $request
     *
     * @return Success
     */
    public function index1(FilmsListRequest $request): Success
    {
        $filters = $request->validated();
        $userId = (int) auth()->id();

        $perPage = $filters['per_page'] ?? 8;

        $films = $this->filmListService->getFilmList($filters, $userId, $perPage);

//        return $films;
//        return $this->success(FilmListResource::collection($films));
        return $this->success($this->toArray($films));
    }

    /**
     * Получение списка фильмов.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)//(FilmsListRequest $request)
    {
        $perPage = $request['per_page'] ?? 8;

        $films =
//             Film::select(Film::LIST_FIELDS)
            Film::query()
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
            ->ordered($request->get('order_by'), $request->get('order_to'))
            ->paginate($perPage);

        return $films;
    }

    /**
     * Добавление фильма в базу.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Responsable
     */
    public function store0(Request $request)
    {
        return $this->success([], 201);
    }

    /**
     * Добавление фильма в бд
     *
     * @param StoreFilmRequest $request
     *
     * @return SuccessResponse
     * @throws Throwable
     */
    public function store(StoreFilmRequest $request): Success
    {
        $film = $this->filmCreateService->createFilm($request->validated());

        return $this->success(new FilmResource($film), Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
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
     * @param $id
     * @return void
     */
    public function similar($id)
    {
        return $this->success([]);
    }
}
