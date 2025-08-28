<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Http\Requests\Films\FilmsListRequest;
use App\Services\Films\FilmListService;
use App\Services\Films\FilmCreateService;
use App\Services\Films\FilmService;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Resources\FilmResource;
use App\Http\Responses\Success;
use App\Http\Requests\Films\StoreFilmRequest;
use Illuminate\Support\Facades\Gate;
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
        if (Gate::allows('film-store')) {
            $film = $this->filmCreateService->createFilm($request->validated());

            return $this->success(new FilmResource($film), Response::HTTP_CREATED);
        }

        abort(403, 'Фильм может добавить в БД только Модератор');
    }

        /**
     * Получение информации о фильме.
     *
     * @param  \App\Models\Film  $film
     * @return Responsable
     */
    public function show(int $id): Success
    {
//        $query = Film::with(
//            [
//                'genres',
////                'actors',
////                'directors',
////                'favorites' => fn ($q) => $userId ? $q->where('user_id', $userId) : $q
//            ]
//        );
//
////        return $query->findOrFail($id);
//
//        $film = $query->find($id, ['*']);

        $film = $this->findOrFail($id);

        if (is_null($film)) {
            abort(404, 'Запрашиваемая страница не существует');
        }

        return $this->success($film->append('rating')->loadCount('scores'));

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
     * Получение списка похожих фильмов.
     *
     * @param Film $film
     * @return \App\Http\Responses\Success
     */
    public function similar(int $id, FilmService $service)
    {
//        $service = new FilmService();
        $film = $this->findOrFail($id);

        return $this->success($service->getSimilarFor($film, Film::LIST_FIELDS));
    }

    /**
     * Проверка существования фильма с заданным $id
     * @param int $id
     * @return Film|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function findOrFail(int $id)
    {
        $query = Film::with(
            [
                'genres',
//                'actors',
//                'directors',
//                'favorites' => fn ($q) => $userId ? $q->where('user_id', $userId) : $q
            ]
        );

//        return $query->findOrFail($id);

        $film = $query->find($id, ['*']);

        if (is_null($film)) {
            abort(404, 'Запрашиваемая страница не существует');
        }

        return $film;
    }
}
