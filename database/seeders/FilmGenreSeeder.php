<?php

namespace Database\Seeders;

use App\Models\Film;
use App\Models\Genre;
use Illuminate\Database\Seeder;

final class FilmGenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     *  Запустить сид: sail artisan db:seed --class=FilmGenreSeeder
     */
    public function run(): void
    {
        $genres = Genre::factory()->count(5)->create();

        $films = Film::factory()->count(10)->create();

        $genreIds = $genres->pluck('id')->toArray();
        $genreCount = count($genreIds);

        foreach ($films as $film) {
            $count = min(2, $genreCount);

            $selectedGenreIds = [];
            $availableGenreIds = $genreIds;

            for ($i = 0; $i < $count; $i++) {
                $randomIndex = array_rand($availableGenreIds);
                $selectedGenreIds[] = $availableGenreIds[$randomIndex];
                unset($availableGenreIds[$randomIndex]);
            }

            $film->genres()->syncWithoutDetaching($selectedGenreIds);
        }
    }
}
