<?php

namespace App\Providers;

//use App\Interfaces\FilmsOmdbRepositoryInterface;
use App\Models\Film;
use App\Models\Comment;
use App\Models\Genre;
use App\Models\User;
//use App\Repositories\Films\FilmsOmdbRepository;
use App\Repositories\Favorites\FavoriteRepository;
use App\Repositories\Films\FilmRepository;
use App\Services\Films\FilmService;
use App\Services\Films\OmdbService;
//use App\Support\Import\FilmsRepository;
//use App\Support\Import\TvmazeRepository;
use Illuminate\Support\Facades\Gate;
//use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;
use App\Repositories\Auth\UserRepository;
use App\Services\Auth\AuthService;
use App\Repositories\Genres\GenreRepository;
use App\Services\Genres\GenreService;
use App\Repositories\Comments\CommentRepository;
use App\Services\Comments\CommentService;
use App\Contracts\ExternalFilmRepositoryInterface;
use App\Services\External\OmdbFilmRepository;
use App\Contracts\ExternalCommentRepositoryInterface;
use App\Services\External\ExternalCommentRepository;
use App\Services\Comments\ExternalCommentService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
//        $this->app->bind(FilmsRepository::class, TvmazeRepository::class);
//
//        $this->app->bind(
//            ClientInterface::class,
//            function () {
//                return new GuzzleAdapter(new Client());
//            }
//        );

        // Auth
        $this->app->bind(UserRepository::class, function ($app) {
            return new UserRepository(new \App\Models\User());
        });

        $this->app->bind(AuthService::class, function ($app) {
            return new AuthService($app->make(UserRepository::class));
        });

        $this->app->bind(FilmRepository::class, function ($app) {
            return new FilmRepository(
                new Film(),
                new Genre() // Добавляем второй обязательный аргумент
            );
        });

        $this->app->bind(FilmService::class, function ($app) {
            return new FilmService(
                $app->make(FilmRepository::class),
                $app->make(OmdbService::class), // Добавляем второй обязательный аргумент
                $app->make(FavoriteRepository::class)
            );
        });

        $this->app->bind(OmdbService::class, function ($app) {
            return new OmdbService(/* необходимые зависимости */);
        });

        $this->app->bind(FavoriteRepository::class, function ($app) {
            return new FavoriteRepository();
        });

        // Genres
        $this->app->bind(GenreRepository::class, function ($app) {
            return new GenreRepository(new \App\Models\Genre());
        });

        $this->app->bind(GenreService::class, function ($app) {
            return new GenreService($app->make(GenreRepository::class));
        });

        // Comments
        $this->app->bind(CommentRepository::class, function ($app) {
            return new CommentRepository(new \App\Models\Comment());
        });

        $this->app->bind(CommentService::class, function ($app) {
            return new CommentService($app->make(CommentRepository::class));
        });

        // Внешние источники данных о фильмах
        $this->app->bind(ExternalFilmRepositoryInterface::class,
            OmdbFilmRepository::class);

        // FilmService с обновленными зависимостями
        $this->app->bind(FilmService::class, function ($app) {
            return new FilmService(
                $app->make(FilmRepository::class),
                $app->make(ExternalFilmRepositoryInterface::class), // Используем интерфейс
                $app->make(FavoriteRepository::class)
            );
        });

        // Внешние комментарии
        $this->app->bind(ExternalCommentRepositoryInterface::class,
            ExternalCommentRepository::class);

        $this->app->bind(ExternalCommentService::class, function ($app) {
            return new ExternalCommentService(
                $app->make(ExternalCommentRepositoryInterface::class),
                $app->make(CommentRepository::class)
            );
        });

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Roles based authorization ???
        Gate::before(
            function (User $user, $ability) {
                if ($user->role === User::ROLE_MODERATOR) {
                    return true;
                }
            }
        );

//        Gate::define('comment-delete', function (User $user, Comment $comment) {
//            if ($user->isModerator()) {
//                return true;
//            }
//            return $user->id === $comment->user_id && $comment->comments->isEmpty();
//        });

        Gate::define('film-store', function (User $user) {
            return (int)$user->role === User::ROLE_MODERATOR;
        });

        Gate::define('film-update', function (User $user) {
            return (int)$user->role === User::ROLE_MODERATOR;
        });

    }
}
