<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Http\Requests\Films\FilmsListRequest;
use App\Models\Genre;
use App\Services\Films\FilmListService;
use App\Services\Films\FilmCreateService;
use App\Services\Films\FilmService;
use App\Services\Films\FilmUpdateService;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use App\Http\Requests\Films\UpdateFilmRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Resources\FilmResource;
use App\Http\Responses\Success;
use App\Repositories\Films\FilmRepository;
use App\Http\Requests\Films\StoreFilmRequest;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Illuminate\Support\Facades\DB;

class FilmController extends Controller
{
    public function __construct(
        protected FilmListService $filmListService,
        protected FilmCreateService $filmCreateService,
        protected FilmUpdateService $filmUpdateService,
        protected FilmRepository $filmRepository
    ) {
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
     * Добавление фильма в бд
     *
     * @param StoreFilmRequest $request
     *
     * @return Success
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
     * Получение информации о фильме
     *
     * @param  \App\Models\Film  $film
     * @return Responsable
     */
    public function show(int $id): Success
    {
        $film = Film::findOrFail($id);

        return $this->success($film->append('rating')->loadCount('scores'));
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
        if (Gate::allows('film-update')) {
            $film = $this->filmUpdateService->updateFilm($id, $request->validated());

            return $this->success(new FilmResource($film), Response::HTTP_OK);
        }

        abort(403, 'Обновить фильм может только Модератор');
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
        $film = Film::findOrFail($id);

        return $this->success($service->getSimilarFor($film, Film::LIST_FIELDS));
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
