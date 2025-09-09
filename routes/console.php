<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\UpdateFilms;

//Schedule::call(function () {
////    DB::table('recent_users')->delete();
//    file_put_contents("files/check-all.txt", "\r\nconsole OK!!! ".date("Y-m-d H:i:s"),
//        FILE_APPEND | LOCK_EX);
//})->everyMinute();

//Schedule::job(new UpdateFilms)->everyMinute();//everyFiveMinutes();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
