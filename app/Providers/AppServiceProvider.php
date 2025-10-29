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
use App\Support\Import\FilmsRepository;
use App\Support\Import\TvmazeRepository;
use Illuminate\Support\Facades\Gate;
//use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;

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

//        Queue::failing(function (JobFailed $event) {
//             $event->connectionName;
//             $event->job;
//             $event->exception;
//        });

//        Event::listen(function (QueueBusy $event) {
//            Notification::route('mail', 'dev@example.com')
//                ->notify(new QueueHasLongWaitTime(
//                    $event->connection,
//                    $event->queue,
//                    $event->size
//                ));
//        });
    }
}
