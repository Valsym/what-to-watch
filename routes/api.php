<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\FilmController;
use App\Http\Controllers\Api\GenreController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;


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
// Аутентификация
Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

// Защищенные маршруты
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/user', [UserController::class, 'show'])->name('user.show');
    Route::patch('/user', [UserController::class, 'update'])->name('user.update');
//    Route::get('/user', [UserController::class, 'index'])->name('user.index');
});

//Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
//Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
//Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
//
//Route::prefix('/user')->middleware('auth:sanctum')->group(function () {
//    Route::get('/', [UserController::class, 'index'])->name('user.index');
//    Route::get('/{user}', [UserController::class, 'show'])->name('user.show');
//    Route::/*middleware('is_moderator')->*/patch('/', [UserController::class, 'update'])->name('user.update');
//});

Route::get('/films/{film}/similar', [FilmController::class, 'similar'])->name('films.similar');

Route::get('/films', [FilmController::class, 'index'])->name('films.index');
Route::post('/films/', [FilmController::class, 'store'])
    ->middleware('auth:sanctum', 'is_moderator')->name('film.store');
Route::get('/films/{film}', [FilmController::class, 'show'])->name('film.show');
Route::patch('/films/{film}', [FilmController::class, 'update'])
    ->middleware('auth:sanctum', 'is_moderator')->name('film.update');

// Жанры
Route::get('/genres', [GenreController::class, 'index'])->name('genre.index');
Route::patch('/genres/{genre}', [GenreController::class, 'update'])
    ->middleware('auth:sanctum')
    ->name('genre.update');

Route::middleware('auth:sanctum')->get('/favorite', [FavoriteController::class, 'index'])->name('favorite.index');
Route::middleware('auth:sanctum')->post('/films/{film}/favorite', [FavoriteController::class, 'store'])->name('favorite.store');
Route::middleware('auth:sanctum')->delete('/films/{film}/favorite', [FavoriteController::class, 'destroy'])->name('favorite.destroy');
Route::get ('/favorite/{film}/status', [FavoriteController::class, 'status'])->name('favorite.status');

// Комментарии
Route::get('films/{film}/comments', [CommentController::class, 'index'])->name('comments.index');
Route::middleware('auth:sanctum')->group(function () {
    Route::post('films/{film}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::patch('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
});

//Route::get('films/{film}/comments', [CommentController::class, 'index'])->name('comments.index');
//Route::middleware('auth:sanctum')->group(function () {
//    Route::post('films/{film}/comments', [CommentController::class, 'store'])->name('comments.store');
//    Route::patch('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
//});
//Route::middleware('auth:sanctum')->delete('/comments/{comment}', [CommentController::class, 'destroy'])
//    ->name('comments.destroy');

Route::prefix('/promo')->group(function () {
    Route::get('/', [FilmController::class, 'showPromo'])->name('promo.show');
    Route::post('/promo/{id}', [FilmController::class, 'createPromo'])->
        middleware('auth:sanctum', 'is_moderator')->
        name('promo.create');
});
