<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
//use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

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
        //
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
    }
}
