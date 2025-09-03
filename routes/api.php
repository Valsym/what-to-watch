<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PromoController;
use App\Http\Middleware\CheckModerator;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::post('/login', function() {
//    $t = 4;
//    $t++;
//
//    return view('welcome', [AuthController::class, 'login']);
//});

Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

Route::middleware('auth:sanctum')->get('/user/{user}', [UserController::class, 'show'])->name('user.show');
Route::middleware('auth:sanctum')->patch('/user', [UserController::class, 'update'])->name('user.update');

Route::get('/films/{film}/similar', [FilmController::class, 'similar'])->name('films.similar');

Route::get('/films', [FilmController::class, 'index'])->name('films.index');
Route::middleware('auth:sanctum')->post('/films/{film}', [FilmController::class, 'store'])->name('film.store');
Route::get('/films/{film}', [FilmController::class, 'show'])->name('film.show');
Route::middleware('auth:sanctum')->patch('/films/{film}', [FilmController::class, 'update'])->name('film.update');

Route::get('/genres', [GenreController::class, 'index'])->name('genre.index');
Route::patch('/genres/{genre}', [GenreController::class, 'update'])->
    middleware('auth:sanctum', 'is_moderator')->//CheckModerator::class)->
    name('genre.update');

Route::get('/favorite', [FavoriteController::class, 'index'])->name('favorite.index');
Route::middleware('auth:sanctum')->post('/films/{film}/favorite', [FavoriteController::class, 'store'])->name('favorite.store');
Route::middleware('auth:sanctum')->delete('/films/{film}/favorite', [FavoriteController::class, 'destroy'])->name('favorite.destroy');
Route::get ('/favorite/{film}/status', [FavoriteController::class, 'status'])->name('favorite.status');

Route::get('films/{film}/comments', [CommentController::class, 'index'])->name('comments.index');
Route::middleware('auth:sanctum')->group(function () {
    Route::post('films/{film}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::patch('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
});
Route::middleware('auth:sanctum')->delete('/comments/{comment}', [CommentController::class, 'destroy'])
    ->name('comments.destroy');//->middleware('can:comment-delete');
;
//Route::middleware(['auth:sanctum', 'moderator'])->delete('/comments/{comment}', [CommentController::class, 'destroy'])
//    ->name('comments.destroy');
// Комментарии
//Route::prefix('/comments')->group(function () {
//    Route::get('/{id}', [CommentController::class, 'index'])->name('comments.index');
//    Route::middleware('auth:sanctum')->group(function () {
//        Route::post('/{id}', [CommentController::class, 'store'])->name('comments.store');
//        Route::patch('/{comment}', [CommentController::class, 'update'])->middleware(
//            'can:update-comment,comment'
//        )->name('comments.update');
//        Route::delete('/{id}', [CommentController::class, 'destroy'])->middleware(
//            'can:delete-comment,comment'
//        )->name('comments.destroy');
//    });
//});

//Route::post('/promo', [PromoController::class, 'store'])->name('promo.store');
//Route::get('/promo', [FilmController::class, 'showPromo'])->name('promo.show');
Route::prefix('/promo')->group(function () {
    Route::get('/', [FilmController::class, 'showPromo'])->name('promo.show');
    Route::post('/promo/{id}', [FilmController::class, 'createPromo'])->
        middleware('auth:sanctum', 'is_moderator')->
        name('promo.create');
});
