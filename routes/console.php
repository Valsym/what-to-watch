<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    \Illuminate\Support\Facades\Log::info('CONSOLE.PHP SCHEDULER WORKS! ' . now());
})->everyMinute();

Schedule::command('test:scheduler')
    ->everyMinute()
    ->name('test-scheduler-command');

// Добавим задачу для синхронизации комментариев
Schedule::job(new \App\Jobs\SyncExternalCommentsJob())
    ->dailyAt('03:00')
    ->timezone('Europe/Moscow')
    ->name('sync-external-comments');

// Для локального окружения - каждый час для тестирования
if (app()->environment('local')) {
    Schedule::job(new \App\Jobs\SyncExternalCommentsJob())
        ->hourly()
        ->withoutOverlapping()
        ->name('sync-external-comments-local');
}

//// Добавим задачу напрямую здесь
//Schedule::call(function () {
//    \Illuminate\Support\Facades\Log::info('CONSOLE.PHP SCHEDULER WORKS! ' . now());
//})->everyMinute();
//
//Schedule::command('test:scheduler')
//    ->everyMinute()
//    ->name('test-from-console');

//Schedule::call(function () {
////    DB::table('recent_users')->delete();
//    file_put_contents("files/check-all.txt", "\r\nconsole OK!!! ".date("Y-m-d H:i:s"),
//        FILE_APPEND | LOCK_EX);
//})->everyMinute();

//Schedule::job(new UpdateFilms)->everyMinute();//everyFiveMinutes();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
