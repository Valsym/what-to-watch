<?php

namespace Database\Factories;

use App\Models\Film;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Film>
 */
class FilmFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'released' => $this->faker->year(),
            'description' => $this->faker->paragraph(3),
            'run_time' => $this->faker->numberBetween(80, 180),
//            'rating' => $this->faker->randomFloat(1, 4, 10),
//            'imdb_votes' => $this->faker->numberBetween(1000, 10000),
            'imdb_id' => $this->faker->unique()->regexify('tt[0-9]{7}'),
            'poster_image' => $this->faker->imageUrl(),
            'preview_image' => $this->faker->imageUrl(),
            'background_color' => $this->faker->hexColor(),
            'background_image' => $this->faker->imageUrl(),
            'video_link' => $this->faker->url(),
            'preview_video_link' => $this->faker->url(),
            'status' => Film::STATUS_READY,
        ];
    }
}
