<?php

use App\Models\Film;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Responses\ErrorResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\AuthenticationException;
use App\Jobs\UpdateFilm;
use App\Jobs\UpdateFilms;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Carbon;

//private $check_file = "files/check-all.txt";

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'is_moderator' => \App\Http\Middleware\CheckModerator::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ModelNotFoundException $e) {
            return new ErrorResponse(
                message: 'Запрашиваемая сущность не найдена',
                statusCode: Response::HTTP_NOT_FOUND
            );
        });

        $exceptions->render(function (NotFoundHttpException $e) {
            return new ErrorResponse(
                message: 'Запрашиваемая страница не существует',
                statusCode: Response::HTTP_NOT_FOUND
            );
        });
        $exceptions->render(function (AuthenticationException $e) {
            return response()->json(['message' => 'Запрос требует аутентификации'], 401);
        });
    })

    ->withSchedule(function (Schedule $schedule) {
        $schedule->call(UpdateFilms::class)->everyMinute();
    })

//    ->withSchedule(function (Schedule $schedule) {
////        $film = Film::where('id', 1);//->first();
//        $schedule->call(UpdateFilms::class)->everyMinute()
//            ->onSuccess(function () {
//                // Задача успешно выполнена...
//                file_put_contents("files/check-all.txt", "\r\nOK!!! ".date("Y-m-d H:i:s"),
//                    FILE_APPEND | LOCK_EX);
//            })
//            ->onFailure(function () {
//                // Не удалось выполнить задачу...
//                file_put_contents("files/check-all.txt", "\r\nError!!! ".date("Y-m-d H:i:s"),
//                    FILE_APPEND | LOCK_EX);
//            });
////            ->appendOutputTo("files/check-all.txt");
//    })

    ->create();
