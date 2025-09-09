<?php

namespace App\Jobs;

use App\Models\Film;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class UpdateFilms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public $failOnTimeout = true;
    private string $check_file = "files/check-all.txt";

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//        $film = Film::where('status', Film::STATUS_PENDING)->first();
//        $film = Film::where('id', 1)->first();
//        UpdateFilm::dispatch($film);

//        Film::where('status', Film::STATUS_PENDING)->chunk(1000, function ($films) {
//            /** @var Film $film */
//            foreach ($films as $num => $film) {
//                UpdateFilm::dispatch($film);
//                if($num>3) return;
//            }
//        });

//        $films = Film::where('status', Film::STATUS_PENDING);
        $film = Film::where('id', 1)->first();
        UpdateFilm::dispatchSync($film);

//        foreach ($films as $film) {
////            file_put_contents($this->check_file, "\r\nnum=$num data: ".data());
//            UpdateFilm::dispatch($film);
////            if($num>3) return;
//            break;
//        }
    }

    /**
     * Обработать провал задания.
     */
    public function failed(?Throwable $exception): void
    {
        // Отправляем пользователю уведомление об ошибке и т.д.
        echo '!!! '.$exception;
    }
}
