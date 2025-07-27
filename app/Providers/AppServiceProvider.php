<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
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
        Gate::define('comment-delete', function (User $user, Comment $comment) {
            if ($user->isModerator()) {
                return true;
            }
            return $user->id === $comment->user_id && $comment->comments->isEmpty();
        });
    }
}
